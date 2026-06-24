<template>
    <div class="admin-layout">
        <aside class="sidebar" :class="{ open: sidebarOpen }">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <img v-if="brand.logo" :src="brand.logo" :alt="brand.name" class="sb-logo" />
                    <span v-else class="sb-name">{{ brand.name }}</span>
                </div>
                <button class="sidebar-close mobile-only" @click="sidebarOpen = false">Close</button>
            </div>

            <nav class="sidebar-nav">
                <Link :href="route('tenant.admin.dashboard')" class="nav-item" :class="{ active: isActive('dashboard') }"><span class="nav-icon">Db</span> Dashboard</Link>
                <Link :href="route('tenant.admin.orders.index')" class="nav-item" :class="{ active: isActive('orders') }"><span class="nav-icon">Or</span> Orders <span class="badge" v-if="pendingCount > 0">{{ pendingCount }}</span></Link>
                <Link :href="route('tenant.admin.categories.index')" class="nav-item" :class="{ active: isActive('categories') }"><span class="nav-icon">Ct</span> Categories</Link>
                <Link :href="route('tenant.admin.items.index')" class="nav-item" :class="{ active: isActive('items') }"><span class="nav-icon">Mn</span> Menu Items</Link>
                <Link :href="route('tenant.admin.pages.index')" class="nav-item" :class="{ active: isActive('pages') }"><span class="nav-icon">Pg</span> Pages</Link>
                <Link :href="route('tenant.admin.media.index')" class="nav-item" :class="{ active: isActive('media') }"><span class="nav-icon">Img</span> Media</Link>
                <Link :href="route('tenant.admin.users.index')" class="nav-item" :class="{ active: isActive('users') }"><span class="nav-icon">Sf</span> Staff</Link>
                <Link :href="route('tenant.admin.reports.index')" class="nav-item" :class="{ active: isActive('reports') }"><span class="nav-icon">Rp</span> Reports</Link>
                <Link :href="route('tenant.admin.settings.index')" class="nav-item" :class="{ active: isActive('settings') }"><span class="nav-icon">St</span> Settings</Link>
                <Link :href="route('tenant.admin.settings.stripe')" class="nav-item" :class="{ active: isActive('stripe') }"><span class="nav-icon">Pay</span> Stripe Connect</Link>
            </nav>

            <div class="sidebar-footer">
                <Link :href="route('tenant.storefront')" class="footer-link" target="_blank">View Store</Link>
                <a :href="route('platform.billing.index')" class="footer-link">Subscription</a>
                <form @submit.prevent="logout"><button type="submit" class="logout-btn">Sign Out</button></form>
            </div>
        </aside>

        <div class="sidebar-overlay" v-if="sidebarOpen" @click="sidebarOpen = false"></div>

        <div class="main-area">
            <div class="topbar">
                <button class="hamburger mobile-only" @click="sidebarOpen = true">Menu</button>
                <div class="topbar-title">{{ pageTitle }}</div>
                <div class="topbar-right"><span class="topbar-user">{{ user?.name }}</span></div>
            </div>

            <div class="flash-container" v-if="flash.success || flash.error">
                <div class="flash success" v-if="flash.success">{{ flash.success }}</div>
                <div class="flash error" v-if="flash.error">{{ flash.error }}</div>
            </div>

            <div class="content"><slot /></div>
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
    const url = page.url.replace(/\/$/, '') || '/'
    if (section === 'dashboard') return url === '/admin'

    const map = {
        orders: '/admin/orders',
        categories: '/admin/categories',
        items: '/admin/items',
        pages: '/admin/pages',
        media: '/admin/media',
        users: '/admin/users',
        reports: '/admin/reports',
        settings: '/admin/settings',
        stripe: '/admin/settings/stripe',
    }
    return url.startsWith(map[section] || '')
}

function logout() {
    router.post(route('tenant.admin.logout'))
}
</script>

<style scoped>
.admin-layout { display: flex; min-height: 100vh; background: #f5f8f7; }
.sidebar { width: 244px; background: #17272b; color: #d1d5db; display: flex; flex-direction: column; flex-shrink: 0; }
.sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,.08); display: flex; align-items: center; justify-content: space-between; }
.sb-logo { max-height: 40px; max-width: 140px; object-fit: contain; }
.sb-name { font-size: 16px; font-weight: 900; color: #fff; }
.sidebar-close { background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 13px; }
.sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
.nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #b7c8cc; text-decoration: none; font-size: 14px; font-weight: 700; transition: all .15s; position: relative; }
.nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
.nav-item.active { background: rgba(255,122,89,.15); color: #ffb199; border-right: 3px solid #ff7a59; }
.nav-icon { width: 24px; height: 22px; display: inline-flex; align-items: center; justify-content: center; border-radius: 5px; background: rgba(255,255,255,.07); font-size: 10px; font-weight: 900; }
.badge { margin-left: auto; background: #ff7a59; color: #fff; font-size: 10px; font-weight: 900; min-width: 18px; height: 18px; border-radius: 9px; display: flex; align-items: center; justify-content: center; padding: 0 4px; }
.sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.08); display: flex; flex-direction: column; gap: 8px; }
.footer-link, .logout-btn { color: #8ca2a7; font-size: 13px; text-decoration: none; background: none; border: none; cursor: pointer; padding: 0; text-align: left; }
.footer-link:hover { color: #fff; }
.logout-btn:hover { color: #fecdd3; }
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 49; }
.main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar { background: #fff; border-bottom: 1px solid #e5edf0; padding: 0 24px; height: 60px; display: flex; align-items: center; gap: 16px; }
.hamburger { background: #edf7f4; border: none; border-radius: 7px; font-size: 13px; cursor: pointer; color: #0f766e; padding: 8px 12px; font-weight: 900; }
.topbar-title { font-size: 16px; font-weight: 900; color: #17272b; flex: 1; }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.topbar-user { font-size: 13px; color: #657477; }
.flash-container { padding: 12px 24px 0; }
.flash { padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 700; }
.flash.success { background: #dcfce7; color: #15803d; }
.flash.error { background: #fee2e2; color: #dc2626; }
.content { padding: 24px; flex: 1; }
.mobile-only { display: none; }
@media (max-width: 768px) {
    .sidebar { position: fixed; top: 0; left: -270px; height: 100vh; z-index: 50; transition: left .3s; width: 270px; }
    .sidebar.open { left: 0; }
    .sidebar-overlay { display: block; }
    .mobile-only { display: flex !important; }
}
</style>