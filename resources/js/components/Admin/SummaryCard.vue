<template>
    <div class="summary-card">
        <div class="summary-card__label">{{ label }}</div>
        <div class="summary-card__value">{{ value }}</div>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    value: [String, Number],
    icon: String,
})
</script>
