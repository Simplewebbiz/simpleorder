<template>
    <Head>
        <title>SimpleOrder.biz &mdash; Simple Orders. Happy Customers. More Sales.</title>
        <meta name="description" :content="metaDescription">
    </Head>

    <div class="home-page">
        <HomeHeader />

        <HomeHero :hero-image-url="page?.hero_image_url" />

        <HomeFeatureHighlights />

        <section v-if="page?.content" class="home-cms-content" v-html="page.content"></section>

        <HomeHowItWorks />

        <HomeProductShowcase />

        <HomeFooter :nav-pages="navPages" />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import HomeHeader from '../../../components/Platform/HomeHeader.vue'
import HomeHero from '../../../components/Platform/HomeHero.vue'
import HomeFeatureHighlights from '../../../components/Platform/HomeFeatureHighlights.vue'
import HomeHowItWorks from '../../../components/Platform/HomeHowItWorks.vue'
import HomeProductShowcase from '../../../components/Platform/HomeProductShowcase.vue'
import HomeFooter from '../../../components/Platform/HomeFooter.vue'

const props = defineProps({ page: Object, navPages: Array, plans: Array })

const metaDescription = computed(() =>
    props.page?.summary || 'The all-in-one ordering, payment, and reporting system built for restaurants and bars.'
)
</script>

<style scoped>
.home-page { background: #fffaf3; overflow-x: clip; }
.home-cms-content { max-width: 880px; margin: 58px auto; padding: 0 22px; color: #344448; font-size: 18px; line-height: 1.7; }
.home-cms-content :deep(h2) { color: #14213d; font-size: 32px; margin-bottom: 12px; }
.home-cms-content :deep(h3) { color: #14213d; font-size: 24px; margin: 20px 0 8px; }
.home-cms-content :deep(a) { color: #2563eb; font-weight: 900; }
.home-cms-content :deep(img) { max-width: 100%; border-radius: 8px; margin: 18px 0; }
</style>
