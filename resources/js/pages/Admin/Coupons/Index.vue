<template>
    <AdminLayout page-title="Coupons">
        <div class="page-header">
            <div>
                <h1 class="page-title">Coupons</h1>
                <p class="page-subtitle">Create discounts for lunch specials, first orders, free delivery, and limited promos.</p>
            </div>
            <button class="btn-primary" @click="startCreate">Add Coupon</button>
        </div>

        <div class="editor-card" v-if="editing">
            <div class="editor-header">
                <h2>{{ form.id ? 'Edit Coupon' : 'New Coupon' }}</h2>
                <button class="link-btn" @click="cancelEdit">Cancel</button>
            </div>
            <form class="coupon-form" @submit.prevent="save">
                <div class="field-group wide">
                    <label>Name</label>
                    <input v-model="form.name" type="text" required placeholder="Lunch special" />
                </div>
                <div class="field-group">
                    <label>Code</label>
                    <input v-model="form.code" type="text" required placeholder="LUNCH10" @input="form.code = form.code.toUpperCase()" />
                </div>
                <div class="field-group">
                    <label>Discount Type</label>
                    <select v-model="form.type">
                        <option value="percent">Percent off</option>
                        <option value="fixed">Dollar off</option>
                        <option value="free_delivery">Free delivery</option>
                    </select>
                </div>
                <div class="field-group" v-if="form.type !== 'free_delivery'">
                    <label>{{ form.type === 'percent' ? 'Percent' : 'Amount' }}</label>
                    <input v-model="form.value" type="number" min="0" step="0.01" required />
                </div>
                <div class="field-group">
                    <label>Minimum Order</label>
                    <input v-model="form.minimum_subtotal" type="number" min="0" step="0.01" />
                </div>
                <div class="field-group">
                    <label>Max Uses</label>
                    <input v-model="form.max_redemptions" type="number" min="1" placeholder="No limit" />
                </div>
                <div class="field-group">
                    <label>Starts</label>
                    <input v-model="form.starts_at" type="datetime-local" />
                </div>
                <div class="field-group">
                    <label>Ends</label>
                    <input v-model="form.ends_at" type="datetime-local" />
                </div>
                <label class="check-row">
                    <input v-model="form.is_active" type="checkbox" />
                    Active
                </label>
                <div class="actions">
                    <button class="btn-primary" type="submit">{{ form.id ? 'Save Coupon' : 'Create Coupon' }}</button>
                </div>
            </form>
        </div>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Discount</th>
                        <th>Minimum</th>
                        <th>Uses</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="coupon in coupons" :key="coupon.id">
                        <td><span class="code-pill">{{ coupon.code }}</span></td>
                        <td>{{ coupon.name }}</td>
                        <td>{{ discountLabel(coupon) }}</td>
                        <td>${{ fmt(coupon.minimum_subtotal) }}</td>
                        <td>{{ coupon.used_count }}<template v-if="coupon.max_redemptions"> / {{ coupon.max_redemptions }}</template></td>
                        <td><span class="status" :class="coupon.is_active ? 'active' : 'inactive'">{{ coupon.is_active ? 'Active' : 'Paused' }}</span></td>
                        <td class="row-actions">
                            <button class="link-btn" @click="startEdit(coupon)">Edit</button>
                            <button class="link-btn danger" @click="remove(coupon)">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="coupons.length === 0">
                        <td colspan="7" class="empty-cell">No coupons yet. Create one for a launch special or free delivery promo.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'

const props = defineProps({ coupons: { type: Array, default: () => [] } })
const editing = ref(false)

const blankForm = () => ({
    id: null,
    name: '',
    code: '',
    type: 'percent',
    value: 10,
    minimum_subtotal: 0,
    max_redemptions: '',
    starts_at: '',
    ends_at: '',
    is_active: true,
})

const form = reactive(blankForm())

function startCreate() {
    Object.assign(form, blankForm())
    editing.value = true
}

function startEdit(coupon) {
    Object.assign(form, {
        id: coupon.id,
        name: coupon.name,
        code: coupon.code,
        type: coupon.type,
        value: coupon.value,
        minimum_subtotal: coupon.minimum_subtotal,
        max_redemptions: coupon.max_redemptions || '',
        starts_at: toInputDate(coupon.starts_at),
        ends_at: toInputDate(coupon.ends_at),
        is_active: coupon.is_active,
    })
    editing.value = true
}

function cancelEdit() {
    editing.value = false
    Object.assign(form, blankForm())
}

function save() {
    const payload = { ...form, max_redemptions: form.max_redemptions || null }
    if (form.id) {
        router.put(route('tenant.admin.coupons.update', form.id), payload, { onSuccess: cancelEdit })
    } else {
        router.post(route('tenant.admin.coupons.store'), payload, { onSuccess: cancelEdit })
    }
}

function remove(coupon) {
    if (!confirm('Delete coupon ' + coupon.code + '?')) return
    router.delete(route('tenant.admin.coupons.destroy', coupon.id))
}

function discountLabel(coupon) {
    if (coupon.type === 'percent') return Number(coupon.value).toFixed(0) + '% off'
    if (coupon.type === 'fixed') return '$' + fmt(coupon.value) + ' off'
    return 'Free delivery'
}

function toInputDate(value) {
    if (!value) return ''
    return new Date(value).toISOString().slice(0, 16)
}

const fmt = (value) => Number(value || 0).toFixed(2)
</script>

<style scoped>
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
.page-title { margin: 0 0 4px; font-size: 22px; font-weight: 900; color: #17272b; }
.page-subtitle { margin: 0; color: #647477; font-size: 14px; }
.btn-primary { background: #e85d04; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-weight: 900; cursor: pointer; }
.editor-card, .card { background: #fff; border: 1px solid #e3ecea; border-radius: 10px; box-shadow: 0 1px 3px rgba(16, 24, 40, .05); }
.editor-card { padding: 20px; margin-bottom: 18px; }
.editor-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.editor-header h2 { margin: 0; font-size: 18px; }
.coupon-form { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; align-items: end; }
.field-group { display: flex; flex-direction: column; gap: 6px; }
.field-group.wide { grid-column: span 2; }
.field-group label, .check-row { font-size: 12px; font-weight: 900; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
.field-group input, .field-group select { border: 1.5px solid #dfe7e5; border-radius: 8px; padding: 9px 11px; font-size: 14px; }
.check-row { display: flex; align-items: center; gap: 8px; padding-bottom: 10px; }
.actions { display: flex; justify-content: flex-end; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.data-table th { background: #f9fafb; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 900; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
.data-table td { padding: 13px 16px; border-bottom: 1px solid #f3f4f6; }
.code-pill { display: inline-flex; padding: 5px 10px; border-radius: 999px; background: #fff7ed; color: #c2410c; font-weight: 900; letter-spacing: .04em; }
.status { display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 900; text-transform: uppercase; }
.status.active { background: #dcfce7; color: #15803d; }
.status.inactive { background: #f3f4f6; color: #6b7280; }
.row-actions { display: flex; gap: 12px; }
.link-btn { border: none; background: none; color: #e85d04; font-weight: 900; cursor: pointer; padding: 0; }
.link-btn.danger { color: #dc2626; }
.empty-cell { text-align: center; color: #7b8b8f; padding: 36px; }
@media (max-width: 900px) { .coupon-form { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 620px) { .page-header { flex-direction: column; } .coupon-form { grid-template-columns: 1fr; } .field-group.wide { grid-column: span 1; } }
</style>