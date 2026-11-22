<script setup>
import { ref, onMounted } from 'vue'
import { getMe, logout } from '@/lib/auth.js'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
import WelcomeTour from '@/components/vue/WelcomeTour.vue'
import PublicacionesView from '@/components/publicaciones/PublicacionesView.vue'
import PodcastView from '@/components/podcast/PodcastView.vue'

const user = ref({})
const tab = ref('publicaciones')

onMounted(async () => {
  try {
    user.value = await getMe()
  } catch (err) {
    console.error(err)
  }
})
</script>

<template>
  <div class="navbar-fixed">
    <Navbar v-bind="user" />
  </div>

  <aside class="sidebar-area">
    <Sidebar />
    <div class="tour-slot">
      <WelcomeTour auto />
    </div>
  </aside>

  <main class="dashboard">
    <div class="tabs">
      <button class="tab" :class="{ active: tab === 'publicaciones' }" @click="tab = 'publicaciones'">
        Publicaciones
      </button>
      <button class="tab" :class="{ active: tab === 'podcast' }" @click="tab = 'podcast'">
        Podcast
      </button>
    </div>

    <div class="view" :class="{ active: tab === 'publicaciones' }">
      <PublicacionesView v-if="tab === 'publicaciones'" :current-user-id="user.id" />
    </div>

    <div class="view" :class="{ active: tab === 'podcast' }">
      <PodcastView v-if="tab === 'podcast'" :current-user-id="user.id" />
    </div>
  </main>
</template>

<style scoped>

    :global(:root){

    --primary:#2563eb;

    --secondary:#7c3aed;

    --dark:#0f172a;

    --muted:#64748b;

    --border:#e2e8f0;
  }

    :global(body){

    margin:0;

    background:
      linear-gradient(
        180deg,
        #f8fafc 0%,
        #eef2ff 100%
      );

    font-family:
      Inter,
      system-ui,
      sans-serif;
  }

  /* NAVBAR */

  .navbar-fixed{

    position:fixed;

    top:0;

    width:100%;

    z-index:1000;

    background:
      rgba(255,255,255,.85);

    backdrop-filter:blur(16px);

    border-bottom:
      1px solid rgba(226,232,240,.7);
  }

  /* SIDEBAR */

  .sidebar-area{

    position:fixed;

    top:90px;

    left:18px;

    width:220px;

    z-index:50;
  }

  .tour-slot{
    margin-top:1rem;
  }

  /* DASHBOARD */

  .dashboard{

    max-width:860px;

    margin:auto;

    padding:
      100px
      1.5rem
      4rem;
  }

  /* TABS */

  .tabs{

    display:flex;

    gap:.5rem;

    margin-bottom:2rem;

    padding:.4rem;

    border-radius:18px;

    background:
      rgba(255,255,255,.75);

    backdrop-filter:blur(14px);

    border:
      1px solid rgba(255,255,255,.5);

    box-shadow:
      0 10px 30px rgba(15,23,42,.05);
  }

  .tab{

    flex:1;

    position:relative;

    padding:
      1rem
      1.4rem;

    border:none;

    border-radius:14px;

    background:none;

    cursor:pointer;

    font-size:.95rem;

    font-weight:700;

    color:#64748b;

    transition:.25s ease;
  }

  .tab:hover{

    color:#0f172a;

    background:
      rgba(241,245,249,.9);
  }

  .tab.active{

    color:white;

    background:
      linear-gradient(
        135deg,
        var(--primary),
        var(--secondary)
      );

    box-shadow:
      0 10px 25px rgba(37,99,235,.2);
  }

  /* VIEWS */

  .view{

    display:none;
  }

  .view.active{

    display:block;

    animation:
      fadein .25s ease;
  }

  @keyframes fadein{

    from{

      opacity:0;

      transform:
        translateY(10px);
    }

    to{

      opacity:1;

      transform:
        translateY(0);
    }
  }

  /* CHATBOT */

  .chatbot-area{

    position:fixed;

    top:90px;

    right:20px;

    width:320px;

    z-index:100;
  }

  /* RESPONSIVE */

  @media (max-width:1400px){

    .chatbot-area{

      width:280px;
    }
  }

  @media (max-width:1200px){

    .chatbot-area{

      display:none;
    }

    .dashboard{

      max-width:820px;
    }
  }

  @media (max-width:768px){

    .sidebar-area{

      display:none;
    }

    .dashboard{

      width:95%;

      padding:
        90px
        1rem
        3rem;
    }

    .tab{

      font-size:.85rem;

      padding:.9rem;
    }
  }

</style>
