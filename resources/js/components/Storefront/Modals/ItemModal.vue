<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div class="modal-backdrop" v-if="open" @click.self="close">
                <div class="modal-box">
                    <button class="modal-close" @click="close">✕</button>

                    <!-- Item image -->
                    <div class="modal-image" v-if="item?.image">
                        <img :src="item.image.permalink" :alt="item.name" />
                    </div>

                    <div class="modal-body">
                        <div class="modal-item-name">{{ item?.name }}</div>
                        <div class="modal-item-price">${{ basePrice.toFixed(2) }}</div>
                        <div class="modal-item-desc" v-if="item?.description" v-html="item.description"></div>

                        <!-- Options -->
                        <div v-for="option in item?.options" :key="option.id" class="option-group">
                            <div class="option-label">
                                {{ option.label }}
                                <span class="option-required" v-if="option.required">Required</span>
                            </div>

                            <!-- Select (single) -->
                            <div v-if="option.input_type === 'select'" class="option-values">
                                <label
                                    v-for="val in option.values"
                                    :key="val.id"
                                    class="option-value"
                                    :class="{ selected: isSelected(option.id, val.id) }"
                                >
                                    <input
                                        type="radio"
                                        :name="'opt_' + option.id"
                                        :value="val.id"
                                        :checked="isSelected(option.id, val.id)"
                                        @change="selectSingle(option.id, val)"
                                        hidden
                                    />
                                    <span class="ov-check">{{ isSelected(option.id, val.id) ? '●' : '○' }}</span>
                                    <span class="ov-label">{{ val.label }}</span>
                                    <span class="ov-price" v-if="val.price > 0">+{{ formatOptionPrice(val) }}</span>
                                </label>
                            </div>

                            <!-- Multiselect (multiple) -->
                            <div v-else class="option-values">
                                <label
                                    v-for="val in option.values"
                                    :key="val.id"
                                    class="option-value"
                                    :class="{ selected: isSelected(option.id, val.id) }"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="isSelected(option.id, val.id)"
                                        @change="toggleMulti(option.id, val)"
                                        hidden
                                    />
                                    <span class="ov-check">{{ isSelected(option.id, val.id) ? '☑' : '☐' }}</span>
                                    <span class="ov-label">{{ val.label }}</span>
                                    <span class="ov-price" v-if="val.price > 0">+{{ formatOptionPrice(val) }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Qty + notes -->
                        <div class="modal-extras">
                            <div class="qty-row">
                                <div class="qty-label">Quantity</div>
                                <div class="qty-control">
                                    <button @click="qty = Math.max(1, qty - 1)">−</button>
                                    <span>{{ qty }}</span>
                                    <button @click="qty++">+</button>
                                </div>
                            </div>

                            <div class="notes-row">
                                <label class="notes-label">Special Instructions</label>
                                <textarea v-model="comments" class="notes-input" rows="2" placeholder="Allergies, preferences..."></textarea>
                            </div>
                        </div>

                        <!-- Running total -->
                        <div class="modal-total">
                            <span>Item Total</span>
                            <span class="modal-total-price">${{ itemTotal.toFixed(2) }}</span>
                        </div>

                        <!-- Add to cart button -->
                        <button class="add-btn" :disabled="!canAdd" @click="addToCart">
                            Add to Order — ${{ itemTotal.toFixed(2) }}
                        </button>

                        <div class="add-error" v-if="addError">{{ addError }}</div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useCartStore } from '../../../stores/cart'
import axios from 'axios'

const props = defineProps({
    open: Boolean,
    item: Object,
    editCartItemId: { type: Number, default: null },
})

const emit = defineEmits(['close'])
const cart = useCartStore()

const qty = ref(1)
const comments = ref('')
const selections = ref({}) // { optionId: [{ id, label, price, price_type }] }
const addError = ref('')

watch(() => props.open, (v) => {
    if (v) {
        qty.value = 1
        comments.value = ''
        addError.value = ''
        selections.value = {}
        // pre-select first value for required single-select
        for (const opt of props.item?.options || []) {
            if (opt.required && opt.input_type === 'select' && opt.values?.length) {
                selections.value[opt.id] = [opt.values[0]]
            }
        }
    }
})

const basePrice = computed(() => Number(props.item?.price || 0))

const itemTotal = computed(() => {
    let price = basePrice.value
    let pct = 1
    for (const vals of Object.values(selections.value)) {
        for (const v of vals) {
            if (v.price_type === 'percent') pct += Number(v.price) / 100
            else price += Number(v.price)
        }
    }
    return Math.round(price * pct * qty.value * 100) / 100
})

const canAdd = computed(() => {
    for (const opt of props.item?.options || []) {
        if (opt.required && (!selections.value[opt.id] || selections.value[opt.id].length === 0)) return false
    }
    return true
})

function isSelected(optId, valId) {
    return (selections.value[optId] || []).some(v => v.id === valId)
}

function selectSingle(optId, val) {
    selections.value[optId] = [val]
}

function toggleMulti(optId, val) {
    const current = selections.value[optId] || []
    if (current.some(v => v.id === val.id)) {
        selections.value[optId] = current.filter(v => v.id !== val.id)
    } else {
        selections.value[optId] = [...current, val]
    }
}

function formatOptionPrice(val) {
    if (val.price_type === 'percent') return Number(val.price) + '%'
    return '$' + Number(val.price).toFixed(2)
}

async function addToCart() {
    if (!canAdd.value) return
    addError.value = ''
    try {
        const payload = {
            item_id: props.item.id,
            qty: qty.value,
            comments: comments.value,
            selections: Object.entries(selections.value).map(([optId, vals]) => ({
                option_id: optId,
                values: vals.map(v => v.id),
            })),
        }
        if (props.editCartItemId) payload.cart_item_id = props.editCartItemId
        const { data } = await axios.post('/ordering/cart/item', payload)
        cart.init(data.cart)
        close()
    } catch (e) {
        addError.value = e.response?.data?.message || 'Could not add item. Please try again.'
    }
}

function close() { emit('close') }
</script>

<style scoped>
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; display: flex; align-items: flex-end; justify-content: center; }
@media (min-width: 640px) { .modal-backdrop { align-items: center; } }
.modal-box { background: #fff; border-radius: 20px 20px 0 0; width: 100%; max-width: 540px; max-height: 92vh; overflow-y: auto; position: relative; }
@media (min-width: 640px) { .modal-box { border-radius: 16px; } }
.modal-close { position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,.06); border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 14px; cursor: pointer; z-index: 1; color: #374151; }
.modal-image { height: 220px; overflow: hidden; border-radius: 20px 20px 0 0; }
.modal-image img { width: 100%; height: 100%; object-fit: cover; }
.modal-body { padding: 24px; }
.modal-item-name { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.modal-item-price { font-size: 16px; color: #e85d04; font-weight: 700; margin-bottom: 10px; }
.modal-item-desc { font-size: 14px; color: #6b7280; line-height: 1.5; margin-bottom: 20px; }
.option-group { margin-bottom: 20px; }
.option-label { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #374151; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
.option-required { background: #fef3c7; color: #92400e; font-size: 10px; padding: 2px 8px; border-radius: 4px; }
.option-values { display: flex; flex-direction: column; gap: 8px; }
.option-value { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.15s; font-size: 14px; }
.option-value.selected { border-color: #e85d04; background: #fff7f3; }
.ov-check { font-size: 16px; color: #e85d04; flex-shrink: 0; }
.ov-label { flex: 1; font-weight: 500; }
.ov-price { color: #6b7280; font-size: 12px; }
.modal-extras { margin-top: 20px; display: flex; flex-direction: column; gap: 16px; }
.qty-row { display: flex; align-items: center; justify-content: space-between; }
.qty-label { font-weight: 600; }
.qty-control { display: flex; align-items: center; gap: 16px; }
.qty-control button { width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid #e5e7eb; background: #fff; font-size: 18px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #1a1a1a; }
.qty-control button:hover { border-color: #e85d04; color: #e85d04; }
.qty-control span { font-size: 17px; font-weight: 700; min-width: 24px; text-align: center; }
.notes-label { font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; }
.notes-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 10px; font-size: 14px; resize: none; font-family: inherit; }
.notes-input:focus { outline: none; border-color: #e85d04; }
.modal-total { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 8px; font-weight: 700; }
.modal-total-price { font-size: 18px; color: #1a1a1a; }
.add-btn { width: 100%; margin-top: 12px; background: #e85d04; color: #fff; border: none; padding: 16px; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background 0.15s; }
.add-btn:hover:not(:disabled) { background: #c44d03; }
.add-btn:disabled { opacity: 0.5; cursor: default; }
.add-error { color: #dc2626; font-size: 13px; margin-top: 8px; text-align: center; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
