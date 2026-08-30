<template>
  <div class="row g-4">
    <!-- Reports Header & Range selection -->
    <div class="col-12">
      <div class="card border-0 shadow-sm p-3 bg-white d-flex flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
          <h6 class="mb-0 fw-bold">Performance & Sales Overview</h6>
          <small class="text-muted">Metrics calculated automatically from settled transactions</small>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" @click="loadReportData">
          <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
        </button>
      </div>
    </div>

    <!-- KPI Metric Summary Cards -->
    <div class="col-12 col-sm-6 col-xl-4">
      <SummaryCard
        title="Total Sales Revenue"
        :value="`$${report.total_sales.toFixed(2)}`"
        icon="bi-cash-coin"
        color="success"
        subtitle="Gross completed volume"
      />
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
      <SummaryCard
        title="Total Orders Processed"
        :value="report.total_orders"
        icon="bi-bag-check"
        color="primary"
        subtitle="Completed customer orders"
      />
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
      <SummaryCard
        title="Average Order Value"
        :value="`$${report.average_order_value.toFixed(2)}`"
        icon="bi-graph-up-arrow"
        color="info"
        subtitle="Mean basket size per ticket"
      />
    </div>

    <!-- Sales breakdown by Payment Method -->
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-bottom py-3">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-pie-chart text-primary me-2"></i> Revenue by Payment Method
          </h6>
        </div>
        <div class="card-body p-4">
          <div class="d-flex flex-column gap-3">
            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="small fw-semibold text-secondary"><i class="bi bi-cash-stack me-1"></i> Cash Payments</span>
                <span class="fw-bold">${{ report.cash_sales.toFixed(2) }}</span>
              </div>
              <div class="progress" style="height: 10px;">
                <div
                  class="progress-bar bg-success"
                  role="progressbar"
                  :style="{ width: `${calculatePct(report.cash_sales, report.total_sales)}%` }"
                ></div>
              </div>
            </div>

            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="small fw-semibold text-secondary"><i class="bi bi-credit-card me-1"></i> Card Payments</span>
                <span class="fw-bold">${{ report.card_sales.toFixed(2) }}</span>
              </div>
              <div class="progress" style="height: 10px;">
                <div
                  class="progress-bar bg-primary"
                  role="progressbar"
                  :style="{ width: `${calculatePct(report.card_sales, report.total_sales)}%` }"
                ></div>
              </div>
            </div>

            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="small fw-semibold text-secondary"><i class="bi bi-qr-code-scan me-1"></i> Online / QR Payments</span>
                <span class="fw-bold">${{ report.online_sales.toFixed(2) }}</span>
              </div>
              <div class="progress" style="height: 10px;">
                <div
                  class="progress-bar bg-info"
                  role="progressbar"
                  :style="{ width: `${calculatePct(report.online_sales, report.total_sales)}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Best Selling Products Table -->
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-bottom py-3">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-trophy text-warning me-2"></i> Best Selling Menu Items
          </h6>
        </div>
        <div class="card-body p-0 table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Product</th>
                <th class="text-center">Quantity Sold</th>
                <th class="text-end">Revenue</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="bestSellingProducts.length === 0">
                <td colspan="3" class="text-center py-4 text-muted">No sales items recorded yet.</td>
              </tr>
              <tr v-for="item in bestSellingProducts" :key="item.product_id">
                <td class="fw-semibold text-dark">{{ item.product_name }}</td>
                <td class="text-center">
                  <span class="badge bg-light text-dark border">{{ item.quantity_sold }}</span>
                </td>
                <td class="text-end fw-bold text-success">${{ Number(item.revenue).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { reportService } from '../services/api';
import SummaryCard from '../components/SummaryCard.vue';

const report = ref({
  total_sales: 0,
  total_orders: 0,
  average_order_value: 0,
  cash_sales: 0,
  card_sales: 0,
  online_sales: 0
});

const bestSellingProducts = ref([]);

const calculatePct = (amount, total) => {
  if (!total || total <= 0) return 0;
  return Math.min(100, Math.round((amount / total) * 100));
};

const loadReportData = async () => {
  try {
    const [salesRes, bestRes] = await Promise.all([
      reportService.getSalesReport(),
      reportService.getBestSellingProducts()
    ]);

    if (salesRes.data.success) {
      const data = salesRes.data.data;
      const summary = data.summary || data;
      const payments = data.payment_breakdown || [];

      let cash = 0;
      let card = 0;
      let online = 0;

      payments.forEach(p => {
        if (p.method === 'cash') cash = p.total;
        else if (p.method === 'card') card = p.total;
        else if (p.method === 'online') online = p.total;
      });

      report.value = {
        total_sales: Number(summary.total_sales || 0),
        total_orders: Number(summary.total_orders || 0),
        average_order_value: Number(summary.average_order_value || 0),
        cash_sales: Number(data.cash_sales ?? cash),
        card_sales: Number(data.card_sales ?? card),
        online_sales: Number(data.online_sales ?? online)
      };
    }

    if (bestRes.data.success) {
      const list = bestRes.data.data || [];
      bestSellingProducts.value = list.map(item => ({
        product_id: item.product_id,
        product_name: item.name || item.product_name,
        quantity_sold: item.total_quantity_sold ?? item.quantity_sold ?? 0,
        revenue: item.total_revenue_generated ?? item.revenue ?? 0
      }));
    }
  } catch (err) {
    console.error('Error loading reports:', err);
  }
};

onMounted(() => {
  loadReportData();
});
</script>
