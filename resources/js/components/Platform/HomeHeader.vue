<template>
    <header class="home-header">
        <div class="home-header__inner">
            <a href="/" class="home-header__brand">
                <span class="home-header__mark" aria-hidden="true">🍴</span>
                <span class="home-header__word"><span class="navy">simple</span><span class="orange">order</span><span class="blue">.biz</span></span>
            </a>

            <nav class="home-header__nav" aria-label="Primary">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="/plans">Pricing</a>
                <a href="#for-restaurants">For Restaurants</a>
                <a href="#resources">Resources</a>
            </nav>

            <div class="home-header__actions">
                <a href="/login" class="btn btn--outline">Login</a>
                <a href="/register" class="btn btn--primary">Get Started</a>
            </div>

            <button
                class="home-header__toggle"
                type="button"
                :aria-expanded="mobileOpen"
                aria-controls="home-mobile-nav"
                @click="mobileOpen = !mobileOpen"
            >
                <span class="sr-only">Toggle menu</span>
                <span class="home-header__bars" :class="{ open: mobileOpen }"><span></span><span></span><span></span></span>
            </button>
        </div>

        <div v-show="mobileOpen" id="home-mobile-nav" class="home-header__mobile">
            <a href="#features" @click="mobileOpen = false">Features</a>
            <a href="#how-it-works" @click="mobileOpen = false">How It Works</a>
            <a href="/plans" @click="mobileOpen = false">Pricing</a>
            <a href="#for-restaurants" @click="mobileOpen = false">For Restaurants</a>
            <a href="#resources" @click="mobileOpen = false">Resources</a>
            <div class="home-header__mobile-actions">
                <a href="/login" class="btn btn--outline">Login</a>
                <a href="/register" class="btn btn--primary">Get Started</a>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref } from 'vue'

const mobileOpen = ref(false)
</script>

<style scoped>
.home-header { position: sticky; top: 0; z-index: 40; background: rgba(255, 255, 255, .92); backdrop-filter: blur(8px); border-bottom: 1px solid #f1e6d8; }
.home-header__inner { max-width: 1240px; margin: 0 auto; padding: 14px 22px; display: flex; align-items: center; gap: 28px; }
.home-header__brand { display: flex; align-items: center; gap: 10px; text-decoration: none; font-size: 22px; font-weight: 900; }
.home-header__mark { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: #fef1e6; font-size: 18px; }
.navy { color: #14213d; }
.orange { color: #f97316; }
.blue { color: #2563eb; }
.home-header__nav { display: flex; align-items: center; gap: 4px; flex: 1; }
.home-header__nav a { color: #405257; text-decoration: none; padding: 9px 14px; border-radius: 999px; font-weight: 700; font-size: 15px; }
.home-header__nav a:hover { color: #f97316; background: #fef1e6; }
.home-header__actions { display: flex; align-items: center; gap: 10px; }
.btn { border-radius: 999px; padding: 11px 20px; font-weight: 800; font-size: 14px; text-decoration: none; white-space: nowrap; }
.btn--outline { color: #f97316; border: 2px solid #f97316; background: #fff; }
.btn--outline:hover { background: #fef1e6; }
.btn--primary { background: #f97316; color: #fff; box-shadow: 0 10px 24px rgba(249, 115, 22, .3); }
.btn--primary:hover { background: #e85d04; }
.home-header__toggle { display: none; border: none; background: none; padding: 8px; cursor: pointer; }
.home-header__bars { display: flex; flex-direction: column; gap: 5px; width: 24px; }
.home-header__bars span { display: block; height: 2px; background: #14213d; border-radius: 2px; transition: transform .2s, opacity .2s; }
.home-header__bars.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.home-header__bars.open span:nth-child(2) { opacity: 0; }
.home-header__bars.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
.home-header__mobile { display: flex; flex-direction: column; gap: 2px; padding: 10px 22px 18px; border-top: 1px solid #f1e6d8; }
.home-header__mobile a { color: #14213d; text-decoration: none; font-weight: 700; padding: 10px 4px; }
.home-header__mobile-actions { display: flex; gap: 10px; margin-top: 12px; }
.home-header__mobile-actions .btn { flex: 1; text-align: center; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }

@media (max-width: 900px) {
    .home-header__nav, .home-header__actions { display: none; }
    .home-header__toggle { display: inline-flex; }
}
</style>
