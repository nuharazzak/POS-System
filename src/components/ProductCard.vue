<template>
  <div class="card h-100 border shadow-sm product-card transition-all" :class="{ 'border-danger border-opacity-25 bg-light': product.is_out_of_stock }">
    <div class="card-body d-flex flex-column p-3">
      <!-- Top badges: Category & Stock -->
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 text-truncate" style="max-width: 120px;">
          {{ product.category_name || 'Item' }}
        </span>

        <span v-if="product.is_out_of_stock" class="badge bg-danger">
          Out of Stock
        </span>
        <span v-else-if="product.is_low_stock" class="badge bg-warning text-dark">
          Low Stock ({{ product.stock_quantity }})
        </span>
        <span v-else class="badge bg-light text-muted border">
          Stock: {{ product.stock_quantity }}
        </span>
      </div>

      <!-- Product Name & Description -->
      <h6 class="card-title fw-bold text-dark mb-1 text-truncate" :title="product.name">
        {{ product.name }}
      </h6>
      <p class="card-text text-muted small flex-grow-1 mb-2 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.8rem; line-height: 1.3;">
        {{ product.description || 'Delicious freshly prepared item.' }}
      </p>

      <!-- Bottom price & action -->
      <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
        <span class="fs-5 fw-bold text-primary">
          {{ currency }}{{ Number(product.price).toFixed(2) }}
        </span>

        <button
          type="button"
          class="btn btn-sm"
          :class="product.is_out_of_stock ? 'btn-outline-secondary' : 'btn-primary'"
          :disabled="product.is_out_of_stock"
          @click="$emit('add-to-cart', product)"
        >
          <i class="bi bi-cart-plus me-1"></i>
          {{ product.is_out_of_stock ? 'Sold Out' : 'Add' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  product: {
    type: Object,
    required: true
  },
  currency: {
    type: String,
    default: '$'
  }
});

defineEmits(['add-to-cart']);
</script>

<style scoped>
.product-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.product-card:hover:not(.bg-light) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}
</style>
