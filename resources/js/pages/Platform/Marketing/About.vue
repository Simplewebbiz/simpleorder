<template>
    <MarketingShell>
        <section class="page-hero">
            <div>
                <p class="eyebrow">About SimpleOrder</p>
                <h1>We help restaurants look good online and handle orders with less stress.</h1>
                <p>SimpleOrder is built around a practical idea: every restaurant should have a polished website, an easy menu, and a clear way to manage orders without hiring a developer.</p>
            </div>
            <div class="photo"></div>
        </section>

        <section class="story-grid">
            <article><span>For owners</span><h2>Control your restaurant site</h2><p>Edit Home, About, and Contact pages, upload images, manage menu items, and connect Stripe from your dashboard.</p></article>
            <article><span>For staff</span><h2>Keep orders moving</h2><p>Staff can see incoming orders, update preparation status, and keep the customer informed.</p></article>
            <article><span>For guests</span><h2>A smooth ordering experience</h2><p>Customers browse photos, order quickly, and receive updates by email and text when configured.</p></article>
        </section>
    </MarketingShell>
</template>

<script setup>
import MarketingShell from '../../../components/Platform/MarketingShell.vue'
</script>

<style scoped>
.page-hero { max-width: 1180px; margin: 0 auto; padding: 58px 22px 70px; display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 42px; align-items: center; }
.eyebrow { color: #0f766e; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; }
h1 { font-size: 48px; line-height: 1.06; font-weight: 900; }
p { color: #405257; font-size: 18px; line-height: 1.65; margin-top: 18px; }
.photo { min-height: 380px; border-radius: 8px; background: url('https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=900&q=80') center/cover; box-shadow: 0 24px 54px rgba(31,45,48,.14); }
.story-grid { max-width: 1180px; margin: 0 auto; padding: 0 22px 76px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
article { background: #fff; border: 1px solid #f0e4d7; border-radius: 8px; padding: 24px; box-shadow: 0 12px 28px rgba(31,45,48,.06); }
article span { color: #ef6c3e; font-size: 12px; font-weight: 900; text-transform: uppercase; }
h2 { font-size: 22px; margin-top: 10px; }
article p { font-size: 15px; margin-top: 10px; }
@media (max-width: 900px) { .page-hero, .story-grid { grid-template-columns: 1fr; } h1 { font-size: 36px; } .photo { min-height: 280px; } }
</style>