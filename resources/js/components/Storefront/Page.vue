<template>
    <section class="page-hero" :style="heroStyle">
        <div class="hero-inner">
            <p class="eyebrow">{{ settings.store_name }}</p>
            <h1>{{ page.title }}</h1>
            <p v-if="page.summary" class="summary">{{ page.summary }}</p>
        </div>
    </section>

    <section class="page-content">
        <div class="content-wrap" v-html="page.content"></div>
    </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    page: Object,
    settings: Object,
})

const heroStyle = computed(() => {
    if (props.page?.hero?.permalink) {
        return { backgroundImage: `linear-gradient(90deg, rgba(255, 247, 237, .94), rgba(255, 247, 237, .72)), url(${props.page.hero.permalink})` }
    }
    return {}
})
</script>

<style scoped>
.page-hero { min-height: 340px; background: linear-gradient(135deg, #fff7ed, #ccfbf1); background-size: cover; background-position: center; display: flex; align-items: center; }
.hero-inner { max-width: 980px; width: 100%; margin: 0 auto; padding: 56px 22px; }
.eyebrow { color: #0f766e; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; }
h1 { max-width: 720px; color: #17272b; font-size: 48px; line-height: 1.05; font-weight: 900; }
.summary { max-width: 650px; color: #42575c; font-size: 18px; line-height: 1.6; margin-top: 18px; }
.page-content { padding: 56px 22px; background: #fff; }
.content-wrap { max-width: 820px; margin: 0 auto; color: #243b3f; font-size: 17px; line-height: 1.75; }
.content-wrap :deep(h2) { color: #17272b; font-size: 30px; margin: 28px 0 12px; }
.content-wrap :deep(h3) { color: #17272b; font-size: 23px; margin: 24px 0 10px; }
.content-wrap :deep(p) { margin: 0 0 18px; }
.content-wrap :deep(a) { color: #0f766e; font-weight: 800; }
.content-wrap :deep(img) { max-width: 100%; border-radius: 8px; margin: 18px 0; box-shadow: 0 18px 34px rgba(31, 45, 48, .12); }
@media (max-width: 720px) { h1 { font-size: 34px; } .page-hero { min-height: 280px; } }
</style>