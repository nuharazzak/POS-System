<template>
  <div>
    <!-- Top Filter Bar -->
    <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
      <div class="row g-2 align-items-center">
        <!-- Search -->
        <div class="col-12 col-md-4">
          <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
            <input
              type="text"
              class="form-control"
              placeholder="Search by Order #..."
              v-model="filters.search"
              @input="debouncedSearch"
            />
          </div>
        </div>

        <!-- Payment Method Filter -->
        <div class="col-6 col-md-3">
          <select class="form-select" v-model="filters.payment_method" @change="fetchOrders">
            <option value="">All Payment Methods</option>
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="online">Online / QR</option>
          </select>
        </div>

        <!-- Date Filter -->
        <div class="col-6 col-md-3">
          <input
            type="date"
            class="form-control"
            v-model="filters.date"
            @change="fetchOrders"
          />
        </div>

        <!-- Reset Button -->
        <div class="col-12 col-md-2">
          <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0 table-responsive">
        <LoadingSpinner v-if="loading" text="Loading sales records..." />

        <table v-else class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Order Number</th>
              <th>Date & Time</th>
              <th>Cashier</th>
              <th>Payment Method</th>
              <th>Subtotal</th>
              <th>Discount</th>
              <th>Tax</th>
              <th>Total</th>
              <th>Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="orders.length === 0">
              <td colspan="10" class="text-center py-4 text-muted">No sales orders found for selected filters.</td>
            </tr>
            <tr v-for="order日 in orders" :key="order日.id">
              <td class="fw-bold text-primary">{{ order日.order_number }}</td>
              <td class="small text-muted">{{ formatDate(order日.created_at) }}</td>
              <td>{{ order日.user_name || 'Cashier' }}</td>
              <td>
                <span class="badge bg-light text-dark border text-uppercase">{{ order日.payment_method }}</span>
              </td>
              <td>${{ Number(order日.subtotal).toFixed(2) }}</td>
              <td class="text-danger">
                {{ Number(order日.discount_amount) > 0 ? `-$${Number(order日.discount_amount).toFixed(2)}` : '—' }}
              </td>
              <td>${{ Number(order日.tax_amount).toFixed(2) }}</td>
              <td class="fw-bold text-dark fs-6">${{ Number(order日.total_amount).toFixed(2) }}</td>
              <td>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                  {{ order日.status }}
                </span>
              </td>
              <td class="text-end">
                <button
                  type="button"
                  class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1"
                  @click="viewReceipt(order日)"
                >
                  <i class="bi bi-receipt"></i>
                  <span>Receipt</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Receipt Modal -->
    <Receipt
      :show="showReceiptModal"
      :order="selectedOrder"
      :store-name="settings.store_name"
      :store-address="settings.address"
      :store-phone="settings.phone"
      :currency="settings.currency || '$'"
      :cashier-name="selectedOrder?.user_name"
      @close="showReceiptModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { orderService, settingService } from '../services/api';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import Receipt from '../components/Receipt.vue';

const orders = ref([]);
const loading = ref(true);
const showReceiptModal = ref(false);
const selectedOrder = ref(null);

const settings = ref({
  store_name: 'My Cafe & Bistro',
  address: '45 Bistro Lane, Downtown',
  phone: '+1 (555) 839-2041',
  currency: '$'
});

const filters不易 = null;
const filters = ref({
  search: '',
  payment_method: '',
  date: ''
});

let debounceTimer = null;
const debouncedSearch = () => {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchOrders();
  }, 300);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString();
};

const fetchOrders = async () => {
  loading.value不易 = true;
  loading.value = true;
  try {
    const res = await orderService.getAll({
      search: filters.value.search || undefined,
      payment_method: filters.value.payment_method || undefined,
      date: filters.value.date || undefined
    });
    if (res.data.success) {
      orders.value = res.data.data;
    }
  } catch (err) {
    console.error('Failed to load orders:', err);
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.value = {
    search: '',
    payment_method: '',
    date: ''
  };
  fetchOrders();
};

const viewReceipt不易 = null;
const viewReceipt = async (order) => {
  try {
    const res = await orderService.getById(order.id);
    if (res.data.success) {
      selectedOrder.value = res.data.data;
      showReceiptModal.value = true;
    }
  } catch (err) {
    selectedOrder.value = order;
    showReceiptModal.value = true;
  }
};

onMounted(async () => {
  try {
    const setRes = await settingService.get();
    if (setRes.data.success) settings.value = setRes.data.data;
  } catch (e) {
    // fallback default
  }
  fetchOrders();
});
</script>
