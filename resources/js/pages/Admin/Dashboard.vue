<template>
    <AdminLayout page-title="Dashboard" :pending-count="pendingOrders.length">
        <div class="dashboard-grid" v-if="canManage">
            <section class="setup-panel">
                <div class="setup-copy">
                    <p class="eyebrow">Setup Progress</p>
                    <h2>{{ completedSteps }} of {{ onboarding.length }} steps complete</h2>
                    <p>Finish these items so the restaurant can take orders with fewer surprises.</p>
                </div>
                <div class="setup-meter" aria-hidden="true">
                    <span :style="{ width: setupPercent + '%' }"></span>
                </div>
                <div class="setup-list">
                    <a v-for="step in onboarding" :key="step.key" :href="step.href" class="setup-item" :class="{ done: step.done }">
                        <span class="setup-check">{{ step.done ? 'Done' : 'Next' }}</span>
                        <span>{{ step.label }}</span>
                    </a>
                </div>
            </section>

            <section class="quick-panel">
                <p class="eyebrow">Quick Links</p>
                <div class="quick-actions">
                    <Link :href="route('tenant.admin.orders.index')">Order Queue</Link>
                    <Link :href="route('tenant.admin.items.create')">Add Menu Item</Link>
                    <Link :href="route('tenant.admin.pages.index')">Edit Website</Link>
                    <Link :href="route('tenant.admin.settings.stripe')">Payments</Link>
                </div>
            </section>
        </div>

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
                <div class="stat-label">Open Orders</div>
                <div class="stat-value orange">{{ stats.pending }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">This Month</div>
                <div class="stat-value">${{ fmt(stats.month_revenue) }}</div>
            </div>
        </div>

        <Transition name="alert-slide">
            <div class="new-order-alert" v-if="newOrderAlert">
                <span>New order received: <strong>#{{ newOrderAlert.increment_id }}</strong></span>
                <button @click="viewOrder(newOrderAlert)">View</button>
                <button class="dismiss" @click="newOrderAlert = null">Dismiss</button>
            </div>
        </Transition>

        <div class="columns">
            <section>
                <div class="section-header">
                    <h2 class="section-title">Open Orders</h2>
                    <Link :href="route('tenant.admin.orders.index', { status: 'placed' })" class="view-all">View queue</Link>
                </div>
                <div class="card order-list">
                    <Link v-for="order in pendingOrders" :key="order.id" :href="route('tenant.admin.orders.show', order.id)" class="order-row">
                        <span class="order-num">#{{ order.increment_id }}</span>
                        <span>{{ order.contact_firstname }} {{ order.contact_lastname }}</span>
                        <span class="status-badge" :class="order.status">{{ order.status }}</span>
                        <span class="order-total">${{ fmt(order.total) }}</span>
                    </Link>
                    <div v-if="pendingOrders.length === 0" class="empty-cell">No open orders right now.</div>
                </div>
            </section>

            <section>
                <div class="section-header">
                    <h2 class="section-title">Recent Orders</h2>
                    <Link :href="route('tenant.admin.orders.index')" class="view-all">View all</Link>
                </div>
                <div class="card order-list">
                    <Link v-for="order in recentOrders" :key="order.id" :href="route('tenant.admin.orders.show', order.id)" class="order-row">
                        <span class="order-num">#{{ order.increment_id }}</span>
                        <span>{{ order.contact_firstname }} {{ order.contact_lastname }}</span>
                        <span class="method-chip" :class="order.method">{{ order.method }}</span>
                        <span class="text-muted">{{ timeAgo(order.created_at) }}</span>
                    </Link>
                    <div v-if="recentOrders.length === 0" class="empty-cell">No orders yet.</div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '../../components/Admin/Layout.vue'

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    recentOrders: { type: Array, default: () => [] },
    pendingOrders: { type: Array, default: () => [] },
    onboarding: { type: Array, default: () => [] },
})

const page = usePage()
const canManage = ['owner', 'manager'].includes(page.props.auth?.tenant_user?.role)
const newOrderAlert = ref(null)
let channel = null

const completedSteps = computed(() => props.onboarding.filter(step => step.done).length)
const setupPercent = computed(() => props.onboarding.length ? Math.round((completedSteps.value / props.onboarding.length) * 100) : 0)

onMounted(() => {
    const tenantId = page.props.auth?.tenant?.id || page.props.auth?.tenant_user?.tenant_id
    if (!window.Echo || !tenantId) return

    channel = window.Echo.channel('admin.' + tenantId + '.orders')
        .listen('.order-placed', (event) => {
            newOrderAlert.value = event.order
            router.reload({ only: ['stats', 'recentOrders', 'pendingOrders'] })
        })
})

onUnmounted(() => {
    const tenantId = page.props.auth?.tenant?.id || page.props.auth?.tenant_user?.tenant_id
    if (channel && window.Echo && tenantId) window.Echo.leaveChannel('admin.' + tenantId + '.orders')
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
.dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(260px, .8fr); gap: 18px; margin-bottom: 22px; }
.setup-panel, .quick-panel, .stat-card, .card { background: #fff; border: 1px solid #e3ecea; border-radius: 10px; box-shadow: 0 1px 3px rgba(16, 24, 40, .05); }
.setup-panel { padding: 22px; }
.setup-copy h2 { margin: 4px 0 6px; color: #17272b; font-size: 22px; }
.setup-copy p:last-child { color: #647477; margin: 0 0 18px; }
.eyebrow { margin: 0; font-size: 11px; font-weight: 900; text-transform: uppercase; color: #0f766e; letter-spacing: .08em; }
.setup-meter { height: 10px; border-radius: 999px; background: #e8f3f0; overflow: hidden; margin-bottom: 16px; }
.setup-meter span { display: block; height: 100%; background: linear-gradient(90deg, #0f766e, #ff7a59); }
.setup-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; }
.setup-item { display: flex; gap: 10px; align-items: center; padding: 11px; border: 1px solid #e5edf0; border-radius: 8px; color: #314246; text-decoration: none; font-size: 13px; font-weight: 800; }
.setup-item.done { color: #0f766e; background: #eefaf5; }
.setup-check { min-width: 42px; text-align: center; border-radius: 999px; padding: 4px 8px; background: #fff4ed; color: #c2410c; font-size: 10px; text-transform: uppercase; }
.setup-item.done .setup-check { background: #dff8eb; color: #15803d; }
.quick-panel { padding: 22px; }
.quick-actions { display: grid; gap: 10px; margin-top: 14px; }
.quick-actions a { text-decoration: none; color: #17272b; font-weight: 900; border: 1px solid #e5edf0; border-radius: 8px; padding: 12px; background: #fbfdfc; }
.quick-actions a:hover { border-color: #ffb199; color: #c2410c; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card { padding: 20px; }
.stat-label { font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; color: #7b8b8f; margin-bottom: 8px; }
.stat-value { font-size: 28px; font-weight: 900; color: #17272b; }
.stat-value.orange { color: #e85d04; }
.new-order-alert { background: #fff7ed; border: 2px solid #e85d04; border-radius: 10px; padding: 14px 18px; display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.new-order-alert span { flex: 1; font-size: 14px; }
.new-order-alert button { background: #e85d04; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 800; cursor: pointer; }
.new-order-alert button.dismiss { background: #fff; color: #7b8b8f; border: 1px solid #eadfd7; }
.columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.section-title { font-size: 17px; font-weight: 900; color: #17272b; margin: 0; }
.view-all { font-size: 13px; color: #e85d04; text-decoration: none; font-weight: 800; }
.order-list { overflow: hidden; }
.order-row { display: grid; grid-template-columns: 72px 1fr auto auto; gap: 12px; align-items: center; padding: 13px 16px; color: #314246; text-decoration: none; border-bottom: 1px solid #edf2f1; font-size: 14px; }
.order-row:last-child { border-bottom: none; }
.order-row:hover { background: #fbf7f2; }
.order-num, .order-total { font-weight: 900; color: #17272b; }
.text-muted { color: #7b8b8f; font-size: 12px; }
.status-badge, .method-chip { padding: 3px 9px; border-radius: 999px; font-size: 10px; font-weight: 900; text-transform: uppercase; }
.status-badge.placed { background: #e0f2fe; color: #0369a1; }
.status-badge.received { background: #fef3c7; color: #92400e; }
.status-badge.ready { background: #dcfce7; color: #15803d; }
.status-badge.complete { background: #f3f4f6; color: #6b7280; }
.status-badge.cancelled { background: #fee2e2; color: #dc2626; }
.method-chip.pickup { background: #ede9fe; color: #6d28d9; }
.method-chip.delivery { background: #dbeafe; color: #1d4ed8; }
.empty-cell { text-align: center; color: #7b8b8f; padding: 32px; }
.alert-slide-enter-active, .alert-slide-leave-active { transition: all .25s; }
.alert-slide-enter-from, .alert-slide-leave-to { opacity: 0; transform: translateY(-8px); }
@media (max-width: 960px) { .dashboard-grid, .columns { grid-template-columns: 1fr; } }
@media (max-width: 620px) { .order-row { grid-template-columns: 64px 1fr; } .order-row span:nth-child(3), .order-row span:nth-child(4) { justify-self: start; } }
</style>