import { chromium, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { mkdtemp, mkdir, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { resolve, join } from 'node:path';
import { randomBytes } from 'node:crypto';
import { spawn, execFileSync } from 'node:child_process';
import { createServer } from 'node:net';

const root = resolve(import.meta.dirname, '..');
const workspace = await mkdtemp(join(process.env.ARIS_QA_TMP || tmpdir(), 'aris-browser-'));
for (const dir of ['storage/app/private', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs']) {
    await mkdir(join(workspace, dir), { recursive: true });
}
await writeFile(join(workspace, '.synthetic'), 'Solo pruebas locales');
await writeFile(join(workspace, 'database.sqlite'), '');
const password = randomBytes(18).toString('hex');
const env = { ...process.env, APP_ENV: 'testing', APP_DEBUG: 'false', APP_KEY: `base64:${randomBytes(32).toString('base64')}`,
    APP_CONFIG_CACHE: join(workspace, 'config.php'), DB_CONNECTION: 'sqlite', DB_DATABASE: join(workspace, 'database.sqlite'), DB_URL: '',
    LARAVEL_STORAGE_PATH: join(workspace, 'storage'), CACHE_STORE: 'array', SESSION_DRIVER: 'file', SESSION_SECURE_COOKIE: 'false',
    MAIL_MAILER: 'array', QUEUE_CONNECTION: 'sync', ARIS_BROWSER_PASSWORD: password };
const php = process.env.ARIS_PHP || 'php8.4';
const fixture = JSON.parse(execFileSync(php, ['tests/Browser/seed.php'], { cwd: root, env, encoding: 'utf8' }));
const probe = createServer();
await new Promise((done) => probe.listen(0, '127.0.0.1', done));
const port = probe.address().port;
await new Promise((done) => probe.close(done));
const base = `http://127.0.0.1:${port}`;
const server = spawn(php, ['-S', `127.0.0.1:${port}`, join(root, 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php')], {
    cwd: join(root, 'public'), env: { ...env, APP_URL: base }, stdio: ['ignore', 'pipe', 'pipe'],
});
let serverLog = '';
server.stdout.on('data', (bytes) => serverLog += bytes);
server.stderr.on('data', (bytes) => serverLog += bytes);
let browser;
const report = [];
try {
    await new Promise((done, reject) => {
        server.once('error', reject);
        server.stderr.on('data', (bytes) => { if (bytes.toString().includes('started')) done(); });
        server.once('exit', (code) => reject(new Error(`Servidor finalizó: ${code}`)));
    });
    browser = await chromium.launch({ executablePath: process.env.ARIS_CHROMIUM || undefined });
    const context = await browser.newContext();
    const page = await context.newPage();
    const login = async (role) => {
        await context.clearCookies();
        await page.goto(`${base}/ingresar`);
        await page.locator('[name=email]').fill(`${role}@example.invalid`);
        await page.locator('[name=password]').fill(password);
        await page.locator('button[type=submit]').click();
        await page.waitForURL('**/cuenta');
    };
    const audit = async (name) => {
        const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
        report.push({ name, violations: result.violations });
        await page.screenshot({ path: join(workspace, `${name}.png`), fullPage: true });
        expect(result.violations, `${name}: accesibilidad`).toEqual([]);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${name}: desbordamiento`).toBe(true);
    };
    for (const role of ['aprendiz', 'instructor', 'evaluador', 'administrador']) {
        await login(role);
        for (const width of [390, 768, 1440]) {
            await page.setViewportSize({ width, height: 900 });
            await page.goto(`${base}/entregas/versiones/${fixture.version}`);
            await audit(`${role}-${width}`);
        }
    }
    await login('aprendiz');
    await page.goto(`${base}/entregas/versiones/${fixture.version}`);
    await page.keyboard.press('Tab');
    await expect(page.getByText('Saltar al contenido principal')).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(page.locator('main')).toBeFocused();
    const transfer = await page.evaluateHandle(() => {
        const data = new DataTransfer();
        data.items.add(new File(['%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF'], 'prueba.pdf', { type: 'application/pdf' }));
        return data;
    });
    await page.locator('[data-dropzone]').dispatchEvent('drop', { dataTransfer: transfer });
    await expect(page.locator('[data-file-status]')).toContainText('prueba.pdf');
    await page.getByRole('button', { name: 'Guardar evidencia privada' }).click();
    await expect(page.getByRole('link', { name: 'prueba.pdf' })).toBeVisible();
    await page.getByRole('button', { name: 'Enviar versión', exact: true }).click();
    await expect(page.locator('input[type=file]')).toHaveCount(0);
    await expect(page.getByText('Enviada · contenido congelado', { exact: false })).toBeVisible();
    await audit('aprendiz-congelado');
    await login('instructor');
    await page.goto(`${base}/entregas/versiones/${fixture.version}`);
    await page.getByRole('button', { name: 'Iniciar revisión' }).click();
    await page.locator('[name=resultado]').selectOption('cumple');
    await page.locator('[name=retroalimentacion]').fill('Evidencia sintética suficiente para QA.');
    await page.getByRole('button', { name: 'Registrar evaluación' }).click();
    await page.getByRole('button', { name: 'Reabrir entrega', exact: true }).click();
    await expect(page.locator('dialog')).toBeVisible();
    await expect(page.locator('[name=motivo]')).toBeFocused();
    await expect(page.locator('[name=motivo]')).toHaveAttribute('required', '');
    await audit('reapertura-dialogo');
    await page.keyboard.press('Escape');
    await expect(page.getByRole('button', { name: 'Reabrir entrega', exact: true })).toBeFocused();
    await page.getByRole('button', { name: 'Reabrir entrega', exact: true }).click();
    await page.locator('[name=motivo]').fill('Comprobar el nuevo intento desde el navegador.');
    await page.getByRole('button', { name: 'Reabrir para nuevo intento' }).click();
    await expect(page.getByText('Reapertura auditada.', { exact: false })).toBeVisible();
    console.log(`QA de navegador aprobada; artefactos: ${workspace}`);
} finally {
    await browser?.close();
    server.kill('SIGTERM');
    await writeFile(join(workspace, 'resultados.json'), JSON.stringify(report, null, 2));
    await writeFile(join(workspace, 'servidor.log'), serverLog);
    console.log(`Evidencia QA: ${workspace}`);
}
