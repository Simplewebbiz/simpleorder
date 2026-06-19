<template>
    <div class="form-input">
        <label v-if="label" class="form-label">{{ label }}</label>
        <input
            :type="type"
            :value="modelValue"
            :maxlength="maxlength"
            :placeholder="placeholder"
            class="form-field"
            :class="{ error: error }"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <span class="form-error" v-if="error">{{ error }}</span>
    </div>
</template>

<script setup>
defineProps({
    modelValue: String,
    label: String,
    type: { type: String, default: 'text' },
    error: String,
    maxlength: [String, Number],
    placeholder: String,
})
defineEmits(['update:modelValue'])
</script>

<style scoped>
.form-input { display: flex; flex-direction: column; gap: 4px; }
.form-label { font-size: 13px; font-weight: 600; color: #374151; }
.form-field { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 14px; color: #1a1a1a; width: 100%; box-sizing: border-box; outline: none; transition: border-color 0.15s; }
.form-field:focus { border-color: #e85d04; }
.form-field.error { border-color: #dc2626; }
.form-error { font-size: 12px; color: #dc2626; }
</style>
