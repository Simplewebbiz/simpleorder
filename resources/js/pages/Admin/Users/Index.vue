<template>
    <AdminLayout page-title="Staff">
        <div class="page-header">
            <div>
                <h1 class="page-title">Staff Members</h1>
                <p class="page-sub">Give owners, managers, and kitchen staff the right access.</p>
            </div>
            <Link v-if="!showForm" :href="route('tenant.admin.users.create')" class="btn-primary">Add Staff</Link>
            <Link v-else :href="route('tenant.admin.users.index')" class="btn-secondary">Cancel</Link>
        </div>

        <div class="form-card" v-if="showForm">
            <div class="card-header">{{ editing ? 'Edit Staff Member' : 'Add Staff Member' }}</div>
            <form @submit.prevent="save" class="staff-form">
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Name</label>
                        <input v-model="form.name" type="text" class="field-input" required />
                        <div class="field-error" v-if="form.errors.name">{{ form.errors.name }}</div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Email</label>
                        <input v-model="form.email" type="email" class="field-input" required />
                        <div class="field-error" v-if="form.errors.email">{{ form.errors.email }}</div>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Phone</label>
                        <input v-model="form.phone" type="tel" class="field-input" />
                        <div class="field-error" v-if="form.errors.phone">{{ form.errors.phone }}</div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Password</label>
                        <input v-model="form.password" type="password" class="field-input" :required="!editing" autocomplete="new-password" />
                        <div class="field-help">{{ editing ? 'Leave blank to keep current password.' : 'Minimum 8 characters.' }}</div>
                        <div class="field-error" v-if="form.errors.password">{{ form.errors.password }}</div>
                    </div>
                </div>

                <div class="role-grid">
                    <label v-for="role in roles" :key="role.value" class="role-option" :class="{ selected: form.role === role.value }">
                        <input v-model="form.role" type="radio" :value="role.value" />
                        <span class="role-title">{{ role.label }}</span>
                        <span class="role-desc">{{ role.description }}</span>
                    </label>
                </div>
                <div class="field-error" v-if="form.errors.role">{{ form.errors.role }}</div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : (editing ? 'Save Staff Member' : 'Create Staff Member') }}
                    </button>
                    <Link :href="route('tenant.admin.users.index')" class="btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td class="fw-bold">{{ user.name }}</td>
                        <td class="text-muted">{{ user.email }}</td>
                        <td class="text-muted">{{ user.phone || '-' }}</td>
                        <td><span class="badge" :class="user.role">{{ roleLabel(user.role) }}</span></td>
                        <td class="actions-cell">
                            <Link :href="route('tenant.admin.users.edit', user.id)" class="action-link">Edit</Link>
                            <button
                                class="action-link danger"
                                @click="destroy(user)"
                            >Remove</button>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="5" class="empty-cell">No staff members yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'

const props = defineProps({
    users: Array,
    editing: Object,
    showForm: Boolean,
    roles: { type: Array, default: () => [] },
})

const editing = computed(() => props.editing || null)
const showForm = computed(() => props.showForm || Boolean(props.editing))

const form = useForm({
    name: editing.value?.name || '',
    email: editing.value?.email || '',
    phone: editing.value?.phone || '',
    role: editing.value?.role || 'fulfillment',
    password: '',
})

function save() {
    if (editing.value) {
        form.put(route('tenant.admin.users.update', editing.value.id))
    } else {
        form.post(route('tenant.admin.users.store'))
    }
}

function destroy(user) {
    if (!confirm('Remove ' + user.name + '?')) return
    router.delete(route('tenant.admin.users.destroy', user.id))
}

function roleLabel(role) {
    return props.roles.find(candidate => candidate.value === role)?.label || role
}
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
.page-title { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
.page-sub { color: #6b7280; font-size: 14px; }
.btn-primary, .btn-secondary { border: none; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 800; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.btn-primary { background: #e85d04; color: #fff; }
.btn-secondary { background: #f3f4f6; color: #374151; }
.form-card, .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
.form-card { padding: 22px; margin-bottom: 20px; }
.card-header { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin-bottom: 18px; }
.staff-form { display: flex; flex-direction: column; gap: 16px; }
.field-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
.field-group { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
.field-input { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; font-size: 14px; }
.field-input:focus { outline: none; border-color: #e85d04; }
.field-help { font-size: 12px; color: #9ca3af; }
.field-error { color: #dc2626; font-size: 12px; font-weight: 700; }
.role-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.role-option { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 14px; cursor: pointer; display: flex; flex-direction: column; gap: 6px; }
.role-option.selected { border-color: #e85d04; box-shadow: 0 0 0 3px rgba(232,93,4,.12); }
.role-option input { margin: 0; }
.role-title { font-weight: 900; color: #111827; }
.role-desc { color: #6b7280; font-size: 12px; line-height: 1.45; }
.form-actions { display: flex; gap: 10px; align-items: center; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.data-table th { background: #f9fafb; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
.data-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.fw-bold { font-weight: 700; }
.text-muted { color: #9ca3af; }
.badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
.badge.owner { background: #fef3c7; color: #92400e; }
.badge.manager { background: #dbeafe; color: #1d4ed8; }
.badge.fulfillment { background: #dcfce7; color: #15803d; }
.actions-cell { display: flex; gap: 12px; align-items: center; }
.action-link { color: #e85d04; text-decoration: none; font-weight: 700; font-size: 13px; background: none; border: none; cursor: pointer; padding: 0; }
.action-link.danger { color: #dc2626; }
.empty-cell { text-align: center; color: #9ca3af; padding: 40px; }
@media (max-width: 860px) { .field-row, .role-grid { grid-template-columns: 1fr; } .page-header { align-items: flex-start; flex-direction: column; } }
</style>