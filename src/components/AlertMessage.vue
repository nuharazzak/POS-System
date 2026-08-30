<template>
  <div
    v-if="message"
    class="alert alert-dismissible fade show d-flex align-items-center shadow-sm"
    :class="`alert-${variant}`"
    role="alert"
  >
    <i class="bi me-2 fs-5" :class="iconClass"></i>
    <div class="flex-grow-1">
      <div v-if="title" class="fw-bold">{{ title }}</div>
      <div>{{ message }}</div>
    </div>
    <button
      v-if="dismissible"
      type="button"
      class="btn-close"
      aria-label="Close"
      @click="$emit('dismiss')"
    ></button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  message: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'info' // 'success', 'danger', 'warning', 'info'
  },
  dismissible: {
    type: Boolean,
    default: true
  }
});

defineEmits(['dismiss']);

const iconClass = computed(() => {
  switch (props.variant) {
    case 'success': return 'bi-check-circle-fill';
    case 'danger': return 'bi-exclamation-triangle-fill';
    case 'warning': return 'bi-exclamation-circle-fill';
    default: return 'bi-info-circle-fill';
  }
});
</script>
