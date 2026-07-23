<template>
    <footer id="resources" class="home-footer">
        <div class="home-footer__inner">
            <div class="home-footer__brand">
                <a href="/" class="home-footer__logo">
                    <span aria-hidden="true">🍴</span> SimpleOrder<span class="orange">.biz</span>
                </a>
                <p>The all-in-one ordering, payment, and reporting system built for restaurants and bars.</p>
            </div>

            <div class="home-footer__col">
                <strong>Product</strong>
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="/plans">Pricing</a>
            </div>

            <div class="home-footer__col">
                <strong>For Restaurants</strong>
                <a href="#for-restaurants">Why SimpleOrder</a>
                <a href="/register">Get Started Free</a>
                <a href="/login">Restaurant Login</a>
            </div>

            <div class="home-footer__col">
                <strong>Resources</strong>
                <a href="/about">About</a>
                <a href="/contact">Contact</a>
                <a v-for="item in navPages" :key="item.slug" :href="hrefFor(item.slug)">{{ item.nav_label || item.title }}</a>
            </div>
        </div>

        <div class="home-footer__bottom">
            <span>&copy; {{ year }} SimpleOrder.biz. All rights reserved.</span>
        </div>
    </footer>
</template>

<script setup>
defineProps({
    navPages: { type: Array, default: () => [] },
})

const year = new Date().getFullYear()

function hrefFor(slug) {
    if (slug === 'home') return '/'
    if (slug === 'plans') return '/plans'
    if (slug === 'about') return '/about'
    if (slug === 'contact') return '/contact'
    return '/' + slug
}
</script>

<style scoped>
.home-footer { background: #14213d; color: #cbd5e1; padding: 60px 22px 24px; }
.home-footer__inner { max-width: 1240px; margin: 0 auto; display: grid; grid-template-columns: 1.4fr 1fr 1fr 1fr; gap: 32px; }
.home-footer__logo { display: flex; align-items: center; gap: 8px; color: #fff; font-size: 20px; font-weight: 900; text-decoration: none; }
.orange { color: #f97316; }
.home-footer__brand p { margin-top: 14px; line-height: 1.6; max-width: 320px; color: #94a3b8; }
.home-footer__col { display: flex; flex-direction: column; gap: 10px; }
.home-footer__col strong { color: #fff; font-size: 14px; margin-bottom: 4px; }
.home-footer__col a { color: #94a3b8; text-decoration: none; font-size: 14px; }
.home-footer__col a:hover { color: #f97316; }
.home-footer__bottom { max-width: 1240px; margin: 40px auto 0; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.1); font-size: 13px; color: #64748b; }

@media (max-width: 760px) {
    .home-footer__inner { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
    .home-footer__inner { grid-template-columns: 1fr; }
}
</style>
