<template>
    <div class="storefront-home">
        <section class="hero" :class="{ 'has-image': hasHeroImage }" :style="heroStyle">
            <div class="hero-content">
                <div class="hero-copy">
                    <p class="eyebrow">Fresh online ordering</p>
                    <h1>{{ brand.name || settings.store_name || 'Order Online' }}</h1>
                    <p class="hero-sub" v-if="storeOpen">{{ page?.summary || 'Fresh food, easy ordering, pickup and delivery from our kitchen to your table.' }}</p>
                    <p class="hero-sub closed" v-else>We are currently closed. Check back during our regular hours.</p>
                    <div class="hero-actions">
                        <button v-if="storeOpen" class="hero-cta" @click="startOrder">Order Now</button>
                        <a href="/menu" class="hero-secondary" @click.prevent="startOrder">View Menu</a>
                    </div>
                </div>
                <div class="hero-card">
                    <div class="dish-card top-card">
                        <span>Pickup</span>
                        <strong v-if="settings.allow_pickup">Available today</strong>
                        <strong v-else>Unavailable</strong>
                    </div>
                    <div class="dish-card bottom-card">
                        <span>Delivery</span>
                        <strong v-if="settings.allow_delivery">Fresh to your door</strong>
                        <strong v-else>Pickup only</strong>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="page?.content" class="intro-section">
            <div class="content-wrap" v-html="page.content"></div>
        </section>

        <section class="categories-section">
            <div class="container">
                <div class="section-header">
                    <p class="eyebrow">Menu</p>
                    <h2>Browse our favorites</h2>
                </div>
                <div class="categories-grid">
                    <button v-for="cat in menu" :key="cat.id" class="category-card" @click="$emit('browse', cat)">
                        <div class="category-image" :style="cat.image ? { backgroundImage: 'url(' + cat.image.permalink + ')' } : {}">
                            <div class="category-image-fallback" v-if="!cat.image">{{ cat.name.charAt(0) }}</div>
                        </div>
                        <div class="category-info">
                            <h3>{{ cat.name }}</h3>
                            <p v-if="cat.description">{{ stripTags(cat.description) }}</p>
                            <span class="item-count">{{ cat.items ? cat.items.length : 0 }} items</span>
                        </div>
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { isStoreOpen } from '../../stores/cart'

const props = defineProps({
    menu: Array,
    settings: Object,
    page: Object,
})

const emit = defineEmits(['browse'])
const inertiaPage = usePage()
const brand = inertiaPage.props.tenant_brand || {}
const storeOpen = computed(() => isStoreOpen(props.settings))

const heroImage = computed(() => {
    if (props.settings?.hero_image_url) return props.settings.hero_image_url
    if (props.page?.hero?.permalink) return props.page.hero.permalink

    const firstCatWithImage = props.menu?.find(c => c.image)
    return firstCatWithImage?.image?.permalink || ''
})

const hasHeroImage = computed(() => Boolean(heroImage.value))

const heroStyle = computed(() => {
    if (heroImage.value) {
        return { backgroundImage: `linear-gradient(90deg, rgba(23,39,43,.72), rgba(23,39,43,.34)), url(${heroImage.value})` }
    }

    return {}
})

function startOrder() {
    if (props.menu?.length) emit('browse', props.menu[0])
}

function stripTags(html) {
    if (!html) return ''
    const text = html.replace(/<[^>]*>/g, '')
    return text.substring(0, 90) + (text.length > 90 ? '...' : '')
}
</script>

<style scoped>
.hero { min-height: 520px; background: linear-gradient(135deg, #fff7ed, #ccfbf1); background-size: cover; background-position: center; display: flex; align-items: center; }
.hero-content { max-width: 1180px; width: 100%; margin: 0 auto; padding: 70px 22px; display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 40px; align-items: center; }
.eyebrow { color: #0f766e; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; }
h1 { color: #17272b; font-size: 58px; line-height: 1.02; font-weight: 900; max-width: 720px; }
.hero-sub { color: #405257; font-size: 20px; line-height: 1.6; max-width: 650px; margin-top: 20px; }
.hero-sub.closed { color: #be123c; }
.hero.has-image .eyebrow,
.hero.has-image h1,
.hero.has-image .hero-sub { color: #fff; text-shadow: 0 2px 18px rgba(0,0,0,.28); }
.hero.has-image .hero-sub.closed { color: #fecdd3; }
.hero.has-image .hero-secondary { background: rgba(255,255,255,.94); }
.hero.has-image .hero-cta { box-shadow: 0 14px 28px rgba(0,0,0,.18); }
.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 30px; }
.hero-cta, .hero-secondary { border-radius: 8px; padding: 14px 22px; font-size: 15px; font-weight: 900; text-decoration: none; cursor: pointer; }
.hero-cta { background: #ff7a59; color: #fff; border: none; }
.hero-secondary { background: #fff; color: #0f766e; border: 1px solid #b7ded7; }
.hero-card { position: relative; min-height: 260px; }
.dish-card { position: absolute; width: 250px; min-height: 120px; background: rgba(255,255,255,.88); border: 1px solid rgba(255,255,255,.8); box-shadow: 0 24px 50px rgba(31,45,48,.16); border-radius: 8px; padding: 22px; display: flex; flex-direction: column; justify-content: end; }
.dish-card span { color: #ef6c3e; font-weight: 900; font-size: 13px; text-transform: uppercase; }
.dish-card strong { color: #17272b; font-size: 23px; line-height: 1.1; margin-top: 8px; }
.top-card { right: 70px; top: 5px; }
.bottom-card { right: 0; bottom: 0; background: rgba(236,253,245,.92); }
.intro-section { padding: 48px 22px 20px; background: #fff; }
.content-wrap { max-width: 820px; margin: 0 auto; color: #344448; font-size: 17px; line-height: 1.75; }
.content-wrap :deep(h2) { color: #17272b; font-size: 30px; margin-bottom: 12px; }
.content-wrap :deep(img) { max-width: 100%; border-radius: 8px; margin: 18px 0; }
.categories-section { padding: 56px 0 70px; background: #fbfdfc; }
.container { max-width: 1180px; margin: 0 auto; padding: 0 22px; }
.section-header { margin-bottom: 24px; }
.section-header h2 { color: #17272b; font-size: 32px; font-weight: 900; }
.categories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; }
.category-card { background: #fff; border: 1px solid #e5edf0; border-radius: 8px; overflow: hidden; text-align: left; cursor: pointer; transition: all .2s; padding: 0; box-shadow: 0 10px 24px rgba(31,45,48,.06); }
.category-card:hover { border-color: #ffb199; transform: translateY(-2px); box-shadow: 0 18px 34px rgba(31,45,48,.1); }
.category-image { height: 160px; background: linear-gradient(135deg, #fed7aa, #99f6e4); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; }
.category-image-fallback { font-size: 48px; font-weight: 900; color: rgba(255,255,255,.92); }
.category-info { padding: 16px; }
.category-info h3 { color: #17272b; font-size: 18px; font-weight: 900; margin-bottom: 6px; }
.category-info p { color: #657477; font-size: 13px; margin-bottom: 10px; line-height: 1.5; }
.item-count { color: #0f766e; font-size: 12px; font-weight: 900; }
@media (max-width: 860px) { .hero-content { grid-template-columns: 1fr; } .hero-card { display: none; } h1 { font-size: 40px; } }
</style>