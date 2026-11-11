#!/usr/bin/env node
/**
 * Verificador de enlaces relativos de la documentación de ARIS 2.0.
 *
 * Uso:
 *   node scripts/verificar-enlaces-docs.mjs
 *
 * Comprueba que cada enlace relativo de tipo Markdown apunte a un archivo existente dentro
 * del repositorio. Ignora enlaces absolutos (http, https, mailto), anclas puras y las rutas
 * que no son de documentación.
 *
 * También resuelve las rutas a archivos `.md` escritas entre comillas invertidas, primero
 * desde la raíz del repositorio y luego desde el directorio del archivo origen.
 *
 * No valida el contenido de los archivos enlazados ni renderiza Mermaid: solo resuelve rutas.
 * Salida: lista de enlaces rotos y código de salida 1 si hay al menos uno.
 */

import { readdir, readFile, stat } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const RAIZ = process.cwd();
const OBJETIVO = ['README.md', 'AGENTS.md', 'AVISO-DE-DERECHOS.md', 'docs'];
const EXTENSIONES = new Set(['.md', '.markdown']);
const PATRON_ENLACE = /\[[^\]]*\]\(([^)\s]+)(?:\s+"[^"]*")?\)/g;
const PATRON_RUTA_MD = /`([A-Za-z0-9_./-]+\.md)`/g;

/** Recorre un directorio y devuelve los archivos Markdown encontrados. */
async function listarMarkdown(ruta) {
  const absoluta = path.resolve(RAIZ, ruta);
  if (!existsSync(absoluta)) return [];

  const info = await stat(absoluta);
  if (info.isFile()) return EXTENSIONES.has(path.extname(absoluta)) ? [absoluta] : [];

  const entradas = await readdir(absoluta, { withFileTypes: true });
  const archivos = [];
  for (const entrada of entradas) {
    if (entrada.name === '.git' || entrada.name === 'node_modules' || entrada.name === 'vendor') {
      continue;
    }
    const hijo = path.join(ruta, entrada.name);
    if (entrada.isDirectory()) {
      archivos.push(...(await listarMarkdown(hijo)));
    } else if (EXTENSIONES.has(path.extname(entrada.name))) {
      archivos.push(path.resolve(RAIZ, hijo));
    }
  }
  return archivos;
}

/** Convierte un destino de enlace en una ruta de archivo, o null si no aplica. */
function resolverDestino(destino, archivoOrigen) {
  if (/^(https?:|mailto:|tel:|data:)/i.test(destino)) return null;
  if (destino.startsWith('#')) return null;

  const sinAncla = destino.split('#')[0].split('?')[0];
  if (!sinAncla) return null;

  const decodificado = decodeURI(sinAncla);
  return path.resolve(path.dirname(archivoOrigen), decodificado);
}

async function main() {
  const archivos = [];
  for (const objetivo of OBJETIVO) {
    archivos.push(...(await listarMarkdown(objetivo)));
  }
  archivos.sort();

  const rotos = [];
  let revisados = 0;

  for (const archivo of archivos) {
    const contenido = await readFile(archivo, 'utf8');
    for (const coincidencia of contenido.matchAll(PATRON_ENLACE)) {
      const destino = coincidencia[1];
      const resuelto = resolverDestino(destino, archivo);
      if (!resuelto) continue;

      revisados += 1;
      if (!existsSync(resuelto)) {
        rotos.push({
          archivo: path.relative(RAIZ, archivo),
          destino,
          esperado: path.relative(RAIZ, resuelto),
        });
      }
    }

    // Rutas .md citadas entre comillas invertidas.
    for (const coincidencia of contenido.matchAll(PATRON_RUTA_MD)) {
      const destino = coincidencia[1];
      const desdeRaiz = path.resolve(RAIZ, destino);
      const desdeOrigen = path.resolve(path.dirname(archivo), destino);
      revisados += 1;

      if (!existsSync(desdeRaiz) && !existsSync(desdeOrigen)) {
        rotos.push({
          archivo: path.relative(RAIZ, archivo),
          destino: `\`${destino}\``,
          esperado: path.relative(RAIZ, desdeRaiz),
        });
      }
    }
  }

  console.log(`Archivos Markdown revisados: ${archivos.length}`);
  console.log(`Enlaces relativos comprobados: ${revisados}`);

  if (rotos.length === 0) {
    console.log('Resultado: OK — no se encontraron enlaces rotos.');
    return 0;
  }

  console.log(`Resultado: ${rotos.length} enlace(s) roto(s):`);
  for (const roto of rotos) {
    console.log(`  - ${roto.archivo}: ${roto.destino} (esperado: ${roto.esperado})`);
  }
  return 1;
}

process.exitCode = await main();
