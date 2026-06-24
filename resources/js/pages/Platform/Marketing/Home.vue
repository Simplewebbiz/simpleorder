<template>
    <MarketingShell>
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">Restaurant ordering made lighter</p>
                <h1>Beautiful food websites that take orders and keep guests updated.</h1>
                <p class="lede">SimpleOrder gives each restaurant its own homepage, menu, contact page, payments, order dashboard, and easy page editor.</p>
                <div class="hero-actions">
                    <Link :href="route('register')" class="primary">Start Your Restaurant Site</Link>
                    <Link :href="route('plans')" class="secondary">View Plans</Link>
                </div>
            </div>
            <div class="hero-photo" role="img" aria-label="Friends enjoying food at a restaurant"></div>
        </section>

        <section class="feature-band">
            <div class="feature-card"><span>01</span><strong>Tenant websites</strong><p>Every restaurant gets Home, About, Contact, and Menu pages.</p></div>
            <div class="feature-card"><span>02</span><strong>Online ordering</strong><p>Customers order for pickup or delivery with a clean cart flow.</p></div>
            <div class="feature-card"><span>03</span><strong>Owner dashboard</strong><p>Restaurants see orders, reports, staff, pages, images, and Stripe settings.</p></div>
        </section>

        <section class="split-section">
            <div>
                <p class="eyebrow">Built for daily restaurant work</p>
                <h2>Menus, payments, text alerts, and email updates in one place.</h2>
            </div>
            <div class="check-grid">
                <div>Menu item photos and categories</div>
                <div>Stripe payment setup per tenant</div>
                <div>Order status updates for customers</div>
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
import { Link } from '@inertiajs/vue3'
import MarketingShell from '../../../components/Platform/MarketingShell.vue'

defineProps({ plans: Array })
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
.hero-photo { min-height: 440px; border-radius: 8px; background: url('https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=900&q=80') center/cover; box-shadow: 0 28px 60px rgba(31,45,48,.16); }
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