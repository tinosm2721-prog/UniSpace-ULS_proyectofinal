<script setup>
import { ref } from 'vue'
import { getComentarios, crearComentario } from '@/lib/comentarios.js'
import { toggleLike } from '@/lib/like.js'

const props = defineProps({
  id: { type: [Number, String], required: true },
  tipo: { type: String, default: 'publicacion' },
  totalComentarios: { type: Number, default: 0 },
  totalLikes: { type: Number, default: 0 },
  userLiked: { type: Boolean, default: false },
})

const open = ref(false)
const loaded = ref(false)
const loading = ref(false)
const comentarios = ref([])
const offset = ref(0)
const hasMore = ref(false)
const nuevoComentario = ref('')
const enviando = ref(false)
const cargandoMas = ref(false)

const countComentarios = ref(props.totalComentarios)
const countLikes = ref(props.totalLikes)
const liked = ref(props.userLiked)
const likeLoading = ref(false)

async function toggleComentarios() {
  open.value = !open.value
  if (open.value && !loaded.value) {
    loading.value = true
    try {
      const data = await getComentarios(props.id, props.tipo)
      comentarios.value = data?.comentarios || []
      offset.value = comentarios.value.length
      hasMore.value = !!data?.hasMore
      loaded.value = true
    } catch (err) {
      console.error(err)
    } finally {
      loading.value = false
    }
  }
}

async function cargarMas() {
  cargandoMas.value = true
  try {
    const data = await getComentarios(props.id, props.tipo, 10, offset.value)
    comentarios.value.push(...(data?.comentarios || []))
    offset.value += data?.comentarios?.length || 0
    hasMore.value = !!data?.hasMore
  } catch (err) {
    console.error(err)
  } finally {
    cargandoMas.value = false
  }
}

async function enviarComentario() {
  const texto = nuevoComentario.value.trim()
  if (!texto) return
  enviando.value = true
  try {
    const nuevo = await crearComentario({
      comentario: texto,
      commentable_id: props.id,
      commentable_type: props.tipo,
    })
    comentarios.value.unshift(nuevo)
    countComentarios.value++
    nuevoComentario.value = ''
  } catch (err) {
    alert(err.message || 'Error al comentar')
  } finally {
    enviando.value = false
  }
}

async function onLike() {
  if (likeLoading.value) return
  likeLoading.value = true
  try {
    const result = await toggleLike(props.id, props.tipo)
    liked.value = result.liked
    countLikes.value = result.total_likes
  } catch (err) {
    console.error(err)
  } finally {
    likeLoading.value = false
  }
}
</script>

<template>
  <div class="cl-wrapper">
    <div class="stats">
      <button type="button" class="btn-action btn-comentarios" @click="toggleComentarios">
        <span>💬</span>
        <span class="comentarios-count">{{ countComentarios }}</span>
        <small>Comentarios</small>
      </button>

      <button type="button" class="btn-action btn-like" :class="{ liked }" @click="onLike">
        <span class="like-icon">{{ liked ? '❤️' : '🤍' }}</span>
        <span class="like-count">{{ countLikes }}</span>
        <small>Likes</small>
      </button>
    </div>

    <div v-if="open" class="comentarios-section" style="display:flex">
      <div class="comentarios-lista">
        <div v-if="loading" class="loading">Cargando comentarios...</div>
        <div v-else-if="!comentarios.length" class="empty-comments">Aún no hay comentarios</div>
        <div v-for="c in comentarios" :key="c.id" class="comentario-item">
          <div class="comentario-header">
            <div class="comentario-avatar">
              <router-link :to="`/usuario/${c.user_id}/usuario`">{{ c.user?.nombre }}</router-link>
            </div>
          </div>
          <p>{{ c.comentario || c.texto_comentario || '' }}</p>
        </div>
      </div>

      <form class="form-comentario" @submit.prevent="enviarComentario">
        <textarea
          v-model="nuevoComentario"
          class="input-comentario"
          placeholder="Escribe un comentario..."
          maxlength="5000"
          rows="3"
          required
        ></textarea>
        <div class="comentario-footer">
          <span class="comentario-info">Máximo 5000 caracteres</span>
          <button type="submit" class="btn-enviar" :disabled="enviando">
            {{ enviando ? 'Enviando...' : 'Comentar' }}
          </button>
        </div>
      </form>

      <button v-if="hasMore" class="btn-cargar-mas" :disabled="cargandoMas" @click="cargarMas">
        {{ cargandoMas ? 'Cargando...' : 'Cargar más comentarios' }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.cl-wrapper{
    display:flex;
    flex-direction:column;
    gap:1rem;
    width:100%;
  }

  .stats{
    display:flex;
    align-items:center;
    gap:1rem;
    flex-wrap:wrap;
  }

  .btn-action{
    display:flex;
    align-items:center;
    gap:.55rem;
    padding:.7rem 1rem;
    border:none;
    border-radius:16px;
    background: rgba(255,255,255,.75);
    backdrop-filter:blur(12px);
    box-shadow: 0 6px 20px rgba(15,23,42,.06);
    cursor:pointer;
    transition:.25s ease;
    color:#334155;
    font-weight:700;
  }

  .btn-action:hover{
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(15,23,42,.12);
  }

  .btn-action small{
    color:#64748b;
    font-size:.78rem;
    font-weight:600;
  }

  .btn-like.liked{
    background: linear-gradient(135deg, rgba(239,68,68,.12), rgba(244,63,94,.12));
    color:#dc2626;
  }

  .comentarios-section{
    display:flex;
    flex-direction:column;
    gap:1rem;
    padding:1rem;
    border-radius:22px;
    background: rgba(255,255,255,.72);
    backdrop-filter:blur(14px);
    border: 1px solid rgba(255,255,255,.4);
    box-shadow: 0 10px 25px rgba(15,23,42,.05);
  }

  .comentarios-lista{
    display:flex;
    flex-direction:column;
    gap:.9rem;
  }

  .comentario-item{
    background: linear-gradient(180deg, #ffffff, #f8fafc);
    border: 1px solid #e2e8f0;
    border-radius:18px;
    padding:1rem;
    transition:.2s ease;
  }

  .comentario-item:hover{
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(15,23,42,.06);
  }

  .comentario-header{
    display:flex;
    align-items:center;
    gap:.75rem;
    margin-bottom:.7rem;
  }

  .comentario-avatar{
    width:42px;
    height:42px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    color:white;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
  }

  .comentario-header strong{
    color:#0f172a;
    font-size:.92rem;
  }

  .comentario-item p{
    margin:0;
    color:#475569;
    line-height:1.7;
  }

  .form-comentario{
    display:flex;
    flex-direction:column;
    gap:.8rem;
  }

  .input-comentario{
    width:100%;
    border:none;
    outline:none;
    resize:none;
    border-radius:18px;
    padding:1rem;
    font-size:.95rem;
    font-family:inherit;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    transition:.25s ease;
  }

  .input-comentario:focus{
    border-color:#2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,.12);
  }

  .comentario-footer{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
  }

  .comentario-info{
    font-size:.82rem;
    color:#94a3b8;
  }

  .btn-enviar{
    border:none;
    border-radius:14px;
    padding:.85rem 1.3rem;
    font-weight:700;
    color:white;
    cursor:pointer;
    transition:.25s ease;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    box-shadow: 0 10px 20px rgba(37,99,235,.18);
  }

  .btn-enviar:hover{
    transform: translateY(-2px);
    box-shadow: 0 15px 28px rgba(37,99,235,.25);
  }

  .btn-enviar:disabled{
    opacity:.7;
    cursor:not-allowed;
  }

  .btn-cargar-mas{
    border:none;
    padding:.9rem 1rem;
    border-radius:14px;
    background:#f1f5f9;
    color:#334155;
    cursor:pointer;
    font-weight:700;
    transition:.25s ease;
  }

  .btn-cargar-mas:hover{
    background:#e2e8f0;
  }

  .empty-comments{
    text-align:center;
    padding:2rem;
    color:#64748b;
  }

  .loading{
    text-align:center;
    padding:1rem;
    color:#64748b;
  }

  .cl-error{
    color:#dc2626;
    text-align:center;
  }

  @media (max-width:768px){
    .stats{
      gap:.7rem;
    }
    .btn-action{
      width:100%;
      justify-content:center;
    }
    .comentario-footer{
      flex-direction:column;
      align-items:stretch;
    }
    .btn-enviar{
      width:100%;
    }
  }
</style>
