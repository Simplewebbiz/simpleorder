import { defineStore } from 'pinia'
import axios from 'axios'

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        method: null,
        delivery_address: { address: null, city: null, state: null, zip: null },
        tip: null,
        coupon_code: null,
        totals: null,
        ui: {
            addressModalOpen: false,
            itemModalOpen: false,
            closedModalOpen: false,
            pendingItem: null,
        },
    }),

    getters: {
        itemCount: (state) => state.items.reduce((n, i) => n + parseInt(i.qty), 0),

        subtotal: (state) => {
            return state.items.reduce((total, item) => {
                return total + calcItemPrice(item) * item.qty
            }, 0)
        },
    },

    actions: {
        init(cartData) {
            this.items = cartData.items || []
            this.method = cartData.method
            this.delivery_address = cartData.delivery_address || { address: null, city: null, state: null, zip: null }
            this.tip = cartData.tip
            this.coupon_code = cartData.coupon_code
            this.totals = cartData.totals || null
        },

        async save(data = {}) {
            const response = await axios.post('/ordering/cart/save', {
                method: this.method,
                delivery_address: this.delivery_address,
                tip: this.tip,
                coupon_code: this.coupon_code,
                ...data,
            })
            this.init(response.data)
            return response.data
        },

        async addItem(item) {
            const response = await axios.post('/ordering/cart/item', item)
            this.init(response.data)
            return response.data
        },

        async removeItem(cartId) {
            const response = await axios.post('/ordering/cart/item/remove', { cart_id: cartId })
            this.init(response.data)
        },

        openAddItem(item, settings) {
            if (!isStoreOpen(settings)) {
                this.ui.closedModalOpen = true
                return
            }
            this.ui.pendingItem = { ...item, qty: 1, selections: [] }
            if (!this.method || (this.method === 'delivery' && !this.delivery_address?.address)) {
                this.ui.addressModalOpen = true
            } else {
                this.ui.itemModalOpen = true
            }
        },
    },
})

function calcItemPrice(item) {
    let price = parseFloat(item.price)
    let percent = 1.0
    if (item.selections) {
        for (const sel of item.selections) {
            for (const opt of sel.selections || []) {
                if (opt.price_type === 'percent') percent += parseFloat(opt.price) / 100
                else price += parseFloat(opt.price)
            }
        }
    }
    return Math.round(price * percent * 100) / 100
}

export function isStoreOpen(settings) {
    if (typeof settings?.ordering_status?.open === 'boolean') {
        return settings.ordering_status.open
    }

    if (!settings?.store_hours) return true
    const tz = settings.timezone || 'America/Chicago'
    const now = new Date(new Date().toLocaleString('en-US', { timeZone: tz }))
    const days = ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa']
    const dayKey = days[now.getDay()]
    const hours = settings.store_hours[dayKey]
    if (!hours || hours.closed) return false
    if (!hours.from || !hours.to) return false
    const from = parseTime(hours.from)
    const to = parseTime(hours.to)
    if (!from || !to) return false

    const nowMins = now.getHours() * 60 + now.getMinutes()
    const fromMins = from[0] * 60 + from[1]
    const toMins = to[0] * 60 + to[1]

    if (toMins < fromMins) return nowMins >= fromMins || nowMins <= toMins
    return nowMins >= fromMins && nowMins <= toMins
}

function parseTime(str) {
    const match = String(str || '').trim().toUpperCase().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/)
    if (!match) return null

    let h = parseInt(match[1])
    const m = parseInt(match[2])
    const ampm = match[3]
    if (h > 23 || m > 59) return null
    if (ampm === 'PM' && h !== 12) h += 12
    if (ampm === 'AM' && h === 12) h = 0
    return [h, m]
}
