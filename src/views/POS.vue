<template>
  <div class="container-fluid p-0">
    <div class="row g-3">
      <!-- Left Column: Search, Categories & Products Grid -->
      <div class="col-12 col-lg-8 col-xl-8">
        <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
          <!-- Search & Filter Bar -->
          <div class="row g-2 align-items-center mb-3">
            <div class="col-12 col-md-6">
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-search text-muted"></i>
                </span>
                <input
                  type="text"
                  class="form-control bg-light border-start-0"
                  placeholder="Search products by name..."
                  v-model="searchQuery"
                />
                <button
                  v-if="searchQuery"
                  class="btn btn-light border"
                  type="button"
                  @click="searchQuery = ''"
                >
                  <i class="bi bi-x"></i>
                </button>
              </div>
            </div>

            <!-- Categories Filter Badges/Tabs -->
            <div class="col-12 col-md-6">
              <div class="d-flex gap-1 overflow-auto pb-1" style="white-space: nowrap;">
                <button
                  type="button"
                  class="btn btn-sm"
                  :class="selectedCategoryId === null ? 'btn-primary' : 'btn-outline-secondary'"
                  @click="selectedCategoryId = null"
                >
                  All Items
                </button>
                <button
                  v-for="cat in categories"
                  :key="cat.id"
                  type="button"
                  class="btn btn-sm"
                  :class="selectedCategoryId === cat.id ? 'btn-primary' : 'btn-outline-secondary'"
                  @click="selectedCategoryId = cat.id"
                >
                  {{ cat.name }}
                </button>
              </div>
            </div>
          </div>

          <!-- Product Grid Component -->
          <ProductGrid
            :products="filteredProducts"
            :loading="loadingProducts"
            :currency="currency"
            @add-to-cart="addToCart"
          />
        </div>
      </div>

      <!-- Right Column: Cart Component -->
      <div class="col-12 col-lg-4 col-xl-4">
        <Cart
          :cart-items="cartItems"
          :subtotal="subtotal"
          :discount-type="discountType"
          :discount-value="discountValue"
          :discount-amount="discountAmount"
          :tax-rate="taxRate"
          :tax-amount="taxAmount"
          :grand-total="grandTotal"
          :currency="currency"
          @update-qty="updateCartQty"
          @remove-item="removeCartItem"
          @clear-cart="clearCart"
          @update-discount-type="discountType = $event"
          @update-discount-value="discountValue = $event"
          @open-checkout="showCheckoutModal = true"
        />
      </div>
    </div>

    <!-- Checkout Modal -->
    <CheckoutModal
      :show="showCheckoutModal"
      :subtotal="subtotal"
      :discount-type="discountType"
      :discount-value="discountValue"
      :discount-amount="discountAmount"
      :tax-rate="taxRate"
      :tax-amount="taxAmount"
      :grand-total="grandTotal"
      :currency="currency"
      :processing="isProcessingOrder"
      :error-message="checkoutError"
      @close="showCheckoutModal = false"
      @submit-order="handleOrderCheckout"
    />

    <!-- Receipt Modal -->
    <Receipt
      :show="showReceiptModal"
      :order="completedOrder"
      :store-name="settings.store_name"
      :store-address="settings.address"
      :store-phone="settings.phone"
      :currency="currency"
      :cashier-name="currentUser?.name"
      @close="showReceiptModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { productService, categoryService, settingService, orderService } from '../services/api';
import { useAuth } from '../composables/useAuth';
import ProductGrid from '../components/ProductGrid.vue';
import Cart from '../components/Cart.vue';
import CheckoutModal from '../components/CheckoutModal.vue';
import Receipt from '../components/Receipt.vue';

const { currentUser } = useAuth();

// Products & Categories state
const products = ref([]);
const categories = ref([]);
const loadingProducts = ref(true);
const searchQuery = ref('');
const selectedCategoryId = ref(null);

// Settings state
const settings = ref({
  store_name: 'My Cafe & Bistro',
  address: '45 Bistro Lane, Downtown',
  phone: '+1 (555) 839-2041',
  currency: '$',
  tax_rate: 10.0,
  low_stock_threshold: 5
});

const currency = computed(() => settings.value.currency || '$');
const taxRate = computed(() => Number(settings.value.tax_rate) || 10);

// Cart state
const cartItems = ref([]);
const discountType = ref('percentage'); // 'percentage' or 'fixed'
const discountValue = ref(0);

// Modals & Transactions
const showCheckoutModal = ref(false);
const showReceiptModal = ref(false);
const isProcessingOrder = ref(false);
const checkoutError = ref('');
const completedOrder = ref(null);

// Filtered products computed property
const filteredProducts = computed(() => {
  return products.value.filter(product => {
    const matchesCategory = selectedCategoryId.value === null || product.category_id === selectedCategoryId.value;
    const matchesSearch = !searchQuery.value || product.name.toLowerCase().includes(searchQuery.value.toLowerCase().trim());
    return matchesCategory && matchesSearch;
  });
});

// Cart Calculations
const calculateSubtotal = () => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.price * item.quantity);
  }, 0);
};

const subtotal = computed(() => calculateSubtotal());

const calculateDiscount = () => {
  const currentSubtotal = subtotal.value;
  if (currentSubtotal <= 0 || discountValue.value <= 0) return 0;

  if (discountType.value === 'percentage') {
    const pct = Math.min(100, Math.max(0, discountValue.value));
    return (currentSubtotal * pct) / 100;
  } else {
    return Math.min(currentSubtotal, Math.max(0, discountValue.value));
  }
};

const discountAmount = computed(() => calculateDiscount());

const calculateTax = () => {
  const taxableAmount = Math.max(0, subtotal.value - discountAmount.value);
  return (taxableAmount * taxRate.value) / 100;
};

const taxAmount = computed(() => calculateTax());

const calculateTotal = () => {
  const taxable = Math.max(0, subtotal.value - discountAmount.value);
  return taxable + taxAmount.value;
};

const grandTotal = computed(() => calculateTotal());

// Cart Actions
const addToCart = (product) => {
  if (product.stock_quantity <= 0) return;

  const existing = cartItems.value.find(item => item.id === product.id);
  if (existing) {
    if (existing.quantity < product.stock_quantity) {
      existing.quantity += 1;
    }
  } else {
    cartItems.value.push({
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1,
      stock_quantity: product.stock_quantity
    });
  }
};

const updateCartQty = ({ item, quantity }) => {
  const target = cartItems.value.find(i => i.id === item.id);
  if (!target) return;

  if (quantity <= 0) {
    removeCartItem(item);
  } else if (quantity <= target.stock_quantity) {
    target.quantity = quantity;
  }
};

const removeCartItem = (item) => {
  cartItems.value = cartItems.value.filter(i => i.id !== item.id);
};

const clearCart = () => {
  cartItems.value = [];
  discountValue.value = 0;
};

// Initial Data Loader
const fetchInitialData = async () => {
  loadingProducts.value = true;
  try {
    const [productsRes, categoriesRes, settingsRes] = await Promise.all([
      productService.getAll({ active_only: 1 }),
      categoryService.getAll(),
      settingService.get()
    ]);

    if (productsRes.data.success) {
      products.value = productsRes.data.data;
    }
    if (categoriesRes.data.success) {
      categories.value = categoriesRes.data.data;
    }
    if (settingsRes.data.success) {
      settings.value = settingsRes.data.data;
    }
  } catch (err) {
    console.error('Failed to load POS data:', err);
  } finally {
    loadingProducts.value = false;
  }
};

// Checkout & Order Creation
const handleOrderCheckout = async (paymentData) => {
  isProcessingOrder.value = true;
  checkoutError.value = '';

  const orderPayload = {
    items: cartItems.value.map(item => ({
      product_id: item.id,
      quantity: item.quantity
    })),
    discount_type: discountType.value,
    discount_value: discountValue.value,
    payment_method: paymentData.payment_method,
    amount_received: paymentData.amount_received
  };

  try {
    const response = await orderService.create(orderPayload);

    if (response.data.success) {
      completedOrder.value = response.data.data;
      showCheckoutModal.value = false;
      showReceiptModal.value = true;

      // Clear cart
      clearCart();

      // Refresh product stock list from server
      await fetchInitialData();
    }
  } catch (err) {
    checkoutError.value = err.response?.data?.message || 'Failed to complete order. Please check stock and try again.';
  } finally {
    isProcessingOrder.value = false;
  }
};

onMounted(() => {
  fetchInitialData();
});
</script>
