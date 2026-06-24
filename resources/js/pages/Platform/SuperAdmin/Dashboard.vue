<template>
    <div class="sa-wrap">
        <header class="sa-nav">
            <div class="sa-brand">
                <div class="brand-logo">SO</div>
                <span class="brand-name">SimpleOrder</span>
                <span class="sa-badge">Super Admin</span>
            </div>
            <nav class="sa-links">
                <Link :href="route('platform.superadmin.index')" class="sa-link active">Dashboard</Link>
                <Link :href="route('platform.superadmin.tenants.index')" class="sa-link">Tenants</Link>
                <Link :href="route('platform.superadmin.plans.index')" class="sa-link">Plans</Link>
                <Link :href="route('platform.superadmin.marketing-pages.index')" class="sa-link">Website CMS</Link>
                <Link :href="route('platform.superadmin.settings')" class="sa-link">Settings</Link>
            </nav>
            <Link :href="route('platform.logout')" method="post" as="button" class="sa-logout">Logout</Link>
        </header>

        <main class="sa-main">
            <h1 class="page-title">Platform Overview</h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ stats.total_tenants }}</div>
                    <div class="stat-label">Total Tenants</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-value">{{ stats.active_tenants }}</div>
                    <div class="stat-label">Active Subscriptions</div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-value">{{ stats.trialing_tenants }}</div>
                    <div class="stat-label">On Trial</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ stats.total_plans }}</div>
                    <div class="stat-label">Plans</div>
                </div>
            </div>

            <h2 class="section-title">Recent Tenants</h2>
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Store</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tenant in recent_tenants" :key="tenant.id">
                        <td><strong>{{ tenant.name }}</strong><br><small>{{ tenant.id }}</small></td>
                        <td>{{ tenant.plan?.name ?? '-' }}</td>
                        <td><span :class="['badge', tenant.subscription_status]">{{ tenant.subscription_status }}</span></td>
                        <td>{{ formatDate(tenant.created_at) }}</td>
                        <td>
                            <Link :href="route('platform.superadmin.tenants.show', tenant.id)" class="btn-sm">View</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    stats: Object,
    recent_tenants: Array,
})

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString() : '-'
}
</script>

<style scoped>
.sa-wrap { min-height: 100vh; background: #f8fafc; }
.sa-nav { background: #1a1a1a; color: #fff; display: flex; align-items: center; gap: 24px; padding: 0 24px; height: 56px; }
.sa-brand { display: flex; align-items: center; gap: 10px; margin-right: auto; }
.brand-logo { width: 32px; height: 32px; background: #e85d04; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 13px; }
.brand-name { font-weight: 700; font-size: 16px; }
.sa-badge { background: #e85d04; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; }
.sa-links { display: flex; gap: 4px; }
.sa-link { color: #9ca3af; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 14px; }
.sa-link:hover, .sa-link.active { color: #fff; background: rgba(255,255,255,.1); }
.sa-logout { background: none; border: 1px solid #374151; color: #9ca3af; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; }
.sa-main { max-width: 1100px; margin: 0 auto; padding: 32px 24px; }
.page-title { font-size: 24px; font-weight: 800; color: #111; margin-bottom: 24px; }
.section-title { font-size: 16px; font-weight: 700; color: #374151; margin: 32px 0 12px; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 8px; }
.stat-card { background: #fff; border-radius: 12px; padding: 20px 24px; border: 1px solid #e5e7eb; }
.stat-card.green { border-left: 4px solid #10b981; }
.stat-card.yellow { border-left: 4px solid #f59e0b; }
.stat-value { font-size: 32px; font-weight: 800; color: #111; }
.stat-label { font-size: 13px; color: #6b7280; margin-top: 4px; }
.sa-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; }
.sa-table th { background: #f9fafb; text-align: left; padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6b7280; }
.sa-table td { padding: 12px 16px; border-top: 1px solid #f3f4f6; font-size: 14px; }
.sa-table small { color: #9ca3af; }
.badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.badge.active { background: #d1fae5; color: #065f46; }
.badge.trialing { background: #fef3c7; color: #92400e; }
.badge.canceled, .badge.cancelled { background: #fee2e2; color: #991b1b; }
.btn-sm { background: #f3f4f6; color: #374151; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; }
.btn-sm:hover { background: #e5e7eb; }
</style>
