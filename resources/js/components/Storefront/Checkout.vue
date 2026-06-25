<template>
    <div class="checkout container">
        <!-- Store closed -->
        <div v-if="!storeOpen" class="closed-notice">
            <h3>We're currently closed. Please check back during our hours.</h3>
        </div>

        <!-- Empty cart -->
        <div v-else-if="cart.items.length === 0" class="empty-cart">
            <h3>Your cart is empty. <button @click="$emit('back')">Browse our menu</button></h3>
        </div>

        <!-- Checkout form -->
        <div v-else class="checkout__grid">
            <div class="checkout__form">
                <!-- Method toggle -->
                <div class="method-toggle">
                    <button :class="{ active: payment.method === 'pickup' }" @click="setMethod('pickup')">Pickup</button>
                    <button :class="{ active: payment.method === 'delivery' }" @click="setMethod('delivery')" v-if="settings.allow_delivery">Delivery</button>
                </div>

                <!-- Delivery address -->
                <div v-if="payment.method === 'delivery'" class="form-section">
                    <h3>Delivery Address</h3>
                    <FormInput v-model="payment.delivery_address.address" label="Street Address" :error="errors.address" />
                    <div class="row-3">
                        <FormInput v-model="payment.delivery_address.city" label="City" :error="errors.city" />
                        <FormInput v-model="payment.delivery_address.state" label="State" :error="errors.state" maxlength="2" />
                        <FormInput v-model="payment.delivery_address.zip" label="ZIP" :error="errors.zip" maxlength="5" />
                    </div>
                </div>

                <!-- Contact -->
                <div class="form-section">
                    <h3>Contact Information</h3>
                    <div class="row-2">
                        <FormInput v-model="payment.contact_firstname" label="First Name" :error="errors.contact_firstname" />
                        <FormInput v-model="payment.contact_lastname" label="Last Name" :error="errors.contact_lastname" />
                    </div>
                    <div class="row-2">
                        <FormInput v-model="payment.contact_email" label="Email" type="email" :error="errors.contact_email" />
                        <FormInput v-model="payment.contact_phone" label="Phone" type="tel" :error="errors.contact_phone" />
                    </div>
                </div>

                <!-- Billing -->
                <div class="form-section">
                    <h3>Billing Information</h3>
                    <div class="row-2">
                        <FormInput v-model="payment.billing_firstname" label="First Name" :error="errors.billing_firstname" />
                        <FormInput v-model="payment.billing_lastname" label="Last Name" :error="errors.billing_lastname" />
                    </div>
                    <FormInput v-model="payment.billing_address" label="Address" :error="errors.billing_address" />
                    <div class="row-3">
                        <FormInput v-model="payment.billing_city" label="City" :error="errors.billing_city" />
                        <FormInput v-model="payment.billing_state" label="State" :error="errors.billing_state" maxlength="2" />
                        <FormInput v-model="payment.billing_zip" label="ZIP" :error="errors.billing_zip" maxlength="5" />
                    </div>
                </div>

                <!-- Card -->
                <div class="form-section">
                    <h3>Payment</h3>
                    <div class="stripe-fields">
                        <div class="stripe-field-wrap">
                            <label>Card Number</label>
                            <div ref="cardNumber" class="stripe-input"></div>
                        </div>
                        <div class="row-2">
                            <div class="stripe-field-wrap">
                                <label>Expiry</label>
                                <div ref="cardExpiry" class="stripe-input"></div>
                            </div>
                            <div class="stripe-field-wrap">
                                <label>CVC</label>
                                <div ref="cardCvc" class="stripe-input"></div>
                            </div>
                        </div>
                    </div>
                    <div v-if="errors.stripe" class="error-text">{{ errors.stripe }}</div>
                </div>

                <!-- Notes -->
                <div class="form-section">
                    <label>Special Instructions</label>
                    <textarea v-model="payment.notes" rows="3" placeholder="Allergies, special requests..."></textarea>
                </div>
            </div>

            <!-- Order summary sidebar -->
            <div class="checkout__summary">
                <h3>Order Summary</h3>
                <CheckoutItem
                    v-for="item in cart.items"
                    :key="item.cart_id"
                    :item="item"
                />

                <div class="checkout__totals">
                    <div class="total-row"><span>Subtotal</span><span>${{ fmt(subtotal) }}</span></div>
                    <div class="total-row"><span>Tax</span><span>${{ fmt(tax) }}</span></div>
                    <div class="total-row" v-if="payment.method === 'delivery'"><span>Delivery</span><span>${{ fmt(delivery) }}</span></div>
                    <div class="total-row tip-row">
                        <span>Tip</span>
                        <input v-model="payment.tip" type="number" step="0.01" min="0" placeholder="0.00" class="tip-input" @change="refreshTotals" />
                    </div>
                    <div class="coupon-row">
                        <input v-model="couponCode" type="text" placeholder="Promo code" @input="couponCode = couponCode.toUpperCase()" />
                        <button type="button" @click="applyCoupon" :disabled="couponLoading">Apply</button>
                    </div>
                    <div class="coupon-message" :class="{ good: couponApplied }" v-if="couponMessage">{{ couponMessage }}</div>
                    <div class="total-row discount" v-if="discount > 0"><span>Discount</span><span>- ${{ fmt(discount) }}</span></div>
                    <div class="total-row grand-total"><span>Total</span><span>${{ fmt(total) }}</span></div>
                </div>

                <button @click="pay" :disabled="loading" class="btn-place-order">
                    <span v-if="loading">Processing...</span>
                    <span v-else>Place Order — ${{ fmt(total) }}</span>
                </button>

                <p class="recaptcha-notice">
                    Protected by reCAPTCHA.
                    <a href="https://policies.google.com/privacy" target="_blank">Privacy</a> &amp;
                    <a href="https://policies.google.com/terms" target="_blank">Terms</a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCartStore, isStoreOpen } from '../../stores/cart'
import FormInput from '../UI/FormInput.vue'
import CheckoutItem from './CheckoutItem.vue'
import axios from 'axios'

const props = defineProps({ settings: Object })
const emit = defineEmits(['placed', 'back'])

const cart = useCartStore()
const storeOpen = ref(isStoreOpen(props.settings))
const loading = ref(false)
const couponLoading = ref(false)
const couponCode = ref(cart.coupon_code || '')
const couponMessage = ref('')
const couponApplied = ref(false)
const errors = ref({})

const payment = ref({
    method: cart.method || 'pickup',
    delivery_address: { ...cart.delivery_address },
    contact_firstname: '',
    contact_lastname: '',
    contact_email: '',
    contact_phone: '',
    billing_firstname: '',
    billing_lastname: '',
    billing_address: '',
    billing_city: '',
    billing_state: '',
    billing_zip: '',
    tip: null,
    coupon_code: cart.coupon_code || '',
    notes: '',
})

// Stripe elements
const cardNumber = ref(null)
const cardExpiry = ref(null)
const cardCvc = ref(null)
let stripeCard = null

const subtotal = computed(() =>
    cart.items.reduce((t, i) => {
        let price = parseFloat(i.price), pct = 1
        for (const s of i.selections || [])
            for (const v of s.selections || [])
                v.price_type === 'percent' ? (pct += parseFloat(v.price)/100) : (price += parseFloat(v.price))
        return t + Math.round(price * pct * i.qty * 100) / 100
    }, 0)
)

const tax = computed(() => {
    const rate = parseFloat(props.settings.tax_rate || 0) / 100
    const taxable = cart.items
        .filter(i => i.taxable)
        .reduce((t, i) => {
            let price = parseFloat(i.price), pct = 1
            for (const s of i.selections || [])
                for (const v of s.selections || [])
                    v.price_type === 'percent' ? (pct += parseFloat(v.price)/100) : (price += parseFloat(v.price))
            return t + Math.round(price * pct * i.qty * 100) / 100
        }, 0)
    return Math.round(taxable * rate * 100) / 100
})

const delivery = computed(() => {
    if (payment.value.method !== 'delivery') return 0
    const hasFood = cart.items.some(i => i.type === 'food')
    return hasFood
        ? parseFloat(props.settings.food_charge || 0)
        : parseFloat(props.settings.grocery_charge || 0)
})

const tip = computed(() => Math.round(parseFloat(payment.value.tip || 0) * 100) / 100)
const serverTotals = computed(() => cart.totals || {})
const discount = computed(() => Number(serverTotals.value.discount || 0))
const total = computed(() => serverTotals.value.total !== undefined
    ? Number(serverTotals.value.total)
    : Math.round((subtotal.value + tax.value + delivery.value + tip.value) * 100) / 100)
const fmt = (n) => Number(n || 0).toFixed(2)

async function refreshTotals() {
    await cart.save({
        method: payment.value.method,
        delivery_address: payment.value.delivery_address,
        tip: payment.value.tip,
        coupon_code: couponCode.value,
    })
}

function setMethod(method) {
    payment.value.method = method
    refreshTotals()
}
async function applyCoupon() {
    couponLoading.value = true
    couponMessage.value = ''
    couponApplied.value = false

    try {
        await cart.save({
            method: payment.value.method,
            delivery_address: payment.value.delivery_address,
            tip: payment.value.tip,
            coupon_code: couponCode.value,
        })
        payment.value.coupon_code = couponCode.value
        if (couponCode.value && cart.totals?.coupon) {
            couponApplied.value = true
            couponMessage.value = cart.totals.coupon.name + ' applied.'
        } else if (couponCode.value) {
            couponMessage.value = 'That code is not available for this order.'
        } else {
            couponMessage.value = ''
        }
    } finally {
        couponLoading.value = false
    }
}
async function pay() {
    errors.value = {}
    loading.value = true

    try {
        // Validate address if delivery
        if (payment.value.method === 'delivery') {
            await axios.post('/ordering/cart/validate-address', payment.value.delivery_address)
            await cart.save({ method: 'delivery', delivery_address: payment.value.delivery_address })
        } else {
            await cart.save({ method: 'pickup' })
        }

        await cart.save({ method: payment.value.method, tip: payment.value.tip, coupon_code: couponCode.value })
        payment.value.coupon_code = couponCode.value

        // Get payment intent
        const { data } = await axios.post('/ordering/cart/validate-payment', payment.value)

        // Confirm card with Stripe
        const stripe = window.Stripe(props.settings.stripe_key)
        const { error, paymentIntent } = await stripe.confirmCardPayment(data.intent, {
            payment_method: {
                card: stripeCard,
                billing_details: {
                    name: payment.value.billing_firstname + ' ' + payment.value.billing_lastname,
                    address: {
                        line1: payment.value.billing_address,
                        city: payment.value.billing_city,
                        state: payment.value.billing_state,
                        postal_code: payment.value.billing_zip,
                    },
                },
            },
        })

        if (error) { errors.value.stripe = error.message; return }

        if (['succeeded', 'requires_capture'].includes(paymentIntent.status)) {
            const { data: orderData } = await axios.post('/ordering/cart/order', payment.value)
            emit('placed', orderData.order)
        }
    } catch (e) {
        if (e.response?.data?.errors) errors.value = e.response.data.errors
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    const stripe = window.Stripe(props.settings.stripe_key)
    const elements = stripe.elements()
    const style = { base: { fontSize: '16px', color: '#1a1a1a' } }
    stripeCard = elements.create('cardNumber', { style })
    stripeCard.mount(cardNumber.value)
    elements.create('cardExpiry', { style }).mount(cardExpiry.value)
    elements.create('cardCvc', { style }).mount(cardCvc.value)
})
</script>

<style scoped>
.coupon-row { display: flex; gap: 8px; margin: 12px 0; }
.coupon-row input { flex: 1; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 9px 11px; font-weight: 700; text-transform: uppercase; }
.coupon-row button { border: none; border-radius: 8px; background: #0f766e; color: #fff; padding: 9px 14px; font-weight: 800; cursor: pointer; }
.coupon-row button:disabled { opacity: .55; cursor: default; }
.coupon-message { font-size: 12px; color: #b45309; margin: -4px 0 10px; }
.coupon-message.good { color: #15803d; }
.total-row.discount { color: #15803d; font-weight: 800; }
</style>