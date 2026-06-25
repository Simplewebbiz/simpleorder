<template>
    <div class="order-placed">
        <div class="container">

            <!-- Store logo at top of confirmation -->
            <div class="confirmation-brand" v-if="brand.logo || brand.name">
                <img v-if="brand.logo" :src="brand.logo" :alt="brand.name" class="conf-logo" />
                <span v-else class="conf-name">{{ brand.name }}</span>
            </div>

            <div class="confirmation-card" v-if="order">
                <!-- Status header -->
                <div class="conf-header" :class="'status-' + order.status">
                    <div class="conf-icon">{{ statusIcon }}</div>
                    <h1 class="conf-title">{{ statusTitle }}</h1>
                    <p class="conf-sub">Order #{{ order.increment_id }}</p>
                </div>

                <!-- Progress tracker -->
                <div class="progress-tracker" v-if="order.status !== 'cancelled'">
                    <div
                        v-for="(step, i) in progressSteps"
                        :key="step.key"
                        class="progress-step"
                        :class="{
                            active: step.key === order.status,
                            complete: stepIndex(step.key) < stepIndex(order.status)
                        }"
                    >
                        <div class="step-dot">{{ stepIndex(step.key) < stepIndex(order.status) ? '✓' : (i + 1) }}</div>
                        <div class="step-label">{{ step.label }}</div>
                    </div>
                </div>

                <div class="conf-body">
                    <div class="conf-grid">

                        <!-- Left column -->
                        <div class="conf-col">
                            <!-- Method info -->
                            <div class="conf-section">
                                <div class="conf-section-title">Order Method</div>
                                <div class="method-block pickup" v-if="order.method === 'pickup'">
                                    <div class="method-icon">🏪</div>
                                    <div>
                                        <div class="method-title">Pickup</div>
                                        <div class="method-addr" v-if="brand.address && brand.address.address">
                                            {{ brand.address.address }}<br>
                                            {{ brand.address.city }}, {{ brand.address.state }} {{ brand.address.zip }}
                                        </div>
                                        <div class="method-note">We'll notify you when it's ready!</div>
                                        <a v-if="brand.phone" :href="'tel:' + brand.phone" class="method-phone">{{ brand.phone }}</a>
                                    </div>
                                </div>
                                <div class="method-block delivery" v-else>
                                    <div class="method-icon">🚗</div>
                                    <div>
                                        <div class="method-title">Delivery</div>
                                        <div class="method-addr">
                                            {{ order.delivery_address }}<br>
                                            {{ order.delivery_city }}, {{ order.delivery_state }} {{ order.delivery_zip }}
                                        </div>
                                        <div class="method-note">We'll notify you when it's on the way!</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact -->
                            <div class="conf-section">
                                <div class="conf-section-title">Contact Information</div>
                                <div class="detail-row"><span>Name</span><span>{{ order.contact_firstname }} {{ order.contact_lastname }}</span></div>
                                <div class="detail-row"><span>Email</span><span>{{ order.contact_email }}</span></div>
                                <div class="detail-row"><span>Phone</span><span>{{ order.contact_phone }}</span></div>
                            </div>

                            <!-- Billing -->
                            <div class="conf-section">
                                <div class="conf-section-title">Billing</div>
                                <div class="detail-row"><span>Card</span><span>{{ order.card_brand?.toUpperCase() }} •••• {{ order.card_last4 }}</span></div>
                                <div class="detail-row"><span>Billing Address</span><span>{{ order.billing_address }}, {{ order.billing_city }}, {{ order.billing_state }}</span></div>
                            </div>
                        </div>

                        <!-- Right column — items + totals -->
                        <div class="conf-col">
                            <div class="conf-section">
                                <div class="conf-section-title">Items Ordered</div>
                                <div class="order-item" v-for="item in order.items" :key="item.id">
                                    <div class="oi-row">
                                        <span class="oi-name">{{ item.qty }}× {{ item.name }}</span>
                                        <span class="oi-price">${{ lineTotal(item).toFixed(2) }}</span>
                                    </div>
                                    <div class="oi-option" v-for="opt in item.options" :key="opt.label">
                                        <span>{{ opt.label }}:</span>
                                        <span v-for="v in opt.values" :key="v.label">{{ v.label }}</span>
                                    </div>
                                    <div class="oi-note" v-if="item.comments">{{ item.comments }}</div>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="order-totals">
                                <div class="total-row"><span>Subtotal</span><span>${{ fmt(order.subtotal) }}</span></div>
                                <div class="total-row"><span>Tax</span><span>${{ fmt(order.tax) }}</span></div>
                                <div class="total-row" v-if="order.delivery > 0"><span>Delivery</span><span>${{ fmt(order.delivery) }}</span></div>
                                <div class="total-row" v-if="order.tip > 0"><span>Tip</span><span>${{ fmt(order.tip) }}</span></div>
                                <div class="total-row grand"><span>Total</span><span>${{ fmt(order.total) }}</span></div>
                            </div>

                            <div class="order-notes" v-if="order.comments">
                                <div class="conf-section-title">Order Notes</div>
                                <p>{{ order.comments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Store contact footer on receipt -->
                <div class="receipt-contact" v-if="brand.phone || brand.email">
                    <div class="rc-title">Questions? Contact us:</div>
                    <a v-if="brand.phone" :href="'tel:' + brand.phone" class="rc-item">📞 {{ brand.phone }}</a>
                    <a v-if="brand.email" :href="'mailto:' + brand.email" class="rc-item">✉️ {{ brand.email }}</a>
                </div>
            </div>

            <div class="loading-card" v-else>
                <template v-if="loadError">
                    <p class="load-error">{{ loadError }}</p>
                    <a href="/" class="home-link">Back to menu</a>
                </template>
                <template v-else>
                    <div class="spinner"></div>
                    <p>Loading your order...</p>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    order: Object,
    settings: Object,
    orderKey: String,
})

const page = usePage()
const brand = page.props.tenant_brand || {}
const order = ref(props.order || null)
const loadError = ref('')

const progressSteps = computed(() => [
    { key: 'placed', label: 'Placed' },
    { key: 'received', label: 'Received' },
    { key: 'ready', label: order.value?.method === 'delivery' ? 'On the Way' : 'Ready' },
    { key: 'complete', label: 'Complete' },
])

const statusOrder = ['placed', 'received', 'ready', 'complete']
const stepIndex = (key) => statusOrder.indexOf(key)

const statusIcon = computed(() => ({
    placed: '📋', received: '👨‍🍳', ready: order.value?.method === 'delivery' ? '🚗' : '🎉', complete: '⭐', cancelled: '❌',
}[order.value?.status] || '📦'))

const statusTitle = computed(() => ({
    placed: 'Order Confirmed!',
    received: "We're on it!",
    ready: order.value?.method === 'delivery' ? 'On its way!' : 'Ready for pickup!',
    complete: 'Order Complete — Thank you!',
    cancelled: 'Order Cancelled',
}[order.value?.status] || 'Order Update'))

const fmt = (n) => Number(n || 0).toFixed(2)

function lineTotal(item) {
    let price = parseFloat(item.price), pct = 1
    for (const opt of item.options || [])
        for (const v of opt.values || [])
            v.price_type === 'percent' ? (pct += parseFloat(v.price) / 100) : (price += parseFloat(v.price))
    return Math.round(price * pct * item.qty * 100) / 100
}

// Real-time updates via Echo
let channel = null
onMounted(async () => {
    try {
        if (!order.value && props.orderKey) {
            const { data } = await axios.post('/ordering/order/' + props.orderKey, {})
            order.value = data
        }
    } catch (error) {
        loadError.value = 'We could not find that order. Please contact the restaurant if you need help.'
        return
    }

    if (order.value?.key && window.Echo) {
        channel = window.Echo.channel('order.' + order.value.key)
            .listen('.order-updated', async () => {
                const { data } = await axios.post('/ordering/order/' + order.value.key, {})
                order.value = data
            })
    }
})

onUnmounted(() => {
    if (channel && window.Echo) window.Echo.leaveChannel('order.' + order.value?.key)
})
</script>

<style scoped>
.order-placed { padding: 32px 0 64px; background: #f5f5f5; min-height: 80vh; }
.container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
.confirmation-brand { text-align: center; margin-bottom: 24px; }
.conf-logo { max-height: 56px; max-width: 200px; object-fit: contain; }
.conf-name { font-size: 22px; font-weight: 800; color: #1a1a1a; }
.confirmation-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
.conf-header { padding: 32px; text-align: center; }
.conf-header.status-placed { background: linear-gradient(135deg, #1a1a1a, #374151); color: #fff; }
.conf-header.status-received { background: linear-gradient(135deg, #1d4ed8, #3b82f6); color: #fff; }
.conf-header.status-ready { background: linear-gradient(135deg, #15803d, #22c55e); color: #fff; }
.conf-header.status-complete { background: linear-gradient(135deg, #92400e, #f59e0b); color: #fff; }
.conf-header.status-cancelled { background: linear-gradient(135deg, #7f1d1d, #ef4444); color: #fff; }
.conf-icon { font-size: 52px; margin-bottom: 12px; }
.conf-title { font-size: 28px; font-weight: 800; margin-bottom: 6px; }
.conf-sub { opacity: 0.8; font-size: 15px; }
.progress-tracker { display: flex; padding: 20px 32px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; gap: 0; }
.progress-step { flex: 1; text-align: center; position: relative; }
.progress-step::before { content: ''; position: absolute; top: 16px; left: -50%; right: 50%; height: 2px; background: #e5e7eb; z-index: 0; }
.progress-step:first-child::before { display: none; }
.progress-step.complete::before { background: #22c55e; }
.progress-step.active::before { background: #e85d04; }
.step-dot { width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; margin: 0 auto 8px; position: relative; z-index: 1; }
.progress-step.complete .step-dot { background: #22c55e; color: #fff; }
.progress-step.active .step-dot { background: #e85d04; color: #fff; }
.step-label { font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.04em; }
.progress-step.complete .step-label { color: #15803d; }
.progress-step.active .step-label { color: #e85d04; }
.conf-body { padding: 32px; }
.conf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
@media (max-width: 640px) { .conf-grid { grid-template-columns: 1fr; } }
.conf-section { margin-bottom: 24px; }
.conf-section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #f3f4f6; }
.method-block { display: flex; gap: 14px; padding: 14px; background: #f9fafb; border-radius: 8px; }
.method-icon { font-size: 28px; flex-shrink: 0; }
.method-title { font-weight: 700; margin-bottom: 4px; }
.method-addr { font-size: 13px; color: #374151; line-height: 1.5; margin-bottom: 6px; }
.method-note { font-size: 12px; color: #6b7280; font-style: italic; }
.method-phone { font-size: 13px; color: #e85d04; text-decoration: none; font-weight: 600; }
.detail-row { display: flex; justify-content: space-between; font-size: 14px; padding: 7px 0; border-bottom: 1px solid #f3f4f6; }
.detail-row span:first-child { color: #6b7280; }
.detail-row span:last-child { font-weight: 500; text-align: right; max-width: 60%; }
.order-item { border-bottom: 1px solid #f3f4f6; padding: 10px 0; }
.oi-row { display: flex; justify-content: space-between; font-size: 14px; }
.oi-name { font-weight: 600; }
.oi-price { font-weight: 600; }
.oi-option { font-size: 12px; color: #6b7280; margin-top: 2px; display: flex; gap: 4px; }
.oi-note { font-size: 12px; color: #9ca3af; font-style: italic; margin-top: 3px; }
.order-totals { border-top: 2px solid #f3f4f6; margin-top: 8px; }
.total-row { display: flex; justify-content: space-between; font-size: 14px; padding: 7px 0; }
.total-row.grand { font-size: 16px; font-weight: 800; border-top: 2px solid #1a1a1a; margin-top: 6px; padding-top: 10px; }
.order-notes { margin-top: 16px; font-size: 14px; color: #374151; }
.receipt-contact { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.rc-title { font-size: 13px; color: #6b7280; font-weight: 600; }
.rc-item { font-size: 14px; color: #e85d04; text-decoration: none; font-weight: 600; }
.loading-card { text-align: center; padding: 80px 20px; background: #fff; border-radius: 16px; }
.load-error { max-width: 420px; margin: 0 auto 18px; color: #7f1d1d; font-weight: 700; line-height: 1.5; }
.home-link { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 20px; border-radius: 999px; background: #e85d04; color: #fff; font-weight: 800; text-decoration: none; }
.spinner { width: 40px; height: 40px; border: 3px solid #e5e7eb; border-top-color: #e85d04; border-radius: 50%; animation: spin 0.7s linear infinite; margin: 0 auto 16px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
