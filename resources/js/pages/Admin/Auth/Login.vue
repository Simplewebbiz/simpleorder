<template>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <img v-if="brand.logo" :src="brand.logo" :alt="brand.name" class="brand-img" />
                <div v-else class="brand-name">{{ brand.name || 'Store Admin' }}</div>
            </div>
            <h2 class="auth-title">Staff Sign In</h2>

            <form @submit.prevent="submit">
                <div class="field-group">
                    <label class="field-label">Email</label>
                    <input v-model="form.email" type="email" class="field-input" :class="{ error: errors.email }" required autofocus />
                    <div class="field-error" v-if="errors.email">{{ errors.email }}</div>
                </div>
                <div class="field-group">
                    <label class="field-label">Password</label>
                    <input v-model="form.password" type="password" class="field-input" :class="{ error: errors.password }" required />
                    <div class="field-error" v-if="errors.password">{{ errors.password }}</div>
                </div>

                <div class="auth-error" v-if="errors.login">{{ errors.login }}</div>

                <button type="submit" class="auth-btn" :disabled="submitting">
                    {{ submitting ? 'Signing in...' : 'Sign In' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const brand = page.props.tenant_brand || {}
const errors = page.props.errors || {}
const submitting = ref(false)

const form = reactive({ email: '', password: '' })

function submit() {
    submitting.value = true
    router.post(route('tenant.admin.login.post'), form, {
        onFinish: () => { submitting.value = false },
        onError: () => { form.password = '' },
    })
}
</script>

<style scoped>
.auth-page { min-height: 100vh; background: #f5f5f5; display: flex; align-items: center; justify-content: center; padding: 20px; }
.auth-card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
.auth-brand { text-align: center; margin-bottom: 28px; }
.brand-img { max-height: 56px; max-width: 180px; object-fit: contain; }
.brand-name { font-size: 22px; font-weight: 800; color: #1a1a1a; }
.auth-title { font-size: 17px; font-weight: 700; margin-bottom: 24px; color: #374151; }
.field-group { margin-bottom: 16px; }
.field-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 6px; }
.field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; font-size: 14px; transition: border-color 0.15s; }
.field-input:focus { outline: none; border-color: #e85d04; }
.field-input.error { border-color: #ef4444; }
.field-error { color: #dc2626; font-size: 12px; margin-top: 4px; }
.auth-error { background: #fee2e2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
.auth-btn { width: 100%; background: #e85d04; color: #fff; border: none; padding: 13px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; }
.auth-btn:hover:not(:disabled) { background: #c44d03; }
.auth-btn:disabled { opacity: 0.5; cursor: default; }
</style>
