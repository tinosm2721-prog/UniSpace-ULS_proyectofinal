export const prerender = false;

import { buildLiveContext, answerFromContext } from "../../lib/live-context.js";

const SYSTEM_PROMPT = `Sos Luterano, el asistente virtual oficial de la Universidad Luterana Salvadoreña (ULS) en UniSpace. Hablás con "vos", de forma amable y clara. Usás la información EN TIEMPO REAL del foro (publicaciones actuales e históricas de esta consulta), podcasts, edificios y aulas. Si algo no está en los datos, lo decís y sugerís el mapa, el tour o contactar uls@uls.edu.sv / (503) 2133-2600.

Datos fijos ULS:
- Dirección: Intersección NorOriente Carretera a Los Planes de Renderos Km. 3 y Autopista a Comalapa, Bo. San Jacinto, San Salvador.
- Sitio: https://uls.edu.sv/sitioweb/
- Campus virtual: https://campus.uls.edu.sv
- Académica: https://academica.uls.edu.sv
- Coordenadas del campus: 13.6754187, -89.193982
`;

export async function POST({ request, cookies }) {
  try {
    const { messages } = await request.json();

    if (!messages || !Array.isArray(messages)) {
      return new Response(JSON.stringify({ error: "Mensajes inválidos" }), {
        status: 400,
        headers: { "Content-Type": "application/json" },
      });
    }

    const token = cookies.get("token")?.value;
    const live = await buildLiveContext(token);
    const lastUser = [...messages].reverse().find((m) => m.role === "user")?.content || "";
    const fallback = answerFromContext(lastUser, live);

    const apiKey = import.meta.env.OPENROUTER_API_KEY;

    if (!apiKey) {
      return new Response(JSON.stringify({ message: fallback, source: "local" }), {
        status: 200,
        headers: { "Content-Type": "application/json" },
      });
    }

    const response = await fetch("https://openrouter.ai/api/v1/chat/completions", {
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

    const data = await response.json();

    if (!response.ok) {
      return new Response(JSON.stringify({ message: fallback, source: "local" }), {
        status: 200,
        headers: { "Content-Type": "application/json" },
      });
    }

    const text = data.choices?.[0]?.message?.content || fallback;

    return new Response(JSON.stringify({ message: text, source: "live" }), {
      status: 200,
      headers: { "Content-Type": "application/json" },
    });
  } catch (error) {
    console.error("Error en chat API:", error);
    return new Response(
      JSON.stringify({ message: "Puchica, tuve un problema leyendo UniSpace. Intentá de nuevo en un toque." }),
      { status: 200, headers: { "Content-Type": "application/json" } }
    );
  }
}
