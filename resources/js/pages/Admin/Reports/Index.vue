<template>
    <AdminLayout page-title="Reports">
        <div class="reports-page">
            <!-- Date filter -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label>From</label>
                    <input type="date" v-model="filters.from" @change="reload" />
                </div>
                <div class="filter-group">
                    <label>To</label>
                    <input type="date" v-model="filters.to" @change="reload" />
                </div>
                <button @click="exportCsv" class="btn-export">
                    Export CSV
                </button>
            </div>

            <!-- Summary cards -->
            <div class="summary-cards">
                <SummaryCard label="Orders" :value="summary.order_count" icon="receipt" />
                <SummaryCard label="Revenue" :value="'$' + fmt(summary.total_revenue)" icon="dollar" />
                <SummaryCard label="Avg Order" :value="'$' + fmt(summary.avg_order)" icon="trending-up" />
                <SummaryCard label="Tax Collected" :value="'$' + fmt(summary.total_tax)" icon="percent" />
                <SummaryCard label="Tips" :value="'$' + fmt(summary.total_tips)" icon="heart" />
                <SummaryCard label="Discounts" :value="'$' + fmt(summary.total_discount)" icon="percent" />
                <SummaryCard label="Delivery Fees" :value="'$' + fmt(summary.total_delivery)" icon="truck" />
            </div>

            <!-- Method breakdown -->
            <div class="method-breakdown">
                <div class="method-pill pickup">
                    Pickup: {{ summary.pickup_count }}
                </div>
                <div class="method-pill delivery">
                    Delivery: {{ summary.delivery_count }}
                </div>
            </div>

            <!-- Revenue chart -->
            <div class="chart-section">
                <h3>Revenue by Day</h3>
                <Bar :data="chartData" :options="chartOptions" />
            </div>

            <!-- Top items -->
            <div class="top-items">
                <h3>Top Items Ordered</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Qty Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in topItems" :key="item.name">
                            <td>{{ i + 1 }}</td>
                            <td>{{ item.name }}</td>
                            <td>{{ item.qty }}</td>
                        </tr>
                        <tr v-if="topItems.length === 0">
                            <td colspan="3" class="empty">No orders in this date range.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js'
import AdminLayout from '../../../components/Admin/Layout.vue'
import SummaryCard from '../../../components/Admin/SummaryCard.vue'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps({
    summary: Object,
    by_day: Array,
    top_items: Array,
    filters: Object,
})

const filters = ref({ ...props.filters })
const topItems = computed(() => props.top_items || [])

const fmt = (n) => Number(n || 0).toFixed(2)

function reload() {
    router.get(route('tenant.admin.reports.index'), filters.value, { preserveState: true, replace: true })
}

function exportCsv() {
    window.location = route('tenant.admin.reports.export') + '?' + new URLSearchParams(filters.value).toString()
}

const chartData = computed(() => ({
    labels: props.by_day.map(d => d.date),
    datasets: [
        {
            label: 'Revenue',
            data: props.by_day.map(d => d.revenue),
            backgroundColor: '#e85d04',
            borderRadius: 4,
        },
        {
            label: 'Orders',
            data: props.by_day.map(d => d.orders),
            backgroundColor: '#fca311',
            borderRadius: 4,
        },
    ],
}))

const chartOptions = {
    responsive: true,
    plugins: {
        legend: { position: 'top' },
    },
    scales: {
        y: { beginAtZero: true },
    },
}
</script>
