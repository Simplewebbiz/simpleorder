<template>
    <MarketingShell>
        <section class="plans-page">
            <div class="intro">
                <p class="eyebrow">Plans</p>
                <h1>Choose the plan that fits your restaurant.</h1>
                <p>Each plan includes a tenant website, menu tools, ordering, admin dashboard, reports, page editor, and payment setup.</p>
            </div>

            <div class="plans-grid">
                <article v-for="plan in plans" :key="plan.id" class="plan-card" :class="{ featured: plan.slug === 'pro' }">
                    <div class="plan-name">{{ plan.name }}</div>
                    <p>{{ plan.description }}</p>
                    <div class="price">${{ (plan.price_monthly / 100).toFixed(0) }}<span>/month</span></div>
                    <ul>
                        <li>{{ plan.max_items ? plan.max_items + ' menu items' : 'Unlimited menu items' }}</li>
                        <li>{{ plan.max_categories ? plan.max_categories + ' categories' : 'Unlimited categories' }}</li>
                        <li>{{ plan.custom_domain ? 'Custom domain support' : 'SimpleOrder subdomain' }}</li>
                        <li v-if="plan.order_reports">Order and sales reports</li>
                    </ul>
                    <Link :href="route('register')" class="choose">Start with {{ plan.name }}</Link>
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
.plans-page { max-width: 1180px; margin: 0 auto; padding: 58px 22px 76px; }
.intro { max-width: 720px; margin-bottom: 30px; }
.eyebrow { color: #0f766e; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; }
h1 { font-size: 48px; line-height: 1.06; font-weight: 900; }
.intro p { color: #405257; font-size: 18px; line-height: 1.65; margin-top: 16px; }
.plans-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
.plan-card { background: #fff; border: 1px solid #f0e4d7; border-radius: 8px; padding: 26px; box-shadow: 0 12px 28px rgba(31,45,48,.06); }
.plan-card.featured { border-color: #ffb199; box-shadow: 0 20px 44px rgba(255,122,89,.14); }
.plan-name { font-size: 22px; font-weight: 900; }
.plan-card p { color: #657477; line-height: 1.55; margin-top: 10px; min-height: 48px; }
.price { font-size: 42px; font-weight: 900; margin: 22px 0; }
.price span { color: #657477; font-size: 14px; margin-left: 4px; }
ul { list-style: none; padding: 0; margin: 0 0 22px; display: grid; gap: 10px; color: #344448; font-weight: 700; }
li::before { content: '+'; color: #0f766e; margin-right: 8px; }
.choose { display: block; text-align: center; background: #ff7a59; color: #fff; border-radius: 8px; padding: 12px; font-weight: 900; text-decoration: none; }
@media (max-width: 900px) { .plans-grid { grid-template-columns: 1fr; } h1 { font-size: 36px; } }
</style>