import { EDIFICIOS, CAMPUS_CENTER } from "../src/data/campus.js";

const API_URL = process.env.VITE_API_URL || "http://localhost:8000/api";

async function getJSON(path, token) {
  const res = await fetch(`${API_URL}${path}`, {
    headers: { Accept: "application/json", Authorization: `Bearer ${token}` },
  });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return res.json();
}
const getPublicaciones = (token) => getJSON("/publicaciones?page=1&per_page=50", token);
const getPodcast = (token) => getJSON("/podcast", token);

function asList(payload) {
  if (!payload) return [];
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload.data)) return payload.data;
  return [];
}

export async function buildLiveContext(token) {
  let publicaciones = [];
  let podcasts = [];

  if (token) {
    try {
      const pubs = await getPublicaciones(token, 1);
      publicaciones = asList(pubs).slice(0, 40);
    } catch (error) {
      console.error("No se pudieron cargar publicaciones para el chat", error);
    }

    try {
      const pods = await getPodcast(token);
      podcasts = asList(pods).slice(0, 20);
    } catch (error) {
      console.error("No se pudieron cargar podcasts para el chat", error);
    }
  }

  const resumenPubs = publicaciones
    .map((p, i) => {
      const lugar = p.ubicacion || p.aula || "sin ubicación";
      return `${i + 1}. [${p.created_at || ""}] ${p.autor || "Autor"} | ${p.tema || "General"} | ${p.titulo || "Sin título"} | Lugar: ${lugar} | ${String(p.contenido || "").slice(0, 280)}`;
    })
    .join("\n");

  const resumenPods = podcasts
    .map((p, i) => `${i + 1}. ${p.autor || "Autor"} | ${p.titulo || "Sin título"} | ${String(p.descripcion || "").slice(0, 180)}`)
    .join("\n");

  const resumenCampus = EDIFICIOS.map((e) => {
    const aulas = e.aulas.map((a) => a.nombre).join(", ") || "sin aulas";
    return `- ${e.nombre} (${e.lat}, ${e.lng}): ${e.descripcion} Aulas: ${aulas}`;
  }).join("\n");

  return {
    publicaciones,
    podcasts,
    prompt: `
=== DATOS EN TIEMPO REAL DE UNISPACE (usar SIEMPRE para responder sobre publicaciones, podcasts y el campus) ===
Campus: ${CAMPUS_CENTER.nombre}
Dirección: ${CAMPUS_CENTER.direccion}
Coordenadas: ${CAMPUS_CENTER.lat}, ${CAMPUS_CENTER.lng}

EDIFICIOS Y AULAS:
${resumenCampus}

PUBLICACIONES DEL FORO (incluye históricas de esta consulta):
${resumenPubs || "Todavía no hay publicaciones públicas."}

PODCASTS:
${resumenPods || "Todavía no hay podcasts."}

Si preguntan por novedades, resumí las publicaciones más recientes.
Si preguntan por un aula o edificio, indicá nombre, para qué sirve y coordenadas.
Si una publicación tiene ubicación, mencionála.
`,
  };
}

export function answerFromContext(question, context) {
  const q = (question || "").toLowerCase();
  const pubs = context.publicaciones || [];
  const pods = context.podcasts || [];

  if (!q.trim()) {
    return "Escribí tu pregunta y te ayudo con el campus, el foro o UniSpace.";
  }

  if (/(hola|qué onda|que onda|buenas|hey)/.test(q)) {
    return "¡Qué onda! Soy Luterano, tu asistente de la ULS. Puedo contarte publicaciones del foro, ubicaciones de aulas y edificios, el tour de bienvenida y objetos perdidos. ¿En qué te echo la mano?";
  }

  if (/(mapa|edificio|aula|cafetería|cafeteria|biblioteca|campus|ubicación|ubicacion)/.test(q)) {
    const match = EDIFICIOS.find((e) =>
      q.includes(e.nombre.toLowerCase()) ||
      e.aulas.some((a) => q.includes(a.nombre.toLowerCase())) ||
      (q.includes("cafeteria") && e.id === "cafeteria") ||
      (q.includes("cafetería") && e.id === "cafeteria") ||
      (q.includes("biblioteca") && e.id === "biblioteca")
    ) || EDIFICIOS.find((e) => q.includes(e.id));

    if (match) {
      const aulas = match.aulas.map((a) => a.nombre).join(", ") || "este punto no tiene aulas internas";
      return `${match.nombre} está en el campus ULS (${match.lat}, ${match.lng}). ${match.descripcion} Aulas/espacios: ${aulas}. Lo ves en el mapa interactivo del panel.`;
    }

    return `El campus está en ${CAMPUS_CENTER.direccion} (${CAMPUS_CENTER.lat}, ${CAMPUS_CENTER.lng}). En el mapa ves Rectoría, facultades, biblioteca, cafetería, auditorio, capilla, parqueo y cada aula. Abrí “Mapa del campus” en el panel.`;
  }

  if (/(tour|bienvenida|nuevo ingreso|recorrido)/.test(q)) {
    return "El tour de bienvenida te guía desde el portón principal por Rectoría, aulas, biblioteca, cafetería y auditorio. Entrá a “Tour de bienvenida” en el panel y seguí las tarjetas interactivas.";
  }

  if (/(perdid|encontrad|objeto)/.test(q)) {
    return "En “Objetos perdidos” podés reportar o buscar algo. Indicá el edificio o aula donde se perdió o se encontró. También podés preguntarme si alguien lo publicó en el foro.";
  }

  if (/(podcast)/.test(q)) {
    if (!pods.length) return "Aún no hay podcasts en UniSpace. Podés subir el primero desde el dashboard.";
    const top = pods.slice(0, 5).map((p) => `• ${p.titulo || "Sin título"} (${p.autor || "autor"})`).join("\n");
    return `Estos son los podcasts más recientes:\n${top}`;
  }

  const hits = pubs.filter((p) => {
    const blob = `${p.titulo || ""} ${p.tema || ""} ${p.contenido || ""} ${p.ubicacion || ""} ${p.autor || ""}`.toLowerCase();
    return q.split(/\s+/).filter((w) => w.length > 3).some((w) => blob.includes(w));
  });

  if (hits.length) {
    const lines = hits.slice(0, 6).map((p) => {
      const lugar = p.ubicacion ? ` en ${p.ubicacion}` : "";
      return `• ${p.titulo || "Publicación"} (${p.autor || "autor"})${lugar}: ${String(p.contenido || "").slice(0, 160)}`;
    });
    return `Encontré esto en las publicaciones del foro (incluye históricas):\n${lines.join("\n")}`;
  }

  if (pubs.length && /(publicacion|publicación|foro|novedad|noticia|anuncio|qué hay|que hay)/.test(q)) {
    const lines = pubs.slice(0, 6).map((p) => {
      const lugar = p.ubicacion ? ` · ${p.ubicacion}` : "";
      return `• ${p.titulo || "Sin título"} — ${p.autor || "autor"}${lugar}`;
    });
    return `Últimas publicaciones del foro UniSpace:\n${lines.join("\n")}\nSi querés detalle de alguna, decime el título.`;
  }

  if (pubs.length) {
    const latest = pubs[0];
    return `La publicación más reciente es “${latest.titulo || "Sin título"}” de ${latest.autor || "un miembro de la comunidad"}${latest.ubicacion ? ` en ${latest.ubicacion}` : ""}. También te puedo orientar sobre aulas, el mapa, el tour o podcasts.`;
  }

  return "Puedo ayudarte con el mapa del campus, aulas, el foro, podcasts, el tour de bienvenida y objetos perdidos. Preguntame, por ejemplo: “¿dónde queda el aula 101?” o “¿qué se ha publicado?”.";
}
