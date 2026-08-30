<template>
  <div>
    <!-- Top Action Bar -->
    <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <!-- Search & Filter -->
        <div class="d-flex flex-grow-1 gap-2">
          <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
            <input
              type="text"
              class="form-control"
              placeholder="Search products by name..."
              v-model="searchQuery"
            />
          </div>

          <select class="form-select w-auto" v-model="filterCategory">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <!-- Add Product Button (Admin) -->
        <button
          type="button"
          class="btn btn-primary d-inline-flex align-items-center gap-2"
          @click="openAddModal"
        >
          <i class="bi bi-plus-circle"></i>
          <span>Add Product</span>
        </button>
      </div>
    </div>

    <!-- Alert Message -->
    <AlertMessage
      v-if="alert.message"
      :message="alert.message"
      :variant="alert.variant"
      @dismiss="alert.message = ''"
    />

    <!-- Products Table Card -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0 table-responsive">
        <LoadingSpinner v-if="loading" text="Loading product catalog..." />

        <table v-else class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Product Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="filteredProducts.length === 0">
              <td colspan="7" class="text-center py-4 text-muted">No products found matching your filter criteria.</td>
            </tr>
            <tr v-for="product in filteredProducts" :key="product.id">
              <td class="text-muted small">#{{ product.id }}</td>
              <td>
                <div class="fw-semibold text-dark">{{ product.name }}</div>
                <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">
                  {{ product.description }}
                </small>
              </td>
              <td>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                  {{ product.category_name }}
                </span>
              </td>
              <td class="fw-bold text-dark">${{ Number(product.price).toFixed(2) }}</td>
              <td>
                <span v-if="product.is_out_of_stock" class="badge bg-danger">
                  Out of Stock (0)
                </span>
                <span v-else-if="product.is_low_stock" class="badge bg-warning text-dark">
                  Low ({{ product.stock_quantity }})
                </span>
                <span v-else class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                  {{ product.stock_quantity }} units
                </span>
              </td>
              <td>
                <span class="badge" :class="product.is_active ? 'bg-success' : 'bg-secondary'">
                  {{ product.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="text-end">
                <button
                  type="button"
                  class="btn btn-outline-primary btn-sm me-2"
                  @click="openEditModal(product)"
                  title="Edit Product"
                >
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button
                  type="button"
                  class="btn btn-outline-danger btn-sm"
                  @click="confirmDelete(product)"
                  title="Delete Product"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Product Create/Edit Modal -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">
              {{ isEditing ? 'Edit Product' : 'Add New Product' }}
            </h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <form @submit.prevent="saveProduct">
            <div class="modal-body p-4">
              <!-- Form error notice -->
              <div v-if="formError" class="alert alert-danger py-2 small mb-3">
                <i class="bi bi-exclamation-circle me-1"></i> {{ formError }}
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Product Name *</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.name"
                  required
                  placeholder="e.g. Gourmet Burger"
                />
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Category *</label>
                <select class="form-select" v-model="form.category_id" required>
                  <option disabled value="">Select a category</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small fw-semibold text-secondary">Price ($) *</label>
                  <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    class="form-control"
                    v-model.number="form.price"
                    required
                    placeholder="0.00"
                  />
                </div>
                <div class="col-6">
                  <label class="form-label small fw-semibold text-secondary">Stock Quantity *</label>
                  <input
                    type="number"
                    min="0"
                    class="form-control"
                    v-model.number="form.stock_quantity"
                    required
                    placeholder="0"
                  />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Description</label>
                <textarea
                  class="form-control"
                  rows="2"
                  v-model="form.description"
                  placeholder="Ingredients or details..."
                ></textarea>
              </div>

              <div class="form-check form-switch">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="isActiveCheck"
                  v-model="form.is_active"
                />
                <label class="form-check-label small" for="isActiveCheck">
                  Active (Display on POS menu)
                </label>
              </div>
            </div>

            <div class="modal-footer bg-light py-2">
              <button type="button" class="btn btn-secondary" @click="showModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                {{ isEditing ? 'Save Changes' : 'Create Product' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Delete Product"
      :message="`Are you sure you want to delete '${productToDelete?.name}'?`"
      confirm-text="Delete"
      variant="danger"
      @cancel="showDeleteModal = false"
      @confirm="executeDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { productService, categoryService } from '../services/api';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import AlertMessage from '../components/AlertMessage.vue';
import ConfirmModal from '../components/ConfirmModal.vue';

const products = ref([]);
const categories = ref([]);
const loading = ref(true);
const saving = ref(false);
const searchQuery = ref('');
const filterCategory = ref('');

const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const formError = ref('');

const showDeleteModal = ref(false);
const productToDelete = ref(null);

const alert = ref({ message: '', variant: 'success' });

const form = ref({
  name: '',
  category_id: '',
  price: 0,
  stock_quantity: 0,
  description: '',
  is_active: true
});

const filteredProducts = computed(() => {
  return products.value.filter(p => {
    const matchCat = !filterCategory.value || p.category_id === Number(filterCategory.value);
    const matchSearch = !searchQuery.value || p.name.toLowerCase().includes(searchQuery.value.toLowerCase());
    return matchCat && matchSearch;
  });
});

const loadData = async () => {
  loading.value = true;
  try {
    const [pRes, cRes] = await Promise.all([
      productService.getAll(),
      categoryService.getAll()
    ]);
    if (pRes.data.success) products.value = pRes.data.data;
    if (cRes.data.success) categories.value = cRes.data.data;
  } catch (err) {
    alert.value = { message: 'Failed to load products', variant: 'danger' };
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEditing.value = false;
  editId.value = null;
  formError.value = '';
  form.value = {
    name: '',
    category_id: categories.value[0]?.id || '',
    price: 0,
    stock_quantity: 10,
    description: '',
    is_active: true
  };
  showModal.value = true;
};

const openEditModal = (product) => {
  isEditing.value = true;
  editId.value = product.id;
  formError.value = '';
  form.value = {
    name: product.name,
    category_id: product.category_id,
    price: product.price,
    stock_quantity: product.stock_quantity,
    description: product.description || '',
    is_active: product.is_active
  };
  showModal.value = true;
};

const saveProduct = async () => {
  saving.value = true;
  formError.value = '';

  try {
    if (isEditing.value) {
      await productService.update(editId.value, form.value);
      alert.value = { message: 'Product updated successfully!', variant: 'success' };
    } else {
      await productService.create(form.value);
      alert.value = { message: 'Product created successfully!', variant: 'success' };
    }
    showModal.value = false;
    await loadData();
  } catch (err) {
    formError.value = err.response?.data?.message || 'Error saving product.';
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (product) => {
  productToDelete.value = product;
  showDeleteModal.value = true;
};

const executeDelete = async () => {
  showDeleteModal.value = false;
  try {
    const res = await productService.delete(productToDelete.value.id);
    alert.value = { message: res.data.message || 'Product deleted', variant: 'success' };
    await loadData();
  } catch (err) {
    alert.value = { message: err.response?.data?.message || 'Failed to delete product', variant: 'danger' };
  }
};

onMounted(() => {
  loadData();
});
</script>
