<template>
  <div>
    <!-- Loading State -->
    <LoadingSpinner v-if="loading" text="Loading menu products..." />

    <!-- Empty State -->
    <div v-else-if="products.length === 0" class="text-center py-5 bg-white rounded border">
      <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
      <h6 class="fw-semibold text-muted">No products found</h6>
      <p class="small text-secondary mb-0">Try clearing your search query or selecting another category.</p>
    </div>

    <!-- Product Grid -->
    <div v-else class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-3">
      <div v-for="product in products" :key="product.id" class="col">
        <ProductCard
          :product="product"
          :currency="currency"
          @add-to-cart="$emit('add-to-cart', $event)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import ProductCard from './ProductCard.vue';
import LoadingSpinner from './LoadingSpinner.vue';

defineProps({
  products: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  currency: {
    type: String,
    default: '$'
  }
});

defineEmits(['add-to-cart']);
</script>
