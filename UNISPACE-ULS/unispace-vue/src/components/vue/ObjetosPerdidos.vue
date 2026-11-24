<script setup>
import { computed, onMounted, ref } from "vue";
import { LUGARES_BASE, lugaresPlanos } from "@/data/lugares.js";
import { notify, confirmar } from "@/lib/ui.js";
import { NODE_API_URL, authHeaders } from "@/lib/config.js";

const LS_KEY = "unispace-objetos";
const items = ref([]);
const form = ref({
  titulo: "",
  descripcion: "",
  tipo: "perdido",
  lugar_id: "cafeteria",
});
const saving = ref(false);
const filtro = ref("todos");
const lugares = lugaresPlanos();

const visibles = computed(() => {
  if (filtro.value === "todos") return items.value;
  return items.value.filter((i) => i.tipo === filtro.value);
});

function lugarLabel(id) {
  return lugares.find((l) => l.id === id)?.label || id;
}

// Id que el mapa sabe seleccionar (si es un aula interna, su edificio)
function lugarMapa(id) {
  const base = LUGARES_BASE.find((l) => l.id === id || (l.aulas || []).some((a) => a.id === id));
  return base?.id || "cafeteria";
}

function fecha(iso) {
  if (!iso) return "";
  const d = new Date(iso);
  return isNaN(d) ? "" : d.toLocaleDateString("es-SV", { day: "numeric", month: "short", year: "numeric" });
}

function leerLocal() {
  try { return JSON.parse(localStorage.getItem(LS_KEY) || "[]"); } catch { return []; }
}

function guardarLocal() {
  try { localStorage.setItem(LS_KEY, JSON.stringify(items.value)); } catch { /* sin storage */ }
}

function sesionExpirada() {
  notify("Tu sesión expiró. Iniciá sesión de nuevo.", "error");
}

async function load() {
  try {
    const res = await fetch(`${NODE_API_URL}/api/objetos-perdidos`, { credentials: "same-origin", headers: authHeaders() });
    if (res.status === 401) {
      sesionExpirada();
      items.value = [];
      return;
    }
    if (!res.ok) throw new Error("API no disponible");
    items.value = await res.json();
  } catch {
    // Servidor caído: se muestran los reportes guardados en este navegador
    items.value = leerLocal();
  }
}

async function save() {
  if (!form.value.titulo.trim() || saving.value) return;
  saving.value = true;
  const payload = { ...form.value, created_at: new Date().toISOString() };
  try {
    const res = await fetch(`${NODE_API_URL}/api/objetos-perdidos`, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/json", ...authHeaders() },
      body: JSON.stringify(payload),
    });
    if (res.status === 401) {
      sesionExpirada();
      return; // no se guarda nada: el formulario conserva lo escrito
    }
    if (!res.ok) throw new Error("API no disponible");
    const created = await res.json();
    items.value = [created, ...items.value];
    notify("Reporte publicado");
  } catch {
    items.value = [{ id: Date.now(), ...payload }, ...items.value];
    guardarLocal();
    notify("Servidor no disponible: el reporte quedó solo en este navegador.", "warning");
  } finally {
    saving.value = false;
  }
  form.value.titulo = "";
  form.value.descripcion = "";
}

async function resolver(item) {
  const ok = await confirmar(`¿Marcar "${item.titulo}" como resuelto? Se quitará de la lista.`, {
    titulo: "Marcar resuelto",
    aceptar: "Marcar resuelto",
  });
  if (!ok) return;
  try {
    const res = await fetch(`${NODE_API_URL}/api/objetos-perdidos/${item.id}`, {
      method: "DELETE",
      credentials: "same-origin",
      headers: authHeaders(),
    });
    if (res.status === 401) {
      sesionExpirada();
      return;
    }
  } catch {
    /* sin servidor: solo se quita de la copia local */
  }
  items.value = items.value.filter((i) => i.id !== item.id);
  guardarLocal();
  notify("Reporte resuelto");
}

onMounted(load);
</script>

<template>
  <div class="space-y-4">
    <div class="card bg-base-100 shadow-sm">
      <div class="card-body">
        <h2 class="card-title">Reportar objeto</h2>
        <form class="grid md:grid-cols-2 gap-3" @submit.prevent="save">
          <input v-model="form.titulo" class="input input-bordered w-full" placeholder="¿Qué se perdió o encontró?" required />
          <select v-model="form.tipo" class="select select-bordered w-full">
            <option value="perdido">Lo perdí</option>
            <option value="encontrado">Lo encontré</option>
          </select>
          <select v-model="form.lugar_id" class="select select-bordered md:col-span-2 w-full">
            <option v-for="lugar in lugares" :key="lugar.id" :value="lugar.id">{{ lugar.label }}</option>
          </select>
          <textarea v-model="form.descripcion" class="textarea textarea-bordered md:col-span-2 w-full" placeholder="Detalles, color, aula..."></textarea>
          <button class="btn btn-primary md:col-span-2" :disabled="saving">
            <span v-if="saving" class="loading loading-spinner loading-sm"></span>
            Publicar en objetos perdidos
          </button>
        </form>
      </div>
    </div>

    <div role="tablist" class="tabs tabs-box bg-base-100 shadow-sm w-fit">
      <button type="button" role="tab" class="tab" :class="{ 'tab-active': filtro === 'todos' }" @click="filtro = 'todos'">Todos</button>
      <button type="button" role="tab" class="tab" :class="{ 'tab-active': filtro === 'perdido' }" @click="filtro = 'perdido'">Perdidos</button>
      <button type="button" role="tab" class="tab" :class="{ 'tab-active': filtro === 'encontrado' }" @click="filtro = 'encontrado'">Encontrados</button>
    </div>

    <div v-if="!visibles.length" class="alert">Aún no hay reportes. Sé la primera persona en publicar.</div>

    <article v-for="item in visibles" :key="item.id" class="card bg-base-100 shadow-sm">
      <div class="card-body">
        <div class="flex justify-between gap-2">
          <h3 class="font-bold text-lg">{{ item.titulo }}</h3>
          <span class="badge" :class="item.tipo === 'perdido' ? 'badge-warning' : 'badge-success'">{{ item.tipo }}</span>
        </div>
        <p v-if="item.descripcion">{{ item.descripcion }}</p>
        <p class="text-sm opacity-70">📍 {{ lugarLabel(item.lugar_id) }} · {{ fecha(item.created_at) }}</p>
        <div class="card-actions justify-end">
          <router-link class="btn btn-ghost btn-sm" :to="{ path: '/mapa', query: { lugar: lugarMapa(item.lugar_id) } }">Ver en mapa</router-link>
          <button class="btn btn-outline btn-sm" type="button" @click="resolver(item)">Marcar resuelto</button>
        </div>
      </div>
    </article>
  </div>
</template>