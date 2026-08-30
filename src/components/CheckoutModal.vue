<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <!-- Modal Header -->
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <i class="bi bi-wallet2"></i>
            <span>Complete Order & Payment</span>
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body p-4">
          <!-- Summary Box -->
          <div class="bg-light p-3 rounded border mb-3">
            <div class="d-flex justify-content-between text-muted small mb-1">
              <span>Subtotal:</span>
              <span>{{ currency }}{{ subtotal.toFixed(2) }}</span>
            </div>
            <div v-if="discountAmount > 0" class="d-flex justify-content-between text-danger small mb-1">
              <span>Discount:</span>
              <span>-{{ currency }}{{ discountAmount.toFixed(2) }}</span>
            </div>
            <div class="d-flex justify-content-between text-muted small mb-1">
              <span>Tax ({{ taxRate }}%):</span>
              <span>{{ currency }}{{ taxAmount.toFixed(2) }}</span>
            </div>
            <div class="d-flex justify-content-between fs-4 fw-bold text-dark border-top pt-2 mt-2">
              <span>Grand Total:</span>
              <span class="text-primary">{{ currency }}{{ grandTotal.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Payment Method Selection -->
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Select Payment Method</label>
            <div class="row g-2">
              <div class="col-4">
                <input
                  type="radio"
                  class="btn-check"
                  name="paymentOption"
                  id="payCash"
                  value="cash"
                  v-model="selectedMethod"
                />
                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center" for="payCash">
                  <i class="bi bi-cash-stack fs-4 mb-1"></i>
                  <span class="small fw-semibold">Cash</span>
                </label>
              </div>

              <div class="col-4">
                <input
                  type="radio"
                  class="btn-check"
                  name="paymentOption"
                  id="payCard"
                  value="card"
                  v-model="selectedMethod"
                />
                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center" for="payCard">
                  <i class="bi bi-credit-card fs-4 mb-1"></i>
                  <span class="small fw-semibold">Card</span>
                </label>
              </div>

              <div class="col-4">
                <input
                  type="radio"
                  class="btn-check"
                  name="paymentOption"
                  id="payOnline"
                  value="online"
                  v-model="selectedMethod"
                />
                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center" for="payOnline">
                  <i class="bi bi-qr-code-scan fs-4 mb-1"></i>
                  <span class="small fw-semibold">Online / QR</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Cash Payment Details -->
          <div v-if="selectedMethod === 'cash'" class="mb-3 p-3 bg-light rounded border">
            <label class="form-label fw-semibold small text-secondary">Cash Amount Received</label>
            <div class="input-group mb-2">
              <span class="input-group-text bg-white fw-bold">{{ currency }}</span>
              <input
                type="number"
                step="any"
                min="0"
                class="form-control form-control-lg fw-bold"
                v-model.number="amountReceived"
                placeholder="0.00"
                autofocus
              />
            </div>

            <!-- Quick Cash Presets -->
            <div class="d-flex gap-2 mb-3">
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                @click="amountReceived = grandTotal"
              >
                Exact ({{ currency }}{{ grandTotal.toFixed(2) }})
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                @click="amountReceived = Math.ceil(grandTotal / 5) * 5"
              >
                {{ currency }}{{ (Math.ceil(grandTotal / 5) * 5).toFixed(2) }}
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                @click="amountReceived = Math.ceil(grandTotal / 10) * 10"
              >
                {{ currency }}{{ (Math.ceil(grandTotal / 10) * 10).toFixed(2) }}
              </button>
            </div>

            <!-- Change Return Calculation -->
            <div class="d-flex justify-content-between align-items-center p-2 rounded" :class="isPaymentValid ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'">
              <span class="fw-semibold">Change to Return:</span>
              <span class="fs-5 fw-bold">{{ currency }}{{ changeAmount.toFixed(2) }}</span>
            </div>
            <div v-if="!isPaymentValid" class="small text-danger mt-1">
              Amount received must be at least {{ currency }}{{ grandTotal.toFixed(2) }}
            </div>
          </div>

          <!-- Error Alert if any -->
          <div v-if="errorMessage" class="alert alert-danger py-2 small mb-0">
            <i class="bi bi-exclamation-circle me-1"></i> {{ errorMessage }}
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-secondary" :disabled="processing" @click="$emit('close')">
            Cancel
          </button>
          <button
            type="button"
            class="btn btn-success px-4"
            :disabled="!isPaymentValid || processing"
            @click="submitCheckout"
          >
            <span v-if="processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i v-else class="bi bi-check2-circle me-1"></i>
            {{ processing ? 'Processing...' : 'Confirm & Print Receipt' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  subtotal: Number,
  discountType: String,
  discountValue: Number,
  discountAmount: Number,
  taxRate: Number,
  taxAmount: Number,
  grandTotal: Number,
  currency: {
    type: String,
    default: '$'
  },
  processing: Boolean,
  errorMessage: String
});

const emit = defineEmits(['close', 'submit-order']);

const selectedMethod = ref('cash');
const amountReceived = ref(0);

// Auto-fill amount received when modal opens or total changes
watch(
  () => props.grandTotal,
  (newVal) => {
    amountReceived.value = newVal;
  },
  { immediate: true }
);

watch(selectedMethod, (newMethod) => {
  if (newMethod !== 'cash') {
    amountReceived.value = props.grandTotal;
  }
});

const changeAmount = computed(() => {
  if (selectedMethod.value !== 'cash') return 0;
  const received = Number(amountReceived.value) || 0;
  return received >= props.grandTotal ? received - props.grandTotal : 0;
});

const isPaymentValid = computed(() => {
  if (selectedMethod.value === 'cash') {
    return (Number(amountReceived.value) || 0) >= props.grandTotal;
  }
  return true;
});

const submitCheckout = () => {
  if (!isPaymentValid.value) return;

  emit('submit-order', {
    payment_method: selectedMethod.value,
    amount_received: selectedMethod.value === 'cash' ? Number(amountReceived.value) : props.grandTotal
  });
};
</script>
