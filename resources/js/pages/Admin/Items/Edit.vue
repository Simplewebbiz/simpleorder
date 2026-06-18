<template>
    <AdminLayout :page-title="item ? 'Edit Item' : 'New Item'">
        <div class="form-page">
            <div class="back-row">
                <Link :href="route('tenant.admin.items.index')" class="back-link">← Menu Items</Link>
            </div>

            <form @submit.prevent="submit">
                <div class="form-grid">
                    <!-- Basic info -->
                    <div class="card">
                        <div class="card-header">Item Details</div>

                        <div class="field-group">
                            <label class="field-label">Item Name *</label>
                            <input v-model="form.name" type="text" class="field-input" required />
                        </div>
                        <div class="field-group">
                            <label class="field-label">Description</label>
                            <textarea v-model="form.description" class="field-input" rows="3"></textarea>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Price *</label>
                                <div class="price-input-wrap">
                                    <span class="price-sym">$</span>
                                    <input v-model="form.price" type="number" class="field-input price" min="0" step="0.01" required />
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Type</label>
                                <select v-model="form.type" class="field-input sm">
                                    <option value="food">Food</option>
                                    <option value="product">Product</option>
                                </select>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Status</label>
                                <select v-model="form.is_active" class="field-input sm">
                                    <option :value="true">Active</option>
                                    <option :value="false">Hidden</option>
                                </select>
                            </div>
                            <div class="field-group toggle-group">
                                <label class="field-label">Taxable</label>
                                <label class="toggle">
                                    <input type="checkbox" v-model="form.taxable" />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="field-group">
                            <label class="field-label">Categories</label>
                            <div class="checkbox-grid">
                                <label v-for="cat in categories" :key="cat.id" class="checkbox-item">
                                    <input type="checkbox" :value="cat.id" v-model="form.category_ids" />
                                    {{ cat.name }}
                                </label>
                            </div>
                        </div>

                        <!-- Image -->
                        <div class="field-group">
                            <label class="field-label">Item Image</label>
                            <div class="image-upload" v-if="imagePreview || form.image_id">
                                <img :src="imagePreview || currentImage" class="image-preview" alt="" />
                                <button type="button" class="remove-img" @click="clearImage">Remove</button>
                            </div>
                            <div class="upload-zone" v-else @click="$refs.imgInput.click()">
                                <div class="upload-icon">🖼</div>
                                <div>Click to upload</div>
                            </div>
                            <input ref="imgInput" type="file" accept="image/*" hidden @change="onImageChange" />
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="card">
                        <div class="card-header">
                            Option Groups
                            <button type="button" class="add-option-btn" @click="addOption">+ Add Group</button>
                        </div>

                        <div v-if="form.options.length === 0" class="empty-options">
                            No option groups yet. Add one to let customers customize this item.
                        </div>

                        <div v-for="(opt, oi) in form.options" :key="oi" class="option-group">
                            <div class="option-group-header">
                                <input v-model="opt.label" type="text" class="field-input" placeholder="e.g. Size, Toppings" />
                                <select v-model="opt.input_type" class="field-input xs">
                                    <option value="select">Pick One</option>
                                    <option value="multiselect">Multi-Select</option>
                                </select>
                                <label class="toggle-inline">
                                    <input type="checkbox" v-model="opt.required" />
                                    <span class="toggle-slider"></span>
                                    Req
                                </label>
                                <button type="button" class="remove-opt-btn" @click="removeOption(oi)">✕</button>
                            </div>

                            <div class="option-values">
                                <div v-for="(val, vi) in opt.values" :key="vi" class="option-value-row">
                                    <input v-model="val.label" type="text" class="field-input" placeholder="Value label" />
                                    <select v-model="val.price_type" class="field-input xs">
                                        <option value="flat">$</option>
                                        <option value="percent">%</option>
                                    </select>
                                    <input v-model="val.price" type="number" class="field-input xxs" min="0" step="0.01" placeholder="0" />
                                    <button type="button" class="remove-val-btn" @click="removeValue(oi, vi)">✕</button>
                                </div>
                                <button type="button" class="add-value-btn" @click="addValue(oi)">+ Add Value</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" :disabled="saving">
                        {{ saving ? 'Saving...' : (item ? 'Update Item' : 'Create Item') }}
                    </button>
                    <Link :href="route('tenant.admin.items.index')" class="btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'
import axios from 'axios'

const props = defineProps({ item: Object, categories: Array })

const saving = ref(false)
const imageFile = ref(null)
const imagePreview = ref(null)
const currentImage = ref(props.item?.image?.permalink || null)

const form = reactive({
    name: props.item?.name || '',
    description: props.item?.description || '',
    price: props.item?.price ?? '',
    type: props.item?.type || 'food',
    is_active: props.item?.is_active !== false,
    taxable: props.item?.taxable !== false,
    image_id: props.item?.image_id || null,
    category_ids: props.item?.categories?.map(c => c.id) || [],
    options: (props.item?.options || []).map(opt => ({
        id: opt.id,
        label: opt.label,
        input_type: opt.input_type || 'select',
        required: opt.required || false,
        sort: opt.sort || 0,
        values: (opt.values || []).map(v => ({
            id: v.id,
            label: v.label,
            price: v.price || 0,
            price_type: v.price_type || 'flat',
            sort: v.sort || 0,
        })),
    })),
})

function addOption() {
    form.options.push({ label: '', input_type: 'select', required: false, sort: form.options.length, values: [] })
}

function removeOption(i) {
    form.options.splice(i, 1)
}

function addValue(optIdx) {
    form.options[optIdx].values.push({ label: '', price: 0, price_type: 'flat', sort: form.options[optIdx].values.length })
}

function removeValue(optIdx, valIdx) {
    form.options[optIdx].values.splice(valIdx, 1)
}

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
            fd.append('folder', 'items')
            const { data } = await axios.post(route('tenant.admin.media.store'), fd)
            form.image_id = data.id
        }
        if (props.item) {
            router.put(route('tenant.admin.items.update', props.item.id), form)
        } else {
            router.post(route('tenant.admin.items.store'), form)
        }
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.form-page { max-width: 1000px; }
.back-row { margin-bottom: 16px; }
.back-link { color: #e85d04; text-decoration: none; font-weight: 600; font-size: 14px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
@media (max-width: 900px) { .form-grid { grid-template-columns: 1fr; } }
.card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.card-header { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; }
.field-group { margin-bottom: 16px; }
.field-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 6px; }
.field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 9px 12px; font-size: 14px; font-family: inherit; resize: vertical; }
.field-input.sm { width: 140px; }
.field-input.xs { width: 110px; }
.field-input.xxs { width: 80px; }
.field-input.price { padding-left: 28px; }
.field-input:focus { outline: none; border-color: #e85d04; }
.field-row { display: flex; gap: 14px; flex-wrap: wrap; }
.price-input-wrap { position: relative; }
.price-sym { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.toggle-group { display: flex; flex-direction: column; }
.toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: #e5e7eb; border-radius: 24px; cursor: pointer; transition: 0.2s; }
.toggle-slider::before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: 0.2s; }
.toggle input:checked + .toggle-slider { background: #e85d04; }
.toggle input:checked + .toggle-slider::before { transform: translateX(20px); }
.checkbox-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.checkbox-item { display: flex; align-items: center; gap: 6px; font-size: 14px; cursor: pointer; }
.image-upload { display: flex; flex-direction: column; gap: 8px; }
.image-preview { max-height: 120px; max-width: 220px; border-radius: 8px; object-fit: cover; }
.remove-img { background: none; border: 1.5px solid #e5e7eb; border-radius: 6px; padding: 4px 12px; font-size: 12px; cursor: pointer; width: fit-content; }
.upload-zone { border: 2px dashed #e5e7eb; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; font-size: 13px; color: #6b7280; }
.upload-zone:hover { border-color: #e85d04; }
.upload-icon { font-size: 24px; margin-bottom: 4px; }
.add-option-btn { background: none; border: 1.5px solid #e85d04; color: #e85d04; border-radius: 6px; padding: 4px 12px; font-size: 12px; font-weight: 700; cursor: pointer; }
.empty-options { text-align: center; padding: 24px; color: #9ca3af; font-size: 14px; }
.option-group { border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 14px; margin-bottom: 14px; }
.option-group-header { display: flex; gap: 8px; align-items: center; margin-bottom: 12px; flex-wrap: wrap; }
.toggle-inline { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #6b7280; }
.remove-opt-btn { background: none; border: none; color: #9ca3af; font-size: 16px; cursor: pointer; margin-left: auto; }
.remove-opt-btn:hover { color: #ef4444; }
.option-value-row { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; flex-wrap: wrap; }
.remove-val-btn { background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 14px; }
.remove-val-btn:hover { color: #ef4444; }
.add-value-btn { background: none; border: 1.5px dashed #e5e7eb; color: #9ca3af; border-radius: 6px; padding: 6px 14px; font-size: 13px; cursor: pointer; width: 100%; }
.add-value-btn:hover { border-color: #e85d04; color: #e85d04; }
.form-actions { display: flex; gap: 12px; align-items: center; }
.btn-primary { background: #e85d04; color: #fff; border: none; padding: 11px 28px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-primary:disabled { opacity: 0.5; cursor: default; }
.btn-secondary { background: #f3f4f6; color: #374151; text-decoration: none; padding: 11px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; }
</style>
