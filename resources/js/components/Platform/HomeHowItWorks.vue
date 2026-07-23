<template>
    <section id="how-it-works" class="how-it-works">
        <div class="how-it-works__inner">
            <div class="section-head">
                <p class="eyebrow">How It Works</p>
                <h2>Up and running in three simple steps.</h2>
            </div>

            <div class="how-it-works__row">
                <article v-for="step in steps" :key="step.title" class="how-it-works__step">
                    <span class="how-it-works__number" :style="{ background: step.color }">{{ step.number }}</span>
                    <strong>{{ step.title }}</strong>
                    <p>{{ step.description }}</p>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup>
const steps = [
    {
        number: '1',
        title: 'Sign Up Free',
        description: 'Create your restaurant account in minutes. No setup fees, no contracts.',
        color: '#16a34a',
    },
    {
        number: '2',
        title: 'Set Up Your Menu & Payments',
        description: 'Add your menu, categories, and photos, then connect Stripe to start accepting payments.',
        color: '#f97316',
    },
    {
        number: '3',
        title: 'Start Taking Orders',
        description: 'Customers order online for pickup or delivery while you manage everything from your dashboard.',
        color: '#2563eb',
    },
]
</script>

<style scoped>
.how-it-works { background: #f4f8fb; padding: 84px 22px; }
.how-it-works__inner { max-width: 1240px; margin: 0 auto; }
.section-head { text-align: center; max-width: 620px; margin: 0 auto 48px; }
.eyebrow { color: #f97316; font-weight: 900; font-size: 13px; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 10px; }
h2 { font-size: 34px; line-height: 1.15; font-weight: 900; color: #14213d; }
.how-it-works__row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; }
.how-it-works__step { background: #fff; border-radius: 18px; padding: 28px 24px; box-shadow: 0 14px 32px -16px rgba(20, 33, 61, .18); text-align: center; }
.how-it-works__number { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; color: #fff; font-weight: 900; font-size: 17px; margin-bottom: 16px; }
.how-it-works__step strong { display: block; font-size: 19px; color: #14213d; margin-bottom: 8px; }
.how-it-works__step p { color: #5c6b72; line-height: 1.55; margin: 0; font-size: 15px; }

@media (max-width: 860px) {
    .how-it-works { padding: 60px 18px; }
    .how-it-works__row { grid-template-columns: 1fr; }
    h2 { font-size: 28px; }
}
</style>
