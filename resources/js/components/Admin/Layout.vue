<template>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ open: sidebarOpen }">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <img v-if="brand.logo" :src="brand.logo" :alt="brand.name" class="sb-logo" />
                    <span v-else class="sb-name">{{ brand.name }}</span>
                </div>
                <button class="sidebar-close mobile-only" @click="sidebarOpen = false">✕</button>
            </div>

            <nav class="sidebar-nav">
                <Link :href="route('tenant.admin.dashboard')" class="nav-item" :class="{ active: isActive('dashboard') }">
                    <span class="nav-icon">📊</span> Dashboard
                </Link>
                <Link :href="route('tenant.admin.orders.index')" class="nav-item" :class="{ active: isActive('orders') }">
                    <span class="nav-icon">📋</span> Orders
                    <span class="badge" v-if="pendingCount > 0">{{ pendingCount }}</span>
                </Link>
                <Link :href="route('tenant.admin.categories.index')" class="nav-item" :class="{ active: isActive('categories') }">
                    <span class="nav-icon">📂</span> Categories
                </Link>
                <Link :href="route('tenant.admin.items.index')" class="nav-item" :class="{ active: isActive('items') }">
                    <span class="nav-icon">🍽</span> Menu Items
                </Link>
                <Link :href="route('tenant.admin.media.index')" class="nav-item" :class="{ active: isActive('media') }">
                    <span class="nav-icon">🖼</span> Media
                </Link>
                <Link :href="route('tenant.admin.users.index')" class="nav-item" :class="{ active: isActive('users') }">
                    <span class="nav-icon">👥</span> Staff
                </Link>
                <Link :href="route('tenant.admin.reports.index')" class="nav-item" :class="{ active: isActive('reports') }">
                    <span class="nav-icon">📈</span> Reports
                </Link>
                <Link :href="route('tenant.admin.settings.index')" class="nav-item" :class="{ active: isActive('settings') }">
                    <span class="nav-icon">⚙️</span> Settings
                </Link>
                <Link :href="route('tenant.admin.settings.stripe')" class="nav-item" :class="{ active: isActive('stripe') }">
                    <span class="nav-icon">💳</span> Stripe Connect
                </Link>
            </nav>

            <div class="sidebar-footer">
                <Link :href="route('storefront')" class="footer-link" target="_blank">View Store →</Link>
                <a :href="route('platform.billing.index')" class="footer-link">Subscription</a>
                <form @submit.prevent="logout">
                    <button type="submit" class="logout-btn">Sign Out</button>
                </form>
            </div>
        </aside>

        <!-- Overlay (mobile) -->
        <div class="sidebar-overlay" v-if="sidebarOpen" @click="sidebarOpen = false"></div>

        <!-- Main area -->
        <div class="main-area">
            <!-- Top bar -->
            <div class="topbar">
                <button class="hamburger mobile-only" @click="sidebarOpen = true">☰</button>
                <div class="topbar-title">{{ pageTitle }}</div>
                <div class="topbar-right">
                    <span class="topbar-user">{{ user?.name }}</span>
                </div>
            </div>

            <!-- Flash messages -->
            <div class="flash-container" v-if="flash.success || flash.error">
                <div class="flash success" v-if="flash.success">✓ {{ flash.success }}</div>
                <div class="flash error" v-if="flash.error">✕ {{ flash.error }}</div>
            </div>

            <!-- Page content -->
            <div class="content">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const props = defineProps({
    pageTitle: { type: String, default: '' },
    pendingCount: { type: Number, default: 0 },
})

const page = usePage()
const brand = page.props.tenant_brand || {}
const user = page.props.auth?.tenant_user
const flash = page.props.flash || {}
const sidebarOpen = ref(false)

function isActive(section) {
    const url = page.url
    const map = {
        dashboard: '/admin',
        orders: '/admin/orders',
        categories: '/admin/categories',
        items: '/admin/items',
        media: '/admin/media',
        users: '/admin/users',
        reports: '/admin/reports',
        settings: '/admin/settings',
        stripe: '/admin/stripe',
    }
    return url.startsWith(map[section] || '')
}

function logout() {
    router.post(route('tenant.admin.logout'))
}
</script>

<style scoped>
.admin-layout { display: flex; min-height: 100vh; background: #f5f5f5; }
.sidebar { width: 240px; background: #111; color: #d1d5db; display: flex; flex-direction: column; flex-shrink: 0; }
.sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,.08); display: flex; align-items: center; justify-content: space-between; }
.sb-logo { max-height: 40px; max-width: 140px; object-fit: contain; }
.sb-name { font-size: 16px; font-weight: 800; color: #fff; }
.sidebar-close { background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 16px; }
.sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
.nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #9ca3af; text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.15s; position: relative; }
.nav-item:hover { background: rgba(255,255,255,.05); color: #fff; }
.nav-item.active { background: rgba(232,93,4,.15); color: #e85d04; border-right: 3px solid #e85d04; }
.nav-icon { width: 20px; text-align: center; font-size: 16px; }
.badge { margin-left: auto; background: #e85d04; color: #fff; font-size: 10px; font-weight: 800; min-width: 18px; height: 18px; border-radius: 9px; display: flex; align-items: center; justify-content: center; padding: 0 4px; }
.sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.08); display: flex; flex-direction: column; gap: 8px; }
.footer-link { color: #6b7280; font-size: 13px; text-decoration: none; }
.footer-link:hover { color: #9ca3af; }
.logout-btn { background: none; border: none; color: #6b7280; font-size: 13px; cursor: pointer; padding: 0; text-align: left; }
.logout-btn:hover { color: #ef4444; }
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 49; }
.main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 0 24px; height: 60px; display: flex; align-items: center; gap: 16px; }
.hamburger { background: none; border: none; font-size: 20px; cursor: pointer; color: #374151; padding: 4px 8px; }
.topbar-title { font-size: 16px; font-weight: 700; color: #1a1a1a; flex: 1; }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.topbar-user { font-size: 13px; color: #6b7280; }
.flash-container { padding: 12px 24px 0; }
.flash { padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; }
.flash.success { background: #dcfce7; color: #15803d; }
.flash.error { background: #fee2e2; color: #dc2626; }
.content { padding: 24px; flex: 1; }
.mobile-only { display: none; }
@media (max-width: 768px) {
    .sidebar { position: fixed; top: 0; left: -260px; height: 100vh; z-index: 50; transition: left 0.3s; width: 260px; }
    .sidebar.open { left: 0; }
    .sidebar-overlay { display: block; }
    .mobile-only { display: flex !important; }
}
</style>
