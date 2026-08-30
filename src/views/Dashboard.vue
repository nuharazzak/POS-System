<template>
  <div class="row g-4">
    <!-- Top KPI Cards -->
    <div class="col-12 col-sm-6 col-xl-3">
      <SummaryCard
        title="Today's Sales"
        :value="`${currency}${stats.today_sales.toFixed(2)}`"
        icon="bi-currency-dollar"
        color="success"
        subtitle="Total revenue collected today"
      />
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
      <SummaryCard
        title="Today's Orders"
        :value="stats.today_orders"
        icon="bi-receipt-cutoff"
        color="primary"
        subtitle="Completed order tickets"
      />
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
      <SummaryCard
        title="Active Products"
        :value="stats.total_products"
        icon="bi-box-seam"
        color="info"
        subtitle="Available on menu"
      />
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
      <SummaryCard
        title="Low Stock Items"
        :value="stats.low_stock_products"
        icon="bi-exclamation-triangle"
        :color="stats.low_stock_products > 0 ? 'warning' : 'secondary'"
        :subtitle="`Below threshold (${stats.low_stock_threshold})`"
      />
    </div>

    <!-- Quick Action / Start POS Banner -->
    <div class="col-12">
      <div class="card border-0 bg-primary text-white shadow-sm p-4 rounded-3 d-flex flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
          <h4 class="fw-bold mb-1">Welcome back, {{ currentUser?.name }}!</h4>
          <p class="mb-0 text-white-50">Ready to take orders? Click below to jump straight to the cash register.</p>
        </div>
        <router-link to="/pos" class="btn btn-light btn-lg text-primary fw-bold px-4 d-inline-flex align-items-center gap-2 shadow">
          <i class="bi bi-calculator"></i>
          <span>Open POS Register</span>
        </router-link>
      </div>
    </div>

    <!-- Recent Orders & Best Selling Products -->
    <div class="col-12 col-lg-7">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-clock-history me-1 text-primary"></i> Recent Orders
          </h6>
          <router-link to="/sales" class="btn btn-sm btn-outline-primary">View All</router-link>
        </div>
        <div class="card-body p-0 table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Order #</th>
                <th>Time</th>
                <th>Cashier</th>
                <th>Payment</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="recentOrders.length === 0">
                <td colspan="5" class="text-center py-4 text-muted">No sales orders recorded yet today.</td>
              </tr>
              <tr v-for="order in recentOrders" :key="order.id">
                <td class="fw-semibold text-primary">{{ order.order_number }}</td>
                <td class="small text-muted">{{ formatTime(order.created_at) }}</td>
                <td>{{ order.user_name || 'Cashier' }}</td>
                <td>
                  <span class="badge bg-light text-dark border text-uppercase">{{ order.payment_method }}</span>
                </td>
                <td class="text-end fw-bold">{{ currency }}{{ Number(order.total_amount).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-bottom py-3">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-star-fill text-warning me-1"></i> Best Selling Products
          </h6>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            <li v-if="bestSellers.length === 0" class="list-group-item text-center py-4 text-muted border-0">
              No sales data available yet.
            </li>
            <li
              v-for="(item, index) in bestSellers"
              :key="item.product_id"
              class="list-group-item d-flex justify-content-between align-items-center py-3 px-3 border-0 border-bottom"
            >
              <div class="d-flex align-items-center">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle me-3 fw-bold" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                  #{{ index + 1 }}
                </span>
                <div>
                  <div class="fw-semibold text-dark">{{ item.product_name }}</div>
                  <small class="text-muted">{{ item.quantity_sold }} items sold</small>
                </div>
              </div>
              <span class="fw-bold text-success">{{ currency }}{{ Number(item.revenue).toFixed(2) }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { reportService, settingService } from '../services/api';
import { useAuth } from '../composables/useAuth';
import SummaryCard from '../components/SummaryCard.vue';

const { currentUser } = useAuth();
const currency不易 = ref('$');
const currency = ref('$');

const stats = ref({
  today_sales: 0,
  today_orders: 0,
  total_products: 0,
  low_stock_products: 0,
  low_stock_threshold: 5
});

const recentOrders = ref([]);
const bestSellers = ref([]);

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const fetchDashboardData = async () => {
  try {
    const [dashRes, settingsRes] = await Promise.all([
      reportService.getDashboardSummary(),
      settingService.get()
    ]);

    if (dashRes.data.success) {
      stats.value = dashRes.data.data.stats;
      recentOrders.value = dashRes.data.data.recent_orders || [];
      bestSellers.value = dashRes.data.data.best_sellers || [];
    }

    if (settingsRes.data.success) {
      currency.value = settingsRes.data.data.currency || '$';
    }
  } catch (err) {
    console.warn('Dashboard fetch error:', err);
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>
