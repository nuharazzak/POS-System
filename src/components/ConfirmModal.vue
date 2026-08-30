<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill text-warning me-2" v-if="variant === 'danger' || variant === 'warning'"></i>
            {{ title }}
          </h5>
          <button type="button" class="btn-close" @click="$emit('cancel')"></button>
        </div>
        <div class="modal-body py-3">
          <p class="mb-0 text-secondary">{{ message }}</p>
        </div>
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-outline-secondary" @click="$emit('cancel')">
            {{ cancelText }}
          </button>
          <button type="button" class="btn" :class="`btn-${variant}`" @click="$emit('confirm')">
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  variant: {
    type: String,
    default: 'danger'
  }
});

defineEmits(['confirm', 'cancel']);
</script>
