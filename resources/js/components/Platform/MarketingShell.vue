<template>
    <div class="marketing-shell">
        <header class="site-header">
            <Link :href="route('home')" class="brand">SimpleOrder</Link>
            <nav class="nav">
                <Link
                    v-for="item in navPages"
                    :key="item.slug"
                    :href="hrefFor(item.slug)"
                >{{ item.nav_label || item.title }}</Link>
            </nav>
            <div class="actions">
                <Link :href="route('login')" class="login-link">Log In</Link>
                <Link :href="route('register')" class="signup-link">Start Free</Link>
            </div>
        </header>

        <slot />

        <footer class="site-footer">
            <div>
                <strong>SimpleOrder</strong>
                <p>Restaurant websites, ordering, payments, and guest updates.</p>
            </div>
            <div class="footer-links">
                <Link v-for="item in navPages" :key="item.slug" :href="hrefFor(item.slug)">{{ item.nav_label || item.title }}</Link>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    navPages: { type: Array, default: () => [] },
})

function hrefFor(slug) {
    if (slug === 'home') return route('home')
    if (slug === 'plans') return route('plans')
    if (slug === 'about') return route('about')
    if (slug === 'contact') return route('contact')
    return '/' + slug
}
</script>

<style scoped>
.marketing-shell { min-height: 100vh; background: #fffdf8; color: #17272b; }
.site-header { max-width: 1180px; margin: 0 auto; padding: 18px 22px; display: flex; align-items: center; gap: 24px; }
.brand { color: #17272b; font-size: 22px; font-weight: 900; text-decoration: none; }
.nav { display: flex; align-items: center; gap: 6px; flex: 1; }
.nav a { color: #405257; text-decoration: none; padding: 8px 12px; border-radius: 7px; font-weight: 800; font-size: 14px; }
.nav a:hover { color: #0f766e; background: #edf7f4; }
.actions { display: flex; gap: 10px; align-items: center; }
.login-link, .signup-link { border-radius: 8px; padding: 10px 16px; font-weight: 900; text-decoration: none; font-size: 14px; }
.login-link { color: #0f766e; }
.signup-link { background: #ff7a59; color: #fff; }
.site-footer { max-width: 1180px; margin: 0 auto; padding: 34px 22px; display: flex; justify-content: space-between; gap: 22px; border-top: 1px solid #f0e4d7; color: #657477; }
.site-footer strong { color: #17272b; }
.site-footer p { margin-top: 6px; }
.footer-links { display: flex; gap: 16px; flex-wrap: wrap; }
.footer-links a { color: #0f766e; text-decoration: none; font-weight: 800; }
@media (max-width: 760px) { .site-header { flex-wrap: wrap; } .nav { order: 3; flex-basis: 100%; overflow-x: auto; } .actions { margin-left: auto; } .site-footer { flex-direction: column; } }
</style>