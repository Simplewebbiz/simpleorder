<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div class="modal-backdrop" v-if="open" @click.self="close">
                <div class="modal-box">
                    <button class="modal-close" @click="close">✕</button>
                    <div class="modal-body">
                        <div class="closed-icon">🕐</div>
                        <h2 class="closed-title">We're Currently Closed</h2>
                        <p class="closed-msg">Thank you for your interest! We're not accepting orders right now. Please check back during our operating hours.</p>

                        <div class="hours-block" v-if="hasHours">
                            <div class="hours-title">Our Hours</div>
                            <div v-for="(day, key) in normalizedHours" :key="key" class="hours-row" :class="{ today: day.isToday }">
                                <span class="day-name">{{ day.label }}</span>
                                <span class="day-hours">{{ day.closed ? 'Closed' : day.from + ' – ' + day.to }}</span>
                            </div>
                        </div>

                        <div class="contact-row" v-if="brand.phone || brand.email">
                            <div class="contact-label">Questions? Contact us:</div>
                            <a v-if="brand.phone" :href="'tel:' + brand.phone" class="contact-link">📞 {{ brand.phone }}</a>
                            <a v-if="brand.email" :href="'mailto:' + brand.email" class="contact-link">✉️ {{ brand.email }}</a>
                        </div>

                        <button class="close-btn" @click="close">Got it</button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    open: Boolean,
    settings: Object,
})

const emit = defineEmits(['close'])
const page = usePage()
const brand = page.props.tenant_brand || {}

const dayLabels = { mo: 'Monday', tu: 'Tuesday', we: 'Wednesday', th: 'Thursday', fr: 'Friday', sa: 'Saturday', su: 'Sunday' }
const todayKey = ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'][new Date().getDay()]

const hasHours = computed(() => brand.hours && Object.keys(brand.hours).some(k => !brand.hours[k]?.closed))

const normalizedHours = computed(() => {
    if (!brand.hours) return {}
    const result = {}
    for (const [key, label] of Object.entries(dayLabels)) {
        const h = brand.hours[key] || {}
        result[key] = { label, isToday: key === todayKey, closed: h.closed || (!h.from && !h.to), from: h.from || '', to: h.to || '' }
    }
    return result
})

function close() { emit('close') }
</script>

<style scoped>
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-box { background: #fff; border-radius: 16px; width: 100%; max-width: 420px; position: relative; }
.modal-close { position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,.06); border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 14px; cursor: pointer; color: #374151; }
.modal-body { padding: 32px; text-align: center; }
.closed-icon { font-size: 60px; margin-bottom: 16px; }
.closed-title { font-size: 22px; font-weight: 800; margin-bottom: 10px; }
.closed-msg { font-size: 15px; color: #6b7280; line-height: 1.6; margin-bottom: 24px; }
.hours-block { background: #f9fafb; border-radius: 10px; padding: 16px; margin-bottom: 20px; text-align: left; }
.hours-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 10px; }
.hours-row { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; color: #6b7280; }
.hours-row.today { color: #1a1a1a; font-weight: 700; }
.day-name { min-width: 90px; }
.contact-row { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
.contact-label { font-size: 12px; color: #9ca3af; font-weight: 600; }
.contact-link { color: #e85d04; text-decoration: none; font-size: 14px; font-weight: 600; }
.close-btn { background: #1a1a1a; color: #fff; border: none; padding: 12px 36px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; }
.close-btn:hover { background: #374151; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
