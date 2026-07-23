<template>
    <section id="for-restaurants" class="product-showcase">
        <div class="product-showcase__inner">
            <div class="product-showcase__copy">
                <p class="eyebrow">Everything You Need to</p>
                <h2>Run Your <span class="green">Restaurant</span></h2>
                <p class="product-showcase__lede">
                    SimpleOrder.biz makes it easy to take orders, manage payments, track statuses, and get the
                    reports you need to keep your business running smoothly &mdash; all from one dashboard.
                </p>
                <ul class="product-showcase__checks">
                    <li>Menu items, categories, and photos</li>
                    <li>Real-time order status updates</li>
                    <li>Pickup and delivery, your way</li>
                    <li>Sales and order reports</li>
                </ul>
            </div>

            <div class="product-showcase__previews">
                <div class="preview-card preview-card--phone" aria-hidden="true">
                    <div class="phone-frame">
                        <div class="phone-notch"></div>
                        <p class="phone-title">Tony&rsquo;s Pizza</p>
                        <div class="phone-item" v-for="item in menuItems" :key="item.name">
                            <span class="phone-item__thumb" :style="{ background: item.color }"></span>
                            <span class="phone-item__name">{{ item.name }}</span>
                            <span class="phone-item__price">${{ item.price }}</span>
                            <span class="phone-item__add">+</span>
                        </div>
                    </div>
                </div>

                <div class="preview-card preview-card--dashboard" aria-hidden="true">
                    <p class="dashboard-title">Dashboard</p>
                    <div class="dashboard-stats">
                        <div v-for="stat in dashboardStats" :key="stat.label" class="dashboard-stat">
                            <span class="dashboard-stat__label">{{ stat.label }}</span>
                            <span class="dashboard-stat__value">{{ stat.value }}</span>
                        </div>
                    </div>
                    <p class="dashboard-subtitle">Recent Orders</p>
                    <div class="dashboard-orders">
                        <div v-for="order in recentOrders" :key="order.id" class="dashboard-order">
                            <span class="dashboard-order__id">{{ order.id }}</span>
                            <span class="dashboard-order__method">{{ order.method }}</span>
                            <span class="dashboard-order__total">${{ order.total }}</span>
                            <span class="dashboard-order__status" :style="{ background: order.color }">{{ order.status }}</span>
                        </div>
                    </div>
                </div>

                <div class="preview-card preview-card--methods" aria-hidden="true">
                    <p class="methods-title">Order Your Way</p>
                    <div class="methods-row">
                        <div class="methods-pill">
                            <span class="methods-icon" style="background:#16a34a">🥡</span>
                            Pickup
                        </div>
                        <div class="methods-pill">
                            <span class="methods-icon" style="background:#2563eb">🚗</span>
                            Delivery
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const menuItems = [
    { name: 'Pepperoni Pizza', price: '14.99', color: '#fde68a' },
    { name: 'Cheeseburger', price: '12.49', color: '#fca5a5' },
    { name: 'Buffalo Wings', price: '9.99', color: '#fdba74' },
]

const dashboardStats = [
    { label: "Today's Orders", value: '24' },
    { label: "Today's Revenue", value: '$486' },
    { label: 'Pending Orders', value: '5' },
    { label: "This Month's Revenue", value: '$9,240' },
]

const recentOrders = [
    { id: '#1045', method: 'Pickup', total: '23.50', status: 'Ready', color: '#f97316' },
    { id: '#1044', method: 'Delivery', total: '42.00', status: 'Received', color: '#2563eb' },
    { id: '#1043', method: 'Pickup', total: '18.75', status: 'Complete', color: '#16a34a' },
]
</script>

<style scoped>
.product-showcase { padding: 84px 22px; background: #fff; }
.product-showcase__inner { max-width: 1240px; margin: 0 auto; display: grid; grid-template-columns: .9fr 1.1fr; gap: 56px; align-items: center; }
.eyebrow { color: #9333ea; font-weight: 900; font-size: 13px; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 10px; }
h2 { font-size: 36px; line-height: 1.15; font-weight: 900; color: #14213d; }
.green { color: #16a34a; }
.product-showcase__lede { margin-top: 18px; font-size: 17px; line-height: 1.6; color: #4a5a63; }
.product-showcase__checks { list-style: none; margin: 26px 0 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.product-showcase__checks li { display: flex; align-items: center; gap: 10px; font-weight: 700; color: #14213d; font-size: 15px; }
.product-showcase__checks li::before { content: ''; width: 20px; height: 20px; border-radius: 50%; background: #16a34a; flex-shrink: 0; }

.product-showcase__previews { position: relative; display: grid; grid-template-columns: auto 1fr; grid-template-rows: auto auto; gap: 20px; }
.preview-card { background: #fff; border-radius: 18px; box-shadow: 0 20px 44px -18px rgba(20, 33, 61, .28); padding: 18px; }
.preview-card--phone { grid-row: 1 / 3; width: 220px; background: #14213d; padding: 14px; }
.phone-frame { background: #fff; border-radius: 14px; padding: 16px 14px; height: 100%; }
.phone-notch { width: 50px; height: 5px; border-radius: 4px; background: #e5e7eb; margin: 0 auto 12px; }
.phone-title { font-weight: 900; color: #14213d; margin-bottom: 12px; }
.phone-item { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-top: 1px solid #f1f1f1; }
.phone-item__thumb { width: 26px; height: 26px; border-radius: 8px; flex-shrink: 0; }
.phone-item__name { flex: 1; font-size: 12px; font-weight: 700; color: #14213d; }
.phone-item__price { font-size: 12px; color: #5c6b72; }
.phone-item__add { width: 20px; height: 20px; border-radius: 50%; background: #fef1e6; color: #f97316; font-weight: 900; display: flex; align-items: center; justify-content: center; font-size: 13px; }

.preview-card--dashboard { grid-column: 2; grid-row: 1; }
.dashboard-title { font-weight: 900; color: #14213d; font-size: 15px; margin-bottom: 12px; }
.dashboard-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.dashboard-stat { background: #f8fafc; border-radius: 10px; padding: 10px; }
.dashboard-stat__label { display: block; font-size: 10px; color: #5c6b72; font-weight: 700; }
.dashboard-stat__value { display: block; font-size: 16px; font-weight: 900; color: #14213d; margin-top: 3px; }
.dashboard-subtitle { font-weight: 800; color: #14213d; font-size: 12px; margin: 14px 0 8px; }
.dashboard-orders { display: flex; flex-direction: column; gap: 6px; }
.dashboard-order { display: grid; grid-template-columns: 44px 1fr 50px auto; align-items: center; gap: 8px; font-size: 11px; padding: 6px 0; border-top: 1px solid #f1f1f1; }
.dashboard-order__id { color: #5c6b72; }
.dashboard-order__method { color: #14213d; font-weight: 700; }
.dashboard-order__total { color: #14213d; font-weight: 700; }
.dashboard-order__status { color: #fff; font-weight: 800; border-radius: 999px; padding: 3px 8px; text-align: center; }

.preview-card--methods { grid-column: 2; grid-row: 2; }
.methods-title { font-weight: 800; color: #14213d; font-size: 13px; margin-bottom: 12px; }
.methods-row { display: flex; gap: 12px; }
.methods-pill { flex: 1; display: flex; align-items: center; gap: 8px; background: #f8fafc; border-radius: 999px; padding: 8px 14px; font-weight: 800; font-size: 13px; color: #14213d; }
.methods-icon { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; }

@media (max-width: 980px) {
    .product-showcase__inner { grid-template-columns: 1fr; }
    .product-showcase__previews { grid-template-columns: 1fr; }
    .preview-card--phone { grid-row: auto; width: 100%; max-width: 260px; margin: 0 auto; }
    .preview-card--dashboard { grid-column: 1; grid-row: auto; }
    .preview-card--methods { grid-column: 1; grid-row: auto; }
}
@media (max-width: 520px) {
    .product-showcase { padding: 56px 16px; }
    .dashboard-stats { grid-template-columns: repeat(2, 1fr); }
    .dashboard-order { grid-template-columns: 36px 1fr 44px auto; font-size: 10px; }
}
</style>
