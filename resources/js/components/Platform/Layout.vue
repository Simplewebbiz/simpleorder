<template>
    <div class="platform-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <span class="sidebar-brand">SimpleOrder</span>
            </div>
            <nav class="sidebar-nav">
                <Link :href="route('platform.dashboard')" class="nav-item" :class="{ active: isActive('dashboard') }">
                    <span class="nav-icon">📊</span> Dashboard
                </Link>
                <Link :href="route('platform.billing.index')" class="nav-item" :class="{ active: isActive('billing') }">
                    <span class="nav-icon">💳</span> Billing
                </Link>
                <Link v-if="isSuperAdmin" :href="route('platform.super.settings')" class="nav-item" :class="{ active: isActive('super') }">
                    <span class="nav-icon">⚙️</span> Super Admin
                </Link>
            </nav>
            <div class="sidebar-footer">
                <form @submit.prevent="logout">
                    <button type="submit" class="logout-btn">Sign Out</button>
                </form>
            </div>
        </aside>

        <div class="main-area">
            <div class="topbar">
                <div class="topbar-title">{{ title }}</div>
                <div class="topbar-user">{{ user?.name }}</div>
            </div>

            <div class="flash-container" v-if="flash.success || flash.error">
                <div class="flash success" v-if="flash.success">✓ {{ flash.success }}</div>
                <div class="flash error" v-if="flash.error">✕ {{ flash.error }}</div>
            </div>

            <div class="content">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'

defineProps({
    title: { type: String, default: '' },
})

const page = usePage()
const user = page.props.auth?.platform_user
const flash = page.props.flash || {}
const isSuperAdmin = user?.is_super

function isActive(section) {
    const map = {
        dashboard: '/dashboard',
        billing: '/billing',
        super: '/super',
    }
    return page.url.startsWith(map[section] || '')
}

function logout() {
    router.post(route('platform.logout'))
}
</script>

<style scoped>
.platform-layout { display: flex; min-height: 100vh; background: #f5f5f5; }
.sidebar { width: 240px; background: #1e293b; color: #d1d5db; display: flex; flex-direction: column; flex-shrink: 0; }
.sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
.sidebar-brand { font-size: 16px; font-weight: 800; color: #fff; }
.sidebar-nav { flex: 1; padding: 12px 0; }
.nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #9ca3af; text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.15s; }
.nav-item:hover { background: rgba(255,255,255,.05); color: #fff; }
.nav-item.active { background: rgba(99,102,241,.2); color: #818cf8; border-right: 3px solid #818cf8; }
.nav-icon { width: 20px; text-align: center; }
.sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.08); }
.logout-btn { background: none; border: none; color: #6b7280; font-size: 13px; cursor: pointer; padding: 0; }
.logout-btn:hover { color: #ef4444; }
.main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 0 24px; height: 60px; display: flex; align-items: center; justify-content: space-between; }
.topbar-title { font-size: 16px; font-weight: 700; color: #1a1a1a; }
.topbar-user { font-size: 13px; color: #6b7280; }
.flash-container { padding: 12px 24px 0; }
.flash { padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; }
.flash.success { background: #dcfce7; color: #15803d; }
.flash.error { background: #fee2e2; color: #dc2626; }
.content { padding: 24px; flex: 1; }
</style>
