<template>
  <div>
    <!-- Top Action Bar -->
    <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h6 class="mb-0 fw-bold">Menu Categories</h6>
          <small class="text-muted">Organize food, drinks, snacks, and desserts</small>
        </div>
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" @click="openAddModal">
          <i class="bi bi-plus-circle"></i>
          <span>Add Category</span>
        </button>
      </div>
    </div>

    <!-- Alert Notification -->
    <AlertMessage
      v-if="alert.message"
      :message="alert.message"
      :variant="alert.variant"
      @dismiss="alert.message = ''"
    />

    <!-- Categories Grid / List -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0 table-responsive">
        <LoadingSpinner v-if="loading" text="Loading categories..." />

        <table v-else class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 80px;">ID</th>
              <th>Category Name</th>
              <th>Description</th>
              <th class="text-center">Assigned Products</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="categories.length === 0">
              <td colspan="5" class="text-center py-4 text-muted">No categories found.</td>
            </tr>
            <tr v-for="cat in categories" :key="cat.id">
              <td class="text-muted small">#{{ cat.id }}</td>
              <td class="fw-bold text-dark">{{ cat.name }}</td>
              <td class="text-muted">{{ cat.description || '—' }}</td>
              <td class="text-center">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                  {{ cat.products_count ?? 0 }} items
                </span>
              </td>
              <td class="text-end">
                <button
                  type="button"
                  class="btn btn-outline-primary btn-sm me-2"
                  @click="openEditModal(cat)"
                  title="Edit Category"
                >
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button
                  type="button"
                  class="btn btn-outline-danger btn-sm"
                  @click="confirmDelete(cat)"
                  title="Delete Category"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal for Create/Edit Category -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">{{ isEditing ? 'Edit Category' : 'Create Category' }}</h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <form @submit.prevent="saveCategory">
            <div class="modal-body p-4">
              <div v-if="formError" class="alert alert-danger py-2 small mb-3">
                <i class="bi bi-exclamation-circle me-1"></i> {{ formError }}
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Category Name *</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.name"
                  required
                  placeholder="e.g. Beverages"
                />
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Description</label>
                <textarea
                  class="form-control"
                  rows="3"
                  v-model="form.description"
                  placeholder="Brief description of this menu section..."
                ></textarea>
              </div>
            </div>

            <div class="modal-footer bg-light py-2">
              <button type="button" class="btn btn-secondary" @click="showModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                {{ isEditing ? 'Save Changes' : 'Create Category' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Delete Category"
      :message="`Are you sure you want to delete category '${categoryToDelete?.name}'? Note: Categories with assigned products cannot be deleted.`"
      confirm-text="Delete"
      variant="danger"
      @cancel="showDeleteModal = false"
      @confirm="executeDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { categoryService } from '../services/api';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import AlertMessage from '../components/AlertMessage.vue';
import ConfirmModal from '../components/ConfirmModal.vue';

const categories = ref([]);
const loading = ref(true);
const saving = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const formError = ref('');

const showDeleteModal = ref(false);
const categoryToDelete = ref(null);

const alert = ref({ message: '', variant: 'success' });
const form = ref({ name: '', description: '' });

const loadCategories = async () => {
  loading.value = true;
  try {
    const res = await categoryService.getAll();
    if (res.data.success) {
      categories.value = res.data.data;
    }
  } catch (err) {
    alert.value = { message: 'Failed to load categories', variant: 'danger' };
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEditing.value = false;
  editId.value = null;
  formError.value = '';
  form.value = { name: '', description: '' };
  showModal.value = true;
};

const openEditModal = (cat) => {
  isEditing.value = true;
  editId.value = cat.id;
  formError.value = '';
  form.value = { name: cat.name, description: cat.description || '' };
  showModal.value = true;
};

const saveCategory = async () => {
  saving.value = true;
  formError.value = '';

  try {
    if (isEditing.value) {
      await categoryService.update(editId.value, form.value);
      alert.value = { message: 'Category updated successfully!', variant: 'success' };
    } else {
      await categoryService.create(form.value);
      alert.value = { message: 'Category created successfully!', variant: 'success' };
    }
    showModal.value = false;
    await loadCategories();
  } catch (err) {
    formError.value = err.response?.data?.message || 'Error saving category.';
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (cat) => {
  categoryToDelete.value = cat;
  showDeleteModal.value = true;
};

const executeDelete = async () => {
  showDeleteModal.value = false;
  try {
    const res = await categoryService.delete(categoryToDelete.value.id);
    alert.value = { message: res.data.message || 'Category deleted', variant: 'success' };
    await loadCategories();
  } catch (err) {
    alert.value = {
      message: err.response?.data?.message || 'Cannot delete category with associated products.',
      variant: 'danger'
    };
  }
};

onMounted(() => {
  loadCategories();
});
</script>
