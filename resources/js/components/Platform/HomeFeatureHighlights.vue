<template>
    <section id="features" class="home-features">
        <div class="home-features__inner">
            <div v-for="feature in features" :key="feature.title" class="home-features__card">
                <span class="home-features__icon" :style="{ background: feature.color }" aria-hidden="true">{{ feature.icon }}</span>
                <div>
                    <strong>{{ feature.title }}</strong>
                    <p>{{ feature.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const features = [
    {
        title: 'Online Ordering',
        description: 'Easy online ordering for pickup or delivery.',
        icon: '🛒',
        color: '#16a34a',
    },
    {
        title: 'Secure Payments',
        description: 'Accept payments securely and get paid fast.',
        icon: '💳',
        color: '#f97316',
    },
    {
        title: 'Order Management',
        description: 'Manage orders and update statuses in real time.',
        icon: '📋',
        color: '#2563eb',
    },
    {
        title: 'Reports & Insights',
        description: 'Powerful reports to help you track and grow sales.',
        icon: '📈',
        color: '#9333ea',
    },
    {
        title: 'Multi-Tenant',
        description: 'One platform for multiple restaurants or locations.',
        icon: '🏪',
        color: '#ec4899',
    },
]
</script>

<style scoped>
.home-features { padding: 0 22px; margin-top: -46px; position: relative; z-index: 2; }
.home-features__inner { max-width: 1240px; margin: 0 auto; background: #fff; border-radius: 22px; box-shadow: 0 20px 48px -18px rgba(20, 33, 61, .22); padding: 30px; display: grid; grid-template-columns: repeat(5, 1fr); gap: 22px; }
.home-features__card { display: flex; gap: 14px; align-items: flex-start; }
.home-features__icon { flex-shrink: 0; width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; }
.home-features__card strong { display: block; color: #14213d; font-size: 16px; margin-bottom: 4px; }
.home-features__card p { color: #5c6b72; font-size: 14px; line-height: 1.45; margin: 0; }

@media (max-width: 1080px) {
    .home-features__inner { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 700px) {
    .home-features { margin-top: 0; }
    .home-features__inner { grid-template-columns: 1fr; border-radius: 18px; padding: 22px; gap: 20px; }
}
</style>
