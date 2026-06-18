<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div class="modal-backdrop" v-if="open" @click.self="close">
                <div class="modal-box">
                    <button class="modal-close" @click="close">✕</button>

                    <div class="modal-body">
                        <h2 class="modal-title">How would you like to order?</h2>

                        <!-- Method tabs -->
                        <div class="method-tabs">
                            <button
                                v-if="settings.allow_pickup"
                                class="method-tab"
                                :class="{ active: method === 'pickup' }"
                                @click="method = 'pickup'"
                            >
                                🏪 Pickup
                            </button>
                            <button
                                v-if="settings.allow_delivery"
                                class="method-tab"
                                :class="{ active: method === 'delivery' }"
                                @click="method = 'delivery'"
                            >
                                🚗 Delivery
                            </button>
                        </div>

                        <!-- Pickup info -->
                        <div v-if="method === 'pickup'" class="method-detail">
                            <div class="pickup-icon">🏪</div>
                            <div class="pickup-info">
                                <div class="pickup-title">Order online, pick up in store</div>
                                <div class="pickup-addr" v-if="brand.address && brand.address.address">
                                    {{ brand.address.address }}<br>
                                    {{ brand.address.city }}, {{ brand.address.state }} {{ brand.address.zip }}
                                </div>
                                <a v-if="brand.phone" :href="'tel:' + brand.phone" class="pickup-phone">{{ brand.phone }}</a>
                            </div>
                        </div>

                        <!-- Delivery form -->
                        <div v-if="method === 'delivery'" class="method-detail">
                            <label class="field-label">Delivery Address</label>
                            <input
                                v-model="address"
                                type="text"
                                class="field-input"
                                :class="{ error: addrError }"
                                placeholder="123 Main St"
                                @blur="validateAddr"
                            />
                            <div class="field-row">
                                <div class="field-col">
                                    <label class="field-label">City</label>
                                    <input v-model="city" type="text" class="field-input" placeholder="City" />
                                </div>
                                <div class="field-col">
                                    <label class="field-label">State</label>
                                    <input v-model="state" type="text" class="field-input" placeholder="ST" maxlength="2" />
                                </div>
                                <div class="field-col">
                                    <label class="field-label">Zip</label>
                                    <input v-model="zip" type="text" class="field-input" placeholder="00000" maxlength="10" />
                                </div>
                            </div>
                            <div class="addr-error" v-if="addrError">{{ addrError }}</div>
                        </div>

                        <button
                            class="confirm-btn"
                            :disabled="!canConfirm || validating"
                            @click="confirm"
                        >
                            <span v-if="validating">Checking address...</span>
                            <span v-else>Continue with {{ method === 'pickup' ? 'Pickup' : 'Delivery' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    open: Boolean,
    settings: Object,
})

const emit = defineEmits(['close', 'confirm'])
const page = usePage()
const brand = page.props.tenant_brand || {}

const method = ref(props.settings?.allow_pickup ? 'pickup' : 'delivery')
const address = ref('')
const city = ref('')
const state = ref('')
const zip = ref('')
const addrError = ref('')
const validating = ref(false)

const canConfirm = computed(() => {
    if (method.value === 'pickup') return true
    return address.value && city.value && state.value && zip.value && !addrError.value
})

async function validateAddr() {
    addrError.value = ''
    if (!address.value || !city.value || !state.value || !zip.value) return
    validating.value = true
    try {
        await axios.post('/ordering/cart/validate-address', {
            address: address.value,
            city: city.value,
            state: state.value,
            zip: zip.value,
        })
    } catch (e) {
        addrError.value = e.response?.data?.message || 'Address is outside delivery area.'
    } finally {
        validating.value = false
    }
}

async function confirm() {
    if (method.value === 'delivery') {
        await validateAddr()
        if (addrError.value) return
    }
    emit('confirm', {
        method: method.value,
        delivery_address: address.value,
        delivery_city: city.value,
        delivery_state: state.value,
        delivery_zip: zip.value,
    })
}

function close() { emit('close') }
</script>

<style scoped>
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-box { background: #fff; border-radius: 16px; width: 100%; max-width: 480px; position: relative; }
.modal-close { position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,.06); border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 14px; cursor: pointer; color: #374151; }
.modal-body { padding: 32px; }
.modal-title { font-size: 20px; font-weight: 800; margin-bottom: 20px; }
.method-tabs { display: flex; gap: 10px; margin-bottom: 24px; }
.method-tab { flex: 1; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; background: #fff; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.15s; color: #6b7280; }
.method-tab.active { border-color: #e85d04; background: #fff7f3; color: #e85d04; }
.method-detail { margin-bottom: 24px; }
.pickup-icon { font-size: 36px; text-align: center; margin-bottom: 12px; }
.pickup-info { text-align: center; }
.pickup-title { font-weight: 700; margin-bottom: 8px; }
.pickup-addr { font-size: 14px; color: #6b7280; line-height: 1.5; margin-bottom: 8px; }
.pickup-phone { font-size: 14px; color: #e85d04; text-decoration: none; font-weight: 600; }
.field-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 6px; margin-top: 14px; }
.field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; font-size: 14px; transition: border-color 0.15s; }
.field-input:focus { outline: none; border-color: #e85d04; }
.field-input.error { border-color: #ef4444; }
.field-row { display: grid; grid-template-columns: 1fr 80px 100px; gap: 10px; }
.addr-error { color: #dc2626; font-size: 13px; margin-top: 6px; }
.confirm-btn { width: 100%; background: #e85d04; color: #fff; border: none; padding: 14px; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 8px; transition: background 0.15s; }
.confirm-btn:hover:not(:disabled) { background: #c44d03; }
.confirm-btn:disabled { opacity: 0.5; cursor: default; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
