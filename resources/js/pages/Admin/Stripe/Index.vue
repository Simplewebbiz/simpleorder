<template>
    <AdminLayout page-title="Stripe & Payments">
        <div class="stripe-page">

            <!-- Current status banner -->
            <div class="status-banner" :class="bannerClass">
                <div class="banner-icon">{{ bannerIcon }}</div>
                <div class="banner-body">
                    <div class="banner-title">{{ bannerTitle }}</div>
                    <div class="banner-sub">{{ bannerSub }}</div>
                </div>
            </div>

            <!-- Flash -->
            <div class="flash success" v-if="flash?.success">✓ {{ flash.success }}</div>
            <div class="flash error" v-if="flash?.error">✕ {{ flash.error }}</div>

            <!-- ─── Option 1: Stripe Connect ──────────────────────────── -->
            <div class="option-card" :class="{ active: mode === 'connect' }">
                <div class="option-head">
                    <div class="option-num">Option 1</div>
                    <div class="option-badge recommended">Recommended</div>
                </div>
                <div class="option-body">
                    <h2 class="option-title">Stripe Connect <span class="option-subtitle">— OAuth</span></h2>
                    <p class="option-desc">
                        Connect your existing Stripe account with one click. Customers are charged through the platform and funds are automatically transferred to your account after each order.
                        The platform retains a <strong>{{ feePercent }}% fee</strong> per transaction.
                    </p>

                    <div class="connect-benefits">
                        <div class="benefit">✓ No API keys to copy — just click and authorize</div>
                        <div class="benefit">✓ Stripe handles fraud detection and disputes</div>
                        <div class="benefit">✓ Works with your existing Stripe dashboard</div>
                        <div class="benefit warn">⚠ Platform takes {{ feePercent }}% per order</div>
                    </div>

                    <!-- Connected state -->
                    <div class="connect-status connected" v-if="connectActive">
                        <div class="cs-icon">✓</div>
                        <div class="cs-info">
                            <div class="cs-title">Connected</div>
                            <div class="cs-id">Account ID: {{ connectId }}</div>
                        </div>
                        <a :href="route('platform.stripe.redirect')" class="reconnect-btn">Reconnect</a>
                        <form @submit.prevent="disconnect">
                            <button type="submit" class="disconnect-btn">Disconnect</button>
                        </form>
                    </div>

                    <!-- Not connected state -->
                    <div v-else>
                        <a :href="connectUrl" class="stripe-connect-btn">
                            <img src="https://b.stripecdn.com/manage-statics-srv/assets/connect-button/connect-with-stripe-blurple.svg" alt="Connect with Stripe" />
                        </a>
                        <p class="connect-note">You'll be redirected to Stripe to authorize the connection.</p>
                    </div>
                </div>
            </div>

            <!-- ─── Option 2: Direct API Keys ─────────────────────────── -->
            <div class="option-card" :class="{ active: mode === 'direct' }">
                <div class="option-head">
                    <div class="option-num">Option 2</div>
                    <div class="option-badge direct">Direct Keys</div>
                </div>
                <div class="option-body">
                    <h2 class="option-title">Your Own Stripe Keys <span class="option-subtitle">— Direct Integration</span></h2>
                    <p class="option-desc">
                        Paste your own Stripe API keys. Payments go directly into your Stripe account with <strong>no platform fee</strong>.
                        You manage payouts, disputes, and refunds in your own Stripe dashboard.
                    </p>

                    <div class="connect-benefits">
                        <div class="benefit">✓ Zero platform fee — keep 100% (minus Stripe's fee)</div>
                        <div class="benefit">✓ Full control in your own Stripe dashboard</div>
                        <div class="benefit warn">⚠ You manage disputes and refunds directly</div>
                        <div class="benefit warn">⚠ Switching away removes existing direct keys</div>
                    </div>

                    <!-- Currently active (direct mode) -->
                    <div class="direct-active" v-if="mode === 'direct'">
                        <div class="active-indicator">
                            <span class="dot green"></span> Direct keys active
                        </div>
                        <div class="key-preview">
                            <div class="key-row">
                                <span class="key-label">Publishable key</span>
                                <span class="key-val">{{ directPubKey }}</span>
                            </div>
                            <div class="key-row">
                                <span class="key-label">Secret key</span>
                                <span class="key-val masked">••••••••••••••••</span>
                            </div>
                        </div>
                        <button class="remove-keys-btn" @click="showDirectForm = true" v-if="!showDirectForm">Update Keys</button>
                        <form @submit.prevent="removeKeys" style="display:inline">
                            <button type="submit" class="disconnect-btn">Remove Keys</button>
                        </form>
                    </div>

                    <!-- Key entry form -->
                    <form
                        v-if="mode !== 'direct' || showDirectForm"
                        @submit.prevent="saveDirectKeys"
                        class="direct-form"
                    >
                        <div class="field-group">
                            <label class="field-label">
                                Publishable Key
                                <span class="hint">starts with pk_live_ or pk_test_</span>
                            </label>
                            <input
                                v-model="directForm.publishable_key"
                                type="text"
                                class="field-input mono"
                                :class="{ error: directErrors.publishable_key }"
                                placeholder="pk_live_..."
                                autocomplete="off"
                                spellcheck="false"
                            />
                            <div class="field-error" v-if="directErrors.publishable_key">{{ directErrors.publishable_key }}</div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">
                                Secret Key
                                <span class="hint">starts with sk_live_ or sk_test_ — never share this</span>
                            </label>
                            <div class="secret-wrap">
                                <input
                                    v-model="directForm.secret_key"
                                    :type="showSecret ? 'text' : 'password'"
                                    class="field-input mono"
                                    :class="{ error: directErrors.secret_key }"
                                    placeholder="sk_live_..."
                                    autocomplete="off"
                                    spellcheck="false"
                                />
                                <button type="button" class="eye-btn" @click="showSecret = !showSecret">
                                    {{ showSecret ? '🙈' : '👁' }}
                                </button>
                            </div>
                            <div class="field-error" v-if="directErrors.secret_key">{{ directErrors.secret_key }}</div>
                        </div>

                        <div class="key-help">
                            <a href="https://dashboard.stripe.com/apikeys" target="_blank" rel="noopener" class="help-link">
                                Find your API keys in the Stripe Dashboard →
                            </a>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="save-btn" :disabled="savingDirect">
                                {{ savingDirect ? 'Verifying & Saving...' : 'Save Direct Keys' }}
                            </button>
                            <button type="button" class="cancel-btn" v-if="showDirectForm" @click="showDirectForm = false">Cancel</button>
                        </div>

                        <div class="security-note">
                            🔒 Your secret key is encrypted before being stored and is never displayed again after saving.
                        </div>
                    </form>
                </div>
            </div>

            <!-- ─── How payouts work ───────────────────────────────────── -->
            <div class="info-card">
                <h3 class="info-title">How Payments Work</h3>
                <div class="info-grid">
                    <div class="info-col">
                        <div class="info-head">Stripe Connect (Option 1)</div>
                        <ol class="info-steps">
                            <li>Customer pays at checkout</li>
                            <li>Stripe processes the card</li>
                            <li>Platform fee ({{ feePercent }}%) is deducted</li>
                            <li>Remaining funds transfer to your Stripe account</li>
                            <li>Stripe pays out to your bank on your payout schedule</li>
                        </ol>
                    </div>
                    <div class="info-col">
                        <div class="info-head">Direct Keys (Option 2)</div>
                        <ol class="info-steps">
                            <li>Customer pays at checkout</li>
                            <li>Stripe processes the card directly into your account</li>
                            <li>No platform fee deducted</li>
                            <li>Stripe pays out to your bank on your payout schedule</li>
                            <li>You handle disputes in your own dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'
import axios from 'axios'

const props = defineProps({
    connectId:    String,
    connectActive: Boolean,
    connectUrl:   String,
    directPubKey: String,
    directSecSet: Boolean,
    mode:         String,     // 'connect' | 'direct' | 'none'
    platformFee:  Number,
})

const page           = usePage()
const flash          = page.props.flash || {}
const showSecret     = ref(false)
const showDirectForm = ref(false)
const savingDirect   = ref(false)
const directErrors   = ref({})

const directForm = ref({
    publishable_key: '',
    secret_key: '',
})

const feePercent = computed(() => ((props.platformFee || 0.02) * 100).toFixed(0))

const bannerClass = computed(() => ({
    'connect': props.mode === 'connect' ? 'green' : '',
    'direct':  props.mode === 'direct'  ? 'blue'  : '',
    'none':    props.mode === 'none'    ? 'red'   : '',
}[props.mode] || 'red'))

const bannerIcon = computed(() => ({ connect: '✓', direct: '✓', none: '⚠' }[props.mode] || '⚠'))

const bannerTitle = computed(() => ({
    connect: 'Stripe Connect is active — payments are being collected.',
    direct:  'Direct Stripe keys active — payments go to your account.',
    none:    'No payment method configured — orders cannot be completed.',
}[props.mode] || ''))

const bannerSub = computed(() => ({
    connect: `Platform fee: ${feePercent.value}% per order. Connected account: ${props.connectId}`,
    direct:  `Publishable key: ${props.directPubKey}`,
    none:    'Set up Stripe Connect or enter your API keys below to start accepting orders.',
}[props.mode] || ''))

async function saveDirectKeys() {
    savingDirect.value = true
    directErrors.value = {}
    router.post(route('tenant.admin.settings.stripe.direct'), directForm.value, {
        onError: (errors) => { directErrors.value = errors },
        onFinish: () => { savingDirect.value = false },
        onSuccess: () => {
            directForm.value = { publishable_key: '', secret_key: '' }
            showDirectForm.value = false
        },
    })
}

function removeKeys() {
    if (!confirm('Remove your direct Stripe keys? You will need to reconnect Stripe to accept payments.')) return
    router.delete(route('tenant.admin.settings.stripe.direct.remove'))
}

function disconnect() {
    if (!confirm('Disconnect your Stripe account? You will not be able to accept payments until you reconnect.')) return
    router.post(route('platform.stripe.disconnect'))
}
</script>

<style scoped>
.stripe-page { max-width: 820px; display: flex; flex-direction: column; gap: 20px; }
.status-banner { display: flex; gap: 16px; align-items: flex-start; padding: 18px 22px; border-radius: 12px; }
.status-banner.green { background: #dcfce7; border: 1.5px solid #bbf7d0; }
.status-banner.blue  { background: #dbeafe; border: 1.5px solid #bfdbfe; }
.status-banner.red   { background: #fee2e2; border: 1.5px solid #fecaca; }
.banner-icon { font-size: 22px; flex-shrink: 0; margin-top: 2px; }
.banner-title { font-weight: 700; font-size: 15px; margin-bottom: 3px; }
.banner-sub { font-size: 13px; color: #374151; }
.flash { padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; }
.flash.success { background: #dcfce7; color: #15803d; }
.flash.error   { background: #fee2e2; color: #dc2626; }
.option-card { background: #fff; border-radius: 14px; border: 2px solid #e5e7eb; overflow: hidden; transition: border-color 0.2s; }
.option-card.active { border-color: #e85d04; }
.option-head { display: flex; align-items: center; gap: 10px; background: #f9fafb; padding: 12px 24px; border-bottom: 1px solid #e5e7eb; }
.option-num { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; }
.option-badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.option-badge.recommended { background: #dcfce7; color: #15803d; }
.option-badge.direct      { background: #dbeafe; color: #1d4ed8; }
.option-body { padding: 28px; }
.option-title { font-size: 19px; font-weight: 800; margin-bottom: 10px; }
.option-subtitle { font-size: 14px; font-weight: 500; color: #9ca3af; }
.option-desc { font-size: 14px; color: #374151; line-height: 1.6; margin-bottom: 20px; }
.connect-benefits { display: flex; flex-direction: column; gap: 6px; margin-bottom: 24px; }
.benefit { font-size: 13px; color: #374151; }
.benefit.warn { color: #92400e; }
.connect-status { display: flex; align-items: center; gap: 14px; background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 10px; padding: 16px 20px; flex-wrap: wrap; }
.cs-icon { width: 36px; height: 36px; background: #22c55e; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
.cs-info { flex: 1; }
.cs-title { font-weight: 700; font-size: 15px; }
.cs-id { font-size: 12px; color: #6b7280; font-family: monospace; margin-top: 2px; }
.reconnect-btn { background: #f3f4f6; color: #374151; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; }
.disconnect-btn { background: none; border: 1.5px solid #fecaca; color: #dc2626; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
.disconnect-btn:hover { background: #fee2e2; }
.stripe-connect-btn img { height: 40px; display: block; }
.connect-note { font-size: 12px; color: #9ca3af; margin-top: 10px; }
.direct-active { margin-bottom: 20px; }
.active-indicator { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #15803d; margin-bottom: 14px; }
.dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.dot.green { background: #22c55e; }
.key-preview { background: #f9fafb; border-radius: 8px; padding: 14px; margin-bottom: 14px; }
.key-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 13px; }
.key-label { color: #6b7280; font-weight: 600; }
.key-val { font-family: monospace; color: #374151; }
.key-val.masked { color: #9ca3af; }
.remove-keys-btn { background: #f3f4f6; color: #374151; border: 1.5px solid #e5e7eb; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; margin-right: 8px; }
.direct-form { margin-top: 20px; }
.field-group { margin-bottom: 18px; }
.field-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 6px; }
.hint { font-weight: 400; text-transform: none; letter-spacing: 0; color: #9ca3af; font-size: 11px; }
.field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; font-size: 14px; }
.field-input.mono { font-family: monospace; }
.field-input:focus { outline: none; border-color: #e85d04; }
.field-input.error { border-color: #ef4444; }
.field-error { color: #dc2626; font-size: 12px; margin-top: 4px; }
.secret-wrap { position: relative; display: flex; }
.secret-wrap .field-input { padding-right: 40px; }
.eye-btn { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; }
.key-help { margin-bottom: 16px; }
.help-link { font-size: 13px; color: #e85d04; text-decoration: none; font-weight: 600; }
.form-actions { display: flex; gap: 12px; align-items: center; margin-bottom: 14px; }
.save-btn { background: #e85d04; color: #fff; border: none; padding: 11px 28px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.save-btn:hover:not(:disabled) { background: #c44d03; }
.save-btn:disabled { opacity: 0.5; cursor: default; }
.cancel-btn { background: #f3f4f6; color: #374151; border: none; padding: 11px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.security-note { font-size: 12px; color: #6b7280; background: #f9fafb; border-radius: 8px; padding: 10px 14px; }
.info-card { background: #fff; border-radius: 14px; padding: 24px 28px; border: 1.5px solid #e5e7eb; }
.info-title { font-size: 15px; font-weight: 800; margin-bottom: 18px; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 600px) { .info-grid { grid-template-columns: 1fr; } }
.info-head { font-size: 13px; font-weight: 700; margin-bottom: 10px; color: #374151; }
.info-steps { font-size: 13px; color: #6b7280; line-height: 1.7; padding-left: 18px; }
.info-steps li { margin-bottom: 4px; }
</style>
