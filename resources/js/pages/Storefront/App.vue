<template>
    <div id="app" :class="{ 'store-closed': !storeOpen }">
        <StorefrontHeader
            :settings="settings"
            :tenant="tenant"
            :menu="menu"
            :pages="publishedPages"
            @navigate="navigateTo"
        />

        <main class="main-content">
            <StorefrontHome
                v-if="currentView === 'home'"
                :menu="menu"
                :settings="settings"
                :page="homePage"
                @browse="navigateTo('category', $event)"
            />
            <StorefrontCategory
                v-else-if="currentView === 'category'"
                :category="activeCategory"
                :settings="settings"
                @back="navigateTo('home')"
            />
            <StorefrontPage
                v-else-if="currentView === 'page'"
                :page="activePage"
                :settings="settings"
            />
            <StorefrontCheckout
                v-else-if="currentView === 'checkout'"
                :settings="settings"
                @placed="(order) => { currentView = 'order-placed'; placedOrder = order }"
                @back="navigateTo('home')"
            />
            <StorefrontOrderPlaced
                v-else-if="currentView === 'order-placed'"
                :order="placedOrder"
                :settings="settings"
            />
        </main>

        <StorefrontFooter
            :settings="settings"
            :menu="menu"
            :pages="publishedPages"
            @navigate="navigateTo"
        />

        <AddressModal v-if="cart.ui.addressModalOpen" :settings="settings" @done="cart.ui.itemModalOpen = true" />
        <ItemModal v-if="cart.ui.itemModalOpen" :settings="settings" />
        <ClosedModal v-if="cart.ui.closedModalOpen" :settings="settings" />
    </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useCartStore } from '../../stores/cart'

import StorefrontHeader from '../../components/Storefront/Header.vue'
import StorefrontFooter from '../../components/Storefront/Footer.vue'
import StorefrontHome from '../../components/Storefront/Home.vue'
import StorefrontPage from '../../components/Storefront/Page.vue'
import StorefrontCategory from '../../components/Storefront/Category.vue'
import StorefrontCheckout from '../../components/Storefront/Checkout.vue'
import StorefrontOrderPlaced from '../../components/Storefront/OrderPlaced.vue'
import AddressModal from '../../components/Storefront/Modals/AddressModal.vue'
import ItemModal from '../../components/Storefront/Modals/ItemModal.vue'
import ClosedModal from '../../components/Storefront/Modals/ClosedModal.vue'

import { isStoreOpen } from '../../stores/cart'

const props = defineProps({
    menu: { type: Array, default: () => [] },
    pages: { type: Array, default: () => [] },
    cart: Object,
    settings: Object,
    tenant: Object,
    auth: Object,
})

const cart = useCartStore()
const currentView = ref('home')
const activeCategory = ref(null)
const activePage = ref(null)
const placedOrder = ref(null)
const storeOpen = ref(true)

const publishedPages = computed(() => props.pages || [])
const homePage = computed(() => publishedPages.value.find(page => page.slug === 'home') || null)

function navigateTo(view, payload = null, replace = false) {
    const setUrl = (url) => replace ? history.replaceState({}, '', url) : history.pushState({}, '', url)
    if (view === 'home' || view === 'menu') {
        currentView.value = 'home'
        activeCategory.value = null
        activePage.value = null
        setUrl(view === 'menu' ? '/menu' : '/')
        return
    }

    if (view === 'category') {
        currentView.value = 'category'
        activeCategory.value = payload
        activePage.value = null
        setUrl('/' + payload.slug)
        return
    }

    if (view === 'page') {
        currentView.value = 'page'
        activePage.value = payload
        activeCategory.value = null
        setUrl('/' + payload.slug)
        return
    }

    if (view === 'checkout') {
        currentView.value = 'checkout'
        setUrl('/checkout')
    }
}

function routeFromPath() {
    const path = window.location.pathname.replace(/^\//, '').replace(/\/$/, '')

    if (!path || path === 'home') return navigateTo('home', null, true)
    if (path === 'menu') return navigateTo('menu', null, true)
    if (path === 'checkout') return navigateTo('checkout', null, true)
    if (path.startsWith('thank-you/')) {
        currentView.value = 'order-placed'
        return
    }

    const page = publishedPages.value.find(candidate => candidate.slug === path)
    if (page) return navigateTo('page', page, true)

    const cat = props.menu.find(candidate => candidate.slug === path)
    if (cat) return navigateTo('category', cat, true)

    navigateTo('home', null, true)
}

onMounted(() => {
    cart.init(props.cart)
    storeOpen.value = isStoreOpen(props.settings)
    routeFromPath()
    window.addEventListener('popstate', routeFromPath)
})

onUnmounted(() => window.removeEventListener('popstate', routeFromPath))
</script>