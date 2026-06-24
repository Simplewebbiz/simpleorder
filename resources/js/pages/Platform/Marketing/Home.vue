<template>
    <MarketingShell :nav-pages="navPages">
        <section class="hero" :style="heroStyle">
            <div class="hero-overlay">
                <div class="hero-copy">
                    <p class="eyebrow">{{ page.eyebrow }}</p>
                    <h1>{{ page.title }}</h1>
                    <p class="lede">{{ page.summary }}</p>
                    <div class="hero-actions">
                        <a :href="ctaHref" class="primary">{{ page.cta_label || 'Start Your Restaurant Site' }}</a>
                        <a href="/plans" class="secondary">View Pricing</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="cms-content" v-if="page.content" v-html="page.content"></section>

        <section class="feature-band">
            <div class="feature-card"><span>01</span><strong>Restaurant websites</strong><p>Home, About, Pricing, Contact, menu, and order pages built for food businesses.</p></div>
            <div class="feature-card"><span>02</span><strong>Online ordering</strong><p>Customers order for pickup or delivery with a clean checkout flow.</p></div>
            <div class="feature-card"><span>03</span><strong>Owner dashboard</strong><p>Restaurants manage orders, reports, staff, pages, images, and Stripe.</p></div>
        </section>

        <section class="split-section">
            <div>
                <p class="eyebrow dark">Built for daily restaurant work</p>
                <h2>Menus, payments, customer updates, and easy editing in one place.</h2>
            </div>
            <div class="check-grid">
                <div>Menu item photos and categories</div>
                <div>Stripe payment setup per tenant</div>
                <div>Text and email order updates</div>
                <div>Reports for sales and order activity</div>
            </div>
        </section>

        <section class="plans-preview">
            <div class="section-head">
                <p class="eyebrow dark">Plans</p>
                <h2>Start small. Grow when you are ready.</h2>
            </div>
            <div class="plan-row">
                <article v-for="plan in plans" :key="plan.id" class="plan-card">
                    <h3>{{ plan.name }}</h3>
                    <p>{{ plan.description }}</p>
                    <div class="price">${{ (plan.price_monthly / 100).toFixed(0) }}<span>/mo</span></div>
                    <a class="plan-link" href="/register">Start with {{ plan.name }}</a>
                </article>
            </div>
        </section>
    </MarketingShell>
</template>

<script setup>
import { computed } from 'vue'
import MarketingShell from '../../../components/Platform/MarketingShell.vue'

const props = defineProps({ page: Object, navPages: Array, plans: Array })
const fallbackImage = 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=1800&q=80'
const heroImage = computed(() => props.page?.hero_image_url || fallbackImage)
const heroStyle = computed(() => ({ backgroundImage: `url(${heroImage.value})` }))
const ctaHref = computed(() => props.page?.cta_url || '/register')
</script>

<style scoped>
.hero { min-height: 680px; background-position: center; background-size: cover; position: relative; display: flex; align-items: stretch; }
.hero-overlay { width: 100%; min-height: 680px; display: flex; align-items: center; background: linear-gradient(90deg, rgba(14, 21, 20, .78) 0%, rgba(14, 21, 20, .52) 48%, rgba(14, 21, 20, .18) 100%); }
.hero-copy { max-width: 1180px; width: 100%; margin: 0 auto; padding: 78px 22px; color: #fff; }
.eyebrow { color: #99f6e4; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 14px; }
.eyebrow.dark { color: #0f766e; }
h1 { font-size: 68px; line-height: 1.01; font-weight: 900; max-width: 780px; text-shadow: 0 8px 30px rgba(0,0,0,.28); }
.lede { color: rgba(255,255,255,.9); font-size: 21px; line-height: 1.6; max-width: 680px; margin-top: 22px; text-shadow: 0 8px 24px rgba(0,0,0,.24); }
.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 32px; }
.primary, .secondary { border-radius: 8px; padding: 15px 22px; font-weight: 900; text-decoration: none; font-size: 15px; box-shadow: 0 16px 32px rgba(0,0,0,.22); }
.primary { background: #ff7a59; color: #fff; }
.primary:hover { background: #e85d3f; }
.secondary { color: #17272b; background: #fff; }
.secondary:hover { background: #edf7f4; color: #0f766e; }
.cms-content { max-width: 880px; margin: 58px auto; padding: 0 22px; color: #344448; font-size: 18px; line-height: 1.7; }
.cms-content :deep(h2) { color: #17272b; font-size: 32px; margin-bottom: 12px; }
.cms-content :deep(h3) { color: #17272b; font-size: 24px; margin: 20px 0 8px; }
.cms-content :deep(a) { color: #0f766e; font-weight: 900; }
.cms-content :deep(img) { max-width: 100%; border-radius: 8px; margin: 18px 0; }
.feature-band { max-width: 1180px; margin: 0 auto 70px; padding: 0 22px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.feature-card, .plan-card { background: #fff; border: 1px solid #f0e4d7; border-radius: 8px; padding: 22px; box-shadow: 0 12px 28px rgba(31,45,48,.06); }
.feature-card span { color: #ef6c3e; font-weight: 900; font-size: 12px; }
.feature-card strong { display: block; margin: 8px 0; font-size: 18px; }
.feature-card p, .plan-card p { color: #657477; line-height: 1.5; }
.split-section { background: #edf7f4; padding: 64px max(22px, calc((100vw - 1180px) / 2)); display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
h2 { font-size: 38px; line-height: 1.1; font-weight: 900; color: #17272b; }
.check-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.check-grid div { background: #fff; border-radius: 8px; padding: 18px; color: #243b3f; font-weight: 800; }
.plans-preview { max-width: 1180px; margin: 0 auto; padding: 70px 22px; }
.section-head { margin-bottom: 24px; }
.plan-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.plan-card h3 { font-size: 22px; margin-bottom: 8px; }
.price { color: #17272b; font-size: 34px; font-weight: 900; margin-top: 18px; }
.price span { color: #657477; font-size: 14px; }
.plan-link { display: inline-flex; margin-top: 16px; color: #0f766e; font-weight: 900; text-decoration: none; }
.plan-link:hover { color: #ff7a59; }
@media (max-width: 900px) {
    .hero, .hero-overlay { min-height: 560px; }
    h1 { font-size: 42px; }
    .lede { font-size: 18px; }
    .split-section { grid-template-columns: 1fr; }
    .feature-band, .plan-row { grid-template-columns: 1fr; }
    .check-grid { grid-template-columns: 1fr; }
}
</style>