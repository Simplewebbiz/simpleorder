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
                <Link :href="route('platform.superadmin.tenants.index')" class="sa-link active">Tenants</Link>
                <Link :href="route('platform.superadmin.plans.index')" class="sa-link">Plans</Link>
                <Link :href="route('platform.superadmin.marketing-pages.index')" class="sa-link">Website CMS</Link>
                <Link :href="route('platform.superadmin.settings')" class="sa-link">Settings</Link>
            </nav>
            <Link :href="route('platform.logout')" method="post" as="button" class="sa-logout">Logout</Link>
        </header>

        <main class="sa-main">
            <div class="page-header">
                <h1 class="page-title">Tenants</h1>
                <div class="header-right">
                    <input v-model="search" placeholder="Search by name or ID..." class="search-input" />
                    <button @click="openCreate" class="btn-primary">+ New Tenant</button>
                </div>
            </div>

            <div v-if="flash.success" class="alert-success">{{ flash.success }}</div>

            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Store</th>
                        <th>Domain</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Trial Ends</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tenant in filtered" :key="tenant.id">
                        <td>
                            <strong>{{ tenant.name }}</strong>
                            <br><small>{{ tenant.id }}</small>
                        </td>
                        <td>
                            <span v-for="d in tenant.domains" :key="d.id">
                                {{ d.domain }}<br>
                            </span>
                        </td>
                        <td>{{ tenant.plan?.name ?? '-' }}</td>
                        <td><span :class="['badge', tenant.subscription_status]">{{ tenant.subscription_status }}</span></td>
                        <td>{{ formatDate(tenant.trial_ends_at) }}</td>
                        <td>{{ formatDate(tenant.created_at) }}</td>
                        <td class="actions">
                            <Link :href="route('platform.superadmin.tenants.show', tenant.id)" class="btn-sm">View</Link>
                            <button @click="deleteTenant(tenant)" class="btn-sm danger">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="filtered.length === 0">
                        <td colspan="7" class="empty">No tenants found.</td>
                    </tr>
                </tbody>
            </table>
        </main>

        <!-- Create Tenant Modal -->
        <div v-if="modal" class="modal-overlay" @click.self="modal = false">
            <div class="modal">
                <h2 class="modal-title">New Tenant</h2>
                <form @submit.prevent="submit">
                    <div class="field">
                        <label>Store Name</label>
                        <input v-model="form.store_name" required placeholder="e.g. Joe's Pizza" />
                        <div class="err" v-if="errors.store_name">{{ errors.store_name }}</div>
                    </div>
                    <div class="field">
                        <label>Subdomain</label>
                        <div class="input-suffix">
                            <input v-model="form.subdomain" required placeholder="joespizza" pattern="[a-z0-9\-]+" />
                            <span>.simpleorder.biz</span>
                        </div>
                        <div class="err" v-if="errors.subdomain">{{ errors.subdomain }}</div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label>Admin Name</label>
                            <input v-model="form.name" required />
                            <div class="err" v-if="errors.name">{{ errors.name }}</div>
                        </div>
                        <div class="field">
                            <label>Admin Email</label>
                            <input v-model="form.email" type="email" required />
                            <div class="err" v-if="errors.email">{{ errors.email }}</div>
                        </div>
                    </div>
                    <div class="field">
                        <label>Temporary Password</label>
                        <input v-model="form.password" type="text" required minlength="8" />
                        <div class="err" v-if="errors.password">{{ errors.password }}</div>
                    </div>
                    <div class="field">
                        <label>Plan</label>
                        <select v-model="form.plan_id" required>
                            <option value="" disabled>Select a plan</option>
                            <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                        </select>
                        <div class="err" v-if="errors.plan_id">{{ errors.plan_id }}</div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" @click="modal = false" class="btn-cancel">Cancel</button>
                        <button type="submit" class="btn-primary" :disabled="submitting">
                            {{ submitting ? 'Creating...' : 'Create Tenant' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({ tenants: Array, plans: Array })
const page = usePage()
const flash = page.props.flash ?? {}
const errors = page.props.errors ?? {}

const search = ref('')
const modal = ref(false)
const submitting = ref(false)

const form = reactive({ store_name: '', subdomain: '', name: '', email: '', password: '', plan_id: '' })

const filtered = computed(() => {
    if (!search.value) return props.tenants
    const q = search.value.toLowerCase()
    return props.tenants.filter(t =>
        t.name.toLowerCase().includes(q) || t.id.toLowerCase().includes(q)
    )
})

function openCreate() {
    Object.assign(form, { store_name: '', subdomain: '', name: '', email: '', password: '', plan_id: '' })
    modal.value = true
}

function submit() {
    submitting.value = true
    router.post(route('platform.superadmin.tenants.store'), { ...form, password_confirmation: form.password }, {
        onFinish: () => { submitting.value = false },
        onSuccess: () => { modal.value = false },
    })
}

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString() : '-'
}

function deleteTenant(tenant) {
    if (!confirm(`Delete tenant "${tenant.name}"? This will destroy their database and all data.`)) return
    router.delete(route('platform.superadmin.tenants.destroy', tenant.id))
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
.sa-main { max-width: 1200px; margin: 0 auto; padding: 32px 24px; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 800; color: #111; }
.search-input { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 14px; font-size: 14px; width: 280px; }
.search-input:focus { outline: none; border-color: #e85d04; }
.alert-success { background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
.sa-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; }
.sa-table th { background: #f9fafb; text-align: left; padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6b7280; }
.sa-table td { padding: 12px 16px; border-top: 1px solid #f3f4f6; font-size: 14px; vertical-align: top; }
.sa-table small { color: #9ca3af; }
.actions { display: flex; gap: 6px; white-space: nowrap; }
.badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.badge.active { background: #d1fae5; color: #065f46; }
.badge.trialing { background: #fef3c7; color: #92400e; }
.badge.canceled, .badge.cancelled { background: #fee2e2; color: #991b1b; }
.btn-sm { background: #f3f4f6; color: #374151; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
.btn-sm:hover { background: #e5e7eb; }
.btn-sm.danger { color: #dc2626; }
.btn-sm.danger:hover { background: #fee2e2; }
.empty { text-align: center; color: #9ca3af; padding: 40px; }
.header-right { display: flex; align-items: center; gap: 12px; }
.btn-primary { background: #e85d04; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; white-space: nowrap; }
.btn-primary:hover:not(:disabled) { background: #c44d03; }
.btn-primary:disabled { opacity: 0.5; cursor: default; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; z-index: 50; }
.modal { background: #fff; border-radius: 16px; padding: 32px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; }
.modal-title { font-size: 20px; font-weight: 800; margin-bottom: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { margin-bottom: 16px; display: flex; flex-direction: column; gap: 4px; }
.field label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6b7280; }
.field input, .field select { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 12px; font-size: 14px; }
.field input:focus, .field select:focus { outline: none; border-color: #e85d04; }
.input-suffix { display: flex; align-items: center; border: 1.5px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
.input-suffix input { border: none; border-radius: 0; flex: 1; }
.input-suffix span { padding: 8px 12px; background: #f9fafb; color: #6b7280; font-size: 13px; white-space: nowrap; }
.err { color: #dc2626; font-size: 12px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }
.btn-cancel { background: #f3f4f6; color: #374151; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
</style>
