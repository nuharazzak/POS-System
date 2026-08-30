<template>
  <div class="d-flex align-items-center justify-content-center p-4">
    <div class="spinner-border text-primary" :class="sizeClass" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <span v-if="text" class="ms-2 text-muted">{{ text }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  text: {
    type: String,
    default: 'Loading...'
  },
  size: {
    type: String,
    default: 'md' // 'sm', 'md', 'lg'
  }
});

const sizeClass = computed(() => {
  if (props.size === 'sm') return 'spinner-border-sm';
  if (props.size === 'lg') return 'p-3';
  return '';
});
</script>
