// Uso:
//   node scripts/fb.mjs agregar "texto" [--fecha 2026-10-15] [--link https://...]
//   node scripts/fb.mjs lista
//   node scripts/fb.mjs ver <id>
//   node scripts/fb.mjs quitar <id> [<id> ...]
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const RAIZ = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const ARCHIVO = path.join(RAIZ, "server", "facebook.json");

function leer() {
  if (!fs.existsSync(ARCHIVO)) return [];
  try {
    return JSON.parse(fs.readFileSync(ARCHIVO, "utf8"));
  } catch {
    console.error("No se pudo leer server/facebook.json (JSON dañado). Revisa el archivo.");
    process.exit(1);
  }
}

function guardar(posts) {
  fs.mkdirSync(path.dirname(ARCHIVO), { recursive: true });
  fs.writeFileSync(ARCHIVO, JSON.stringify(posts, null, 2) + "\n", "utf8");
}

function opcion(args, nombre) {
  const i = args.indexOf(`--${nombre}`);
  if (i === -1) return null;
  const valor = args[i + 1];
  args.splice(i, 2);
  return valor ?? null;
}

function hoy() {
  return new Date().toISOString().slice(0, 10);
}

function siguienteId(posts) {
  const nums = posts.map((p) => Number(String(p.id).replace("fb-", ""))).filter(Number.isFinite);
  return "fb-" + String((nums.length ? Math.max(...nums) : 0) + 1).padStart(3, "0");
}

const [, , cmd, ...resto] = process.argv;

if (cmd === "agregar") {
  const args = [...resto];
  const fecha = opcion(args, "fecha") || hoy();
  const link = opcion(args, "link") || "";
  const texto = args.join(" ").trim();
  if (!texto) {
    console.error('Falta el texto. Ejemplo: node scripts/fb.mjs agregar "Inscripciones abiertas" --fecha 2026-10-15');
    process.exit(1);
  }
  if (!/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
    console.error("La fecha debe ser AAAA-MM-DD, por ejemplo 2026-10-15.");
    process.exit(1);
  }
  const posts = leer();
  const post = { id: siguienteId(posts), fecha, texto, link };
  posts.push(post);
  posts.sort((a, b) => b.fecha.localeCompare(a.fecha));
  guardar(posts);
  console.log(`\n✓ Publicación ${post.id} guardada (${fecha}). Total: ${posts.length}\n`);
} else if (cmd === "lista") {
  const posts = leer();
  if (!posts.length) {
    console.log("\nNo hay publicaciones guardadas todavía.\n");
  } else {
    console.log("");
    for (const p of posts) {
      const corto = p.texto.length > 70 ? p.texto.slice(0, 70) + "…" : p.texto;
      console.log(`  ${p.id}  ${p.fecha}  ${corto}`);
    }
    console.log(`\n  Total: ${posts.length}\n`);
  }
} else if (cmd === "ver") {
  const p = leer().find((x) => x.id === resto[0]);
  if (!p) {
    console.error("No existe ese id. Usa: node scripts/fb.mjs lista");
    process.exit(1);
  }
  console.log(`\n${p.id} · ${p.fecha}\n${p.texto}\n${p.link || "(sin enlace)"}\n`);
} else if (cmd === "quitar") {
  if (!resto.length) {
    console.error("Indica el id. Ejemplo: node scripts/fb.mjs quitar fb-003");
    process.exit(1);
  }
  const posts = leer();
  const quedan = posts.filter((p) => !resto.includes(p.id));
  guardar(quedan);
  console.log(`\n✓ Quitadas ${posts.length - quedan.length}. Total: ${quedan.length}\n`);
} else {
  console.log(`
Uso:
  node scripts/fb.mjs agregar "texto" [--fecha AAAA-MM-DD] [--link URL]
  node scripts/fb.mjs lista
  node scripts/fb.mjs ver <id>
  node scripts/fb.mjs quitar <id> [<id> ...]
`);
}
