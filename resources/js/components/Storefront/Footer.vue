<template>
    <footer class="storefront-footer">
        <div class="container">
            <div class="footer-inner">

                <!-- Brand column -->
                <div class="footer-brand">
                    <img
                        v-if="brand.logo"
                        :src="brand.logo"
                        :alt="brand.name + ' logo'"
                        class="footer-logo"
                    />
                    <div v-else class="footer-name">{{ brand.name }}</div>
                </div>

                <!-- Contact column -->
                <div class="footer-contact" v-if="brand.phone || brand.address || brand.email">
                    <div class="footer-section-title">Contact</div>
                    <div v-if="brand.address && brand.address.address" class="footer-detail">
                        📍 {{ brand.address.address }}<br>
                        {{ brand.address.city }}, {{ brand.address.state }} {{ brand.address.zip }}
                    </div>
                    <div v-if="brand.phone" class="footer-detail">
                        <a :href="'tel:' + brand.phone">📞 {{ brand.phone }}</a>
                    </div>
                    <div v-if="brand.email" class="footer-detail">
                        <a :href="'mailto:' + brand.email">✉️ {{ brand.email }}</a>
                    </div>
                </div>

                <!-- Hours column -->
                <div class="footer-hours" v-if="hasHours">
                    <div class="footer-section-title">Hours</div>
                    <div v-for="(day, key) in normalizedHours" :key="key" class="hours-row" :class="{ today: day.isToday }">
                        <span class="day-name">{{ day.label }}</span>
                        <span class="day-hours">{{ day.closed ? 'Closed' : day.from + ' – ' + day.to }}</span>
                    </div>
                </div>

                <!-- Menu links column -->
                <div class="footer-menu" v-if="menu && menu.length">
                    <div class="footer-section-title">Menu</div>
                    <a
                        v-for="cat in menu"
                        :key="cat.id"
                        href="#"
                        class="footer-link"
                        @click.prevent="$emit('navigate', 'category', cat)"
                    >{{ cat.name }}</a>
                </div>

            </div>

            <div class="footer-bottom">
                <span>© {{ year }} {{ brand.name }}. All rights reserved.</span>
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
})

defineEmits(['navigate'])

const page = usePage()
const brand = page.props.tenant_brand || {}
const year = new Date().getFullYear()

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
.storefront-footer { background: #111; color: #d1d5db; padding: 48px 0 0; margin-top: 64px; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.footer-inner { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 40px; padding-bottom: 40px; }
.footer-logo { max-height: 48px; max-width: 180px; object-fit: contain; margin-bottom: 12px; }
.footer-name { font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 8px; }
.footer-section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; margin-bottom: 12px; }
.footer-detail { font-size: 13px; color: #9ca3af; margin-bottom: 8px; line-height: 1.5; }
.footer-detail a { color: #9ca3af; text-decoration: none; }
.footer-detail a:hover { color: #fff; }
.hours-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; color: #9ca3af; }
.hours-row.today { color: #fff; font-weight: 600; }
.day-name { min-width: 80px; }
.footer-link { display: block; color: #9ca3af; text-decoration: none; font-size: 13px; margin-bottom: 8px; }
.footer-link:hover { color: #fff; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); padding: 20px 0; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #6b7280; flex-wrap: wrap; gap: 8px; }
.powered a { color: #6b7280; text-decoration: none; }
.powered a:hover { color: #9ca3af; }
</style>
