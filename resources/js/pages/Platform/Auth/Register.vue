<template>
    <div class="auth-page">
        <div class="auth-card wide">
            <div class="auth-brand">
                <div class="brand-logo">SO</div>
                <h1 class="brand-name">SimpleOrder</h1>
            </div>
            <h2 class="auth-title">Start your free trial</h2>
            <p class="auth-sub">No credit card required. 14-day free trial on all plans.</p>

            <!-- Plan selection -->
            <div class="plans-row">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="plan-card"
                    :class="{ selected: form.plan_id === plan.id }"
                    @click="form.plan_id = plan.id"
                >
                    <div class="plan-name">{{ plan.name }}</div>
                    <div class="plan-price">${{ (plan.price_monthly / 100).toFixed(0) }}<span>/mo</span></div>
                    <div class="plan-desc">{{ plan.description }}</div>
                    <div class="plan-check" v-if="form.plan_id === plan.id">✓</div>
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="form-grid">
                    <div class="field-group">
                        <label class="field-label">Your Name</label>
                        <input v-model="form.name" type="text" class="field-input" :class="{ error: errors.name }" required />
                        <div class="field-error" v-if="errors.name">{{ errors.name }}</div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Store Name</label>
                        <input v-model="form.store_name" type="text" class="field-input" :class="{ error: errors.store_name }" required />
                        <div class="field-error" v-if="errors.store_name">{{ errors.store_name }}</div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Email</label>
                        <input v-model="form.email" type="email" class="field-input" :class="{ error: errors.email }" required />
                        <div class="field-error" v-if="errors.email">{{ errors.email }}</div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Password</label>
                        <input v-model="form.password" type="password" class="field-input" :class="{ error: errors.password }" required minlength="8" />
                        <div class="field-error" v-if="errors.password">{{ errors.password }}</div>
                    </div>
                </div>

                <!-- Subdomain preview -->
                <div class="subdomain-preview" v-if="slug">
                    Your store URL: <strong>{{ slug }}.simpleorder.com</strong>
                </div>

                <div class="auth-error" v-if="errors.general">{{ errors.general }}</div>

                <button type="submit" class="auth-btn" :disabled="submitting || !form.plan_id">
                    {{ submitting ? 'Creating your store...' : 'Start Free Trial →' }}
                </button>
            </form>

            <div class="auth-footer">
                Already have an account?
                <Link :href="route('login')" class="auth-link">Sign in</Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({ plans: Array })
const page = usePage()
const errors = page.props.errors || {}
const submitting = ref(false)

const form = reactive({ name: '', store_name: '', email: '', password: '', plan_id: props.plans?.[0]?.id || null })

const slug = computed(() => form.store_name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '').substring(0, 32))

function submit() {
    submitting.value = true
    router.post(route('register.post'), { ...form, slug: slug.value }, {
        onFinish: () => { submitting.value = false },
        onError: () => { form.password = '' },
    })
}
</script>

<style scoped>
.auth-page { min-height: 100vh; background: linear-gradient(135deg, #1a1a1a 0%, #e85d04 100%); display: flex; align-items: center; justify-content: center; padding: 20px; }
.auth-card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 540px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
.auth-card.wide { max-width: 640px; }
.auth-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
.brand-logo { width: 44px; height: 44px; background: #e85d04; color: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 900; }
.brand-name { font-size: 22px; font-weight: 800; color: #1a1a1a; }
.auth-title { font-size: 22px; font-weight: 800; margin-bottom: 6px; }
.auth-sub { font-size: 14px; color: #6b7280; margin-bottom: 24px; }
.plans-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin-bottom: 24px; }
.plan-card { border: 2px solid #e5e7eb; border-radius: 10px; padding: 16px; cursor: pointer; transition: all 0.15s; position: relative; }
.plan-card:hover { border-color: #e85d04; }
.plan-card.selected { border-color: #e85d04; background: #fff7f3; }
.plan-name { font-size: 14px; font-weight: 800; margin-bottom: 4px; }
.plan-price { font-size: 22px; font-weight: 800; color: #e85d04; }
.plan-price span { font-size: 14px; font-weight: 500; color: #9ca3af; }
.plan-desc { font-size: 12px; color: #6b7280; margin-top: 4px; }
.plan-check { position: absolute; top: 8px; right: 10px; color: #e85d04; font-weight: 900; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
@media (max-width: 480px) { .form-grid { grid-template-columns: 1fr; } }
.field-group { margin-bottom: 0; }
.field-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 6px; }
.field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; font-size: 14px; transition: border-color 0.15s; }
.field-input:focus { outline: none; border-color: #e85d04; }
.field-input.error { border-color: #ef4444; }
.field-error { color: #dc2626; font-size: 12px; margin-top: 4px; }
.subdomain-preview { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #6b7280; margin-bottom: 16px; }
.auth-error { background: #fee2e2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
.auth-btn { width: 100%; background: #e85d04; color: #fff; border: none; padding: 13px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.15s; }
.auth-btn:hover:not(:disabled) { background: #c44d03; }
.auth-btn:disabled { opacity: 0.5; cursor: default; }
.auth-footer { text-align: center; font-size: 14px; color: #6b7280; margin-top: 20px; }
.auth-link { color: #e85d04; font-weight: 600; text-decoration: none; }
</style>
