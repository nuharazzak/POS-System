<template>
  <div class="card border shadow-sm h-100 d-flex flex-column bg-white">
    <!-- Cart Header -->
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-cart3 fs-5 text-primary"></i>
        <h6 class="mb-0 fw-bold">Current Order</h6>
      </div>
      <span class="badge bg-primary rounded-pill">{{ totalItemCount }} items</span>
    </div>

    <!-- Cart Items Scrollable List -->
    <div class="card-body p-0 flex-grow-1 overflow-auto" style="max-height: calc(100vh - 460px); min-height: 200px;">
      <div v-if="cartItems.length === 0" class="text-center py-5 text-muted">
        <i class="bi bi-cart-x fs-1 opacity-50 d-block mb-2"></i>
        <p class="mb-0 fw-semibold">Cart is empty</p>
        <small class="text-secondary">Click products on the left to add items</small>
      </div>

      <ul v-else class="list-group list-group-flush">
        <CartItem
          v-for="item in cartItems"
          :key="item.id"
          :item="item"
          :currency="currency"
          @update-qty="$emit('update-qty', $event)"
          @remove-item="$emit('remove-item', $event)"
        />
      </ul>
    </div>

    <!-- Cart Financial Summary Section -->
    <div class="card-footer bg-light border-top p-3 mt-auto">
      <!-- Discount Controls -->
      <div class="mb-2">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <small class="fw-semibold text-secondary">Discount</small>
          <div class="btn-group btn-group-sm" role="group">
            <input
              type="radio"
              class="btn-check"
              name="discountType"
              id="discPct"
              value="percentage"
              :checked="discountType === 'percentage'"
              @change="$emit('update-discount-type', 'percentage')"
            />
            <label class="btn btn-outline-secondary btn-sm py-0 px-2" for="discPct">%</label>

            <input
              type="radio"
              class="btn-check"
              name="discountType"
              id="discFix"
              value="fixed"
              :checked="discountType === 'fixed'"
              @change="$emit('update-discount-type', 'fixed')"
            />
            <label class="btn btn-outline-secondary btn-sm py-0 px-2" for="discFix">{{ currency }}</label>
          </div>
        </div>

        <div class="input-group input-group-sm">
          <span class="input-group-text bg-white">
            <i class="bi bi-tag"></i>
          </span>
          <input
            type="number"
            min="0"
            :max="discountType === 'percentage' ? 100 : subtotal"
            step="any"
            class="form-control"
            placeholder="Discount value"
            :value="discountValue"
            @input="$emit('update-discount-value', Number($event.target.value) || 0)"
          />
        </div>
      </div>

      <!-- Financial Calculations Table -->
      <div class="border-top pt-2 small">
        <div class="d-flex justify-content-between text-muted mb-1">
          <span>Subtotal:</span>
          <span>{{ currency }}{{ subtotal.toFixed(2) }}</span>
        </div>
        <div class="d-flex justify-content-between text-muted mb-1" v-if="discountAmount > 0">
          <span class="text-danger">Discount ({{ discountType === 'percentage' ? discountValue + '%' : currency + discountValue }}):</span>
          <span class="text-danger">-{{ currency }}{{ discountAmount.toFixed(2) }}</span>
        </div>
        <div class="d-flex justify-content-between text-muted mb-1">
          <span>Tax ({{ taxRate }}%):</span>
          <span>{{ currency }}{{ taxAmount.toFixed(2) }}</span>
        </div>
        <div class="d-flex justify-content-between fs-5 fw-bold text-dark border-top pt-2 mt-1">
          <span>Total:</span>
          <span class="text-primary">{{ currency }}{{ grandTotal.toFixed(2) }}</span>
        </div>
      </div>

      <!-- Checkout Button & Clear Button -->
      <div class="d-grid gap-2 mt-3">
        <button
          type="button"
          class="btn btn-success btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm"
          :disabled="cartItems.length === 0"
          @click="$emit('open-checkout')"
        >
          <i class="bi bi-credit-card-2-front"></i>
          <span>Checkout ({{ currency }}{{ grandTotal.toFixed(2) }})</span>
        </button>

        <button
          type="button"
          class="btn btn-outline-secondary btn-sm"
          :disabled="cartItems.length === 0"
          @click="$emit('clear-cart')"
        >
          <i class="bi bi-trash me-1"></i> Clear Cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import CartItem from './CartItem.vue';

const props = defineProps({
  cartItems: {
    type: Array,
    default: () => []
  },
  subtotal: {
    type: Number,
    default: 0
  },
  discountType: {
    type: String,
    default: 'percentage'
  },
  discountValue: {
    type: Number,
    default: 0
  },
  discountAmount: {
    type: Number,
    default: 0
  },
  taxRate: {
    type: Number,
    default: 10
  },
  taxAmount: {
    type: Number,
    default: 0
  },
  grandTotal: {
    type: Number,
    default: 0
  },
  currency: {
    type: String,
    default: '$'
  }
});

defineEmits([
  'update-qty',
  'remove-item',
  'clear-cart',
  'update-discount-type',
  'update-discount-value',
  'open-checkout'
]);

const totalItemCount = computed(() => {
  return props.cartItems.reduce((sum, item) => sum + item.quantity, 0);
});
</script>
