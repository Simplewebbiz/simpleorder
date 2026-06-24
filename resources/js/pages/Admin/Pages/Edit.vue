<template>
    <AdminLayout :page-title="'Edit ' + form.title">
        <div class="form-page">
            <div class="back-row">
                <Link :href="route('tenant.admin.pages.index')" class="back-link">Back to pages</Link>
            </div>

            <form @submit.prevent="submit" class="editor-layout">
                <section class="card main-card">
                    <div class="field-group">
                        <label class="field-label">Page Title</label>
                        <input v-model="form.title" type="text" class="field-input" required />
                    </div>

                    <div class="field-group">
                        <label class="field-label">Short Summary</label>
                        <textarea v-model="form.summary" class="field-input" rows="2"></textarea>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Page Content</label>
                        <WysiwygEditor v-model="form.content" />
                    </div>
                </section>

                <aside class="card side-card">
                    <div class="field-group">
                        <label class="field-label">Navigation Label</label>
                        <input v-model="form.menu_label" type="text" class="field-input" />
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">URL Slug</label>
                            <input v-model="form.slug" type="text" class="field-input" />
                        </div>
                        <div class="field-group short-field">
                            <label class="field-label">Sort</label>
                            <input v-model="form.sort" type="number" class="field-input" min="0" />
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Hero Image</label>
                        <div class="hero-preview" v-if="imagePreview || currentHero" :style="{ backgroundImage: 'url(' + (imagePreview || currentHero) + ')' }"></div>
                        <button type="button" class="upload-button" @click="$refs.heroInput.click()">Choose Image</button>
                        <button v-if="imagePreview || currentHero" type="button" class="remove-button" @click="clearHero">Remove Image</button>
                        <input ref="heroInput" type="file" accept="image/*" hidden @change="onHeroChange" />
                    </div>

                    <label class="publish-row">
                        <input type="checkbox" v-model="form.is_published" />
                        Show this page on the website
                    </label>

                    <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save Page' }}</button>
                </aside>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '../../../components/Admin/Layout.vue'
import WysiwygEditor from '../../../components/Admin/WysiwygEditor.vue'

const props = defineProps({ pageModel: Object })

const saving = ref(false)
const heroFile = ref(null)
const imagePreview = ref(null)
const currentHero = ref(props.pageModel?.hero?.permalink || null)

const form = reactive({
    title: props.pageModel.title || '',
    slug: props.pageModel.slug || '',
    menu_label: props.pageModel.menu_label || '',
    summary: props.pageModel.summary || '',
    content: props.pageModel.content || '',
    hero_media_id: props.pageModel.hero_media_id || null,
    is_published: props.pageModel.is_published !== false,
    sort: props.pageModel.sort || 0,
})

function onHeroChange(event) {
    const file = event.target.files?.[0]
    if (!file) return
    heroFile.value = file
    imagePreview.value = URL.createObjectURL(file)
}

function clearHero() {
    heroFile.value = null
    imagePreview.value = null
    currentHero.value = null
    form.hero_media_id = null
}

async function submit() {
    saving.value = true
    try {
        if (heroFile.value) {
            const fd = new FormData()
            fd.append('file', heroFile.value)
            fd.append('folder', 'pages')
            const { data } = await axios.post(route('tenant.admin.media.store'), fd)
            form.hero_media_id = data.id
        }

        router.put(route('tenant.admin.pages.update', props.pageModel.id), form)
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.form-page { max-width: 1180px; }
.back-row { margin-bottom: 16px; }
.back-link { color: #0f766e; text-decoration: none; font-weight: 800; }
.editor-layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 18px; align-items: start; }
.card { background: #fff; border: 1px solid #e5edf0; border-radius: 8px; padding: 22px; box-shadow: 0 10px 24px rgba(31, 45, 48, 0.06); }
.field-group { margin-bottom: 16px; }
.field-row { display: flex; gap: 12px; }
.short-field { width: 100px; flex: 0 0 100px; }
.field-label { display: block; color: #657477; font-size: 12px; font-weight: 800; text-transform: uppercase; margin-bottom: 6px; }
.field-input { width: 100%; border: 1.5px solid #dbe4e8; border-radius: 8px; padding: 10px 12px; font-size: 14px; font-family: inherit; }
.field-input:focus { outline: none; border-color: #ff7a59; }
.hero-preview { height: 170px; border-radius: 8px; background-size: cover; background-position: center; border: 1px solid #e5edf0; margin-bottom: 10px; }
.upload-button, .remove-button, .btn-primary { width: 100%; border: none; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 800; cursor: pointer; }
.upload-button { background: #edf7f4; color: #0f766e; margin-bottom: 8px; }
.remove-button { background: #fff1f2; color: #be123c; margin-bottom: 8px; }
.publish-row { display: flex; align-items: center; gap: 8px; color: #344448; font-weight: 700; margin: 12px 0 18px; }
.btn-primary { background: #ff7a59; color: #fff; }
.btn-primary:disabled { opacity: .55; cursor: default; }
@media (max-width: 900px) { .editor-layout { grid-template-columns: 1fr; } }
</style>