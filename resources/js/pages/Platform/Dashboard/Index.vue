<template>
    <div class="platform-dashboard">
        <!-- Top nav -->
        <header class="platform-nav">
            <div class="nav-brand">
                <div class="brand-logo">SO</div>
                <span class="brand-name">SimpleOrder</span>
            </div>
            <nav class="nav-links">
                <Link :href="route('platform.dashboard')" class="nav-link active">Dashboard</Link>
                <Link :href="route('platform.billing.index')" class="nav-link">Billing</Link>
                <a :href="tenantUrl" class="nav-link" target="_blank">View Store →</a>
            </nav>
            <div class="nav-actions">
                <span class="nav-user">{{ user?.name }}</span>
                <form @submit.prevent="logout">
                    <button type="submit" class="logout-btn">Sign Out</button>
                </form>
            </div>
        </header>

        <div class="content">
            <!-- Welcome card -->
            <div class="welcome-card">
                <div class="welcome-text">
                    <h1 class="welcome-title">Welcome back, {{ user?.name?.split(' ')[0] }}! 👋</h1>
                    <p class="welcome-sub">Your store is <strong>{{ tenant.is_active ? 'live' : 'inactive' }}</strong> at <a :href="tenantUrl" class="store-link" target="_blank">{{ tenantUrl }}</a></p>
                </div>
                <div class="subscription-status">
                    <div class="sub-badge" :class="subscriptionClass">{{ subscriptionLabel }}</div>
                    <Link :href="route('platform.billing.index')" class="manage-link">Manage Plan</Link>
                </div>
            </div>

            <!-- Quick stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-value">{{ stats.total_orders }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">${{ fmt(stats.total_revenue) }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🍽</div>
                    <div class="stat-info">
                        <div class="stat-label">Menu Items</div>
                        <div class="stat-value">{{ stats.item_count }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💳</div>
                    <div class="stat-info">
                        <div class="stat-label">Stripe Connect</div>
                        <div class="stat-value stripe" :class="{ connected: tenant.stripe_connect_active }">
                            {{ tenant.stripe_connect_active ? 'Connected' : 'Not Set Up' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick links -->
            <div class="quick-links">
                <h2 class="section-title">Quick Actions</h2>
                <div class="links-grid">
                    <a :href="route('tenant.admin.dashboard')" class="quick-link-card">
                        <span class="ql-icon">📊</span>
                        <span class="ql-label">Admin Dashboard</span>
                        <span class="ql-arrow">→</span>
                    </a>
                    <a :href="route('tenant.admin.orders.index')" class="quick-link-card">
                        <span class="ql-icon">📋</span>
                        <span class="ql-label">View Orders</span>
                        <span class="ql-arrow">→</span>
                    </a>
                    <a :href="route('tenant.admin.items.index')" class="quick-link-card">
                        <span class="ql-icon">🍽</span>
                        <span class="ql-label">Manage Menu</span>
                        <span class="ql-arrow">→</span>
                    </a>
                    <a :href="route('tenant.admin.settings.stripe')" class="quick-link-card" v-if="!tenant.stripe_connect_active">
                        <span class="ql-icon">💳</span>
                        <span class="ql-label">Set Up Payments</span>
                        <span class="ql-arrow urgent">→</span>
                    </a>
                    <a :href="route('tenant.admin.reports.index')" class="quick-link-card">
                        <span class="ql-icon">📈</span>
                        <span class="ql-label">View Reports</span>
                        <span class="ql-arrow">→</span>
                    </a>
                    <a :href="route('platform.billing.index')" class="quick-link-card">
                        <span class="ql-icon">💰</span>
                        <span class="ql-label">Billing & Plan</span>
                        <span class="ql-arrow">→</span>
                    </a>
                </div>
            </div>

            <!-- Stripe warning -->
            <div class="alert warning" v-if="!tenant.stripe_connect_active">
                <strong>⚠️ Payment setup required.</strong> Connect your Stripe account to start accepting orders.
                <Link :href="route('tenant.admin.settings.stripe')" class="alert-link">Set up now →</Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    tenant: Object,
    user: Object,
    stats: Object,
    tenantUrl: String,
})

const subscriptionLabel = computed(() => {
    const s = props.tenant?.subscription_status
    if (s === 'active') return 'Active'
    if (s === 'trialing') return 'Trial'
    if (s === 'past_due') return 'Past Due'
    if (s === 'canceled') return 'Cancelled'
    return 'Inactive'
})

const subscriptionClass = computed(() => ({
    active: 'green', trialing: 'blue', past_due: 'orange', canceled: 'red',
}[props.tenant?.subscription_status] || 'gray'))

const fmt = (n) => Number(n || 0).toFixed(2)

function logout() {
    router.post(route('platform.logout'))
}
</script>

<style scoped>
.platform-dashboard { min-height: 100vh; background: #f5f5f5; }
.platform-nav { background: #1a1a1a; padding: 0 24px; display: flex; align-items: center; gap: 24px; height: 64px; }
.nav-brand { display: flex; align-items: center; gap: 10px; }
.brand-logo { width: 36px; height: 36px; background: #e85d04; color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; }
.brand-name { font-size: 18px; font-weight: 800; color: #fff; }
.nav-links { display: flex; gap: 4px; flex: 1; margin-left: 24px; }
.nav-link { color: #9ca3af; text-decoration: none; padding: 8px 14px; border-radius: 6px; font-size: 14px; font-weight: 500; }
.nav-link:hover { color: #fff; background: rgba(255,255,255,.07); }
.nav-link.active { color: #fff; background: rgba(232,93,4,.2); }
.nav-actions { display: flex; align-items: center; gap: 12px; }
.nav-user { font-size: 13px; color: #9ca3af; }
.logout-btn { background: none; border: 1.5px solid rgba(255,255,255,.15); color: #9ca3af; padding: 6px 14px; border-radius: 6px; font-size: 13px; cursor: pointer; }
.logout-btn:hover { color: #fff; border-color: rgba(255,255,255,.3); }
.content { max-width: 960px; margin: 0 auto; padding: 32px 24px; }
.welcome-card { background: #fff; border-radius: 14px; padding: 28px; display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.06); flex-wrap: wrap; }
.welcome-title { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
.welcome-sub { font-size: 14px; color: #6b7280; }
.store-link { color: #e85d04; text-decoration: none; font-weight: 600; }
.subscription-status { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
.sub-badge { padding: 4px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
.sub-badge.green { background: #dcfce7; color: #15803d; }
.sub-badge.blue { background: #dbeafe; color: #1d4ed8; }
.sub-badge.orange { background: #fef3c7; color: #92400e; }
.sub-badge.red { background: #fee2e2; color: #dc2626; }
.sub-badge.gray { background: #f3f4f6; color: #6b7280; }
.manage-link { font-size: 13px; color: #e85d04; text-decoration: none; font-weight: 600; }
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 28px; }
.stat-card { background: #fff; border-radius: 10px; padding: 18px; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.stat-icon { font-size: 28px; }
.stat-label { font-size: 12px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
.stat-value { font-size: 22px; font-weight: 800; color: #1a1a1a; }
.stat-value.stripe.connected { color: #15803d; font-size: 14px; }
.stat-value.stripe:not(.connected) { color: #dc2626; font-size: 14px; }
.section-title { font-size: 16px; font-weight: 700; margin-bottom: 14px; }
.links-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 24px; }
.quick-link-card { background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 12px; text-decoration: none; color: #1a1a1a; transition: all 0.15s; }
.quick-link-card:hover { border-color: #e85d04; box-shadow: 0 2px 10px rgba(232,93,4,.1); }
.ql-icon { font-size: 20px; }
.ql-label { flex: 1; font-size: 14px; font-weight: 600; }
.ql-arrow { color: #9ca3af; font-size: 14px; }
.ql-arrow.urgent { color: #e85d04; }
.alert { background: #fef3c7; border: 1.5px solid #f59e0b; border-radius: 10px; padding: 14px 18px; font-size: 14px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.alert.warning { }
.alert-link { color: #e85d04; font-weight: 700; text-decoration: none; margin-left: auto; }
</style>
