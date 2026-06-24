<template>
    <MarketingShell :nav-pages="navPages">
        <section class="page-hero">
            <div>
                <p class="eyebrow">{{ page.eyebrow }}</p>
                <h1>{{ page.title }}</h1>
                <p>{{ page.summary }}</p>
            </div>
            <div class="photo" :style="heroStyle"></div>
        </section>

        <section class="cms-content" v-if="page.content" v-html="page.content"></section>

        <section class="story-grid">
            <article><span>For owners</span><h2>Control your restaurant site</h2><p>Edit public pages, upload images, manage menu items, and connect payments from your dashboard.</p></article>
            <article><span>For staff</span><h2>Keep orders moving</h2><p>Staff can see incoming orders, update preparation status, and keep customers informed.</p></article>
            <article><span>For guests</span><h2>A smooth ordering experience</h2><p>Customers browse photos, order quickly, and receive updates by email and text when configured.</p></article>
        </section>
    </MarketingShell>
</template>

<script setup>
import { computed } from 'vue'
import MarketingShell from '../../../components/Platform/MarketingShell.vue'
const props = defineProps({ page: Object, navPages: Array })
const heroStyle = computed(() => props.page?.hero_image_url ? { backgroundImage: `url(${props.page.hero_image_url})` } : {})
</script>

<style scoped>
.page-hero { max-width: 1180px; margin: 0 auto; padding: 58px 22px 54px; display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 42px; align-items: center; }
.eyebrow { color: #0f766e; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; }
h1 { font-size: 48px; line-height: 1.06; font-weight: 900; }
p { color: #405257; font-size: 18px; line-height: 1.65; margin-top: 18px; }
.photo { min-height: 380px; border-radius: 8px; background: linear-gradient(135deg, #fed7aa, #99f6e4); background-size: cover; background-position: center; box-shadow: 0 24px 54px rgba(31,45,48,.14); }
.cms-content { max-width: 880px; margin: 0 auto 46px; padding: 0 22px; color: #344448; font-size: 17px; line-height: 1.75; }
.cms-content :deep(h2) { color: #17272b; font-size: 30px; margin: 22px 0 10px; }
.cms-content :deep(h3) { color: #17272b; font-size: 23px; margin: 18px 0 8px; }
.cms-content :deep(a) { color: #0f766e; font-weight: 900; }
.cms-content :deep(img) { max-width: 100%; border-radius: 8px; margin: 18px 0; }
.story-grid { max-width: 1180px; margin: 0 auto; padding: 0 22px 76px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
article { background: #fff; border: 1px solid #f0e4d7; border-radius: 8px; padding: 24px; box-shadow: 0 12px 28px rgba(31,45,48,.06); }
article span { color: #ef6c3e; font-size: 12px; font-weight: 900; text-transform: uppercase; }
h2 { font-size: 22px; margin-top: 10px; }
article p { font-size: 15px; margin-top: 10px; }
@media (max-width: 900px) { .page-hero, .story-grid { grid-template-columns: 1fr; } h1 { font-size: 36px; } .photo { min-height: 280px; } }
</style>