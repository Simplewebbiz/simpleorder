<template>
    <div class="checkout-item">
        <div class="checkout-item__info">
            <span class="checkout-item__qty">{{ item.qty }}×</span>
            <span class="checkout-item__name">{{ item.name }}</span>
        </div>
        <span class="checkout-item__price">${{ fmt(lineTotal) }}</span>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    item: Object,
})

const lineTotal = computed(() => {
    let price = parseFloat(props.item.price)
    let percent = 1.0
    if (props.item.selections) {
        for (const sel of props.item.selections) {
            for (const opt of sel.selections || []) {
                if (opt.price_type === 'percent') percent += parseFloat(opt.price) / 100
                else price += parseFloat(opt.price)
            }
        }
    }
    return Math.round(price * percent * 100) / 100 * props.item.qty
})

const fmt = (n) => Number(n || 0).toFixed(2)
</script>

<style scoped>
.checkout-item { display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
.checkout-item__info { display: flex; gap: 6px; }
.checkout-item__qty { color: #6b7280; font-weight: 600; }
.checkout-item__name { color: #1a1a1a; }
.checkout-item__price { color: #1a1a1a; font-weight: 600; white-space: nowrap; }
</style>
