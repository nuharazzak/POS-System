<template>
  <header class="navbar navbar-expand bg-white border-bottom px-3 py-2 sticky-top shadow-sm">
    <div class="container-fluid p-0 d-flex justify-content-between align-items-center">
      <!-- Left side: Title/Breadcrumb -->
      <div class="d-flex align-items-center">
        <h5 class="mb-0 fw-bold text-dark">{{ pageTitle }}</h5>
      </div>

      <!-- Right side: Quick stats / Actions / Logout -->
      <div class="d-flex align-items-center gap-3">
        <div class="d-none d-md-flex align-items-center text-muted small">
          <i class="bi bi-clock me-1"></i>
          <span>{{ currentTime }}</span>
        </div>

        <div class="vr mx-1 d-none d-md-block"></div>

        <div class="dropdown">
          <button
            class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            @click="dropdownOpen = !dropdownOpen"
          >
            <i class="bi bi-person-circle fs-6"></i>
            <span class="d-none d-sm-inline">{{ currentUser?.name }}</span>
          </button>
          
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" :class="{ show: dropdownOpen }">
            <li class="dropdown-header">
              Signed in as <strong>{{ currentUser?.email }}</strong>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li v-if="isAdmin">
              <router-link to="/settings" class="dropdown-item d-flex align-items-center gap-2" @click="dropdownOpen = false">
                <i class="bi bi-gear"></i> Settings
              </router-link>
            </li>
            <li>
              <button class="dropdown-item text-danger d-flex align-items-center gap-2" @click="handleLogout">
                <i class="bi bi-box-arrow-right"></i> Logout
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import { authService } from '../services/api';

const route = useRoute();
const router = useRouter();
const { currentUser, isAdmin, clearAuth } = useAuth();

const dropdownOpen = ref(false);
const currentTime = ref('');

let timer = null;
const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});

const pageTitle = computed(() => {
  return route.meta?.title || 'Point of Sale';
});

const handleLogout = async () => {
  dropdownOpen.value = false;
  try {
    await authService.logout();
  } catch (err) {
    console.warn('Logout API response error:', err);
  } finally {
    clearAuth();
    router.push('/login');
  }
};
</script>
