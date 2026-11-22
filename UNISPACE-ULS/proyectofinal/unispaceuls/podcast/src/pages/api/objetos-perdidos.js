export const prerender = false;

import fs from "node:fs/promises";
import path from "node:path";

const file = path.join(process.cwd(), "data", "objetos-perdidos.json");

async function readAll() {
  try {
    const raw = await fs.readFile(file, "utf8");
    return JSON.parse(raw);
  } catch {
    return [];
  }
}

async function writeAll(items) {
  await fs.mkdir(path.dirname(file), { recursive: true });
  await fs.writeFile(file, JSON.stringify(items, null, 2), "utf8");
}

export async function GET() {
  const items = await readAll();
  return new Response(JSON.stringify(items), {
    headers: { "Content-Type": "application/json" },
  });
}

export async function POST({ request }) {
  const body = await request.json();
  const items = await readAll();
  const created = {
    id: Date.now(),
    titulo: String(body.titulo || "").slice(0, 180),
    descripcion: String(body.descripcion || "").slice(0, 800),
    tipo: body.tipo === "encontrado" ? "encontrado" : "perdido",
    lugar_id: body.lugar_id || "cafeteria",
    created_at: new Date().toISOString(),
  };
  items.unshift(created);
  await writeAll(items.slice(0, 200));
  return new Response(JSON.stringify(created), {
    status: 201,
    headers: { "Content-Type": "application/json" },
  });
}
