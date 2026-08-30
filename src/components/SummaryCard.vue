<template>
  <div class="card border-0 shadow-sm h-100">
    <div class="card-body p-3 d-flex align-items-center">
      <div
        class="rounded-3 p-3 d-flex align-items-center justify-content-center me-3"
        :class="`bg-${color} bg-opacity-10 text-${color}`"
        style="width: 52px; height: 52px;"
      >
        <i class="bi fs-3" :class="icon"></i>
      </div>
      <div>
        <div class="text-secondary small fw-semibold">{{ title }}</div>
        <div class="fs-4 fw-bold text-dark">{{ value }}</div>
        <div v-if="subtitle" class="small text-muted" style="font-size: 0.75rem;">
          {{ subtitle }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: String,
    default: 'bi-info-circle'
  },
  color: {
    type: String,
    default: 'primary' // 'primary', 'success', 'warning', 'info', 'danger'
  },
  subtitle: {
    type: String,
    default: ''
  }
});
</script>
