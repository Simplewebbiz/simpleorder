<template>
    <div id="app" :class="{ 'store-closed': !storeOpen }">
        <StorefrontHeader :settings="settings" :tenant="tenant" />

        <main class="main-content">
            <router-view v-if="useRouter" />
            <template v-else>
                <!-- SPA-style navigation handled via Inertia partial reloads + Vue components -->
                <StorefrontHome
                    v-if="currentView === 'home'"
                    :menu="menu"
                    :settings="settings"
                    @browse="currentView = 'category'; activeCategory = $event"
                />
                <StorefrontCategory
                    v-else-if="currentView === 'category'"
                    :category="activeCategory"
                    :settings="settings"
                    @back="currentView = 'home'"
                />
                <StorefrontCheckout
                    v-else-if="currentView === 'checkout'"
                    :settings="settings"
                    @placed="(order) => { currentView = 'order-placed'; placedOrder = order }"
                    @back="currentView = 'home'"
                />
                <StorefrontOrderPlaced
                    v-else-if="currentView === 'order-placed'"
                    :order="placedOrder"
                    :settings="settings"
                />
            </template>
        </main>

        <StorefrontFooter :settings="settings" />

        <!-- Modals -->
        <AddressModal v-if="cart.ui.addressModalOpen" :settings="settings" @done="cart.ui.itemModalOpen = true" />
        <ItemModal v-if="cart.ui.itemModalOpen" :settings="settings" />
        <ClosedModal v-if="cart.ui.closedModalOpen" :settings="settings" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useCartStore } from '../../stores/cart'

import StorefrontHeader from '../../components/Storefront/Header.vue'
import StorefrontFooter from '../../components/Storefront/Footer.vue'
import StorefrontHome from '../../components/Storefront/Home.vue'
import StorefrontCategory from '../../components/Storefront/Category.vue'
import StorefrontCheckout from '../../components/Storefront/Checkout.vue'
import StorefrontOrderPlaced from '../../components/Storefront/OrderPlaced.vue'
import AddressModal from '../../components/Storefront/Modals/AddressModal.vue'
import ItemModal from '../../components/Storefront/Modals/ItemModal.vue'
import ClosedModal from '../../components/Storefront/Modals/ClosedModal.vue'

import { isStoreOpen } from '../../stores/cart'

const props = defineProps({
    menu: Array,
    cart: Object,
    settings: Object,
    tenant: Object,
    auth: Object,
})

const cart = useCartStore()
const currentView = ref('home')
const activeCategory = ref(null)
const placedOrder = ref(null)
const storeOpen = ref(true)
const useRouter = false

onMounted(() => {
    cart.init(props.cart)
    storeOpen.value = isStoreOpen(props.settings)

    // Handle direct URL paths for deep linking
    const path = window.location.pathname.replace(/^\//, '')
    if (path === 'checkout') currentView.value = 'checkout'
    else if (path.startsWith('thank-you/')) {
        currentView.value = 'order-placed'
    } else if (path) {
        const cat = props.menu.find(c => c.slug === path)
        if (cat) { currentView.value = 'category'; activeCategory.value = cat }
    }
})
</script>
