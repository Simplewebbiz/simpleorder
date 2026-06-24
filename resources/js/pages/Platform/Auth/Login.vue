<template>
    <div class="auth-page">
        <Link :href="route('home')" class="brand-link">SimpleOrder</Link>

        <section class="auth-shell">
            <div class="auth-story">
                <p class="eyebrow">Restaurant command center</p>
                <h1>Welcome back to your ordering dashboard.</h1>
                <p class="story-copy">Manage menus, pages, payments, reports, staff, and customer updates from one bright workspace.</p>
                <div class="story-grid">
                    <div><strong>Orders</strong><span>Track every ticket</span></div>
                    <div><strong>Pages</strong><span>Edit your site</span></div>
                    <div><strong>Menu</strong><span>Update photos fast</span></div>
                </div>
            </div>

            <div class="auth-card">
                <div class="card-heading">
                    <span class="brand-mark">SO</span>
                    <div>
                        <p>SimpleOrder</p>
                        <h2>Sign in</h2>
                    </div>
                </div>

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
                        <label class="checkbox-label"><input type="checkbox" v-model="form.remember" /> Remember me</label>
                    </div>

                    <div class="auth-error" v-if="errors.login">{{ errors.login }}</div>

                    <button type="submit" class="auth-btn" :disabled="submitting">{{ submitting ? 'Signing in...' : 'Sign In' }}</button>
                </form>

                <div class="auth-footer">
                    <span>Need a restaurant site?</span>
                    <Link :href="route('register')" class="auth-link">Start free</Link>
                </div>
            </div>
        </section>
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
    router.post(route('login.post'), form, {
        onFinish: () => { submitting.value = false },
        onError: () => { form.password = '' },
    })
}
</script>

<style scoped>
.auth-page { min-height: 100vh; background: linear-gradient(135deg, #fff7ed 0%, #ecfdf5 52%, #e0f2fe 100%); color: #17272b; padding: 28px; position: relative; overflow: hidden; }
.auth-page::before { content: ''; position: absolute; inset: auto -120px -180px auto; width: 420px; height: 420px; background: #ffd7c8; border-radius: 50%; opacity: .55; }
.auth-page::after { content: ''; position: absolute; inset: 110px auto auto -130px; width: 340px; height: 340px; background: #b9f3e7; border-radius: 50%; opacity: .58; }
.brand-link { position: relative; z-index: 1; display: inline-flex; color: #17272b; font-size: 22px; font-weight: 900; text-decoration: none; }
.auth-shell { position: relative; z-index: 1; min-height: calc(100vh - 70px); max-width: 1120px; margin: 0 auto; display: grid; grid-template-columns: minmax(0, 1fr) 430px; gap: 48px; align-items: center; }
.auth-story { max-width: 610px; }
.eyebrow { color: #0f766e; font-size: 13px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 14px; }
h1 { font-size: 56px; line-height: 1.03; font-weight: 900; letter-spacing: 0; }
.story-copy { color: #405257; font-size: 20px; line-height: 1.6; margin-top: 20px; }
.story-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 30px; }
.story-grid div { background: rgba(255,255,255,.72); border: 1px solid rgba(255,255,255,.9); border-radius: 8px; padding: 16px; box-shadow: 0 12px 30px rgba(31,45,48,.08); }
.story-grid strong { display: block; color: #ef6c3e; font-size: 13px; font-weight: 900; text-transform: uppercase; }
.story-grid span { display: block; color: #344448; font-size: 14px; font-weight: 800; margin-top: 5px; }
.auth-card { background: rgba(255,255,255,.9); border: 1px solid rgba(255,255,255,.95); border-radius: 8px; padding: 34px; width: 100%; box-shadow: 0 24px 60px rgba(31,45,48,.16); backdrop-filter: blur(12px); }
.card-heading { display: flex; gap: 14px; align-items: center; margin-bottom: 28px; }
.brand-mark { width: 48px; height: 48px; background: #ff7a59; color: #fff; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 900; }
.card-heading p { color: #657477; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; }
h2 { color: #17272b; font-size: 28px; font-weight: 900; margin-top: 2px; }
.field-group { margin-bottom: 16px; }
.field-label { display: block; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; color: #657477; margin-bottom: 7px; }
.field-input { width: 100%; border: 1.5px solid #dbe4e8; border-radius: 8px; padding: 12px 13px; font-size: 15px; background: #fff; color: #17272b; transition: border-color .15s, box-shadow .15s; }
.field-input:focus { outline: none; border-color: #ff7a59; box-shadow: 0 0 0 4px rgba(255,122,89,.14); }
.field-input.error { border-color: #ef4444; }
.field-error { color: #dc2626; font-size: 12px; margin-top: 5px; }
.remember-row { margin-bottom: 18px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #405257; cursor: pointer; font-weight: 700; }
.auth-error { background: #fee2e2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; font-weight: 700; }
.auth-btn { width: 100%; background: #ff7a59; color: #fff; border: none; padding: 13px; border-radius: 8px; font-size: 15px; font-weight: 900; cursor: pointer; transition: background .15s; }
.auth-btn:hover:not(:disabled) { background: #e85d3f; }
.auth-btn:disabled { opacity: .55; cursor: default; }
.auth-footer { display: flex; justify-content: center; gap: 7px; flex-wrap: wrap; text-align: center; font-size: 14px; color: #657477; margin-top: 20px; }
.auth-link { color: #0f766e; font-weight: 900; text-decoration: none; }
@media (max-width: 900px) { .auth-shell { grid-template-columns: 1fr; gap: 28px; padding: 40px 0; } h1 { font-size: 38px; } .story-grid { grid-template-columns: 1fr; } }
@media (max-width: 520px) { .auth-page { padding: 20px; } .auth-card { padding: 24px; } }
</style>