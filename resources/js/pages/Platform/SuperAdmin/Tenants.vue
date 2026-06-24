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
                <Link :href="route('platform.superadmin.settings')" class="sa-link">Settings</Link>
            </nav>
            <Link :href="route('platform.logout')" method="post" as="button" class="sa-logout">Logout</Link>
        </header>

        <main class="sa-main">
            <div class="page-header">
                <h1 class="page-title">Tenants</h1>
                <input v-model="search" placeholder="Search by name or ID..." class="search-input" />
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
                        <td>{{ tenant.plan?.name ?? '—' }}</td>
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
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({ tenants: Array })
const page = usePage()
const flash = page.props.flash ?? {}

const search = ref('')

const filtered = computed(() => {
    if (!search.value) return props.tenants
    const q = search.value.toLowerCase()
    return props.tenants.filter(t =>
        t.name.toLowerCase().includes(q) || t.id.toLowerCase().includes(q)
    )
})

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString() : '—'
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
</style>
