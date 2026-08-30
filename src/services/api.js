import axios from 'axios';

// Create configured Axios instance
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

// Request Interceptor: Attach Sanctum Bearer token from localStorage
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('pos_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response Interceptor: Handle 401 Unauthorized by logging out & redirecting
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('pos_token');
      localStorage.removeItem('pos_user');
      if (window.location.pathname !== '/login') {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

// Organized API Services
export const authService = {
  login(credentials) {
    return apiClient.post('/auth/login', credentials);
  },
  getUser() {
    return apiClient.get('/auth/user');
  },
  logout() {
    return apiClient.post('/auth/logout');
  }
};

export const categoryService = {
  getAll() {
    return apiClient.get('/categories');
  },
  getById(id) {
    return apiClient.get(`/categories/${id}`);
  },
  create(data) {
    return apiClient.post('/categories', data);
  },
  update(id, data) {
    return apiClient.put(`/categories/${id}`, data);
  },
  delete(id) {
    return apiClient.delete(`/categories/${id}`);
  }
};

export const productService = {
  getAll(params = {}) {
    return apiClient.get('/products', { params });
  },
  getById(id) {
    return apiClient.get(`/products/${id}`);
  },
  create(data) {
    return apiClient.post('/products', data);
  },
  update(id, data) {
    return apiClient.put(`/products/${id}`, data);
  },
  delete(id) {
    return apiClient.delete(`/products/${id}`);
  }
};

export const orderService = {
  create(orderData) {
    return apiClient.post('/orders', orderData);
  },
  getAll(params = {}) {
    return apiClient.get('/orders', { params });
  },
  getById(id) {
    return apiClient.get(`/orders/${id}`);
  }
};

export const reportService = {
  getDashboardSummary() {
    return apiClient.get('/reports/dashboard');
  },
  getSalesReport(params = {}) {
    return apiClient.get('/reports/sales', { params });
  },
  getBestSellingProducts() {
    return apiClient.get('/reports/best-selling-products');
  }
};

export const settingService = {
  get() {
    return apiClient.get('/settings');
  },
  update(data) {
    return apiClient.put('/settings', data);
  }
};

export default apiClient;
