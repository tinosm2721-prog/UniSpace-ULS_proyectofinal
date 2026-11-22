<script setup>
import { computed, onMounted, ref } from "vue";
import { EDIFICIOS, lugaresPlanos } from "../../data/campus.js";

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

async function load() {
  try {
    const res = await fetch("/api/objetos-perdidos");
    items.value = await res.json();
  } catch {
    items.value = JSON.parse(localStorage.getItem("unispace-objetos") || "[]");
  }
}

async function save() {
  if (!form.value.titulo.trim()) return;
  saving.value = true;
  const payload = {
    ...form.value,
    created_at: new Date().toISOString(),
  };
  try {
    const res = await fetch("/api/objetos-perdidos", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const created = await res.json();
    items.value = [created, ...items.value];
  } catch {
    const local = { id: Date.now(), ...payload };
    items.value = [local, ...items.value];
    localStorage.setItem("unispace-objetos", JSON.stringify(items.value));
  }
  form.value.titulo = "";
  form.value.descripcion = "";
  saving.value = false;
}

async function resolver(id) {
  try {
    await fetch(`/api/objetos-perdidos/${id}`, { method: "DELETE" });
  } catch {
    /* local fallback */
  }
  items.value = items.value.filter((i) => i.id !== id);
  localStorage.setItem("unispace-objetos", JSON.stringify(items.value));
}

onMounted(load);
</script>

<template>
  <div class="space-y-4">
    <div class="card bg-base-100 shadow-xl">
      <div class="card-body">
        <h2 class="card-title">Reportar objeto</h2>
        <form class="grid md:grid-cols-2 gap-3" @submit.prevent="save">
          <input v-model="form.titulo" class="input input-bordered" placeholder="¿Qué se perdió o encontró?" required />
          <select v-model="form.tipo" class="select select-bordered">
            <option value="perdido">Lo perdí</option>
            <option value="encontrado">Lo encontré</option>
          </select>
          <select v-model="form.lugar_id" class="select select-bordered md:col-span-2">
            <option v-for="lugar in lugares" :key="lugar.id" :value="lugar.id">{{ lugar.label }}</option>
          </select>
          <textarea v-model="form.descripcion" class="textarea textarea-bordered md:col-span-2" placeholder="Detalles, color, aula..." />
          <button class="btn btn-primary md:col-span-2" :disabled="saving">Publicar en objetos perdidos</button>
        </form>
      </div>
    </div>

    <div class="tabs tabs-boxed">
      <a class="tab" :class="{ 'tab-active': filtro === 'todos' }" @click="filtro = 'todos'">Todos</a>
      <a class="tab" :class="{ 'tab-active': filtro === 'perdido' }" @click="filtro = 'perdido'">Perdidos</a>
      <a class="tab" :class="{ 'tab-active': filtro === 'encontrado' }" @click="filtro = 'encontrado'">Encontrados</a>
    </div>

    <div v-if="!visibles.length" class="alert">Aún no hay reportes. Sé la primera persona en publicar.</div>

    <article v-for="item in visibles" :key="item.id" class="card bg-base-100 shadow">
      <div class="card-body">
        <div class="flex justify-between gap-2">
          <h3 class="font-bold text-lg">{{ item.titulo }}</h3>
          <span class="badge" :class="item.tipo === 'perdido' ? 'badge-warning' : 'badge-success'">{{ item.tipo }}</span>
        </div>
        <p>{{ item.descripcion }}</p>
        <p class="text-sm opacity-70">{{ lugarLabel(item.lugar_id) }}</p>
        <div class="card-actions justify-end">
          <a class="btn btn-ghost btn-sm" :href="`/mapa?lugar=${EDIFICIOS.find(e => e.id === item.lugar_id || e.aulas.some(a => a.id === item.lugar_id))?.id || 'cafeteria'}`">Ver en mapa</a>
          <button class="btn btn-outline btn-sm" type="button" @click="resolver(item.id)">Marcar resuelto</button>
        </div>
      </div>
    </article>
  </div>
</template>
