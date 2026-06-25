<template>
    <AdminLayout page-title="Orders" :pending-count="pendingCount">
        <div class="queue-summary">
            <button
                v-for="card in summaryCards"
                :key="card.status"
                type="button"
                class="summary-card"
                :class="[{ active: filters.status === card.status }, card.status]"
                @click="filterByStatus(card.status)"
            >
                <span>{{ card.label }}</span>
                <strong>{{ card.count }}</strong>
            </button>
            <button type="button" class="summary-card all" :class="{ active: !filters.status }" @click="filterByStatus('')">
                <span>All Orders</span>
                <strong>{{ totalKnownOrders }}</strong>
            </button>
        </div>

        <div class="filter-bar">
            <input
                v-model="filters.search"
                type="text"
                class="filter-input"
                placeholder="Search name, phone, email, or order #"
                @keyup.enter="applyFilters"
            />
            <select v-model="filters.status" class="filter-select" @change="applyFilters">
                <option value="">All Statuses</option>
                <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
            </select>
            <input v-model="filters.date_from" type="date" class="filter-input sm" @change="applyFilters" />
            <input v-model="filters.date_to" type="date" class="filter-input sm" @change="applyFilters" />
            <button class="filter-btn" @click="applyFilters">Filter</button>
            <button class="filter-btn secondary" @click="clearFilters">Clear</button>
        </div>

        <div class="card">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Next Step</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders.data" :key="order.id" :class="'row-' + order.status">
                        <td class="fw-bold">#{{ order.increment_id }}</td>
                        <td>
                            <div>{{ order.contact_firstname }} {{ order.contact_lastname }}</div>
                            <div class="text-sm text-muted">{{ order.contact_phone }}</div>
                        </td>
                        <td><span class="method-chip" :class="order.method">{{ order.method }}</span></td>
                        <td class="text-muted">{{ order.items_count }}</td>
                        <td class="fw-bold">${{ fmt(order.total) }}</td>
                        <td><span class="status-badge" :class="order.status">{{ statusLabel(order.status) }}</span></td>
                        <td class="text-muted text-sm">{{ formatDate(order.created_at) }}</td>
                        <td>
                            <button
                                v-if="order.next_status"
                                class="quick-btn"
                                :class="order.next_status"
                                :disabled="updatingId === order.id"
                                @click="updateStatus(order, order.next_status)"
                            >
                                {{ updatingId === order.id ? 'Updating...' : actionLabel(order.next_status) }}
                            </button>
                            <span v-else class="text-sm text-muted">Done</span>
                        </td>
                        <td><Link :href="route('tenant.admin.orders.show', order.id)" class="action-link">View</Link></td>
                    </tr>
                    <tr v-if="orders.data.length === 0">
                        <td colspan="9" class="empty-cell">No orders match your filters.</td>
                    </tr>
                </tbody>
            </table>

            <div class="pagination" v-if="orders.last_page > 1">
                <button v-for="link in orders.links" :key="link.label" v-html="link.label"
                    class="page-btn" :class="{ active: link.active, disabled: !link.url }"
                    @click="link.url && router.visit(link.url)" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'

const props = defineProps({
    orders: Object,
    filters: Object,
    statusCounts: { type: Object, default: () => ({}) },
    pendingCount: { type: Number, default: 0 },
})

const statuses = ['placed', 'received', 'ready', 'complete', 'cancelled']
const filters = ref({ search: props.filters?.search || '', status: props.filters?.status || '', date_from: props.filters?.date_from || '', date_to: props.filters?.date_to || '' })
const updatingId = ref(null)

const summaryCards = computed(() => [
    { status: 'placed', label: 'New', count: Number(props.statusCounts?.placed || 0) },
    { status: 'received', label: 'Preparing', count: Number(props.statusCounts?.received || 0) },
    { status: 'ready', label: 'Ready', count: Number(props.statusCounts?.ready || 0) },
    { status: 'complete', label: 'Completed', count: Number(props.statusCounts?.complete || 0) },
])

const totalKnownOrders = computed(() => statuses.reduce((sum, status) => sum + Number(props.statusCounts?.[status] || 0), 0))

function applyFilters() {
    router.get(route('tenant.admin.orders.index'), filters.value, { preserveState: true, preserveScroll: true })
}

function filterByStatus(status) {
    filters.value.status = status
    applyFilters()
}

function clearFilters() {
    filters.value = { search: '', status: '', date_from: '', date_to: '' }
    applyFilters()
}

function updateStatus(order, status) {
    updatingId.value = order.id
    router.patch(route('tenant.admin.orders.status', order.id), { status }, {
        preserveScroll: true,
        onFinish: () => { updatingId.value = null },
    })
}

const statusLabel = (s) => ({ placed: 'New', received: 'Preparing', ready: 'Ready', complete: 'Complete', cancelled: 'Cancelled' }[s] || capitalize(s))
const actionLabel = (s) => ({ received: 'Start Preparing', ready: 'Mark Ready', complete: 'Complete' }[s] || statusLabel(s))
const fmt = (n) => Number(n || 0).toFixed(2)
const capitalize = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
</script>

<style scoped>
.queue-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px; }
.summary-card { background: #fff; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 14px 16px; text-align: left; cursor: pointer; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
.summary-card span { display: block; color: #6b7280; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
.summary-card strong { color: #111827; font-size: 24px; font-weight: 900; }
.summary-card.active { border-color: #e85d04; box-shadow: 0 0 0 3px rgba(232,93,4,.12); }
.summary-card.placed { border-left: 5px solid #0369a1; }
.summary-card.received { border-left: 5px solid #d97706; }
.summary-card.ready { border-left: 5px solid #15803d; }
.summary-card.complete { border-left: 5px solid #6b7280; }
.summary-card.all { border-left: 5px solid #7c3aed; }
.filter-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.filter-input { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 12px; font-size: 14px; flex: 1; min-width: 190px; }
.filter-input.sm { flex: 0 0 140px; }
.filter-select { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 12px; font-size: 14px; }
.filter-btn { background: #e85d04; color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; }
.filter-btn.secondary { background: #f3f4f6; color: #374151; }
.card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
.orders-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.orders-table th { background: #f9fafb; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
.orders-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.orders-table tr:last-child td { border-bottom: none; }
.row-placed { background: #f8fcff; }
.fw-bold { font-weight: 700; }
.text-sm { font-size: 12px; }
.text-muted { color: #9ca3af; }
.status-badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
.status-badge.placed { background: #e0f2fe; color: #0369a1; }
.status-badge.received { background: #fef3c7; color: #92400e; }
.status-badge.ready { background: #dcfce7; color: #15803d; }
.status-badge.complete { background: #f3f4f6; color: #6b7280; }
.status-badge.cancelled { background: #fee2e2; color: #dc2626; }
.method-chip { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.method-chip.pickup { background: #ede9fe; color: #6d28d9; }
.method-chip.delivery { background: #dbeafe; color: #1d4ed8; }
.quick-btn { border: none; border-radius: 8px; padding: 8px 12px; font-size: 12px; font-weight: 800; cursor: pointer; white-space: nowrap; }
.quick-btn.received { background: #fef3c7; color: #92400e; }
.quick-btn.ready { background: #dcfce7; color: #15803d; }
.quick-btn.complete { background: #e0f2fe; color: #0369a1; }
.quick-btn:disabled { opacity: .55; cursor: default; }
.action-link { color: #e85d04; text-decoration: none; font-weight: 700; font-size: 13px; }
.empty-cell { text-align: center; color: #9ca3af; padding: 40px; }
.pagination { display: flex; gap: 4px; padding: 16px; justify-content: center; border-top: 1px solid #f3f4f6; }
.page-btn { padding: 6px 12px; border: 1.5px solid #e5e7eb; border-radius: 6px; background: #fff; font-size: 13px; cursor: pointer; }
.page-btn.active { background: #e85d04; color: #fff; border-color: #e85d04; }
.page-btn.disabled { opacity: 0.4; cursor: default; }
@media (max-width: 900px) { .orders-table { min-width: 900px; } .card { overflow-x: auto; } }
</style>
