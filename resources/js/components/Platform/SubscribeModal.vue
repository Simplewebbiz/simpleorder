<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-header">
                <h2>Subscribe to {{ plan.name }}</h2>
                <button class="modal-close" @click="$emit('close')">✕</button>
            </div>
            <div class="modal-body">
                <p>You will be charged <strong>${{ (plan.price / 100).toFixed(2) }}/month</strong>.</p>
                <div id="card-element" class="card-element"></div>
                <div class="card-errors" v-if="error">{{ error }}</div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" @click="$emit('close')">Cancel</button>
                <button class="btn-subscribe" @click="subscribe" :disabled="loading">
                    {{ loading ? 'Processing...' : 'Subscribe Now' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { loadStripe } from '@stripe/stripe-js'
import axios from 'axios'

const props = defineProps({
    plan: Object,
})
const emit = defineEmits(['close', 'subscribed'])

const error = ref('')
const loading = ref(false)
let stripe, elements, cardElement

onMounted(async () => {
    stripe = await loadStripe(window.stripeKey || '')
    elements = stripe.elements()
    cardElement = elements.create('card')
    cardElement.mount('#card-element')
})

async function subscribe() {
    loading.value = true
    error.value = ''
    try {
        const { data } = await axios.post(route('platform.billing.subscribe'), { plan_id: props.plan.id })
        const { error: stripeError } = await stripe.confirmCardPayment(data.client_secret, {
            payment_method: { card: cardElement },
        })
        if (stripeError) {
            error.value = stripeError.message
        } else {
            emit('subscribed')
            emit('close')
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Something went wrong.'
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: #fff; border-radius: 12px; width: 440px; max-width: 95vw; display: flex; flex-direction: column; }
.modal-header { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
.modal-header h2 { font-size: 18px; font-weight: 700; margin: 0; }
.modal-close { background: none; border: none; font-size: 18px; cursor: pointer; color: #6b7280; }
.modal-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
.card-element { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; }
.card-errors { color: #dc2626; font-size: 13px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 12px; }
.btn-cancel { background: none; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 16px; cursor: pointer; font-size: 14px; }
.btn-subscribe { background: #6366f1; color: #fff; border: none; border-radius: 8px; padding: 8px 20px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-subscribe:disabled { opacity: .6; cursor: default; }
</style>
