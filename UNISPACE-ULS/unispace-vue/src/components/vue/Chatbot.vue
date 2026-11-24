<script setup>
import { nextTick, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { lugaresPlanos } from "@/data/lugares.js";
import { crearPublicacion } from "@/lib/publicaciones.js";
import { createPodcast } from "@/lib/podcast.js";
import { notify } from "@/lib/ui.js";
import { NODE_API_URL, authHeaders } from "@/lib/config.js";

const router = useRouter();
const lugares = lugaresPlanos();
const EXT_PODCAST = ["mp3", "wav", "ogg", "mp4", "mov", "avi"];

const open = ref(false);
const loading = ref(false);
const input = ref("");
const messages = ref([
  {
    role: "assistant",
    text: "¡Qué onda! Soy Luterano. Te cuento lo que hay en el foro, aulas, edificios y podcasts, y si querés te ayudo a crear una publicación o un podcast. ¿En qué te echo la mano?",
    time: now(),
  },
]);
const box = ref(null);

function now() {
  return new Date().toLocaleTimeString("es-SV", { hour: "2-digit", minute: "2-digit" });
}

function escapar(t) {
  return String(t || "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function format(text) {
  return escapar(text)
    .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
    .replace(/\n/g, "<br>");
}

function bajar() {
  return nextTick().then(() => box.value?.scrollTo({ top: box.value.scrollHeight, behavior: "smooth" }));
}

async function send() {
  const text = input.value.trim();
  if (!text || loading.value) return;

  messages.value.push({ role: "user", text, time: now() });
  input.value = "";
  loading.value = true;
  await bajar();

  const history = messages.value.slice(-16).map((m) => ({
    role: m.role === "assistant" ? "assistant" : "user",
    content: m.text,
  }));

  try {
    const res = await fetch(`${NODE_API_URL}/api/chat`, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/json", ...authHeaders() },
      body: JSON.stringify({ messages: history }),
    });
    if (res.status === 401) {
      messages.value.push({ role: "assistant", text: "Tu sesión expiró. Iniciá sesión de nuevo para seguir conversando.", time: now() });
      return;
    }
    const data = await res.json();
    const enlace = data.propuesta?.datos?.enlace || "";
    messages.value.push({
      role: "assistant",
      text: data.message || "No pude generar una respuesta.",
      propuesta: data.propuesta || null,
      lugar: data.lugar || null,
      // Campos editables de la tarjeta
      enlace,
      archivo: null,
      modo: enlace ? "enlace" : "archivo",
      error: "",
      publicando: false,
      time: now(),
    });
  } catch {
    messages.value.push({
      role: "assistant",
      text: "No me pude conectar. Revisá tu conexión e intentá de nuevo.",
      time: now(),
    });
  } finally {
    loading.value = false;
    await bajar();
  }
}

function verEnMapa(l) {
  router.push({ path: "/mapa", query: { lugar: l.id } });
}

function onArchivo(m, e) {
  m.error = "";
  const f = e.target.files?.[0] || null;
  if (f && m.propuesta?.tipo === "podcast") {
    const ext = f.name.split(".").pop().toLowerCase();
    if (!EXT_PODCAST.includes(ext)) {
      m.error = `Formato no permitido. Usá: ${EXT_PODCAST.join(", ")}.`;
      e.target.value = "";
      m.archivo = null;
      return;
    }
  }
  if (f && m.propuesta?.tipo === "publicacion" && !f.type.startsWith("image/")) {
    m.error = "Elegí un archivo de imagen.";
    e.target.value = "";
    m.archivo = null;
    return;
  }
  m.archivo = f;
}

function enlaceValido(t) {
  return /^https?:\/\/\S+$/i.test(String(t || "").trim());
}

async function confirmarPropuesta(m) {
  if (!m.propuesta || m.publicando) return;
  m.error = "";
  m.publicando = true;
  try {
    if (m.propuesta.tipo === "podcast") await publicarPodcast(m);
    else await publicarPublicacion(m);
  } catch (e) {
    m.error = e.message || "No se pudo publicar";
  } finally {
    m.publicando = false;
  }
}

async function publicarPublicacion(m) {
  const d = m.propuesta.datos;
  const enlace = m.enlace.trim();
  if (enlace && !enlaceValido(enlace)) throw new Error("El enlace debe empezar con http:// o https://");

  const lugar = lugares.find((l) => l.id === d.lugar_id);
  const fd = new FormData();
  fd.set("tema", d.tema);
  fd.set("titulo", d.titulo);
  fd.set("contenido", enlace ? `${d.contenido}\n\n🔗 ${enlace}` : d.contenido);
  fd.set("publica", "1");
  fd.set("lugar_id", d.lugar_id || "");
  fd.set("ubicacion", lugar?.label || "");
  fd.set("aula", lugar?.aula || "");
  fd.set("lat", lugar?.lat ?? "");
  fd.set("lng", lugar?.lng ?? "");
  if (m.archivo) fd.set("imagen", m.archivo);

  await crearPublicacion(fd);
  m.propuesta = null;
  notify("Publicación creada");
  messages.value.push({ role: "assistant", text: "Listo, tu publicación ya está en el foro. Recargá el inicio para verla.", time: now() });
  bajar();
}

async function publicarPodcast(m) {
  const d = m.propuesta.datos;
  const enlace = m.enlace.trim();

  if (m.modo === "archivo" && !m.archivo) throw new Error("Elegí un archivo de audio o video.");
  if (m.modo === "enlace" && !enlaceValido(enlace)) throw new Error("Pegá un enlace que empiece con http:// o https://");

  const fd = new FormData();
  fd.append("tema", (d.tema || "General").trim());
  fd.append("titulo", d.titulo.trim());
  fd.append("descripcion", (d.descripcion || "").trim());
  fd.append("contenido", m.modo === "archivo" ? m.archivo : enlace);

  try {
    await createPodcast(fd);
  } catch (e) {
    if (m.modo === "enlace" && /must be a file/i.test(e.message || "")) {
      throw new Error("El servidor todavía solo acepta archivos en los podcasts. Subí un archivo mientras se habilitan los enlaces.");
    }
    throw e;
  }

  m.propuesta = null;
  notify("Podcast publicado");
  messages.value.push({ role: "assistant", text: "Listo, tu podcast ya está publicado. Lo ves en la pestaña Podcast.", time: now() });
  bajar();
}

onMounted(() => {
  const params = new URLSearchParams(window.location.search);
  if (params.get("chat") === "open") open.value = true;
});
</script>

<template>
  <div class="fixed bottom-6 right-6 z-[80]">
    <div v-if="open" class="card bg-base-100 shadow-2xl w-[22rem] sm:w-[24rem] h-[32rem] mb-3 border border-base-300">
      <div class="bg-primary text-primary-content px-4 py-3 rounded-t-2xl flex items-center gap-3">
        <div class="avatar avatar-placeholder">
          <div class="bg-primary-content text-primary w-12 rounded-xl font-black">L</div>
        </div>
        <div class="leading-tight">
          <p class="font-extrabold">Luterano</p>
          <p class="text-xs opacity-80">Asistente virtual ULS</p>
          <p class="text-xs"><span class="text-success">●</span> En línea · foro en vivo</p>
        </div>
      </div>

      <div ref="box" class="flex-1 overflow-y-auto p-3 space-y-3 bg-base-200">
        <div
          v-for="(m, i) in messages"
          :key="i"
          class="chat"
          :class="m.role === 'user' ? 'chat-end' : 'chat-start'"
        >
          <div class="chat-header text-[10px] opacity-50">{{ m.time }}</div>
          <div
            class="chat-bubble text-sm"
            :class="m.role === 'user' ? 'chat-bubble-primary' : 'bg-base-100 text-base-content'"
          >
            <span v-html="format(m.text)"></span>

            <button
              v-if="m.lugar"
              type="button"
              class="btn btn-primary btn-xs mt-2"
              @click="verEnMapa(m.lugar)"
            >
              📍 Ver {{ m.lugar.nombre }} en el mapa
            </button>

            <div v-if="m.propuesta" class="card bg-base-200 text-base-content mt-2">
              <div class="card-body p-3 gap-2 text-xs">
                <span class="badge badge-ghost badge-sm">
                  {{ m.propuesta.tipo === 'podcast' ? '🎙️ Podcast' : '📝 Publicación' }}
                  <template v-if="m.propuesta.datos.tema"> · {{ m.propuesta.datos.tema }}</template>
                </span>
                <p class="font-bold text-sm">{{ m.propuesta.datos.titulo }}</p>
                <p v-if="m.propuesta.datos.contenido" class="opacity-80 whitespace-pre-wrap">{{ m.propuesta.datos.contenido }}</p>
                <p v-if="m.propuesta.datos.descripcion" class="opacity-80 whitespace-pre-wrap">{{ m.propuesta.datos.descripcion }}</p>

                <!-- Publicación: imagen y enlace opcionales -->
                <template v-if="m.propuesta.tipo === 'publicacion'">
                  <label class="flex flex-col gap-1">
                    <span class="opacity-70">Imagen (opcional)</span>
                    <input type="file" accept="image/*" class="file-input file-input-bordered file-input-xs w-full" @change="onArchivo(m, $event)" />
                  </label>
                  <label class="flex flex-col gap-1">
                    <span class="opacity-70">Enlace (opcional)</span>
                    <input v-model="m.enlace" type="url" class="input input-bordered input-xs w-full" placeholder="https://..." />
                  </label>
                </template>

                <!-- Podcast: archivo o enlace -->
                <template v-else>
                  <div role="tablist" class="tabs tabs-box tabs-xs w-fit">
                    <button type="button" role="tab" class="tab" :class="{ 'tab-active': m.modo === 'archivo' }" @click="m.modo = 'archivo'">📁 Archivo</button>
                    <button type="button" role="tab" class="tab" :class="{ 'tab-active': m.modo === 'enlace' }" @click="m.modo = 'enlace'">🔗 Enlace</button>
                  </div>
                  <input
                    v-if="m.modo === 'archivo'"
                    type="file"
                    accept=".mp3,.wav,.ogg,.mp4,.mov,.avi,audio/*,video/*"
                    class="file-input file-input-bordered file-input-xs w-full"
                    @change="onArchivo(m, $event)"
                  />
                  <input v-else v-model="m.enlace" type="url" class="input input-bordered input-xs w-full" placeholder="https://www.youtube.com/watch?v=..." />
                </template>

                <p v-if="m.error" class="text-error">{{ m.error }}</p>

                <div class="card-actions justify-end mt-1">
                  <button type="button" class="btn btn-ghost btn-xs" :disabled="m.publicando" @click="m.propuesta = null">Cancelar</button>
                  <button type="button" class="btn btn-primary btn-xs" :disabled="m.publicando" @click="confirmarPropuesta(m)">
                    <span v-if="m.publicando" class="loading loading-spinner loading-xs"></span>
                    Publicar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="loading" class="chat chat-start">
          <div class="chat-bubble bg-base-100"><span class="loading loading-dots loading-sm"></span></div>
        </div>
      </div>

      <form class="p-3 flex gap-2 border-t border-base-300" @submit.prevent="send">
        <input
          v-model="input"
          class="input input-bordered input-sm flex-1 rounded-full"
          maxlength="500"
          placeholder="Escribí tu pregunta..."
        />
        <button class="btn btn-primary btn-sm btn-circle" type="submit" :disabled="loading">➤</button>
      </form>
    </div>

    <button class="btn btn-primary btn-circle w-16 h-16 shadow-xl" type="button" @click="open = !open">
      <span v-if="open" class="text-xl">✕</span>
      <span v-else class="text-xl">🤖</span>
    </button>
  </div>
</template>