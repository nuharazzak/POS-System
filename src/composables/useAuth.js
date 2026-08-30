import { reactive, computed } from 'vue';

// Global user state loaded from localStorage
const storedUser = localStorage.getItem('pos_user');
const state = reactive({
  user: storedUser ? JSON.parse(storedUser) : null,
  token: localStorage.getItem('pos_token') || null,
  settings: {
    store_name: 'My Cafe & Bistro',
    currency: '$',
    tax_rate: 10.0,
    low_stock_threshold: 5
  }
});

export function useAuth() {
  const isAuthenticated = computed(() => !!state.token);
  const isAdmin = computed(() => state.user?.role === 'admin');
  const isCashier = computed(() => state.user?.role === 'cashier');
  const currentUser = computed(() => state.user);

  function setUser(userData, token) {
    state.user = userData;
    state.token = token;
    localStorage.setItem('pos_user', JSON.stringify(userData));
    localStorage.setItem('pos_token', token);
  }

  function clearAuth() {
    state.user = null;
    state.token = null;
    localStorage.removeItem('pos_user');
    localStorage.removeItem('pos_token');
  }

  function updateSettings(newSettings) {
    state.settings = { ...state.settings, ...newSettings };
  }

  return {
    state,
    isAuthenticated,
    isAdmin,
    isCashier,
    currentUser,
    setUser,
    clearAuth,
    updateSettings
  };
}
