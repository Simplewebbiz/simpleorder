<template>
    <AdminLayout :page-title="category ? 'Edit Category' : 'New Category'">
        <div class="form-page">
            <div class="back-row">
                <Link :href="route('tenant.admin.categories.index')" class="back-link">← Categories</Link>
            </div>

            <div class="card">
                <form @submit.prevent="submit">
                    <div class="field-group">
                        <label class="field-label">Category Name *</label>
                        <input v-model="form.name" type="text" class="field-input" :class="{ error: errors.name }" required />
                        <div class="field-error" v-if="errors.name">{{ errors.name }}</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Description</label>
                        <textarea v-model="form.description" class="field-input" rows="3"></textarea>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Sort Order</label>
                            <input v-model="form.sort" type="number" class="field-input sm" min="0" />
                        </div>
                        <div class="field-group">
                            <label class="field-label">Status</label>
                            <select v-model="form.is_active" class="field-input sm">
                                <option :value="true">Active</option>
                                <option :value="false">Hidden</option>
                            </select>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Category Image</label>
                        <div class="image-upload" v-if="imagePreview || form.image_id">
                            <img :src="imagePreview || currentImage" class="image-preview" alt="Category image" />
                            <button type="button" class="remove-img" @click="clearImage">Remove</button>
                        </div>
                        <div class="upload-zone" v-else @click="$refs.imgInput.click()">
                            <div class="upload-icon">🖼</div>
                            <div>Click to upload image</div>
                            <div class="upload-sub">JPG, PNG, WebP</div>
                        </div>
                        <input ref="imgInput" type="file" accept="image/*" hidden @change="onImageChange" />
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : (category ? 'Update Category' : 'Create Category') }}
                        </button>
                        <Link :href="route('tenant.admin.categories.index')" class="btn-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'
import axios from 'axios'

const props = defineProps({ category: Object, errors: Object })

const saving = ref(false)
const imageFile = ref(null)
const imagePreview = ref(null)
const currentImage = ref(props.category?.image?.permalink || null)

const form = reactive({
    name: props.category?.name || '',
    description: props.category?.description || '',
    sort: props.category?.sort ?? 0,
    is_active: props.category?.is_active !== false,
    image_id: props.category?.image_id || null,
})

function onImageChange(e) {
    const file = e.target.files[0]
    if (!file) return
    imageFile.value = file
    imagePreview.value = URL.createObjectURL(file)
}

function clearImage() {
    imageFile.value = null
    imagePreview.value = null
    currentImage.value = null
    form.image_id = null
}

async function submit() {
    saving.value = true
    try {
        if (imageFile.value) {
            const fd = new FormData()
            fd.append('file', imageFile.value)
            fd.append('folder', 'categories')
            const { data } = await axios.post(route('tenant.admin.media.store'), fd)
            form.image_id = data.id
        }
        if (props.category) {
            router.put(route('tenant.admin.categories.update', props.category.id), form)
        } else {
            router.post(route('tenant.admin.categories.store'), form)
        }
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.form-page { max-width: 640px; }
.back-row { margin-bottom: 16px; }
.back-link { color: #e85d04; text-decoration: none; font-weight: 600; font-size: 14px; }
.card { background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.field-group { margin-bottom: 18px; }
.field-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 6px; }
.field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; font-size: 14px; font-family: inherit; resize: vertical; }
.field-input.sm { width: 140px; }
.field-input:focus { outline: none; border-color: #e85d04; }
.field-input.error { border-color: #ef4444; }
.field-error { color: #dc2626; font-size: 12px; margin-top: 4px; }
.field-row { display: flex; gap: 16px; flex-wrap: wrap; }
.image-upload { display: flex; flex-direction: column; align-items: flex-start; gap: 10px; }
.image-preview { max-height: 160px; max-width: 280px; border-radius: 8px; object-fit: cover; }
.remove-img { background: none; border: 1.5px solid #e5e7eb; border-radius: 6px; padding: 5px 14px; font-size: 13px; cursor: pointer; color: #6b7280; }
.remove-img:hover { border-color: #ef4444; color: #ef4444; }
.upload-zone { border: 2px dashed #e5e7eb; border-radius: 10px; padding: 28px; text-align: center; cursor: pointer; font-size: 14px; color: #6b7280; }
.upload-zone:hover { border-color: #e85d04; }
.upload-icon { font-size: 28px; margin-bottom: 6px; }
.upload-sub { font-size: 12px; color: #9ca3af; margin-top: 4px; }
.form-actions { display: flex; gap: 12px; align-items: center; margin-top: 8px; }
.btn-primary { background: #e85d04; color: #fff; border: none; padding: 11px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-primary:disabled { opacity: 0.5; cursor: default; }
.btn-secondary { background: #f3f4f6; color: #374151; text-decoration: none; padding: 11px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; }
</style>
