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
            <div class="breadcrumb">
                <Link :href="route('platform.superadmin.tenants.index')" class="back-link">Back to Tenants</Link>
            </div>

            <div class="tenant-header">
                <div>
                    <h1 class="page-title">{{ tenant.name }}</h1>
                    <div class="tenant-meta">
                        <span class="meta-item">ID: <strong>{{ tenant.id }}</strong></span>
                        <span class="meta-item">Plan: <strong>{{ tenant.plan?.name ?? '-' }}</strong></span>
                        <span :class="['badge', tenant.subscription_status]">{{ tenant.subscription_status }}</span>
                    </div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3 class="card-title">Account Info</h3>
                    <div class="info-row"><span>Status</span><span>{{ tenant.subscription_status }}</span></div>
                    <div class="info-row"><span>Trial Ends</span><span>{{ formatDate(tenant.trial_ends_at) }}</span></div>
                    <div class="info-row"><span>Created</span><span>{{ formatDate(tenant.created_at) }}</span></div>
                    <div class="info-row"><span>Stripe Customer</span><span>{{ tenant.stripe_id ?? '-' }}</span></div>
                </div>
                <div class="info-card">
                    <h3 class="card-title">Domains</h3>
                    <div v-for="d in tenant.domains" :key="d.id" class="domain-row">
                        <span>{{ d.domain }}</span>
                        <span class="badge-sm" v-if="d.is_primary">Primary</span>
                        <span class="badge-sm custom" v-if="d.is_custom">Custom</span>
                    </div>
                    <div v-if="!tenant.domains?.length" class="empty-small">No domains</div>
                </div>
            </div>

            <h2 class="section-title">Recent Orders (last 50)</h2>
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Items</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders" :key="order.id">
                        <td>{{ order.order_number ?? order.id }}</td>
                        <td>{{ order.customer_name ?? order.customer_email ?? '-' }}</td>
                        <td><span :class="['badge', order.status]">{{ order.status }}</span></td>
                        <td>${{ ((order.total ?? 0) / 100).toFixed(2) }}</td>
                        <td>{{ order.items?.length ?? 0 }}</td>
                        <td>{{ formatDate(order.created_at) }}</td>
                    </tr>
                    <tr v-if="orders.length === 0">
                        <td colspan="6" class="empty">No orders yet.</td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    tenant: Object,
    orders: Array,
})

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString() : '-'
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
.breadcrumb { margin-bottom: 16px; }
.back-link { color: #6b7280; text-decoration: none; font-size: 14px; }
.back-link:hover { color: #111; }
.tenant-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 800; color: #111; }
.tenant-meta { display: flex; align-items: center; gap: 16px; margin-top: 6px; }
.meta-item { font-size: 14px; color: #6b7280; }
.badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.badge.active { background: #d1fae5; color: #065f46; }
.badge.trialing { background: #fef3c7; color: #92400e; }
.badge.canceled, .badge.cancelled { background: #fee2e2; color: #991b1b; }
.badge.pending { background: #e0e7ff; color: #3730a3; }
.badge.completed { background: #d1fae5; color: #065f46; }
.badge-sm { font-size: 10px; padding: 2px 8px; border-radius: 20px; background: #f3f4f6; color: #6b7280; }
.badge-sm.custom { background: #e0e7ff; color: #3730a3; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
.info-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; }
.card-title { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.04em; }
.info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; color: #6b7280; }
.info-row span:last-child { color: #111; font-weight: 500; }
.domain-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
.empty-small { color: #9ca3af; font-size: 13px; padding: 8px 0; }
.section-title { font-size: 16px; font-weight: 700; color: #374151; margin-bottom: 12px; }
.sa-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; }
.sa-table th { background: #f9fafb; text-align: left; padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6b7280; }
.sa-table td { padding: 12px 16px; border-top: 1px solid #f3f4f6; font-size: 14px; }
.empty { text-align: center; color: #9ca3af; padding: 40px; }
</style>
