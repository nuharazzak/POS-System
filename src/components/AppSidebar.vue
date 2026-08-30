<template>
  <aside
    class="bg-dark text-white d-flex flex-column flex-shrink-0 p-3"
    style="width: 250px; min-height: 100vh;"
  >
    <!-- Brand -->
    <router-link to="/pos" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none px-2">
      <i class="bi bi-shop-window fs-3 text-primary me-2"></i>
      <div>
        <span class="fs-5 fw-bold d-block lh-1">QuickPOS</span>
        <small class="text-secondary text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Cafe & Restaurant</small>
      </div>
    </router-link>

    <hr class="border-secondary opacity-50 my-3" />

    <!-- Navigation links -->
    <ul class="nav nav-pills flex-column mb-auto gap-1">
      <li class="nav-item">
        <router-link to="/pos" class="nav-link text-white d-flex align-items-center" active-class="active bg-primary">
          <i class="bi bi-calculator fs-5 me-3"></i>
          <span>POS / New Sale</span>
        </router-link>
      </li>

      <li class="nav-item">
        <router-link to="/dashboard" class="nav-link text-white d-flex align-items-center" active-class="active bg-primary">
          <i class="bi bi-speedometer2 fs-5 me-3"></i>
          <span>Dashboard</span>
        </router-link>
      </li>

      <li class="nav-item">
        <router-link to="/sales" class="nav-link text-white d-flex align-items-center" active-class="active bg-primary">
          <i class="bi bi-receipt fs-5 me-3"></i>
          <span>Sales History</span>
        </router-link>
      </li>

      <!-- Admin only sections -->
      <template v-if="isAdmin">
        <li class="nav-header text-uppercase text-secondary fw-semibold px-3 pt-3 pb-1" style="font-size: 0.75rem;">
          Management
        </li>

        <li class="nav-item">
          <router-link to="/products" class="nav-link text-white d-flex align-items-center" active-class="active bg-primary">
            <i class="bi bi-box-seam fs-5 me-3"></i>
            <span>Products</span>
          </router-link>
        </li>

        <li class="nav-item">
          <router-link to="/categories" class="nav-link text-white d-flex align-items-center" active-class="active bg-primary">
            <i class="bi bi-tags fs-5 me-3"></i>
            <span>Categories</span>
          </router-link>
        </li>

        <li class="nav-item">
          <router-link to="/reports" class="nav-link text-white d-flex align-items-center" active-class="active bg-primary">
            <i class="bi bi-bar-chart fs-5 me-3"></i>
            <span>Reports</span>
          </router-link>
        </li>

        <li class="nav-item">
          <router-link to="/settings" class="nav-link text-white d-flex align-items-center" active-class="active bg-primary">
            <i class="bi bi-gear fs-5 me-3"></i>
            <span>Settings</span>
          </router-link>
        </li>
      </template>
    </ul>

    <hr class="border-secondary opacity-50 my-3" />

    <!-- Current User Info & Role Badge -->
    <div class="d-flex align-items-center px-2 py-1">
      <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-weight: 600;">
        {{ currentUser?.name ? currentUser.name.charAt(0).toUpperCase() : 'U' }}
      </div>
      <div class="overflow-hidden me-auto">
        <div class="fw-semibold text-truncate text-white small">{{ currentUser?.name || 'User' }}</div>
        <span class="badge" :class="isAdmin ? 'bg-danger' : 'bg-info text-dark'" style="font-size: 0.65rem;">
          {{ currentUser?.role?.toUpperCase() || 'CASHIER' }}
        </span>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { useAuth } from '../composables/useAuth';

const { currentUser, isAdmin } = useAuth();
</script>
