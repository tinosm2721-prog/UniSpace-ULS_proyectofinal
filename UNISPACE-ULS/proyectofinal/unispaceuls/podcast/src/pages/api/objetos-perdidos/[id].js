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

export async function DELETE({ params }) {
  const id = Number(params.id);
  const items = await readAll();
  const next = items.filter((item) => Number(item.id) !== id);
  await fs.mkdir(path.dirname(file), { recursive: true });
  await fs.writeFile(file, JSON.stringify(next, null, 2), "utf8");
  return new Response(JSON.stringify({ ok: true }), {
    headers: { "Content-Type": "application/json" },
  });
}
