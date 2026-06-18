<template>
    <AdminLayout page-title="Staff">
        <div class="page-header">
            <h1 class="page-title">Staff Members</h1>
            <Link :href="route('tenant.admin.users.create')" class="btn-primary">+ Add Staff</Link>
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
                        <td class="text-muted">{{ user.phone || '—' }}</td>
                        <td><span class="badge" :class="user.role">{{ user.role }}</span></td>
                        <td class="actions-cell">
                            <Link :href="route('tenant.admin.users.edit', user.id)" class="action-link">Edit</Link>
                            <button
                                class="action-link danger"
                                v-if="user.role !== 'owner'"
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
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'

const props = defineProps({ users: Array })

function destroy(user) {
    if (!confirm('Remove ' + user.name + '?')) return
    router.delete(route('tenant.admin.users.destroy', user.id))
}
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 20px; font-weight: 800; }
.btn-primary { background: #e85d04; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 14px; }
.card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.data-table th { background: #f9fafb; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
.data-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.fw-bold { font-weight: 700; }
.text-muted { color: #9ca3af; }
.badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.badge.owner { background: #fef3c7; color: #92400e; }
.badge.manager { background: #dbeafe; color: #1d4ed8; }
.badge.fulfillment { background: #dcfce7; color: #15803d; }
.badge.customer { background: #f3f4f6; color: #6b7280; }
.actions-cell { display: flex; gap: 12px; align-items: center; }
.action-link { color: #e85d04; text-decoration: none; font-weight: 600; font-size: 13px; background: none; border: none; cursor: pointer; padding: 0; }
.action-link.danger { color: #dc2626; }
.empty-cell { text-align: center; color: #9ca3af; padding: 40px; }
</style>
