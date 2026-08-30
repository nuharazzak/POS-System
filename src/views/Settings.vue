<template>
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <AlertMessage
        v-if="alert.message"
        :message="alert.message"
        :variant="alert.variant"
        @dismiss="alert.message = ''"
      />

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-gear-wide-connected text-primary me-2"></i> POS Store Configuration
          </h6>
          <small class="text-muted">Manage receipt headers, active sales tax, and inventory thresholds</small>
        </div>

        <form @submit.prevent="saveSettings">
          <div class="card-body p-4">
            <LoadingSpinner v-if="loading" text="Loading configuration..." />

            <div v-else class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-secondary">Store / Cafe Name *</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.store_name"
                  required
                  :disabled="!isAdmin"
                  placeholder="e.g. Cafe Gourmet"
                />
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-secondary">Currency Symbol *</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.currency"
                  required
                  maxlength="5"
                  :disabled="!isAdmin"
                  placeholder="$"
                />
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold text-secondary">Store Address (on Receipt)</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.address"
                  :disabled="!isAdmin"
                  placeholder="Street address, city, country"
                />
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-secondary">Contact Phone Number</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.phone"
                  :disabled="!isAdmin"
                  placeholder="+1 (555) 000-0000"
                />
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-secondary">Tax Rate (%) *</label>
                <div class="input-group">
                  <input
                    type="number"
                    step="0.1"
                    min="0"
                    max="100"
                    class="form-control"
                    v-model.number="form.tax_rate"
                    required
                    :disabled="!isAdmin"
                  />
                  <span class="input-group-text bg-light">%</span>
                </div>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-secondary">Low Stock Threshold *</label>
                <input
                  type="number"
                  min="0"
                  class="form-control"
                  v-model.number="form.low_stock_threshold"
                  required
                  :disabled="!isAdmin"
                />
              </div>

              <div v-if="!isAdmin" class="col-12">
                <div class="alert alert-warning py-2 small mb-0">
                  <i class="bi bi-lock-fill me-1"></i> You are logged in as a <strong>Cashier</strong>. Only <strong>Admin</strong> users can modify store settings.
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer bg-light py-3 d-flex justify-content-end" v-if="isAdmin">
            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2" :disabled="saving || loading">
              <span v-if="saving" class="spinner-border spinner-border-sm" role="status"></span>
              <i v-else class="bi bi-save"></i>
              <span>{{ saving ? 'Saving Changes...' : 'Save Settings' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { settingService } from '../services/api';
import { useAuth } from '../composables/useAuth';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import AlertMessage from '../components/AlertMessage.vue';

const { isAdmin, updateSettings } = useAuth();

const loading = ref(true);
const saving = ref(false);
const alert = ref({ message: '', variant: 'success' });

const form = ref({
  store_name: '',
  address: '',
  phone: '',
  currency: '$',
  tax_rate: 10.0,
  low_stock_threshold: 5
});

const loadSettings = async () => {
  loading.value = true;
  try {
    const res = await settingService.get();
    if (res.data.success) {
      form.value = {
        store_name: res.data.data.store_name,
        address: res.data.data.address || '',
        phone: res.data.data.phone || '',
        currency: res.data.data.currency || '$',
        tax_rate: Number(res.data.data.tax_rate) || 10,
        low_stock_threshold: Number(res.data.data.low_stock_threshold) || 5
      };
      updateSettings(form.value);
    }
  } catch (err) {
    alert.value = { message: 'Failed to load settings', variant: 'danger' };
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  if (!isAdmin.value) return;

  saving.value = true;
  alert.value = { message: '', variant: 'success' };

  try {
    const res = await settingService.update(form.value);
    if (res.data.success) {
      alert.value = { message: 'Settings saved and applied successfully!', variant: 'success' };
      updateSettings(form.value);
    }
  } catch (err) {
    alert.value = {
      message: err.response?.data?.message || 'Failed to update settings.',
      variant: 'danger'
    };
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadSettings();
});
</script>
