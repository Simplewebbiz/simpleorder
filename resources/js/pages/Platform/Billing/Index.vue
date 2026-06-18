<template>
    <PlatformLayout title="Billing">
        <div class="billing-page">

            <!-- Current subscription status -->
            <div class="billing-status" :class="statusClass">
                <div class="status-label">Subscription Status</div>
                <div class="status-value">{{ statusLabel }}</div>
                <div v-if="upcomingInvoice" class="upcoming-invoice">
                    Next payment of <strong>{{ upcomingInvoice.amount }}</strong> on {{ upcomingInvoice.date }}
                </div>
            </div>

            <!-- Stripe billing portal link (manage payment method, view invoices) -->
            <div class="portal-section">
                <a :href="route('dashboard.billing.portal')" class="btn-portal">
                    Manage Payment Method &amp; Invoices →
                </a>
                <p class="portal-help">Update your card, download invoices, and manage your subscription from the Stripe billing portal.</p>
            </div>

            <!-- Plan cards -->
            <div v-if="!tenant.plan || tenant.subscription_status !== 'active'" class="plans-section">
                <h2>Choose a Plan</h2>
                <div class="plans-grid">
                    <PlanCard
                        v-for="plan in plans"
                        :key="plan.id"
                        :plan="plan"
                        :current="tenant.plan_id === plan.id"
                        @subscribe="openSubscribeModal"
                    />
                </div>
            </div>

            <div v-else class="current-plan">
                <h2>Current Plan: {{ tenant.plan.name }}</h2>
                <p>{{ tenant.plan.description }}</p>
                <div class="plan-actions">
                    <button @click="showPlans = true" class="btn-secondary">Change Plan</button>
                    <button @click="confirmCancel" class="btn-danger">Cancel Subscription</button>
                </div>
            </div>

            <!-- Invoice history -->
            <div class="invoices-section" v-if="invoices.length">
                <h3>Invoice History</h3>
                <table class="invoice-table">
                    <thead>
                        <tr><th>Date</th><th>Amount</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="inv in invoices" :key="inv.id">
                            <td>{{ inv.date }}</td>
                            <td>{{ inv.total }}</td>
                            <td><span :class="'badge-' + inv.status">{{ inv.status }}</span></td>
                            <td><a :href="inv.pdf" target="_blank">Download PDF</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Subscribe modal -->
        <SubscribeModal
            v-if="subscribeModal"
            :plan="selectedPlan"
            @close="subscribeModal = false"
            @subscribed="$inertia.reload()"
        />
    </PlatformLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import PlatformLayout from '../../../components/Platform/Layout.vue'
import PlanCard from '../../../components/Platform/PlanCard.vue'
import SubscribeModal from '../../../components/Platform/SubscribeModal.vue'

const props = defineProps({
    tenant: Object,
    plans: Array,
    subscription: Object,
    upcomingInvoice: Object,
    invoices: Array,
})

const subscribeModal = ref(false)
const selectedPlan = ref(null)
const showPlans = ref(false)

const statusLabel = computed(() => ({
    active: 'Active',
    trialing: 'Free Trial',
    past_due: 'Payment Past Due',
    canceled: 'Cancelled',
    null: 'No Subscription',
}[props.tenant.subscription_status] || 'Unknown'))

const statusClass = computed(() => ({
    active: 'status-active',
    trialing: 'status-trial',
    past_due: 'status-danger',
    canceled: 'status-inactive',
}[props.tenant.subscription_status] || ''))

function openSubscribeModal(plan) {
    selectedPlan.value = plan
    subscribeModal.value = true
}

function confirmCancel() {
    if (confirm('Are you sure you want to cancel? You will retain access until the end of your billing period.')) {
        router.post(route('dashboard.billing.cancel'))
    }
}
</script>
