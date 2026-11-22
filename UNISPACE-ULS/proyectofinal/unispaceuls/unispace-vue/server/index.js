import "dotenv/config";
import express from "express";
import fs from "node:fs/promises";
import path from "node:path";
import { buildLiveContext, answerFromContext } from "./live-context.js";

const app = express();
app.use(express.json());

const SYSTEM_PROMPT = `Sos Luterano, el asistente virtual oficial de la Universidad Luterana Salvadoreña (ULS) en UniSpace. Hablás con "vos", de forma amable y clara. Usás la información EN TIEMPO REAL del foro (publicaciones actuales e históricas de esta consulta), podcasts, edificios y aulas. Si algo no está en los datos, lo decís y sugerís el mapa, el tour o contactar uls@uls.edu.sv / (503) 2133-2600.

Datos fijos ULS:
- Dirección: Intersección NorOriente Carretera a Los Planes de Renderos Km. 3 y Autopista a Comalapa, Bo. San Jacinto, San Salvador.
- Sitio: https://uls.edu.sv/sitioweb/
- Campus virtual: https://campus.uls.edu.sv
- Académica: https://academica.uls.edu.sv
- Coordenadas del campus: 13.6754187, -89.193982
`;

function getToken(req) {
  const m = (req.headers.cookie || "").match(/(?:^|;\s*)token=([^;]+)/);
  return m ? decodeURIComponent(m[1]) : undefined;
}

app.post("/api/chat", async (req, res) => {
  try {
    const { messages } = req.body;
    if (!messages || !Array.isArray(messages)) {
      return res.status(400).json({ error: "Mensajes inválidos" });
    }

    const live = await buildLiveContext(getToken(req));
    const lastUser = [...messages].reverse().find((m) => m.role === "user")?.content || "";
    const fallback = answerFromContext(lastUser, live);

    const apiKey = process.env.OPENROUTER_API_KEY;
    if (!apiKey) return res.json({ message: fallback, source: "local" });

    const r = await fetch("https://openrouter.ai/api/v1/chat/completions", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${apiKey}`,
        "HTTP-Referer": "https://unispace.uls.edu.sv",
        "X-Title": "UniSpace ULS",
      },
      body: JSON.stringify({
        model: "openrouter/auto",
        max_tokens: 1024,
        temperature: 0.4,
        messages: [
          { role: "system", content: SYSTEM_PROMPT + "\n" + live.prompt },
          ...messages.slice(-16),
        ],
      }),
    });
    const data = await r.json();
    if (!r.ok) return res.json({ message: fallback, source: "local" });

    res.json({ message: data.choices?.[0]?.message?.content || fallback, source: "live" });
  } catch (error) {
    console.error("Error en chat API:", error);
    res.json({ message: "Puchica, tuve un problema leyendo UniSpace. Intentá de nuevo en un toque." });
  }
});

const file = path.join(process.cwd(), "data", "objetos-perdidos.json");

async function readAll() {
  try { return JSON.parse(await fs.readFile(file, "utf8")); } catch { return []; }
}
async function writeAll(items) {
  await fs.mkdir(path.dirname(file), { recursive: true });
  await fs.writeFile(file, JSON.stringify(items, null, 2), "utf8");
}

app.get("/api/objetos-perdidos", async (_req, res) => res.json(await readAll()));

app.post("/api/objetos-perdidos", async (req, res) => {
  const body = req.body || {};
  const created = {
    id: Date.now(),
    titulo: String(body.titulo || "").slice(0, 180),
    descripcion: String(body.descripcion || "").slice(0, 800),
    tipo: body.tipo === "encontrado" ? "encontrado" : "perdido",
    lugar_id: body.lugar_id || "cafeteria",
    created_at: new Date().toISOString(),
  };
  const items = await readAll();
  items.unshift(created);
  await writeAll(items.slice(0, 200));
  res.status(201).json(created);
});

app.delete("/api/objetos-perdidos/:id", async (req, res) => {
  const id = Number(req.params.id);
  await writeAll((await readAll()).filter((i) => Number(i.id) !== id));
  res.json({ ok: true });
});

app.listen(3001, () => console.log("Servidor local en http://localhost:3001"));
