<template>
    <AdminLayout page-title="Categories">
        <div class="page-header">
            <h1 class="page-title">Categories</h1>
            <Link :href="route('tenant.admin.categories.create')" class="btn-primary">+ Add Category</Link>
        </div>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Sort</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="cat in categories" :key="cat.id">
                        <td>
                            <img v-if="cat.image" :src="cat.image.permalink" class="thumb" :alt="cat.name" />
                            <div v-else class="thumb-empty">{{ cat.name.charAt(0) }}</div>
                        </td>
                        <td class="fw-bold">{{ cat.name }}</td>
                        <td class="text-muted">{{ cat.items_count }}</td>
                        <td>
                            <span class="badge" :class="cat.is_active ? 'green' : 'gray'">{{ cat.is_active ? 'Active' : 'Hidden' }}</span>
                        </td>
                        <td class="text-muted">{{ cat.sort }}</td>
                        <td class="actions-cell">
                            <Link :href="route('tenant.admin.categories.edit', cat.id)" class="action-link">Edit</Link>
                            <button class="action-link danger" @click="destroy(cat)">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="6" class="empty-cell">No categories yet. <Link :href="route('tenant.admin.categories.create')" class="action-link">Create one →</Link></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'

const props = defineProps({ categories: Array })

function destroy(cat) {
    if (!confirm('Delete "' + cat.name + '"? This cannot be undone.')) return
    router.delete(route('tenant.admin.categories.destroy', cat.id))
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
.thumb { width: 48px; height: 48px; border-radius: 6px; object-fit: cover; }
.thumb-empty { width: 48px; height: 48px; border-radius: 6px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; color: #d1d5db; }
.fw-bold { font-weight: 700; }
.text-muted { color: #9ca3af; }
.badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.badge.green { background: #dcfce7; color: #15803d; }
.badge.gray { background: #f3f4f6; color: #6b7280; }
.actions-cell { display: flex; gap: 12px; align-items: center; }
.action-link { color: #e85d04; text-decoration: none; font-weight: 600; font-size: 13px; background: none; border: none; cursor: pointer; padding: 0; }
.action-link.danger { color: #dc2626; }
.empty-cell { text-align: center; color: #9ca3af; padding: 40px; }
</style>
