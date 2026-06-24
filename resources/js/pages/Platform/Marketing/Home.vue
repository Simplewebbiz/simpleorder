<template>
    <MarketingShell :nav-pages="navPages">
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">{{ page.eyebrow }}</p>
                <h1>{{ page.title }}</h1>
                <p class="lede">{{ page.summary }}</p>
                <div class="hero-actions">
                    <Link :href="page.cta_url || route('register')" class="primary">{{ page.cta_label || 'Start Your Restaurant Site' }}</Link>
                    <Link :href="route('plans')" class="secondary">View Pricing</Link>
                </div>
            </div>
            <div class="hero-photo" :style="heroStyle" role="img" aria-label="People enjoying food at a restaurant"></div>
        </section>

        <section class="cms-content" v-if="page.content" v-html="page.content"></section>

        <section class="feature-band">
            <div class="feature-card"><span>01</span><strong>Restaurant websites</strong><p>Home, About, Pricing, Contact, menu, and order pages built for food businesses.</p></div>
            <div class="feature-card"><span>02</span><strong>Online ordering</strong><p>Customers order for pickup or delivery with a clean checkout flow.</p></div>
            <div class="feature-card"><span>03</span><strong>Owner dashboard</strong><p>Restaurants manage orders, reports, staff, pages, images, and Stripe.</p></div>
        </section>

        <section class="split-section">
            <div>
                <p class="eyebrow">Built for daily restaurant work</p>
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
                <p class="eyebrow">Plans</p>
                <h2>Start small. Grow when you are ready.</h2>
            </div>
            <div class="plan-row">
                <article v-for="plan in plans" :key="plan.id" class="plan-card">
                    <h3>{{ plan.name }}</h3>
                    <p>{{ plan.description }}</p>
                    <div class="price">${{ (plan.price_monthly / 100).toFixed(0) }}<span>/mo</span></div>
                </article>
            </div>
        </section>
    </MarketingShell>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import MarketingShell from '../../../components/Platform/MarketingShell.vue'

const props = defineProps({ page: Object, navPages: Array, plans: Array })
const heroStyle = computed(() => props.page?.hero_image_url ? { backgroundImage: `url(${props.page.hero_image_url})` } : {})
</script>

<style scoped>
.hero { max-width: 1180px; margin: 0 auto; padding: 46px 22px 70px; display: grid; grid-template-columns: minmax(0, 1fr) 430px; gap: 44px; align-items: center; }
.eyebrow { color: #0f766e; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; }
h1 { font-size: 58px; line-height: 1.02; font-weight: 900; max-width: 720px; }
.lede { color: #405257; font-size: 20px; line-height: 1.6; max-width: 650px; margin-top: 20px; }
.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 30px; }
.primary, .secondary { border-radius: 8px; padding: 14px 20px; font-weight: 900; text-decoration: none; }
.primary { background: #ff7a59; color: #fff; }
.secondary { color: #0f766e; background: #edf7f4; }
.hero-photo { min-height: 440px; border-radius: 8px; background: linear-gradient(135deg, #fed7aa, #99f6e4); background-position: center; background-size: cover; box-shadow: 0 28px 60px rgba(31,45,48,.16); }
.cms-content { max-width: 880px; margin: -22px auto 58px; padding: 0 22px; color: #344448; font-size: 18px; line-height: 1.7; }
.cms-content :deep(h2) { color: #17272b; font-size: 32px; margin-bottom: 12px; }
.cms-content :deep(h3) { color: #17272b; font-size: 24px; margin: 20px 0 8px; }
.cms-content :deep(a) { color: #0f766e; font-weight: 900; }
.cms-content :deep(img) { max-width: 100%; border-radius: 8px; margin: 18px 0; }
.feature-band { max-width: 1180px; margin: -24px auto 70px; padding: 0 22px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.feature-card, .plan-card { background: #fff; border: 1px solid #f0e4d7; border-radius: 8px; padding: 22px; box-shadow: 0 12px 28px rgba(31,45,48,.06); }
.feature-card span { color: #ef6c3e; font-weight: 900; font-size: 12px; }
.feature-card strong { display: block; margin: 8px 0; font-size: 18px; }
.feature-card p, .plan-card p { color: #657477; line-height: 1.5; }
.split-section { background: #edf7f4; padding: 64px max(22px, calc((100vw - 1180px) / 2)); display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
h2 { font-size: 38px; line-height: 1.1; font-weight: 900; }
.check-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.check-grid div { background: #fff; border-radius: 8px; padding: 18px; color: #243b3f; font-weight: 800; }
.plans-preview { max-width: 1180px; margin: 0 auto; padding: 70px 22px; }
.section-head { margin-bottom: 24px; }
.plan-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.plan-card h3 { font-size: 22px; margin-bottom: 8px; }
.price { color: #17272b; font-size: 34px; font-weight: 900; margin-top: 18px; }
.price span { color: #657477; font-size: 14px; }
@media (max-width: 900px) { .hero, .split-section { grid-template-columns: 1fr; } .feature-band, .plan-row { grid-template-columns: 1fr; } h1 { font-size: 40px; } .hero-photo { min-height: 300px; } .check-grid { grid-template-columns: 1fr; } }
</style>