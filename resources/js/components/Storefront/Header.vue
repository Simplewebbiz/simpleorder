<template>
    <header class="storefront-header" :class="{ 'scrolled': scrolled }">
        <div class="container">
            <div class="header-inner">

                <!-- Logo / Store Name -->
                <a href="/" class="header-brand" @click.prevent="$emit('navigate', 'home')">
                    <img
                        v-if="brand.logo"
                        :src="brand.logo"
                        :alt="brand.name + ' logo'"
                        class="header-logo"
                    />
                    <span v-else class="header-name">{{ brand.name }}</span>
                </a>

                <!-- Desktop nav -->
                <nav class="header-nav desktop-only">
                    <a
                        v-for="cat in menu"
                        :key="cat.id"
                        href="#"
                        class="nav-link"
                        @click.prevent="$emit('navigate', 'category', cat)"
                    >{{ cat.name }}</a>
                </nav>

                <!-- Right side -->
                <div class="header-actions">
                    <!-- Cart button -->
                    <button class="cart-btn" @click="$emit('navigate', 'checkout')">
                        <span class="cart-icon">🛒</span>
                        <span class="cart-count" v-if="cart.itemCount > 0">{{ cart.itemCount }}</span>
                        <span class="cart-label desktop-only">Order</span>
                    </button>

                    <!-- Mobile hamburger -->
                    <button class="mobile-menu-btn mobile-only" @click="mobileOpen = !mobileOpen">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>

            <!-- Contact bar (desktop) -->
            <div class="contact-bar desktop-only" v-if="brand.phone || brand.address">
                <span v-if="brand.phone" class="contact-item">
                    <a :href="'tel:' + brand.phone">📞 {{ brand.phone }}</a>
                </span>
                <span v-if="brand.address && brand.address.address" class="contact-item">
                    📍 {{ brand.address.address }}, {{ brand.address.city }}, {{ brand.address.state }}
                </span>
                <span v-if="!isOpen" class="closed-badge">Currently Closed</span>
            </div>
        </div>

        <!-- Mobile nav drawer -->
        <Transition name="slide-down">
            <div class="mobile-nav" v-if="mobileOpen">
                <a
                    v-for="cat in menu"
                    :key="'m-' + cat.id"
                    href="#"
                    class="mobile-nav-link"
                    @click.prevent="mobileOpen = false; $emit('navigate', 'category', cat)"
                >{{ cat.name }}</a>
                <div class="mobile-contact" v-if="brand.phone">
                    <a :href="'tel:' + brand.phone">📞 {{ brand.phone }}</a>
                </div>
            </div>
        </Transition>
    </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useCartStore, isStoreOpen } from '../../stores/cart'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    menu: { type: Array, default: () => [] },
    settings: Object,
})

defineEmits(['navigate'])

const cart = useCartStore()
const page = usePage()
const brand = page.props.tenant_brand || {}
const mobileOpen = ref(false)
const scrolled = ref(false)
const isOpen = ref(isStoreOpen(props.settings))

function onScroll() {
    scrolled.value = window.scrollY > 10
}

onMounted(() => window.addEventListener('scroll', onScroll))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.storefront-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #1a1a1a;
    transition: box-shadow 0.2s;
}
.storefront-header.scrolled { box-shadow: 0 2px 16px rgba(0,0,0,.3); }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.header-inner { display: flex; align-items: center; gap: 20px; padding: 14px 0; }
.header-brand { display: flex; align-items: center; text-decoration: none; flex-shrink: 0; }
.header-logo { max-height: 48px; max-width: 180px; object-fit: contain; }
.header-name { color: #fff; font-size: 20px; font-weight: 800; letter-spacing: -0.02em; }
.header-nav { display: flex; gap: 4px; flex: 1; }
.nav-link { color: #d1d5db; text-decoration: none; padding: 8px 14px; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.15s; }
.nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
.header-actions { display: flex; align-items: center; gap: 10px; margin-left: auto; }
.cart-btn { display: flex; align-items: center; gap: 8px; background: #e85d04; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; position: relative; transition: background 0.15s; }
.cart-btn:hover { background: #c44d03; }
.cart-count { background: #fff; color: #e85d04; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; }
.mobile-menu-btn { display: flex; flex-direction: column; gap: 5px; background: none; border: none; cursor: pointer; padding: 8px; }
.mobile-menu-btn span { display: block; width: 24px; height: 2px; background: #fff; border-radius: 2px; }
.contact-bar { display: flex; gap: 20px; padding: 8px 0; border-top: 1px solid rgba(255,255,255,0.08); }
.contact-item { font-size: 12px; color: #9ca3af; }
.contact-item a { color: #9ca3af; text-decoration: none; }
.contact-item a:hover { color: #fff; }
.closed-badge { background: #7f1d1d; color: #fca5a5; font-size: 12px; padding: 2px 10px; border-radius: 4px; font-weight: 600; }
.mobile-nav { background: #111; border-top: 1px solid rgba(255,255,255,0.1); }
.mobile-nav-link { display: block; color: #d1d5db; padding: 14px 20px; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 15px; }
.mobile-nav-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
.mobile-contact { padding: 14px 20px; color: #9ca3af; font-size: 14px; }
.mobile-contact a { color: #9ca3af; text-decoration: none; }
.desktop-only { display: flex; }
.mobile-only { display: none; }
@media (max-width: 768px) {
    .desktop-only { display: none !important; }
    .mobile-only { display: flex !important; }
}
.slide-down-enter-active, .slide-down-leave-active { transition: max-height 0.3s ease; overflow: hidden; }
.slide-down-enter-from, .slide-down-leave-to { max-height: 0; }
.slide-down-enter-to, .slide-down-leave-from { max-height: 400px; }
</style>
