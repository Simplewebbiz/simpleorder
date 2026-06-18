<template>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="brand-logo">SO</div>
                <h1 class="brand-name">SimpleOrder</h1>
            </div>
            <h2 class="auth-title">Sign in to your account</h2>

            <form @submit.prevent="submit">
                <div class="field-group">
                    <label class="field-label">Email address</label>
                    <input v-model="form.email" type="email" class="field-input" :class="{ error: errors.email }" required autofocus />
                    <div class="field-error" v-if="errors.email">{{ errors.email }}</div>
                </div>
                <div class="field-group">
                    <label class="field-label">Password</label>
                    <input v-model="form.password" type="password" class="field-input" :class="{ error: errors.password }" required />
                    <div class="field-error" v-if="errors.password">{{ errors.password }}</div>
                </div>

                <div class="remember-row">
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="form.remember" /> Remember me
                    </label>
                </div>

                <div class="auth-error" v-if="errors.login">{{ errors.login }}</div>

                <button type="submit" class="auth-btn" :disabled="submitting">
                    {{ submitting ? 'Signing in...' : 'Sign In' }}
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account?
                <Link :href="route('platform.register')" class="auth-link">Get started free →</Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()
const errors = page.props.errors || {}
const submitting = ref(false)

const form = reactive({ email: '', password: '', remember: false })

function submit() {
    submitting.value = true
    router.post(route('platform.login'), form, {
        onFinish: () => { submitting.value = false },
        onError: () => { form.password = '' },
    })
}
</script>

<style scoped>
.auth-page { min-height: 100vh; background: linear-gradient(135deg, #1a1a1a 0%, #e85d04 100%); display: flex; align-items: center; justify-content: center; padding: 20px; }
.auth-card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
.auth-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 32px; }
.brand-logo { width: 44px; height: 44px; background: #e85d04; color: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 900; }
.brand-name { font-size: 22px; font-weight: 800; color: #1a1a1a; }
.auth-title { font-size: 18px; font-weight: 700; margin-bottom: 24px; color: #374151; }
.field-group { margin-bottom: 16px; }
.field-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 6px; }
.field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; font-size: 14px; transition: border-color 0.15s; }
.field-input:focus { outline: none; border-color: #e85d04; }
.field-input.error { border-color: #ef4444; }
.field-error { color: #dc2626; font-size: 12px; margin-top: 4px; }
.remember-row { margin-bottom: 16px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #374151; cursor: pointer; }
.auth-error { background: #fee2e2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
.auth-btn { width: 100%; background: #e85d04; color: #fff; border: none; padding: 13px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.15s; }
.auth-btn:hover:not(:disabled) { background: #c44d03; }
.auth-btn:disabled { opacity: 0.5; cursor: default; }
.auth-footer { text-align: center; font-size: 14px; color: #6b7280; margin-top: 20px; }
.auth-link { color: #e85d04; font-weight: 600; text-decoration: none; }
</style>
