<template>
    <section class="home-hero">
        <div class="home-hero__inner">
            <div class="home-hero__copy">
                <h1>
                    Simple Orders.<br>
                    Happy Customers.<br>
                    <span class="orange">More Sales.</span>
                </h1>
                <p class="home-hero__lede">
                    The all-in-one ordering, payment, and reporting system built for restaurants and bars.
                </p>
                <div class="home-hero__actions">
                    <a href="/register" class="btn btn--primary btn--lg">Get Started Free</a>
                    <a href="#how-it-works" class="btn btn--secondary btn--lg">
                        See How It Works
                        <span class="play" aria-hidden="true">&#9654;</span>
                    </a>
                </div>
                <ul class="home-hero__trust">
                    <li>No Setup Fees</li>
                    <li>Cancel Anytime</li>
                    <li>Free Training</li>
                </ul>
            </div>

            <div class="home-hero__media">
                <img
                    :src="heroImage"
                    alt="A diverse group of friends laughing together while sharing pizza, burgers, fries, and drinks at a colorful, casual restaurant"
                    loading="eager"
                    width="1200"
                    height="800"
                >
                <div class="home-hero__badge">
                    <span class="home-hero__badge-icon" aria-hidden="true">📊</span>
                    Track Orders. Manage Statuses. Grow Your Business.
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ heroImageUrl: { type: String, default: '' } })

const fallbackImage = '/images/marketing/hero-friends.jpg'
const heroImage = computed(() => props.heroImageUrl || fallbackImage)
</script>

<style scoped>
.home-hero { position: relative; overflow: hidden; background: #fffaf3; padding: 56px 22px 84px; }
.home-hero__inner { max-width: 1240px; margin: 0 auto; display: grid; grid-template-columns: 1.05fr 1fr; gap: 48px; align-items: center; }
h1 { font-size: 56px; line-height: 1.06; font-weight: 900; color: #14213d; letter-spacing: -.01em; }
.orange { color: #f97316; }
.home-hero__lede { margin-top: 22px; font-size: 19px; line-height: 1.6; color: #4a5a63; max-width: 520px; }
.home-hero__actions { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 30px; }
.btn { border-radius: 999px; padding: 11px 20px; font-weight: 800; font-size: 14px; text-decoration: none; white-space: nowrap; display: inline-flex; align-items: center; gap: 10px; }
.btn--lg { padding: 16px 26px; font-size: 16px; }
.btn--primary { background: #f97316; color: #fff; box-shadow: 0 14px 28px rgba(249, 115, 22, .32); }
.btn--primary:hover { background: #e85d04; }
.btn--secondary { background: #fff; color: #14213d; border: 2px solid #e7e0d6; }
.btn--secondary:hover { border-color: #2563eb; color: #2563eb; }
.play { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #2563eb; color: #fff; font-size: 9px; }
.home-hero__trust { display: flex; gap: 22px; flex-wrap: wrap; list-style: none; margin: 26px 0 0; padding: 0; color: #35474d; font-weight: 700; font-size: 14px; }
.home-hero__trust li { display: flex; align-items: center; gap: 7px; }
.home-hero__trust li::before { content: ''; width: 18px; height: 18px; border-radius: 50%; background: #16a34a; flex-shrink: 0; }
.home-hero__media { position: relative; }
.home-hero__media img { width: 100%; height: auto; aspect-ratio: 3 / 2; object-fit: cover; border-radius: 22px; box-shadow: 0 30px 60px -20px rgba(20, 33, 61, .35); }
.home-hero__badge { position: absolute; right: -18px; bottom: -22px; max-width: 260px; background: #16a34a; color: #fff; font-weight: 800; font-size: 14px; line-height: 1.35; padding: 16px 18px; border-radius: 16px; box-shadow: 0 16px 30px rgba(22, 163, 74, .35); display: flex; align-items: flex-start; gap: 10px; }
.home-hero__badge-icon { font-size: 18px; }

@media (max-width: 980px) {
    .home-hero__inner { grid-template-columns: 1fr; }
    h1 { font-size: 42px; }
    .home-hero__media { order: -1; }
    .home-hero__badge { right: 12px; bottom: -18px; }
}
@media (max-width: 520px) {
    .home-hero { padding: 36px 16px 64px; }
    h1 { font-size: 34px; }
    .home-hero__lede { font-size: 17px; }
    .btn--lg { width: 100%; justify-content: center; }
    .home-hero__actions { flex-direction: column; }
    .home-hero__badge { position: static; margin-top: 16px; max-width: none; }
}
</style>
