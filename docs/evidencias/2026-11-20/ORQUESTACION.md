# Registro de Orquestación Verificable — NATIVE MULTI-AGENT MODE

**Fecha:** 2026-11-20 · **Orquestador:** OpenCode v2.3.2

## Inventario de agentes instalados (descubiertos exhaustivamente)

| Herramienta | Versión | Ubicación | Autenticado | Estado |
|---|---|---|---|---|
| OpenCode | v2.3.2 | ~/.opencode/bin/opencode | Sí | **Invocado** (orquestador) |
| OpenCode2 | v2.3.2 | ~/.opencode/bin/opencode2 | Sí | Instalado, no invocado |
| Codex CLI | 0.171.0 | ~/.local/bin/codex | Sí | **Invocado** (REV-SEC/REV-PERF) |
| Kilo Code CLI | 7.8.18 | ~/.kilo/bin/kilo | Sí (kilo-auto/free) | **Invocado** (REV-PERF, REV-UI, REV-CRUZADA) |
| Claude Code | 2.1.197 | ~/.local/bin/claude | Sí | Invocado (REV-UI) — timeout |
| Gemini CLI | 0.68.0 | ~/.local/bin/gemini | Sí | Invocado (REV-UI) — HTTP 503 persistente |
| Hermes Agent | v0.22.5 | ~/.local/bin/hermes | Sí | Invocado en sesión anterior |
| Orca IDE / Orchestration | runtime listo | /usr/bin/orca + ~/.config/orca | Sí | **Invocado** (Run/tareas/workers) |
| Cursor CLI | 3.29.9 | ~/.local/bin/cursor | Sí | Instalado, no invocado |
| Kiro CLI | 2.36.0 | ~/.local/bin/kiro-cli | Sí | Instalado, no invocado |
| GitHub Copilot CLI | 1.0.109 | ~/.local/bin/copilot | Sí | Instalado, no invocado |
| Antigravity | v2.28.2 | /opt/Antigravity-x64 | N/A | App GUI, no CLI operativo |
| MCP AgentRing | relay operativo | tool MCP | Sí | Disponible; sin llamadas de voz (no autorizadas) |

## Invocaciones reales de esta sesión

| Agente | Método | Tarea | Estado | Resultado |
|---|---|---|---|---|
| Orca | `orchestration run-create` | Run post-B3 | COMPLETADO | run_8d5df44d5914 |
| Orca | `worker-start` x2 (+1 reintento) | REV-SEC / REV-PERF | **FALLIDO** | timeout `agent_readiness`; reasignado |
| Codex | `codex exec` | REV-SEC / REV-PERF | COMPLETADO | 4 + 3 hallazgos |
| Claude | `claude -p` | REV-UI | **FALLIDO** | exit 124 timeout |
| Gemini | `gemini -p` | REV-UI | **FALLIDO** | HTTP 503 alta demanda |
| Kilo | `kilo run` | REV-PERF | COMPLETADO | 3 OPT confirmados |
| Kilo | `kilo run` | REV-UI | COMPLETADO | 17 problemas + propuestas |
| Kilo | `kilo run` | REV-CRUZADA | EN CURSO | — |

## Misiones completadas por OpenCode (integración)
- Misión A: entregables persistidos en `docs/evidencias/2026-11-20/` + MANIFEST-SHA256 (32 archivos).
- Misión C: latestVersion + withCount + paginación lista; 169/169, 893 aserciones.
- Misión D: npm ci, vite build, Playwright 14 axe 0 violaciones, 14 capturas.
- Misión E: SQLite 169/169 y MySQL 8.4.13 169/169, 893 aserciones.
- Misión B: iconos SVG nav, aria-label, h2, scope/caption, contraste/enlaces, btn-fantasma; axe 0 violaciones tras cambios.

## Notas
- No se simuló ningún agente. Los fallos (Orca, Claude, Gemini) se registraron y se reasignaron.
- No se usó password sudo; sin commits/push/despliegues.
