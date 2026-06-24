<template>
    <footer class="storefront-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand">
                    <img v-if="brand.logo" :src="brand.logo" :alt="brand.name + ' logo'" class="footer-logo" />
                    <div v-else class="footer-name">{{ brand.name }}</div>
                    <p class="footer-note">Fresh ordering for pickup and delivery.</p>
                </div>

                <div class="footer-contact" v-if="brand.phone || brand.address || brand.email">
                    <div class="footer-section-title">Contact</div>
                    <div v-if="brand.address && brand.address.address" class="footer-detail">
                        {{ brand.address.address }}<br>
                        {{ brand.address.city }}, {{ brand.address.state }} {{ brand.address.zip }}
                    </div>
                    <div v-if="brand.phone" class="footer-detail"><a :href="'tel:' + brand.phone">{{ brand.phone }}</a></div>
                    <div v-if="brand.email" class="footer-detail"><a :href="'mailto:' + brand.email">{{ brand.email }}</a></div>
                </div>

                <div class="footer-hours" v-if="hasHours">
                    <div class="footer-section-title">Hours</div>
                    <div v-for="(day, key) in normalizedHours" :key="key" class="hours-row" :class="{ today: day.isToday }">
                        <span class="day-name">{{ day.label }}</span>
                        <span class="day-hours">{{ day.closed ? 'Closed' : day.from + ' - ' + day.to }}</span>
                    </div>
                </div>

                <div class="footer-menu">
                    <div class="footer-section-title">Website</div>
                    <a href="/" class="footer-link" @click.prevent="$emit('navigate', 'home')">Home</a>
                    <a href="/menu" class="footer-link" @click.prevent="$emit('navigate', 'menu')">Menu</a>
                    <a
                        v-for="page in navPages"
                        :key="page.id"
                        :href="'/' + page.slug"
                        class="footer-link"
                        @click.prevent="$emit('navigate', 'page', page)"
                    >{{ page.menu_label || page.title }}</a>
                </div>
            </div>

            <div class="footer-bottom">
                <span>Copyright {{ year }} {{ brand.name }}. All rights reserved.</span>
                <span class="powered">Powered by <a href="/" target="_blank">SimpleOrder</a></span>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    settings: Object,
    menu: { type: Array, default: () => [] },
    pages: { type: Array, default: () => [] },
})

defineEmits(['navigate'])

const page = usePage()
const brand = page.props.tenant_brand || {}
const year = new Date().getFullYear()
const navPages = computed(() => (props.pages || []).filter(item => item.slug !== 'home'))

const dayLabels = { mo: 'Monday', tu: 'Tuesday', we: 'Wednesday', th: 'Thursday', fr: 'Friday', sa: 'Saturday', su: 'Sunday' }
const todayKey = ['su','mo','tu','we','th','fr','sa'][new Date().getDay()]

const hasHours = computed(() => brand.hours && Object.keys(brand.hours).some(k => !brand.hours[k]?.closed && brand.hours[k]?.from))

const normalizedHours = computed(() => {
    if (!brand.hours) return {}
    const result = {}
    for (const [key, label] of Object.entries(dayLabels)) {
        const h = brand.hours[key] || {}
        result[key] = {
            label,
            isToday: key === todayKey,
            closed: h.closed || (!h.from && !h.to),
            from: h.from || '',
            to: h.to || '',
        }
    }
    return result
})
</script>

<style scoped>
.storefront-footer { background: #17272b; color: #d8e7e4; padding: 48px 0 0; }
.container { max-width: 1180px; margin: 0 auto; padding: 0 22px; }
.footer-inner { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 36px; padding-bottom: 38px; }
.footer-logo { max-height: 48px; max-width: 180px; object-fit: contain; margin-bottom: 12px; }
.footer-name { font-size: 21px; font-weight: 900; color: #fff; margin-bottom: 8px; }
.footer-note { color: #9ab0b5; font-size: 13px; line-height: 1.5; }
.footer-section-title { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; color: #7dd3c7; margin-bottom: 12px; }
.footer-detail { font-size: 13px; color: #b7c8cc; margin-bottom: 8px; line-height: 1.5; }
.footer-detail a, .footer-link { color: #b7c8cc; text-decoration: none; }
.footer-detail a:hover, .footer-link:hover { color: #fff; }
.hours-row { display: flex; justify-content: space-between; gap: 18px; font-size: 13px; margin-bottom: 6px; color: #b7c8cc; }
.hours-row.today { color: #fff; font-weight: 800; }
.day-name { min-width: 80px; }
.footer-link { display: block; font-size: 13px; margin-bottom: 8px; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,.09); padding: 20px 0; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #8ca2a7; flex-wrap: wrap; gap: 8px; }
.powered a { color: #8ca2a7; text-decoration: none; }
.powered a:hover { color: #fff; }
</style>