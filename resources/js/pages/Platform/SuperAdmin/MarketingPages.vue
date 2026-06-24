<template>
    <div class="sa-wrap">
        <header class="sa-nav">
            <div class="sa-brand">
                <div class="brand-logo">SO</div>
                <span class="brand-name">SimpleOrder</span>
                <span class="sa-badge">Super Admin</span>
            </div>
            <nav class="sa-links">
                <Link :href="route('platform.superadmin.index')" class="sa-link">Dashboard</Link>
                <Link :href="route('platform.superadmin.tenants.index')" class="sa-link">Tenants</Link>
                <Link :href="route('platform.superadmin.plans.index')" class="sa-link">Plans</Link>
                <Link :href="route('platform.superadmin.marketing-pages.index')" class="sa-link active">Website CMS</Link>
                <Link :href="route('platform.superadmin.settings')" class="sa-link">Settings</Link>
            </nav>
            <Link :href="route('platform.logout')" method="post" as="button" class="sa-logout">Logout</Link>
        </header>

        <main class="sa-main">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Website CMS</h1>
                    <p class="page-subtitle">Edit the public simpleorder.biz pages for restaurant owners.</p>
                </div>
                <a href="/" target="_blank" class="btn-preview">Preview Website</a>
            </div>

            <div v-if="flash.success" class="alert-success">{{ flash.success }}</div>

            <div class="cms-grid">
                <aside class="page-list">
                    <button
                        v-for="pageItem in pages"
                        :key="pageItem.id"
                        class="page-tab"
                        :class="{ active: selected?.id === pageItem.id }"
                        @click="select(pageItem)"
                    >
                        <span>{{ pageItem.nav_label || pageItem.title }}</span>
                        <small>/{{ pageItem.slug }}</small>
                    </button>
                </aside>

                <section v-if="selected" class="editor-card">
                    <div class="image-preview" :style="form.hero_image_url ? { backgroundImage: 'url(' + form.hero_image_url + ')' } : {}">
                        <span v-if="!form.hero_image_url">Hero image preview</span>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="form-row">
                            <div class="field">
                                <label>Page Title</label>
                                <input v-model="form.title" required />
                            </div>
                            <div class="field">
                                <label>Navigation Label</label>
                                <input v-model="form.nav_label" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label>URL Slug</label>
                                <input v-model="form.slug" required />
                            </div>
                            <div class="field short-field">
                                <label>Sort</label>
                                <input v-model.number="form.sort" type="number" min="0" />
                            </div>
                        </div>

                        <div class="field">
                            <label>Small Eyebrow Text</label>
                            <input v-model="form.eyebrow" placeholder="Restaurant ordering made lighter" />
                        </div>

                        <div class="field">
                            <label>Hero Image URL</label>
                            <input v-model="form.hero_image_url" placeholder="https://... food or people image" />
                            <small>Use a bright food or restaurant people image. Unsplash image URLs work well.</small>
                        </div>

                        <div class="field">
                            <label>Short Summary</label>
                            <textarea v-model="form.summary" rows="3"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label>Button Text</label>
                                <input v-model="form.cta_label" />
                            </div>
                            <div class="field">
                                <label>Button Link</label>
                                <input v-model="form.cta_url" placeholder="/register" />
                            </div>
                        </div>

                        <div class="field">
                            <label>Page Content</label>
                            <MarketingEditor v-model="form.content" />
                        </div>

                        <label class="publish-row"><input type="checkbox" v-model="form.is_published" /> Show in navigation</label>

                        <div class="actions-row">
                            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save Page' }}</button>
                            <a :href="previewUrl" target="_blank" class="btn-secondary">Preview This Page</a>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import MarketingEditor from '../../../components/Platform/MarketingEditor.vue'

const props = defineProps({ pages: Array })
const page = usePage()
const flash = page.props.flash ?? {}
const selected = ref(props.pages?.[0] || null)
const saving = ref(false)

const form = reactive(blank(selected.value))
const previewUrl = computed(() => selected.value?.slug === 'home' ? '/' : '/' + selected.value?.slug)

function blank(pageItem = null) {
    return {
        title: pageItem?.title || '',
        slug: pageItem?.slug || '',
        nav_label: pageItem?.nav_label || '',
        eyebrow: pageItem?.eyebrow || '',
        summary: pageItem?.summary || '',
        content: pageItem?.content || '',
        hero_image_url: pageItem?.hero_image_url || '',
        cta_label: pageItem?.cta_label || '',
        cta_url: pageItem?.cta_url || '',
        is_published: pageItem?.is_published !== false,
        sort: pageItem?.sort || 0,
    }
}

function select(pageItem) {
    selected.value = pageItem
    Object.assign(form, blank(pageItem))
}

function submit() {
    saving.value = true
    router.put(route('platform.superadmin.marketing-pages.update', selected.value.id), form, {
        preserveScroll: true,
        onSuccess: () => {
            Object.assign(selected.value, { ...form })
        },
        onFinish: () => { saving.value = false },
    })
}
</script>

<style scoped>
.sa-wrap { min-height: 100vh; background: #f8fafc; }
.sa-nav { background: #17272b; color: #fff; display: flex; align-items: center; gap: 24px; padding: 0 24px; min-height: 58px; flex-wrap: wrap; }
.sa-brand { display: flex; align-items: center; gap: 10px; margin-right: auto; }
.brand-logo { width: 32px; height: 32px; background: #ff7a59; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 13px; color: #fff; }
.brand-name { font-weight: 800; font-size: 16px; }
.sa-badge { background: #ff7a59; color: #fff; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; }
.sa-links { display: flex; gap: 4px; flex-wrap: wrap; }
.sa-link { color: #b7c8cc; text-decoration: none; padding: 7px 11px; border-radius: 6px; font-size: 14px; font-weight: 700; }
.sa-link:hover, .sa-link.active { color: #fff; background: rgba(255,255,255,.1); }
.sa-logout { background: none; border: 1px solid #405257; color: #b7c8cc; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; }
.sa-main { max-width: 1180px; margin: 0 auto; padding: 32px 24px; }
.page-header { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 22px; }
.page-title { font-size: 26px; font-weight: 900; color: #111; }
.page-subtitle { color: #657477; margin-top: 4px; }
.btn-preview, .btn-secondary { background: #edf7f4; color: #0f766e; text-decoration: none; border-radius: 8px; padding: 10px 16px; font-weight: 900; }
.alert-success { background: #dcfce7; color: #15803d; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-weight: 700; }
.cms-grid { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 18px; align-items: start; }
.page-list { display: grid; gap: 8px; }
.page-tab { border: 1px solid #e5edf0; background: #fff; border-radius: 8px; padding: 14px; text-align: left; cursor: pointer; box-shadow: 0 8px 20px rgba(31,45,48,.04); }
.page-tab.active { border-color: #ff7a59; box-shadow: 0 12px 28px rgba(255,122,89,.12); }
.page-tab span { display: block; color: #17272b; font-weight: 900; }
.page-tab small { color: #657477; margin-top: 4px; display: block; }
.editor-card { background: #fff; border: 1px solid #e5edf0; border-radius: 8px; padding: 22px; box-shadow: 0 12px 28px rgba(31,45,48,.06); }
.image-preview { height: 260px; border-radius: 8px; background: linear-gradient(135deg, #fff7ed, #ccfbf1); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; color: #657477; font-weight: 900; margin-bottom: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.short-field { max-width: 160px; }
.field { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 12px; font-weight: 900; text-transform: uppercase; color: #657477; }
.field input, .field textarea { border: 1.5px solid #dbe4e8; border-radius: 8px; padding: 10px 12px; font: inherit; }
.field input:focus, .field textarea:focus { outline: none; border-color: #ff7a59; }
.field small { color: #8ca2a7; font-size: 12px; }
.publish-row { display: flex; align-items: center; gap: 8px; color: #344448; font-weight: 800; margin-bottom: 18px; }
.actions-row { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-primary { background: #ff7a59; color: #fff; border: none; border-radius: 8px; padding: 11px 22px; font-weight: 900; cursor: pointer; }
.btn-primary:disabled { opacity: .55; cursor: default; }
@media (max-width: 900px) { .cms-grid { grid-template-columns: 1fr; } .form-row { grid-template-columns: 1fr; } .page-header { align-items: flex-start; flex-direction: column; } }
</style>