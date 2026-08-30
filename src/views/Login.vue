<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
    <div class="card border-0 shadow-lg" style="max-width: 420px; width: 100%;">
      <div class="card-body p-4 p-md-5">
        <!-- Logo & Title -->
        <div class="text-center mb-4">
          <div class="d-inline-flex p-3 bg-primary bg-opacity-10 text-primary rounded-circle mb-2">
            <i class="bi bi-shop-window fs-1"></i>
          </div>
          <h4 class="fw-bold text-dark mb-1">Restaurant POS</h4>
          <p class="text-muted small">Sign in to start your cashier or management shift</p>
        </div>

        <!-- Error Alert -->
        <AlertMessage
          v-if="errorMessage"
          :message="errorMessage"
          variant="danger"
          @dismiss="errorMessage = ''"
        />

        <!-- Login Form -->
        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary">Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
              <input
                type="email"
                class="form-control"
                v-model="email"
                placeholder="name@example.com"
                required
                autocomplete="email"
              />
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-semibold text-secondary">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
              <input
                :type="showPassword ? 'text' : 'password'"
                class="form-control"
                v-model="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
              />
              <button
                type="button"
                class="btn btn-outline-secondary"
                @click="showPassword = !showPassword"
              >
                <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </button>
            </div>
          </div>

          <button
            type="submit"
            class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
            <i v-else class="bi bi-box-arrow-in-right"></i>
            <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
          </button>
        </form>

        <!-- Demo Accounts Helper -->
        <div class="mt-4 pt-3 border-top text-center">
          <p class="small text-muted mb-2 fw-semibold">Quick Demo Login:</p>
          <div class="d-flex justify-content-center gap-2">
            <button
              type="button"
              class="btn btn-outline-primary btn-sm"
              @click="fillDemo('admin@example.com', 'password')"
            >
              <i class="bi bi-shield-lock me-1"></i> Admin
            </button>
            <button
              type="button"
              class="btn btn-outline-secondary btn-sm"
              @click="fillDemo('cashier@example.com', 'password')"
            >
              <i class="bi bi-person me-1"></i> Cashier
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { authService } from '../services/api';
import { useAuth } from '../composables/useAuth';
import AlertMessage from '../components/AlertMessage.vue';

const router = useRouter();
const { setUser } = useAuth();

const email = ref('admin@example.com');
const password = ref('password');
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');

const fillDemo = (demoEmail, demoPass) => {
  email.value = demoEmail;
  password.value = demoPass;
};

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await authService.login({
      email: email.value,
      password: password.value
    });

    if (response.data.success) {
      const { token, user } = response.data.data;
      setUser(user, token);
      router.push('/pos');
    }
  } catch (error) {
    if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Failed to connect to backend server. Please verify Laravel is running.';
    }
  } finally {
    loading.value = false;
  }
};
</script>
