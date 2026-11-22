<script setup>
import { nextTick, onMounted, ref } from "vue";

const open = ref(false);
const loading = ref(false);
const input = ref("");
const messages = ref([
  {
    role: "assistant",
    text: "¡Qué onda! Soy Luterano. Te cuento en tiempo real lo que hay en el foro, aulas, edificios, podcasts y objetos perdidos. ¿En qué te echo la mano?",
    time: now(),
  },
]);
const box = ref(null);

function now() {
  return new Date().toLocaleTimeString("es-SV", { hour: "2-digit", minute: "2-digit" });
}

function format(text) {
  return String(text || "")
    .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
    .replace(/\n/g, "<br>");
}

async function send() {
  const text = input.value.trim();
  if (!text || loading.value) return;

  messages.value.push({ role: "user", text, time: now() });
  input.value = "";
  loading.value = true;
  await nextTick();
  box.value?.scrollTo({ top: box.value.scrollHeight, behavior: "smooth" });

  const history = messages.value
    .filter((m) => m.role !== "system")
    .slice(-16)
    .map((m) => ({
      role: m.role === "assistant" ? "assistant" : "user",
      content: m.text,
    }));

  try {
    const res = await fetch("/api/chat", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ messages: history }),
    });
    const data = await res.json();
    messages.value.push({
      role: "assistant",
      text: data.message || "No pude generar una respuesta.",
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
    await nextTick();
    box.value?.scrollTo({ top: box.value.scrollHeight, behavior: "smooth" });
  }
}

onMounted(() => {
  const params = new URLSearchParams(window.location.search);
  if (params.get("chat") === "open") open.value = true;
});
</script>

<template>
  <div class="fixed bottom-6 right-6 z-[80]">
    <div v-if="open" class="card bg-base-100 shadow-2xl w-[22rem] sm:w-[24rem] h-[32rem] mb-3 border border-base-200">
      <div class="bg-primary text-primary-content px-4 py-3 rounded-t-2xl flex items-center gap-3">
        <div class="avatar placeholder">
          <div class="bg-primary-content text-primary w-12 rounded-xl grid place-items-center font-black">L</div>
        </div>
        <div class="leading-tight">
          <p class="font-extrabold">Luterano</p>
          <p class="text-xs opacity-80">Asistente virtual ULS</p>
          <p class="text-xs"><span class="text-success">●</span> En línea · foro en vivo</p>
        </div>
      </div>

      <div ref="box" class="flex-1 overflow-y-auto p-3 space-y-3 bg-slate-50 h-[21rem]">
        <div
          v-for="(m, i) in messages"
          :key="i"
          class="chat"
          :class="m.role === 'user' ? 'chat-end' : 'chat-start'"
        >
          <div class="chat-header text-[10px] opacity-50">{{ m.time }}</div>
          <div
            class="chat-bubble text-sm"
            :class="m.role === 'user' ? 'chat-bubble-primary' : 'chat-bubble-secondary bg-white text-slate-800'"
            v-html="format(m.text)"
          />
        </div>
        <div v-if="loading" class="chat chat-start">
          <div class="chat-bubble bg-white"><span class="loading loading-dots loading-sm"></span></div>
        </div>
      </div>

      <form class="p-3 flex gap-2 border-t" @submit.prevent="send">
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
