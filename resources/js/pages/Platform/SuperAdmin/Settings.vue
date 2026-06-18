<template>
    <div class="superadmin-settings">
        <!-- Nav -->
        <header class="sa-nav">
            <div class="sa-brand">
                <div class="brand-logo">SO</div>
                <span class="brand-name">SimpleOrder</span>
                <span class="sa-badge">Super Admin</span>
            </div>
            <nav class="sa-links">
                <Link :href="route('platform.superadmin.tenants')" class="sa-link">Tenants</Link>
                <Link :href="route('platform.superadmin.settings')" class="sa-link active">API Settings</Link>
                <Link :href="route('platform.superadmin.plans')" class="sa-link">Plans</Link>
            </nav>
        </header>

        <div class="content">
            <div class="page-header">
                <h1 class="page-title">Platform API Settings</h1>
                <p class="page-sub">These keys apply to the entire platform. Tenant-level Stripe Connect is configured per-tenant.</p>
            </div>

            <!-- Flash -->
            <div class="flash success" v-if="flash?.success">✓ {{ flash.success }}</div>

            <form @submit.prevent="save">

                <!-- Stripe -->
                <div class="settings-card">
                    <div class="card-head">
                        <div class="service-info">
                            <div class="service-icon stripe">S</div>
                            <div>
                                <div class="service-name">Stripe</div>
                                <div class="service-desc">Platform subscription billing (Stripe Billing + Cashier). Tenant payments use Stripe Connect — configured per tenant.</div>
                            </div>
                        </div>
                        <div class="service-status" :class="stripeStatus.cls">{{ stripeStatus.label }}</div>
                    </div>

                    <div class="fields-grid">
                        <div class="field-group full">
                            <label class="field-label">Publishable Key <span class="hint">starts with pk_</span></label>
                            <input v-model="form.stripe_key" type="text" class="field-input" placeholder="pk_live_..." autocomplete="off" />
                        </div>
                        <div class="field-group">
                            <label class="field-label">Secret Key <span class="hint">starts with sk_</span></label>
                            <div class="secret-wrap">
                                <input v-model="form.stripe_secret" :type="show.stripe_secret ? 'text' : 'password'" class="field-input" placeholder="sk_live_..." autocomplete="off" />
                                <button type="button" class="eye-btn" @click="show.stripe_secret = !show.stripe_secret">{{ show.stripe_secret ? '🙈' : '👁' }}</button>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Webhook Secret <span class="hint">from Stripe dashboard</span></label>
                            <div class="secret-wrap">
                                <input v-model="form.stripe_webhook_secret" :type="show.stripe_webhook ? 'text' : 'password'" class="field-input" placeholder="whsec_..." autocomplete="off" />
                                <button type="button" class="eye-btn" @click="show.stripe_webhook = !show.stripe_webhook">{{ show.stripe_webhook ? '🙈' : '👁' }}</button>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Platform Fee per Order <span class="hint">0.02 = 2%</span></label>
                            <input v-model="form.stripe_platform_fee" type="number" class="field-input sm" min="0" max="1" step="0.001" placeholder="0.02" />
                        </div>
                    </div>

                    <div class="card-actions">
                        <button type="button" class="test-btn" :disabled="testing === 'stripe'" @click="test('stripe')">
                            {{ testing === 'stripe' ? 'Testing...' : 'Test Connection' }}
                        </button>
                        <div class="test-result" :class="testResults.stripe?.ok ? 'ok' : 'fail'" v-if="testResults.stripe">
                            {{ testResults.stripe.ok ? '✓' : '✕' }} {{ testResults.stripe.message }}
                        </div>
                    </div>
                </div>

                <!-- Resend -->
                <div class="settings-card">
                    <div class="card-head">
                        <div class="service-info">
                            <div class="service-icon resend">R</div>
                            <div>
                                <div class="service-name">Resend</div>
                                <div class="service-desc">All transactional email — order confirmations, status updates, subscription reminders.</div>
                            </div>
                        </div>
                        <div class="service-status" :class="resendStatus.cls">{{ resendStatus.label }}</div>
                    </div>

                    <div class="fields-grid">
                        <div class="field-group full">
                            <label class="field-label">API Key <span class="hint">starts with re_</span></label>
                            <div class="secret-wrap">
                                <input v-model="form.resend_api_key" :type="show.resend ? 'text' : 'password'" class="field-input" placeholder="re_..." autocomplete="off" />
                                <button type="button" class="eye-btn" @click="show.resend = !show.resend">{{ show.resend ? '🙈' : '👁' }}</button>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">From Address</label>
                            <input v-model="form.mail_from_address" type="email" class="field-input" placeholder="noreply@simpleorder.com" />
                        </div>
                        <div class="field-group">
                            <label class="field-label">From Name</label>
                            <input v-model="form.mail_from_name" type="text" class="field-input" placeholder="SimpleOrder" />
                        </div>
                    </div>

                    <div class="card-actions">
                        <button type="button" class="test-btn" :disabled="testing === 'resend'" @click="test('resend')">
                            {{ testing === 'resend' ? 'Testing...' : 'Test Connection' }}
                        </button>
                        <div class="test-result" :class="testResults.resend?.ok ? 'ok' : 'fail'" v-if="testResults.resend">
                            {{ testResults.resend.ok ? '✓' : '✕' }} {{ testResults.resend.message }}
                        </div>
                    </div>
                </div>

                <!-- Twilio -->
                <div class="settings-card">
                    <div class="card-head">
                        <div class="service-info">
                            <div class="service-icon twilio">T</div>
                            <div>
                                <div class="service-name">Twilio SMS</div>
                                <div class="service-desc">Text customers on order status changes (received, ready, complete, cancelled).</div>
                            </div>
                        </div>
                        <div class="service-status" :class="twilioStatus.cls">{{ twilioStatus.label }}</div>
                    </div>

                    <div class="fields-grid">
                        <div class="field-group full toggle-row">
                            <label class="field-label">Enable SMS Notifications</label>
                            <label class="toggle">
                                <input type="checkbox" v-model="form.twilio_enabled" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Account SID</label>
                            <input v-model="form.twilio_sid" type="text" class="field-input" placeholder="ACxxxxxxxx..." autocomplete="off" />
                        </div>
                        <div class="field-group">
                            <label class="field-label">Auth Token</label>
                            <div class="secret-wrap">
                                <input v-model="form.twilio_token" :type="show.twilio ? 'text' : 'password'" class="field-input" placeholder="Auth token" autocomplete="off" />
                                <button type="button" class="eye-btn" @click="show.twilio = !show.twilio">{{ show.twilio ? '🙈' : '👁' }}</button>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">From Number <span class="hint">E.164 format: +15551234567</span></label>
                            <input v-model="form.twilio_from" type="text" class="field-input" placeholder="+15551234567" />
                        </div>
                    </div>

                    <div class="card-actions">
                        <div class="test-phone-wrap">
                            <input v-model="testPhone" type="text" class="field-input sm" placeholder="+15551234567" />
                            <button type="button" class="test-btn" :disabled="testing === 'twilio' || !form.twilio_enabled" @click="test('twilio')">
                                {{ testing === 'twilio' ? 'Sending...' : 'Send Test SMS' }}
                            </button>
                        </div>
                        <div class="test-result" :class="testResults.twilio?.ok ? 'ok' : 'fail'" v-if="testResults.twilio">
                            {{ testResults.twilio.ok ? '✓' : '✕' }} {{ testResults.twilio.message }}
                        </div>
                    </div>
                </div>

                <!-- Google Maps -->
                <div class="settings-card">
                    <div class="card-head">
                        <div class="service-info">
                            <div class="service-icon maps">G</div>
                            <div>
                                <div class="service-name">Google Maps</div>
                                <div class="service-desc">Geocoding API used to validate delivery addresses are within a tenant's configured radius. Optional — without it, all addresses are accepted.</div>
                            </div>
                        </div>
                        <div class="service-status" :class="mapsStatus.cls">{{ mapsStatus.label }}</div>
                    </div>

                    <div class="fields-grid">
                        <div class="field-group full">
                            <label class="field-label">API Key <span class="hint">must have Geocoding API enabled</span></label>
                            <div class="secret-wrap">
                                <input v-model="form.google_maps_api_key" :type="show.maps ? 'text' : 'password'" class="field-input" placeholder="AIzaSy..." autocomplete="off" />
                                <button type="button" class="eye-btn" @click="show.maps = !show.maps">{{ show.maps ? '🙈' : '👁' }}</button>
                            </div>
                        </div>
                    </div>

                    <div class="card-actions">
                        <button type="button" class="test-btn" :disabled="testing === 'google_maps'" @click="test('google_maps')">
                            {{ testing === 'google_maps' ? 'Testing...' : 'Test API Key' }}
                        </button>
                        <div class="test-result" :class="testResults.google_maps?.ok ? 'ok' : 'fail'" v-if="testResults.google_maps">
                            {{ testResults.google_maps.ok ? '✓' : '✕' }} {{ testResults.google_maps.message }}
                        </div>
                    </div>
                </div>

                <div class="save-bar">
                    <button type="submit" class="save-btn" :disabled="saving">
                        {{ saving ? 'Saving...' : 'Save All Settings' }}
                    </button>
                    <p class="save-note">Sensitive values are encrypted at rest and masked on this page after saving.</p>
                </div>

            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({ settings: Object })
const page  = usePage()
const flash = page.props.flash || {}

const saving  = ref(false)
const testing = ref(null)
const testPhone = ref('')
const testResults = reactive({})

const show = reactive({
    stripe_secret: false, stripe_webhook: false,
    resend: false, twilio: false, maps: false,
})

const form = reactive({
    stripe_key:            props.settings?.stripe_key || '',
    stripe_secret:         props.settings?.stripe_secret || '',
    stripe_webhook_secret: props.settings?.stripe_webhook_secret || '',
    stripe_platform_fee:   props.settings?.stripe_platform_fee || '0.02',
    resend_api_key:        props.settings?.resend_api_key || '',
    mail_from_address:     props.settings?.mail_from_address || '',
    mail_from_name:        props.settings?.mail_from_name || 'SimpleOrder',
    google_maps_api_key:   props.settings?.google_maps_api_key || '',
    twilio_enabled:        props.settings?.twilio_enabled === '1' || props.settings?.twilio_enabled === true,
    twilio_sid:            props.settings?.twilio_sid || '',
    twilio_token:          props.settings?.twilio_token || '',
    twilio_from:           props.settings?.twilio_from || '',
})

const isSet = (key) => props.settings?.[key] && props.settings[key] !== ''

const stripeStatus  = computed(() => isSet('stripe_key') ? { cls: 'connected', label: 'Connected' } : { cls: 'not-set', label: 'Not Configured' })
const resendStatus  = computed(() => isSet('resend_api_key') ? { cls: 'connected', label: 'Connected' } : { cls: 'not-set', label: 'Not Configured' })
const twilioStatus  = computed(() => {
    if (!isSet('twilio_sid')) return { cls: 'not-set', label: 'Not Configured' }
    return form.twilio_enabled ? { cls: 'connected', label: 'Enabled' } : { cls: 'disabled', label: 'Disabled' }
})
const mapsStatus    = computed(() => isSet('google_maps_api_key') ? { cls: 'connected', label: 'Connected' } : { cls: 'optional', label: 'Optional' })

async function test(service) {
    testing.value = service
    testResults[service] = null
    try {
        const payload = { service }
        if (service === 'twilio') payload.phone = testPhone.value
        const { data } = await axios.post(route('platform.superadmin.settings.test'), payload)
        testResults[service] = data
    } catch (e) {
        testResults[service] = { ok: false, message: e.response?.data?.message || 'Request failed.' }
    } finally {
        testing.value = null
    }
}

function save() {
    saving.value = true
    router.post(route('platform.superadmin.settings.update'), form, {
        onFinish: () => { saving.value = false },
    })
}
</script>

<style scoped>
.superadmin-settings { min-height: 100vh; background: #f5f5f5; }
.sa-nav { background: #0f0f0f; padding: 0 28px; display: flex; align-items: center; gap: 24px; height: 60px; }
.sa-brand { display: flex; align-items: center; gap: 10px; }
.brand-logo { width: 34px; height: 34px; background: #e85d04; color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; }
.brand-name { font-size: 17px; font-weight: 800; color: #fff; }
.sa-badge { background: #7c3aed; color: #fff; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.05em; }
.sa-links { display: flex; gap: 4px; flex: 1; margin-left: 16px; }
.sa-link { color: #9ca3af; text-decoration: none; padding: 7px 14px; border-radius: 6px; font-size: 14px; font-weight: 500; }
.sa-link:hover { color: #fff; background: rgba(255,255,255,.07); }
.sa-link.active { color: #fff; background: rgba(124,58,237,.25); }
.content { max-width: 860px; margin: 0 auto; padding: 32px 24px; }
.page-header { margin-bottom: 28px; }
.page-title { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
.page-sub { font-size: 14px; color: #6b7280; }
.flash { padding: 12px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
.flash.success { background: #dcfce7; color: #15803d; }
.settings-card { background: #fff; border-radius: 14px; padding: 28px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
.service-info { display: flex; gap: 16px; align-items: flex-start; }
.service-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 900; color: #fff; flex-shrink: 0; }
.service-icon.stripe  { background: #635bff; }
.service-icon.resend  { background: #000; }
.service-icon.twilio  { background: #f22f46; }
.service-icon.maps    { background: #4285f4; }
.service-name { font-size: 17px; font-weight: 800; margin-bottom: 4px; }
.service-desc { font-size: 13px; color: #6b7280; line-height: 1.5; max-width: 480px; }
.service-status { padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; white-space: nowrap; flex-shrink: 0; }
.service-status.connected { background: #dcfce7; color: #15803d; }
.service-status.not-set   { background: #fee2e2; color: #dc2626; }
.service-status.disabled  { background: #f3f4f6; color: #6b7280; }
.service-status.optional  { background: #fef3c7; color: #92400e; }
.fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
@media (max-width: 640px) { .fields-grid { grid-template-columns: 1fr; } }
.field-group { display: flex; flex-direction: column; }
.field-group.full { grid-column: 1 / -1; }
.field-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 6px; }
.hint { font-weight: 400; text-transform: none; letter-spacing: 0; color: #9ca3af; }
.field-input { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 9px 12px; font-size: 14px; width: 100%; font-family: monospace; }
.field-input.sm { max-width: 200px; }
.field-input:focus { outline: none; border-color: #e85d04; }
.secret-wrap { position: relative; display: flex; }
.secret-wrap .field-input { padding-right: 40px; }
.eye-btn { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; }
.toggle-row { flex-direction: row; align-items: center; justify-content: space-between; background: #f9fafb; border-radius: 8px; padding: 12px 16px; }
.toggle { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: #e5e7eb; border-radius: 24px; cursor: pointer; transition: 0.2s; }
.toggle-slider::before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: 0.2s; }
.toggle input:checked + .toggle-slider { background: #e85d04; }
.toggle input:checked + .toggle-slider::before { transform: translateX(20px); }
.card-actions { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; padding-top: 4px; border-top: 1px solid #f3f4f6; }
.test-phone-wrap { display: flex; gap: 10px; align-items: center; }
.test-btn { background: #f3f4f6; color: #374151; border: 1.5px solid #e5e7eb; padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.15s; }
.test-btn:hover:not(:disabled) { border-color: #e85d04; color: #e85d04; }
.test-btn:disabled { opacity: 0.5; cursor: default; }
.test-result { font-size: 13px; font-weight: 600; padding: 6px 12px; border-radius: 6px; }
.test-result.ok   { background: #dcfce7; color: #15803d; }
.test-result.fail { background: #fee2e2; color: #dc2626; }
.save-bar { display: flex; align-items: center; gap: 20px; padding: 24px 0; flex-wrap: wrap; }
.save-btn { background: #e85d04; color: #fff; border: none; padding: 13px 36px; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.15s; }
.save-btn:hover:not(:disabled) { background: #c44d03; }
.save-btn:disabled { opacity: 0.5; cursor: default; }
.save-note { font-size: 12px; color: #9ca3af; }
</style>
