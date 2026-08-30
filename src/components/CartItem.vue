<template>
  <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-0 border-bottom">
    <div class="me-auto overflow-hidden pe-2">
      <div class="fw-semibold text-truncate text-dark" style="max-width: 140px;" :title="item.name">
        {{ item.name }}
      </div>
      <div class="small text-muted">
        {{ currency }}{{ Number(item.price).toFixed(2) }} × {{ item.quantity }} =
        <span class="fw-bold text-dark">{{ currency }}{{ (item.price * item.quantity).toFixed(2) }}</span>
      </div>
    </div>

    <!-- Stepper Controls & Delete Button -->
    <div class="d-flex align-items-center gap-1">
      <div class="btn-group btn-group-sm border rounded">
        <button
          type="button"
          class="btn btn-light btn-sm px-2 py-0"
          :disabled="item.quantity <= 1"
          @click="$emit('update-qty', { item, quantity: item.quantity - 1 })"
          title="Decrease quantity"
        >
          <i class="bi bi-dash"></i>
        </button>

        <span class="btn btn-white btn-sm px-2 py-0 fw-bold border-start border-end" style="min-width: 28px; cursor: default;">
          {{ item.quantity }}
        </span>

        <button
          type="button"
          class="btn btn-light btn-sm px-2 py-0"
          :disabled="item.quantity >= item.stock_quantity"
          @click="$emit('update-qty', { item, quantity: item.quantity + 1 })"
          title="Increase quantity"
        >
          <i class="bi bi-plus"></i>
        </button>
      </div>

      <button
        type="button"
        class="btn btn-outline-danger btn-sm p-1 border-0 ms-1"
        @click="$emit('remove-item', item)"
        title="Remove item"
      >
        <i class="bi bi-trash fs-6"></i>
      </button>
    </div>
  </li>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true
  },
  currency: {
    type: String,
    default: '$'
  }
});

defineEmits(['update-qty', 'remove-item']);
</script>
