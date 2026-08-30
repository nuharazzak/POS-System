<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6);">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header border-0 pb-0 no-print">
          <h6 class="modal-title fw-bold text-success">
            <i class="bi bi-check-circle-fill me-1"></i> Order Completed
          </h6>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Printable Receipt Area -->
          <div id="printable-receipt" class="bg-white p-3 border rounded text-dark font-monospace" style="font-size: 0.85rem;">
            <!-- Header -->
            <div class="text-center mb-3">
              <h5 class="fw-bold mb-0 text-uppercase">{{ storeName }}</h5>
              <div class="small text-muted">{{ storeAddress }}</div>
              <div class="small text-muted">Tel: {{ storePhone }}</div>
              <div class="fw-bold my-2 text-decoration-underline">SALES RECEIPT</div>
            </div>

            <div class="border-top border-bottom py-1 mb-2 small">
              <div class="d-flex justify-content-between">
                <span>Order:</span>
                <span class="fw-bold">{{ order?.order_number }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Date:</span>
                <span>{{ orderDate }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Cashier:</span>
                <span>{{ order?.user_name || cashierName }}</span>
              </div>
            </div>

            <!-- Items table -->
            <table class="table table-sm table-borderless mb-2" style="font-size: 0.82rem;">
              <thead>
                <tr class="border-bottom text-secondary">
                  <th>Item</th>
                  <th class="text-center">Qty</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in order?.items" :key="item.id">
                  <td>
                    <div>{{ item.product_name || item.name }}</div>
                    <small class="text-muted">{{ currency }}{{ Number(item.unit_price || item.price).toFixed(2) }}</small>
                  </td>
                  <td class="text-center align-middle">{{ item.quantity }}</td>
                  <td class="text-end align-middle fw-bold">
                    {{ currency }}{{ Number(item.total_price || (item.price * item.quantity)).toFixed(2) }}
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Financials -->
            <div class="border-top pt-2 small">
              <div class="d-flex justify-content-between mb-1">
                <span>Subtotal:</span>
                <span>{{ currency }}{{ Number(order?.subtotal || 0).toFixed(2) }}</span>
              </div>
              <div v-if="Number(order?.discount_amount) > 0" class="d-flex justify-content-between mb-1 text-danger">
                <span>Discount:</span>
                <span>-{{ currency }}{{ Number(order?.discount_amount).toFixed(2) }}</span>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span>Tax ({{ Number(order?.tax_rate || 10) }}%):</span>
                <span>{{ currency }}{{ Number(order?.tax_amount || 0).toFixed(2) }}</span>
              </div>
              <div class="d-flex justify-content-between fs-6 fw-bold border-top border-bottom py-1 my-1">
                <span>TOTAL:</span>
                <span>{{ currency }}{{ Number(order?.total_amount || 0).toFixed(2) }}</span>
              </div>
            </div>

            <!-- Payment details -->
            <div class="small mt-2 pt-1 border-top">
              <div class="d-flex justify-content-between">
                <span>Payment Method:</span>
                <span class="text-uppercase fw-semibold">{{ order?.payment_method }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Amount Received:</span>
                <span>{{ currency }}{{ Number(order?.amount_received || 0).toFixed(2) }}</span>
              </div>
              <div class="d-flex justify-content-between fw-bold text-success">
                <span>Change:</span>
                <span>{{ currency }}{{ Number(order?.change_amount || 0).toFixed(2) }}</span>
              </div>
            </div>

            <!-- Footer greeting -->
            <div class="text-center mt-4 pt-2 border-top">
              <p class="mb-0 fw-bold">*** THANK YOU! ***</p>
              <small class="text-muted">Please visit us again soon!</small>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light no-print d-flex justify-content-between py-2">
          <button type="button" class="btn btn-outline-secondary" @click="$emit('close')">
            Close
          </button>
          <button type="button" class="btn btn-primary d-flex align-items-center gap-2 px-3" @click="printReceipt">
            <i class="bi bi-printer-fill"></i>
            <span>Print Receipt</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: Boolean,
  order: Object,
  storeName: {
    type: String,
    default: 'My Cafe & Bistro'
  },
  storeAddress: {
    type: String,
    default: '45 Bistro Lane, Downtown'
  },
  storePhone: {
    type: String,
    default: '+1 (555) 839-2041'
  },
  currency: {
    type: String,
    default: '$'
  },
  cashierName: {
    type: String,
    default: 'Cashier'
  }
});

defineEmits(['close']);

const orderDate = computed(() => {
  if (props.order?.created_at) {
    return new Date(props.order.created_at).toLocaleString();
  }
  return new Date().toLocaleString();
});

const printReceipt = () => {
  window.print();
};
</script>
