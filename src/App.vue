<template>
  <!-- If guest / Login route, render full-screen view without sidebar/header -->
  <div v-if="isGuestRoute" class="min-vh-100 bg-light">
    <router-view />
  </div>

  <!-- If authenticated, render standard App Layout with Sidebar + Header -->
  <div v-else class="d-flex min-vh-100 bg-light">
    <!-- Collapsible Sidebar -->
    <AppSidebar class="d-none d-md-flex flex-shrink-0 sticky-top" />

    <!-- Main Content Area -->
    <div class="d-flex flex-column flex-grow-1 overflow-hidden">
      <!-- App Header -->
      <AppHeader />

      <!-- Page Dynamic View Container -->
      <main class="flex-grow-1 p-3 p-md-4 overflow-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppSidebar from './components/AppSidebar.vue';
import AppHeader from './components/AppHeader.vue';

const route = useRoute();

const isGuestRoute = computed(() => {
  return route.name === 'Login' || route.path === '/login';
});
</script>
