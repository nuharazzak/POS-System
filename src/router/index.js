import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../composables/useAuth';

// Views
import Login from '../views/Login.vue';
import Dashboard from '../views/Dashboard.vue';
import POS from '../views/POS.vue';
import Products from '../views/Products.vue';
import Categories from '../views/Categories.vue';
import Sales from '../views/Sales.vue';
import Reports from '../views/Reports.vue';
import Settings from '../views/Settings.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guestOnly: true, title: 'Sign In' }
  },
  {
    path: '/',
    redirect: '/pos'
  },
  {
    path: '/pos',
    name: 'POS',
    component: POS,
    meta: { requiresAuth: true, title: 'POS Register' }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true, title: 'Dashboard' }
  },
  {
    path: '/sales',
    name: 'Sales',
    component: Sales,
    meta: { requiresAuth: true, title: 'Sales History' }
  },
  // Admin-only routes
  {
    path: '/products',
    name: 'Products',
    component: Products,
    meta: { requiresAuth: true, requiresAdmin: true, title: 'Product Catalog' }
  },
  {
    path: '/categories',
    name: 'Categories',
    component: Categories,
    meta: { requiresAuth: true, requiresAdmin: true, title: 'Category Management' }
  },
  {
    path: '/reports',
    name: 'Reports',
    component: Reports,
    meta: { requiresAuth: true, requiresAdmin: true, title: 'Sales Reports' }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
    meta: { requiresAuth: true, requiresAdmin: true, title: 'Store Settings' }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/pos'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation Guards: Enforce authentication and role permissions
router.beforeEach((to, from, next) => {
  const { isAuthenticated, isAdmin } = useAuth();

  // Document Title update
  document.title = to.meta.title ? `${to.meta.title} — QuickPOS` : 'QuickPOS';

  // If already authenticated and trying to visit /login
  if (to.meta.guestOnly && isAuthenticated.value) {
    return next('/pos');
  }

  // If requires authentication and not logged in
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return next('/login');
  }

  // If route is restricted to admins only
  if (to.meta.requiresAdmin && !isAdmin.value) {
    return next('/pos');
  }

  next();
});

export default router;
