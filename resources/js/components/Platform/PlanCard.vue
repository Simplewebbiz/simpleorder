<template>
    <div class="plan-card" :class="{ current: current }">
        <div class="plan-badge" v-if="current">Current Plan</div>
        <div class="plan-name">{{ plan.name }}</div>
        <div class="plan-price">
            <span class="price-amount">${{ (plan.price / 100).toFixed(2) }}</span>
            <span class="price-period">/mo</span>
        </div>
        <div class="plan-description">{{ plan.description }}</div>
        <ul class="plan-features" v-if="plan.features">
            <li v-for="feature in plan.features" :key="feature">{{ feature }}</li>
        </ul>
        <button class="btn-subscribe" @click="$emit('subscribe', plan)" :disabled="current">
            {{ current ? 'Current Plan' : 'Subscribe' }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    plan: Object,
    current: Boolean,
})
defineEmits(['subscribe'])
</script>

<style scoped>
.plan-card { border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; background: #fff; display: flex; flex-direction: column; gap: 12px; position: relative; }
.plan-card.current { border-color: #6366f1; }
.plan-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #6366f1; color: #fff; font-size: 11px; font-weight: 700; padding: 3px 12px; border-radius: 20px; white-space: nowrap; }
.plan-name { font-size: 18px; font-weight: 800; color: #1a1a1a; }
.plan-price { display: flex; align-items: baseline; gap: 2px; }
.price-amount { font-size: 32px; font-weight: 800; color: #1a1a1a; }
.price-period { font-size: 14px; color: #6b7280; }
.plan-description { font-size: 14px; color: #6b7280; }
.plan-features { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px; }
.plan-features li { font-size: 13px; color: #374151; padding-left: 20px; position: relative; }
.plan-features li::before { content: '✓'; position: absolute; left: 0; color: #10b981; font-weight: 700; }
.btn-subscribe { margin-top: auto; background: #6366f1; color: #fff; border: none; border-radius: 8px; padding: 10px 20px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-subscribe:disabled { background: #e5e7eb; color: #9ca3af; cursor: default; }
</style>
