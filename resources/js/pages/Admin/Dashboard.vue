<template>
    <AdminLayout page-title="Dashboard" :pending-count="pendingOrders.length">
        <!-- Stats row -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Today's Orders</div>
                <div class="stat-value">{{ stats.today_orders }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Today's Revenue</div>
                <div class="stat-value">${{ fmt(stats.today_revenue) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending</div>
                <div class="stat-value orange">{{ stats.pending }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">This Month</div>
                <div class="stat-value">${{ fmt(stats.month_revenue) }}</div>
            </div>
        </div>

        <!-- New order alert -->
        <Transition name="alert-slide">
            <div class="new-order-alert" v-if="newOrderAlert">
                <span>🔔 New order received — <strong>#{{ newOrderAlert.increment_id }}</strong></span>
                <button @click="viewOrder(newOrderAlert)">View →</button>
                <button class="dismiss" @click="newOrderAlert = null">✕</button>
            </div>
        </Transition>

        <!-- Recent orders table -->
        <div class="section-header">
            <h2 class="section-title">Recent Orders</h2>
            <Link :href="route('tenant.admin.orders.index')" class="view-all">View All →</Link>
        </div>

        <div class="card">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Time</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in recentOrders" :key="order.id">
                        <td class="order-num">#{{ order.increment_id }}</td>
                        <td>{{ order.contact_firstname }} {{ order.contact_lastname }}</td>
                        <td>
                            <span class="method-chip" :class="order.method">{{ order.method }}</span>
                        </td>
                        <td>
                            <span class="status-badge" :class="order.status">{{ order.status }}</span>
                        </td>
                        <td class="fw-bold">${{ fmt(order.total) }}</td>
                        <td class="text-muted">{{ timeAgo(order.created_at) }}</td>
                        <td>
                            <Link :href="route('tenant.admin.orders.show', order.id)" class="action-link">View</Link>
                        </td>
                    </tr>
                    <tr v-if="recentOrders.length === 0">
                        <td colspan="7" class="empty-cell">No orders yet</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '../../components/Admin/Layout.vue'

const props = defineProps({
    stats: Object,
    recentOrders: Array,
    pendingOrders: { type: Array, default: () => [] },
})

const newOrderAlert = ref(null)
let channel = null

onMounted(() => {
    channel = window.Echo.channel('admin.' + window._tenantId + '.orders')
        .listen('.order-placed', (e) => {
            newOrderAlert.value = e.order
            // Refresh the page data
            router.reload({ only: ['stats', 'recentOrders', 'pendingOrders'] })
        })
})

onUnmounted(() => {
    if (channel) window.Echo.leaveChannel('admin.' + window._tenantId + '.orders')
})

function viewOrder(order) {
    router.visit(route('tenant.admin.orders.show', order.id))
    newOrderAlert.value = null
}

const fmt = (n) => Number(n || 0).toFixed(2)

function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000)
    if (diff < 60) return diff + 's ago'
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago'
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago'
    return Math.floor(diff / 86400) + 'd ago'
}
</script>

<style scoped>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.stat-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-bottom: 8px; }
.stat-value { font-size: 28px; font-weight: 800; color: #1a1a1a; }
.stat-value.orange { color: #e85d04; }
.new-order-alert { background: #fff7ed; border: 2px solid #e85d04; border-radius: 10px; padding: 14px 20px; display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.new-order-alert span { flex: 1; font-size: 14px; }
.new-order-alert button { background: #e85d04; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; }
.new-order-alert button.dismiss { background: none; color: #9ca3af; padding: 8px; }
.alert-slide-enter-active, .alert-slide-leave-active { transition: all 0.3s; }
.alert-slide-enter-from, .alert-slide-leave-to { opacity: 0; transform: translateY(-10px); }
.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.section-title { font-size: 17px; font-weight: 700; }
.view-all { font-size: 13px; color: #e85d04; text-decoration: none; font-weight: 600; }
.card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
.orders-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.orders-table th { background: #f9fafb; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
.orders-table td { padding: 14px 16px; border-bottom: 1px solid #f3f4f6; }
.orders-table tr:last-child td { border-bottom: none; }
.order-num { font-weight: 700; color: #1a1a1a; }
.fw-bold { font-weight: 700; }
.text-muted { color: #9ca3af; }
.status-badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.status-badge.placed { background: #e0f2fe; color: #0369a1; }
.status-badge.received { background: #fef3c7; color: #92400e; }
.status-badge.ready { background: #dcfce7; color: #15803d; }
.status-badge.complete { background: #f3f4f6; color: #6b7280; }
.status-badge.cancelled { background: #fee2e2; color: #dc2626; }
.method-chip { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.method-chip.pickup { background: #ede9fe; color: #6d28d9; }
.method-chip.delivery { background: #dbeafe; color: #1d4ed8; }
.action-link { color: #e85d04; text-decoration: none; font-weight: 600; font-size: 13px; }
.empty-cell { text-align: center; color: #9ca3af; padding: 32px; }
</style>
