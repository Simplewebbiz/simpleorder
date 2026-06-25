<template>
    <AdminLayout :page-title="'Order #' + order.increment_id">
        <div class="order-show">
            <div class="back-row">
                <Link :href="route('tenant.admin.orders.index')" class="back-link">← Back to Orders</Link>
            </div>

            <div class="order-grid">
                <!-- Left column -->
                <div class="left-col">
                    <!-- Status card -->
                    <div class="card status-card">
                        <div class="card-header">Order Status</div>
                        <div class="current-status">
                            <span class="status-badge lg" :class="order.status">{{ capitalize(order.status) }}</span>
                        </div>
                        <div class="status-buttons" v-if="order.status !== 'complete' && order.status !== 'cancelled'">
                            <div class="status-btn-row">
                                <button
                                    v-for="s in nextStatuses"
                                    :key="s"
                                    class="status-btn"
                                    :class="s"
                                    :disabled="updating"
                                    @click="updateStatus(s)"
                                >
                                    {{ capitalize(s) }} →
                                </button>
                            </div>
                            <button
                                class="cancel-btn"
                                :disabled="updating"
                                @click="updateStatus('cancelled')"
                                v-if="order.status !== 'cancelled'"
                            >Cancel Order</button>
                        </div>
                        <div class="status-note" v-if="order.status === 'complete' || order.status === 'cancelled'">
                            This order is {{ order.status }}.
                        </div>
                    </div>

                    <!-- Customer card -->
                    <div class="card">
                        <div class="card-header">Customer</div>
                        <div class="detail-row"><span>Name</span><span>{{ order.contact_firstname }} {{ order.contact_lastname }}</span></div>
                        <div class="detail-row"><span>Email</span><span>{{ order.contact_email }}</span></div>
                        <div class="detail-row"><span>Phone</span><span>{{ order.contact_phone }}</span></div>
                    </div>

                    <!-- Method card -->
                    <div class="card">
                        <div class="card-header">Order Method</div>
                        <div class="method-block" :class="order.method">
                            <div class="method-icon">{{ order.method === 'pickup' ? '🏪' : '🚗' }}</div>
                            <div>
                                <div class="method-title">{{ capitalize(order.method) }}</div>
                                <div class="method-addr" v-if="order.method === 'delivery'">
                                    {{ order.delivery_address }}<br>
                                    {{ order.delivery_city }}, {{ order.delivery_state }} {{ order.delivery_zip }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing card -->
                    <div class="card">
                        <div class="card-header">Billing</div>
                        <div class="detail-row"><span>Card</span><span>{{ order.card_brand?.toUpperCase() }} •••• {{ order.card_last4 }}</span></div>
                        <div class="detail-row"><span>Address</span><span>{{ order.billing_address }}, {{ order.billing_city }}, {{ order.billing_state }}</span></div>
                        <div class="detail-row"><span>Stripe PI</span><span class="mono">{{ order.stripe_payment_intent }}</span></div>
                    </div>
                </div>

                <!-- Right column -->
                <div class="right-col">
                    <!-- Items card -->
                    <div class="card">
                        <div class="card-header">Items</div>
                        <div class="order-item" v-for="item in order.items" :key="item.id">
                            <div class="oi-row">
                                <span class="oi-name">{{ item.qty }}× {{ item.name }}</span>
                                <span class="oi-price">${{ lineTotal(item).toFixed(2) }}</span>
                            </div>
                            <div class="oi-option" v-for="opt in item.options" :key="opt.label">
                                <span class="opt-label">{{ opt.label }}:</span>
                                <span v-for="v in opt.values" :key="v.label" class="opt-val">{{ v.label }}<template v-if="v.price > 0"> (+${{ Number(v.price).toFixed(2) }})</template></span>
                            </div>
                            <div class="oi-note" v-if="item.comments">Note: {{ item.comments }}</div>
                        </div>

                        <div class="totals">
                            <div class="total-row"><span>Subtotal</span><span>${{ fmt(order.subtotal) }}</span></div>
                            <div class="total-row"><span>Tax</span><span>${{ fmt(order.tax) }}</span></div>
                            <div class="total-row" v-if="order.delivery > 0"><span>Delivery</span><span>${{ fmt(order.delivery) }}</span></div>
                            <div class="total-row" v-if="order.tip > 0"><span>Tip</span><span>${{ fmt(order.tip) }}</span></div>
                            <div class="total-row discount" v-if="order.discount > 0"><span>Discount <template v-if="order.coupon_code">({{ order.coupon_code }})</template></span><span>- ${{ fmt(order.discount) }}</span></div>
                            <div class="total-row grand"><span>Grand Total</span><span>${{ fmt(order.total) }}</span></div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="card" v-if="order.comments">
                        <div class="card-header">Order Notes</div>
                        <p class="order-notes">{{ order.comments }}</p>
                    </div>

                    <!-- Timestamps -->
                    <div class="card">
                        <div class="card-header">Timeline</div>
                        <div class="detail-row"><span>Placed</span><span>{{ formatDate(order.created_at) }}</span></div>
                        <div class="detail-row"><span>Updated</span><span>{{ formatDate(order.updated_at) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'
import axios from 'axios'

const props = defineProps({ order: Object })
const updating = ref(false)

const statusFlow = ['placed', 'received', 'ready', 'complete']

const nextStatuses = computed(() => {
    const i = statusFlow.indexOf(props.order.status)
    return i >= 0 && i < statusFlow.length - 1 ? [statusFlow[i + 1]] : []
})

async function updateStatus(status) {
    updating.value = true
    try {
        await axios.patch(route('tenant.admin.orders.status', props.order.id), { status })
        router.reload({ only: ['order'] })
    } finally {
        updating.value = false
    }
}

function lineTotal(item) {
    let price = parseFloat(item.price), pct = 1
    for (const opt of item.options || [])
        for (const v of opt.values || [])
            v.price_type === 'percent' ? (pct += parseFloat(v.price) / 100) : (price += parseFloat(v.price))
    return Math.round(price * pct * item.qty * 100) / 100
}

const fmt = (n) => Number(n || 0).toFixed(2)
const capitalize = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
const formatDate = (d) => d ? new Date(d).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) : '—'
</script>

<style scoped>
.back-row { margin-bottom: 16px; }
.back-link { color: #e85d04; text-decoration: none; font-weight: 600; font-size: 14px; }
.order-grid { display: grid; grid-template-columns: 360px 1fr; gap: 20px; }
@media (max-width: 900px) { .order-grid { grid-template-columns: 1fr; } }
.left-col, .right-col { display: flex; flex-direction: column; gap: 16px; }
.card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.card-header { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #f3f4f6; }
.status-card .current-status { margin-bottom: 16px; }
.status-badge { padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.status-badge.lg { font-size: 13px; padding: 6px 16px; }
.status-badge.placed { background: #e0f2fe; color: #0369a1; }
.status-badge.received { background: #fef3c7; color: #92400e; }
.status-badge.ready { background: #dcfce7; color: #15803d; }
.status-badge.complete { background: #f3f4f6; color: #6b7280; }
.status-badge.cancelled { background: #fee2e2; color: #dc2626; }
.status-btn-row { display: flex; gap: 8px; margin-bottom: 10px; }
.status-btn { flex: 1; padding: 10px; border: none; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; }
.status-btn.received { background: #fef3c7; color: #92400e; }
.status-btn.ready { background: #dcfce7; color: #15803d; }
.status-btn.complete { background: #e0f2fe; color: #0369a1; }
.cancel-btn { width: 100%; padding: 8px; border: 1.5px solid #fee2e2; border-radius: 8px; background: #fff; color: #dc2626; font-size: 13px; font-weight: 700; cursor: pointer; }
.cancel-btn:hover { background: #fee2e2; }
.status-note { font-size: 13px; color: #9ca3af; }
.detail-row { display: flex; justify-content: space-between; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #f9fafb; }
.detail-row span:first-child { color: #6b7280; }
.detail-row span:last-child { font-weight: 500; text-align: right; max-width: 60%; }
.mono { font-family: monospace; font-size: 12px; }
.method-block { display: flex; gap: 12px; padding: 12px; background: #f9fafb; border-radius: 8px; }
.method-icon { font-size: 24px; flex-shrink: 0; }
.method-title { font-weight: 700; margin-bottom: 4px; }
.method-addr { font-size: 13px; color: #6b7280; line-height: 1.5; }
.order-item { padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
.order-item:last-of-type { border-bottom: none; }
.oi-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 4px; }
.oi-name { font-weight: 600; }
.oi-price { font-weight: 600; }
.oi-option { font-size: 12px; color: #6b7280; display: flex; gap: 4px; margin-top: 2px; }
.oi-note { font-size: 12px; color: #9ca3af; font-style: italic; margin-top: 3px; }
.totals { margin-top: 12px; border-top: 2px solid #f3f4f6; }
.total-row { display: flex; justify-content: space-between; font-size: 14px; padding: 7px 0; }
.total-row.discount { color: #15803d; font-weight: 700; }
.total-row.grand { font-size: 16px; font-weight: 800; border-top: 2px solid #1a1a1a; margin-top: 6px; padding-top: 10px; }
.order-notes { font-size: 14px; color: #374151; line-height: 1.5; }
</style>
