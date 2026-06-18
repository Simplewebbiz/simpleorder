<template>
    <div class="storefront-home">
        <!-- Hero -->
        <section class="hero" :style="heroStyle">
            <div class="hero-overlay">
                <div class="hero-content">
                    <div class="hero-logo-wrap" v-if="brand.logo">
                        <img :src="brand.logo" :alt="brand.name" class="hero-logo" />
                    </div>
                    <h1 class="hero-title">{{ brand.name || 'Order Online' }}</h1>
                    <p class="hero-sub" v-if="storeOpen">Fresh &amp; made to order — pickup or delivery</p>
                    <p class="hero-sub closed" v-else>We're currently closed. Check back during our hours!</p>
                    <button v-if="storeOpen" class="hero-cta" @click="startOrder">
                        Order Now
                    </button>
                </div>
            </div>
        </section>

        <!-- Status bar -->
        <div class="status-bar" v-if="storeOpen && (settings.allow_pickup || settings.allow_delivery)">
            <div class="status-pill pickup" v-if="settings.allow_pickup">🏪 Pickup Available</div>
            <div class="status-pill delivery" v-if="settings.allow_delivery">🚗 Delivery Available</div>
        </div>

        <!-- Category grid -->
        <section class="categories-section">
            <div class="container">
                <h2 class="section-title">Browse Our Menu</h2>
                <div class="categories-grid">
                    <button
                        v-for="cat in menu"
                        :key="cat.id"
                        class="category-card"
                        @click="$emit('browse', cat)"
                    >
                        <div
                            class="category-image"
                            :style="cat.image ? { backgroundImage: 'url(' + cat.image.url + ')' } : {}"
                        >
                            <div class="category-image-fallback" v-if="!cat.image">
                                {{ cat.name.charAt(0) }}
                            </div>
                        </div>
                        <div class="category-info">
                            <h3>{{ cat.name }}</h3>
                            <p v-if="cat.description" v-html="stripTags(cat.description)"></p>
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
import { useCartStore, isStoreOpen } from '../../stores/cart'

const props = defineProps({
    menu: Array,
    settings: Object,
})

const emit = defineEmits(['browse'])
const cart = useCartStore()
const page = usePage()
const brand = page.props.tenant_brand || {}
const storeOpen = computed(() => isStoreOpen(props.settings))

const heroStyle = computed(() => {
    const firstCatWithImage = props.menu?.find(c => c.image)
    if (firstCatWithImage?.image?.url) {
        return { backgroundImage: `url(${firstCatWithImage.image.url})` }
    }
    return {}
})

function startOrder() {
    if (props.menu?.length) {
        emit('browse', props.menu[0])
    }
}

function stripTags(html) {
    if (!html) return ''
    return html.replace(/<[^>]*>/g, '').substring(0, 80) + (html.length > 80 ? '…' : '')
}
</script>

<style scoped>
.hero { min-height: 420px; background: #1a1a1a center/cover no-repeat; position: relative; display: flex; align-items: center; }
.hero-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,.55), rgba(0,0,0,.75)); display: flex; align-items: center; justify-content: center; }
.hero-content { text-align: center; color: #fff; padding: 40px 20px; }
.hero-logo { max-height: 80px; max-width: 280px; object-fit: contain; margin-bottom: 16px; }
.hero-title { font-size: clamp(28px, 5vw, 52px); font-weight: 800; margin-bottom: 10px; letter-spacing: -0.02em; }
.hero-sub { font-size: 16px; color: rgba(255,255,255,.75); margin-bottom: 28px; }
.hero-sub.closed { color: #fca5a5; }
.hero-cta { background: #e85d04; color: #fff; border: none; padding: 16px 40px; font-size: 17px; font-weight: 700; border-radius: 8px; cursor: pointer; transition: background 0.15s; }
.hero-cta:hover { background: #c44d03; }
.status-bar { display: flex; justify-content: center; gap: 12px; padding: 12px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap; }
.status-pill { background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; padding: 6px 16px; font-size: 13px; font-weight: 600; color: #374151; }
.status-pill.pickup { border-color: #22c55e; color: #15803d; }
.status-pill.delivery { border-color: #3b82f6; color: #1d4ed8; }
.categories-section { padding: 48px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.section-title { font-size: 24px; font-weight: 800; margin-bottom: 28px; color: #1a1a1a; }
.categories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
.category-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; text-align: left; cursor: pointer; transition: all 0.2s; padding: 0; }
.category-card:hover { border-color: #e85d04; box-shadow: 0 4px 20px rgba(232,93,4,.15); transform: translateY(-2px); }
.category-image { height: 160px; background: #f3f4f6 center/cover no-repeat; display: flex; align-items: center; justify-content: center; }
.category-image-fallback { font-size: 48px; font-weight: 800; color: #d1d5db; }
.category-info { padding: 16px; }
.category-info h3 { font-size: 17px; font-weight: 700; margin-bottom: 6px; }
.category-info p { font-size: 13px; color: #6b7280; margin-bottom: 8px; line-height: 1.4; }
.item-count { font-size: 12px; color: #e85d04; font-weight: 600; }
</style>
