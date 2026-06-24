<template>
    <header class="storefront-header" :class="{ scrolled }">
        <div class="container">
            <div class="header-inner">
                <a href="/" class="header-brand" @click.prevent="$emit('navigate', 'home')">
                    <img v-if="brand.logo" :src="brand.logo" :alt="brand.name + ' logo'" class="header-logo" />
                    <span v-else class="header-name">{{ brand.name }}</span>
                </a>

                <nav class="header-nav desktop-only">
                    <a href="/" class="nav-link" @click.prevent="$emit('navigate', 'home')">Home</a>
                    <a href="/menu" class="nav-link" @click.prevent="$emit('navigate', 'menu')">Menu</a>
                    <a
                        v-for="page in navPages"
                        :key="page.id"
                        :href="'/' + page.slug"
                        class="nav-link"
                        @click.prevent="$emit('navigate', 'page', page)"
                    >{{ page.menu_label || page.title }}</a>
                </nav>

                <div class="header-actions">
                    <button class="cart-btn" @click="$emit('navigate', 'checkout')">
                        <span class="cart-label">Order</span>
                        <span class="cart-count" v-if="cart.itemCount > 0">{{ cart.itemCount }}</span>
                    </button>

                    <button class="mobile-menu-btn mobile-only" @click="mobileOpen = !mobileOpen" aria-label="Open menu">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>

            <div class="contact-bar desktop-only" v-if="brand.phone || brand.address">
                <span v-if="brand.phone" class="contact-item"><a :href="'tel:' + brand.phone">{{ brand.phone }}</a></span>
                <span v-if="brand.address && brand.address.address" class="contact-item">
                    {{ brand.address.address }}, {{ brand.address.city }}, {{ brand.address.state }}
                </span>
                <span v-if="!isOpen" class="closed-badge">Currently Closed</span>
            </div>
        </div>

        <Transition name="slide-down">
            <div class="mobile-nav" v-if="mobileOpen">
                <a href="/" class="mobile-nav-link" @click.prevent="go('home')">Home</a>
                <a href="/menu" class="mobile-nav-link" @click.prevent="go('menu')">Menu</a>
                <a
                    v-for="page in navPages"
                    :key="'m-' + page.id"
                    :href="'/' + page.slug"
                    class="mobile-nav-link"
                    @click.prevent="go('page', page)"
                >{{ page.menu_label || page.title }}</a>
                <div class="mobile-contact" v-if="brand.phone"><a :href="'tel:' + brand.phone">{{ brand.phone }}</a></div>
            </div>
        </Transition>
    </header>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useCartStore, isStoreOpen } from '../../stores/cart'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    menu: { type: Array, default: () => [] },
    pages: { type: Array, default: () => [] },
    settings: Object,
    tenant: Object,
})

const emit = defineEmits(['navigate'])
const cart = useCartStore()
const page = usePage()
const brand = page.props.tenant_brand || {}
const mobileOpen = ref(false)
const scrolled = ref(false)
const isOpen = ref(isStoreOpen(props.settings))
const navPages = computed(() => (props.pages || []).filter(item => item.slug !== 'home'))

function go(view, payload = null) {
    mobileOpen.value = false
    emit('navigate', view, payload)
}

function onScroll() {
    scrolled.value = window.scrollY > 10
}

onMounted(() => window.addEventListener('scroll', onScroll))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.storefront-header { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,.96); backdrop-filter: blur(12px); border-bottom: 1px solid #e5edf0; transition: box-shadow .2s; }
.storefront-header.scrolled { box-shadow: 0 10px 24px rgba(31,45,48,.08); }
.container { max-width: 1180px; margin: 0 auto; padding: 0 20px; }
.header-inner { display: flex; align-items: center; gap: 22px; padding: 14px 0; }
.header-brand { display: flex; align-items: center; text-decoration: none; flex-shrink: 0; }
.header-logo { max-height: 46px; max-width: 180px; object-fit: contain; }
.header-name { color: #17272b; font-size: 21px; font-weight: 900; }
.header-nav { display: flex; gap: 4px; flex: 1; align-items: center; }
.nav-link { color: #405257; text-decoration: none; padding: 8px 12px; border-radius: 7px; font-size: 14px; font-weight: 800; transition: all .15s; }
.nav-link:hover { color: #0f766e; background: #edf7f4; }
.header-actions { display: flex; align-items: center; gap: 10px; margin-left: auto; }
.cart-btn { display: flex; align-items: center; gap: 8px; background: #ff7a59; color: #fff; border: none; padding: 10px 18px; border-radius: 7px; font-weight: 900; font-size: 14px; cursor: pointer; }
.cart-btn:hover { background: #e85d3f; }
.cart-count { background: #fff; color: #e85d3f; border-radius: 50%; min-width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900; padding: 0 5px; }
.mobile-menu-btn { flex-direction: column; gap: 5px; background: none; border: none; cursor: pointer; padding: 8px; }
.mobile-menu-btn span { display: block; width: 24px; height: 2px; background: #17272b; border-radius: 2px; }
.contact-bar { display: flex; gap: 20px; padding: 8px 0; border-top: 1px solid #edf2f4; }
.contact-item { font-size: 12px; color: #657477; }
.contact-item a { color: #657477; text-decoration: none; }
.closed-badge { background: #fff1f2; color: #be123c; font-size: 12px; padding: 2px 10px; border-radius: 4px; font-weight: 800; }
.mobile-nav { background: #fff; border-top: 1px solid #e5edf0; box-shadow: 0 18px 30px rgba(31,45,48,.08); }
.mobile-nav-link { display: block; color: #405257; padding: 14px 20px; text-decoration: none; border-bottom: 1px solid #edf2f4; font-size: 15px; font-weight: 800; }
.mobile-contact { padding: 14px 20px; color: #657477; font-size: 14px; }
.mobile-contact a { color: #657477; text-decoration: none; }
.desktop-only { display: flex; }
.mobile-only { display: none; }
@media (max-width: 768px) { .desktop-only { display: none !important; } .mobile-only { display: flex !important; } }
.slide-down-enter-active, .slide-down-leave-active { transition: max-height .3s ease; overflow: hidden; }
.slide-down-enter-from, .slide-down-leave-to { max-height: 0; }
.slide-down-enter-to, .slide-down-leave-from { max-height: 420px; }
</style>