<template>
    <div class="sa-wrap">
        <header class="sa-nav">
            <div class="sa-brand">
                <div class="brand-logo">SO</div>
                <span class="brand-name">SimpleOrder</span>
                <span class="sa-badge">Super Admin</span>
            </div>
            <nav class="sa-links">
                <Link :href="route('platform.superadmin.index')" class="sa-link">Dashboard</Link>
                <Link :href="route('platform.superadmin.tenants.index')" class="sa-link">Tenants</Link>
                <Link :href="route('platform.superadmin.plans.index')" class="sa-link active">Plans</Link>
                <Link :href="route('platform.superadmin.settings')" class="sa-link">Settings</Link>
            </nav>
            <Link :href="route('platform.logout')" method="post" as="button" class="sa-logout">Logout</Link>
        </header>

        <main class="sa-main">
            <div class="page-header">
                <h1 class="page-title">Plans</h1>
                <button @click="openCreate" class="btn-primary">+ New Plan</button>
            </div>

            <div v-if="flash.success" class="alert-success">{{ flash.success }}</div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Monthly</th>
                        <th>Yearly</th>
                        <th>Tenants</th>
                        <th>Status</th>
                        <th>Sort</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="plan in plans" :key="plan.id">
                        <td>
                            <strong>{{ plan.name }}</strong>
                            <br><small>{{ plan.slug }}</small>
                            <div v-if="plan.description" class="plan-desc">{{ plan.description }}</div>
                        </td>
                        <td>${{ (plan.price_monthly / 100).toFixed(2) }}</td>
                        <td>${{ (plan.price_yearly / 100).toFixed(2) }}</td>
                        <td>{{ plan.tenants_count }}</td>
                        <td><span :class="['badge', plan.is_active ? 'active' : 'inactive']">{{ plan.is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>{{ plan.sort }}</td>
                        <td class="actions">
                            <button @click="openEdit(plan)" class="btn-sm">Edit</button>
                            <button @click="deletePlan(plan)" class="btn-sm danger">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="plans.length === 0">
                        <td colspan="7" class="empty">No plans yet. Create one above.</td>
                    </tr>
                </tbody>
            </table>
        </main>

        <!-- Modal -->
        <div v-if="modal" class="modal-overlay" @click.self="modal = false">
            <div class="modal">
                <h2 class="modal-title">{{ editing ? 'Edit Plan' : 'New Plan' }}</h2>
                <form @submit.prevent="submit">
                    <div class="form-row">
                        <div class="field">
                            <label>Name</label>
                            <input v-model="form.name" required />
                            <div class="err" v-if="errors.name">{{ errors.name }}</div>
                        </div>
                        <div class="field">
                            <label>Slug</label>
                            <input v-model="form.slug" required placeholder="e.g. starter" />
                            <div class="err" v-if="errors.slug">{{ errors.slug }}</div>
                        </div>
                    </div>
                    <div class="field">
                        <label>Description</label>
                        <textarea v-model="form.description" rows="2"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label>Monthly Price (cents)</label>
                            <input v-model.number="form.price_monthly" type="number" min="0" required />
                            <small>e.g. 2900 = $29.00</small>
                            <div class="err" v-if="errors.price_monthly">{{ errors.price_monthly }}</div>
                        </div>
                        <div class="field">
                            <label>Yearly Price (cents)</label>
                            <input v-model.number="form.price_yearly" type="number" min="0" required />
                            <small>e.g. 29900 = $299.00</small>
                            <div class="err" v-if="errors.price_yearly">{{ errors.price_yearly }}</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label>Stripe Monthly Price ID</label>
                            <input v-model="form.stripe_monthly_price_id" placeholder="price_..." />
                        </div>
                        <div class="field">
                            <label>Stripe Yearly Price ID</label>
                            <input v-model="form.stripe_yearly_price_id" placeholder="price_..." />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label>Max Items (0 = unlimited)</label>
                            <input v-model.number="form.max_items" type="number" min="0" />
                        </div>
                        <div class="field">
                            <label>Max Categories (0 = unlimited)</label>
                            <input v-model.number="form.max_categories" type="number" min="0" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label>Sort Order</label>
                            <input v-model.number="form.sort" type="number" />
                        </div>
                        <div class="field checkboxes">
                            <label class="checkbox-row"><input type="checkbox" v-model="form.custom_domain" /> Custom Domain</label>
                            <label class="checkbox-row"><input type="checkbox" v-model="form.order_reports" /> Order Reports</label>
                            <label class="checkbox-row"><input type="checkbox" v-model="form.is_active" /> Active</label>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" @click="modal = false" class="btn-cancel">Cancel</button>
                        <button type="submit" class="btn-primary" :disabled="submitting">
                            {{ submitting ? 'Saving...' : (editing ? 'Update Plan' : 'Create Plan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({ plans: Array })
const page = usePage()
const flash = page.props.flash ?? {}
const errors = page.props.errors ?? {}

const modal = ref(false)
const editing = ref(null)
const submitting = ref(false)

const blank = () => ({
    name: '', slug: '', description: '',
    price_monthly: 0, price_yearly: 0,
    stripe_monthly_price_id: '', stripe_yearly_price_id: '',
    max_items: 0, max_categories: 0,
    custom_domain: false, order_reports: false, is_active: true, sort: 0,
})

const form = reactive(blank())

function openCreate() {
    Object.assign(form, blank())
    editing.value = null
    modal.value = true
}

function openEdit(plan) {
    Object.assign(form, {
        name: plan.name,
        slug: plan.slug,
        description: plan.description ?? '',
        price_monthly: plan.price_monthly,
        price_yearly: plan.price_yearly,
        stripe_monthly_price_id: plan.stripe_monthly_price_id ?? '',
        stripe_yearly_price_id: plan.stripe_yearly_price_id ?? '',
        max_items: plan.max_items ?? 0,
        max_categories: plan.max_categories ?? 0,
        custom_domain: plan.custom_domain,
        order_reports: plan.order_reports,
        is_active: plan.is_active,
        sort: plan.sort ?? 0,
    })
    editing.value = plan
    modal.value = true
}

function submit() {
    submitting.value = true
    if (editing.value) {
        router.put(route('platform.superadmin.plans.update', editing.value.id), form, {
            onFinish: () => { submitting.value = false; modal.value = false },
        })
    } else {
        router.post(route('platform.superadmin.plans.store'), form, {
            onFinish: () => { submitting.value = false; modal.value = false },
        })
    }
}

function deletePlan(plan) {
    if (!confirm(`Delete plan "${plan.name}"? This cannot be undone.`)) return
    router.delete(route('platform.superadmin.plans.destroy', plan.id))
}
</script>

<style scoped>
.sa-wrap { min-height: 100vh; background: #f8fafc; }
.sa-nav { background: #1a1a1a; color: #fff; display: flex; align-items: center; gap: 24px; padding: 0 24px; height: 56px; }
.sa-brand { display: flex; align-items: center; gap: 10px; margin-right: auto; }
.brand-logo { width: 32px; height: 32px; background: #e85d04; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 13px; color: #fff; }
.brand-name { font-weight: 700; font-size: 16px; }
.sa-badge { background: #e85d04; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; }
.sa-links { display: flex; gap: 4px; }
.sa-link { color: #9ca3af; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 14px; }
.sa-link:hover, .sa-link.active { color: #fff; background: rgba(255,255,255,.1); }
.sa-logout { background: none; border: 1px solid #374151; color: #9ca3af; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; }
.sa-main { max-width: 1100px; margin: 0 auto; padding: 32px 24px; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 800; color: #111; }
.alert-success { background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
.sa-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; }
.sa-table th { background: #f9fafb; text-align: left; padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6b7280; }
.sa-table td { padding: 12px 16px; border-top: 1px solid #f3f4f6; font-size: 14px; }
.sa-table small { color: #9ca3af; }
.plan-desc { font-size: 12px; color: #9ca3af; margin-top: 2px; }
.actions { display: flex; gap: 6px; white-space: nowrap; }
.badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.badge.active { background: #d1fae5; color: #065f46; }
.badge.inactive { background: #f3f4f6; color: #6b7280; }
.btn-sm { background: #f3f4f6; color: #374151; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; }
.btn-sm:hover { background: #e5e7eb; }
.btn-sm.danger { color: #dc2626; }
.btn-sm.danger:hover { background: #fee2e2; }
.btn-primary { background: #e85d04; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-primary:hover:not(:disabled) { background: #c44d03; }
.btn-primary:disabled { opacity: 0.5; cursor: default; }
.empty { text-align: center; color: #9ca3af; padding: 40px; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; z-index: 50; }
.modal { background: #fff; border-radius: 16px; padding: 32px; width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; }
.modal-title { font-size: 20px; font-weight: 800; margin-bottom: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { margin-bottom: 16px; display: flex; flex-direction: column; gap: 4px; }
.field label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6b7280; }
.field input, .field textarea, .field select { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 12px; font-size: 14px; }
.field input:focus, .field textarea:focus { outline: none; border-color: #e85d04; }
.field small { color: #9ca3af; font-size: 11px; }
.checkboxes { gap: 8px; justify-content: center; }
.checkbox-row { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151; font-weight: normal; text-transform: none; cursor: pointer; }
.err { color: #dc2626; font-size: 12px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }
.btn-cancel { background: #f3f4f6; color: #374151; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
</style>
