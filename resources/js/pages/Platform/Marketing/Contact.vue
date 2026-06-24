<template>
    <MarketingShell :nav-pages="navPages">
        <section class="contact-page">
            <div class="contact-copy">
                <p class="eyebrow">{{ page.eyebrow }}</p>
                <h1>{{ page.title }}</h1>
                <p>{{ page.summary }}</p>
                <div class="contact-card">
                    <strong>Email</strong>
                    <a href="mailto:hello@simpleweb.biz">hello@simpleweb.biz</a>
                </div>
            </div>

            <div class="contact-photo" :style="heroStyle"></div>

            <section class="cms-content" v-if="page.content" v-html="page.content"></section>

            <form class="contact-form" action="mailto:hello@simpleweb.biz" method="post" enctype="text/plain">
                <label>Name<input name="name" type="text" required></label>
                <label>Email<input name="email" type="email" required></label>
                <label>Restaurant Name<input name="restaurant" type="text"></label>
                <label>Message<textarea name="message" rows="6" required></textarea></label>
                <button type="submit">Send Message</button>
            </form>
        </section>
    </MarketingShell>
</template>

<script setup>
import { computed } from 'vue'
import MarketingShell from '../../../components/Platform/MarketingShell.vue'
const props = defineProps({ page: Object, navPages: Array })
const heroStyle = computed(() => props.page?.hero_image_url ? { backgroundImage: `url(${props.page.hero_image_url})` } : {})
</script>

<style scoped>
.contact-page { max-width: 1180px; margin: 0 auto; padding: 58px 22px 76px; display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 32px 42px; align-items: start; }
.eyebrow { color: #0f766e; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; }
h1 { font-size: 48px; line-height: 1.06; font-weight: 900; }
p { color: #405257; font-size: 18px; line-height: 1.65; margin-top: 16px; }
.contact-card, .contact-form { background: #fff; border: 1px solid #f0e4d7; border-radius: 8px; box-shadow: 0 12px 28px rgba(31,45,48,.06); }
.contact-card { display: inline-flex; flex-direction: column; gap: 6px; padding: 18px; margin-top: 26px; }
.contact-card a { color: #0f766e; font-weight: 900; text-decoration: none; }
.contact-photo { min-height: 260px; border-radius: 8px; background: linear-gradient(135deg, #fed7aa, #99f6e4); background-size: cover; background-position: center; box-shadow: 0 20px 44px rgba(31,45,48,.14); }
.cms-content { color: #344448; font-size: 17px; line-height: 1.75; }
.cms-content :deep(h2) { color: #17272b; font-size: 30px; margin-bottom: 10px; }
.contact-form { padding: 24px; display: grid; gap: 14px; grid-column: 2; grid-row: 2 / span 2; }
label { display: grid; gap: 6px; color: #344448; font-weight: 900; font-size: 13px; }
input, textarea { border: 1.5px solid #dbe4e8; border-radius: 8px; padding: 11px 12px; font: inherit; resize: vertical; }
input:focus, textarea:focus { outline: none; border-color: #ff7a59; }
button { background: #ff7a59; color: #fff; border: none; border-radius: 8px; padding: 13px; font-weight: 900; cursor: pointer; }
@media (max-width: 900px) { .contact-page { grid-template-columns: 1fr; } .contact-form { grid-column: auto; grid-row: auto; } h1 { font-size: 36px; } }
</style>