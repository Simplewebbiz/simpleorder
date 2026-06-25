<template>
    <AdminLayout page-title="Settings">
        <form @submit.prevent="save" enctype="multipart/form-data">
            <div class="settings-grid">

                <!-- Store Info -->
                <div class="card">
                    <div class="card-header">Store Information</div>
                    <div class="field-group">
                        <label class="field-label">Store Name</label>
                        <input v-model="form.store_name" type="text" class="field-input" required />
                    </div>
                    <div class="field-group">
                        <label class="field-label">Order Email (receives order notifications)</label>
                        <input v-model="form.order_email" type="email" class="field-input" />
                    </div>
                    <div class="field-group">
                        <label class="field-label">Phone</label>
                        <input v-model="form.store_phone" type="text" class="field-input" />
                    </div>
                    <div class="field-group">
                        <label class="field-label">Address</label>
                        <input v-model="form.store_address.address" type="text" class="field-input" placeholder="Street address" />
                    </div>
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">City</label>
                            <input v-model="form.store_address.city" type="text" class="field-input" />
                        </div>
                        <div class="field-group" style="flex:0 0 70px">
                            <label class="field-label">State</label>
                            <input v-model="form.store_address.state" type="text" class="field-input" maxlength="2" />
                        </div>
                        <div class="field-group" style="flex:0 0 100px">
                            <label class="field-label">Zip</label>
                            <input v-model="form.store_address.zip" type="text" class="field-input" maxlength="10" />
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Timezone</label>
                        <select v-model="form.timezone" class="field-input">
                            <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
                        </select>
                    </div>
                </div>

                <!-- Logo -->
                <div class="card">
                    <div class="card-header">Logo</div>
                    <div class="logo-preview" v-if="logoPreview || currentLogo">
                        <img :src="logoPreview || currentLogo" alt="Logo" class="logo-img" />
                        <button type="button" class="remove-logo" @click="removeLogo">Remove Logo</button>
                    </div>
                    <div class="upload-zone" v-else @click="$refs.logoInput.click()" @dragover.prevent @drop.prevent="onDrop">
                        <div class="upload-icon">🖼</div>
                        <div class="upload-text">Click or drag to upload logo</div>
                        <div class="upload-sub">PNG, JPG, SVG up to 5MB</div>
                    </div>
                    <input ref="logoInput" type="file" accept="image/*" hidden @change="onLogoChange" />
                    <div class="field-group" style="margin-top:12px">
                        <label class="field-label">Or enter logo URL directly</label>
                        <input v-model="form.logo_url" type="url" class="field-input" placeholder="https://..." />
                    </div>
                </div>

                <!-- Review Requests -->
                <div class="card">
                    <div class="card-header">Review Requests</div>
                    <div class="toggle-row">
                        <label class="toggle-label">Ask completed customers for a review</label>
                        <label class="toggle">
                            <input type="checkbox" v-model="form.review_request_enabled" />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Google Review Link</label>
                        <input v-model="form.review_url" type="url" class="field-input" placeholder="https://g.page/r/..." />
                    </div>
                    <div class="field-group">
                        <label class="field-label">Review Message</label>
                        <textarea v-model="form.review_request_message" class="field-input" rows="3" maxlength="300"></textarea>
                    </div>
                </div>

                <!-- Ordering Options -->
                <div class="card">
                    <div class="card-header">Ordering Options</div>
                    <div class="toggle-row">
                        <label class="toggle-label">Allow Pickup</label>
                        <label class="toggle">
                            <input type="checkbox" v-model="form.allow_pickup" />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-label">Allow Delivery</label>
                        <label class="toggle">
                            <input type="checkbox" v-model="form.allow_delivery" />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="field-group" v-if="form.allow_delivery">
                        <label class="field-label">Delivery Radius (miles)</label>
                        <input v-model="form.delivery_radius" type="number" class="field-input sm" min="0" step="0.1" />
                    </div>
                    <div class="field-group" v-if="form.allow_delivery">
                        <label class="field-label">Food Delivery Charge ($)</label>
                        <input v-model="form.food_charge" type="number" class="field-input sm" min="0" step="0.01" />
                    </div>
                    <div class="field-group" v-if="form.allow_delivery">
                        <label class="field-label">Grocery Delivery Charge ($)</label>
                        <input v-model="form.grocery_charge" type="number" class="field-input sm" min="0" step="0.01" />
                    </div>
                    <div class="field-group">
                        <label class="field-label">Tax Rate (%)</label>
                        <input v-model="form.tax_rate" type="number" class="field-input sm" min="0" max="100" step="0.001" />
                    </div>
                </div>

                <!-- Hours -->
                <div class="card">
                    <div class="card-header">Store Hours</div>
                    <div v-for="(label, key) in dayLabels" :key="key" class="hours-row">
                        <span class="day-name">{{ label }}</span>
                        <label class="toggle">
                            <input type="checkbox" :checked="!form.store_hours[key]?.closed" @change="toggleDay(key, $event)" />
                            <span class="toggle-slider"></span>
                        </label>
                        <template v-if="!form.store_hours[key]?.closed">
                            <input v-model="form.store_hours[key].from" type="time" class="time-input" />
                            <span>–</span>
                            <input v-model="form.store_hours[key].to" type="time" class="time-input" />
                        </template>
                        <span v-else class="closed-text">Closed</span>
                    </div>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="save-btn" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Save Settings' }}
                </button>
                <div class="save-msg success" v-if="savedMsg">Settings saved!</div>
            </div>
        </form>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '../../../components/Admin/Layout.vue'

const props = defineProps({ settings: Object })
const saving = ref(false)
const savedMsg = ref(false)
const logoFile = ref(null)
const logoPreview = ref(null)
const currentLogo = ref(props.settings?.logo_url || null)

const dayLabels = { mo: 'Mon', tu: 'Tue', we: 'Wed', th: 'Thu', fr: 'Fri', sa: 'Sat', su: 'Sun' }
const timezones = ['America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'America/Phoenix', 'Pacific/Honolulu', 'America/Anchorage']

const defaultHours = Object.fromEntries(Object.keys(dayLabels).map(k => [k, { from: '10:00', to: '22:00', closed: false }]))

const form = reactive({
    store_name: props.settings?.store_name || '',
    order_email: props.settings?.order_email || '',
    review_url: props.settings?.review_url || '',
    review_request_enabled: props.settings?.review_request_enabled !== false,
    review_request_message: props.settings?.review_request_message || 'If you enjoyed your order, a quick review helps our restaurant grow.',
    store_phone: props.settings?.store_phone || '',
    store_address: props.settings?.store_address || { address: '', city: '', state: '', zip: '' },
    timezone: props.settings?.timezone || 'America/Chicago',
    allow_pickup: props.settings?.allow_pickup !== false,
    allow_delivery: props.settings?.allow_delivery !== false,
    delivery_radius: props.settings?.delivery_radius || 10,
    food_charge: props.settings?.food_charge || 3,
    grocery_charge: props.settings?.grocery_charge || 5,
    tax_rate: props.settings?.tax_rate || 8.25,
    store_hours: props.settings?.store_hours || defaultHours,
    logo_url: props.settings?.logo_url || '',
})

function toggleDay(key, e) {
    if (!form.store_hours[key]) form.store_hours[key] = { from: '10:00', to: '22:00', closed: false }
    form.store_hours[key].closed = !e.target.checked
}

function onLogoChange(e) {
    const file = e.target.files[0]
    if (!file) return
    logoFile.value = file
    logoPreview.value = URL.createObjectURL(file)
}

function onDrop(e) {
    const file = e.dataTransfer.files[0]
    if (!file) return
    logoFile.value = file
    logoPreview.value = URL.createObjectURL(file)
}

async function removeLogo() {
    logoFile.value = null
    logoPreview.value = null
    currentLogo.value = null
    form.logo_url = ''
    await axios.delete(route('tenant.admin.media.destroy', 'logo'))
}

async function save() {
    saving.value = true
    try {
        if (logoFile.value) {
            const fd = new FormData()
            fd.append('file', logoFile.value)
            fd.append('folder', 'logos')
            const { data } = await axios.post(route('tenant.admin.media.store'), fd)
            form.logo_url = data.url
            currentLogo.value = data.url
            logoFile.value = null
            logoPreview.value = null
        }
        await axios.post(route('tenant.admin.settings.update'), form)
        savedMsg.value = true
        setTimeout(() => { savedMsg.value = false }, 3000)
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 24px; }
.card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.card-header { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #f3f4f6; }
.field-group { margin-bottom: 16px; }
.field-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 6px; }
.field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 9px 12px; font-size: 14px; }
.field-input.sm { width: 120px; }
.field-input:focus { outline: none; border-color: #e85d04; }
.field-row { display: flex; gap: 10px; }
.logo-preview { display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 16px; }
.logo-img { max-height: 80px; max-width: 200px; object-fit: contain; }
.remove-logo { background: none; border: 1.5px solid #e5e7eb; padding: 6px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; color: #6b7280; }
.remove-logo:hover { border-color: #ef4444; color: #ef4444; }
.upload-zone { border: 2px dashed #e5e7eb; border-radius: 10px; padding: 32px; text-align: center; cursor: pointer; transition: border-color 0.15s; }
.upload-zone:hover { border-color: #e85d04; }
.upload-icon { font-size: 32px; margin-bottom: 8px; }
.upload-text { font-weight: 600; margin-bottom: 4px; }
.upload-sub { font-size: 12px; color: #9ca3af; }
.toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f9fafb; }
.toggle-label { font-size: 14px; font-weight: 500; }
.toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: #e5e7eb; border-radius: 24px; cursor: pointer; transition: 0.2s; }
.toggle-slider::before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: 0.2s; }
.toggle input:checked + .toggle-slider { background: #e85d04; }
.toggle input:checked + .toggle-slider::before { transform: translateX(20px); }
.hours-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f9fafb; font-size: 14px; }
.day-name { width: 36px; font-weight: 600; color: #374151; }
.time-input { border: 1.5px solid #e5e7eb; border-radius: 6px; padding: 5px 8px; font-size: 13px; width: 90px; }
.closed-text { color: #9ca3af; font-size: 13px; font-style: italic; flex: 1; }
.form-actions { display: flex; align-items: center; gap: 16px; }
.save-btn { background: #e85d04; color: #fff; border: none; padding: 12px 32px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; }
.save-btn:hover:not(:disabled) { background: #c44d03; }
.save-btn:disabled { opacity: 0.5; cursor: default; }
.save-msg.success { color: #15803d; font-weight: 600; font-size: 14px; }
</style>
