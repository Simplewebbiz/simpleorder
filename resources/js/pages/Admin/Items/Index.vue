<template>
    <AdminLayout page-title="Menu Items">
        <div class="page-header">
            <h1 class="page-title">Menu Items</h1>
            <Link :href="route('tenant.admin.items.create')" class="btn-primary">+ Add Item</Link>
        </div>

        <!-- Category filter tabs -->
        <div class="category-tabs">
            <button
                class="cat-tab"
                :class="{ active: !activeCategory }"
                @click="activeCategory = null"
            >All</button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                class="cat-tab"
                :class="{ active: activeCategory === cat.id }"
                @click="activeCategory = cat.id"
            >{{ cat.name }}</button>
        </div>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in filteredItems" :key="item.id">
                        <td>
                            <img v-if="item.image" :src="item.image.permalink" class="thumb" :alt="item.name" />
                            <div v-else class="thumb-empty">🍽</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ item.name }}</div>
                            <div class="text-sm text-muted" v-if="item.options && item.options.length > 0">{{ item.options.length }} option group{{ item.options.length > 1 ? 's' : '' }}</div>
                        </td>
                        <td class="fw-bold">${{ Number(item.price).toFixed(2) }}</td>
                        <td><span class="badge" :class="item.type">{{ item.type }}</span></td>
                        <td class="text-muted">{{ item.categories?.map(c => c.name).join(', ') }}</td>
                        <td><span class="badge" :class="item.is_active ? 'green' : 'gray'">{{ item.is_active ? 'Active' : 'Hidden' }}</span></td>
                        <td class="actions-cell">
                            <Link :href="route('tenant.admin.items.edit', item.id)" class="action-link">Edit</Link>
                            <button class="action-link danger" @click="destroy(item)">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="filteredItems.length === 0">
                        <td colspan="7" class="empty-cell">No items. <Link :href="route('tenant.admin.items.create')" class="action-link">Add your first item →</Link></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'

const props = defineProps({ items: Array, categories: Array })
const activeCategory = ref(null)

const filteredItems = computed(() => {
    if (!activeCategory.value) return props.items
    return props.items.filter(item => item.categories?.some(c => c.id === activeCategory.value))
})

function destroy(item) {
    if (!confirm('Delete "' + item.name + '"?')) return
    router.delete(route('tenant.admin.items.destroy', item.id))
}
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.page-title { font-size: 20px; font-weight: 800; }
.btn-primary { background: #e85d04; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 14px; }
.category-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
.cat-tab { background: #fff; border: 1.5px solid #e5e7eb; border-radius: 20px; padding: 5px 16px; font-size: 13px; font-weight: 600; cursor: pointer; color: #6b7280; }
.cat-tab.active { background: #e85d04; color: #fff; border-color: #e85d04; }
.card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.data-table th { background: #f9fafb; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
.data-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.thumb { width: 44px; height: 44px; border-radius: 6px; object-fit: cover; }
.thumb-empty { width: 44px; height: 44px; border-radius: 6px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.fw-bold { font-weight: 700; }
.text-sm { font-size: 12px; }
.text-muted { color: #9ca3af; }
.badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.badge.green { background: #dcfce7; color: #15803d; }
.badge.gray { background: #f3f4f6; color: #6b7280; }
.badge.food { background: #fef3c7; color: #92400e; }
.badge.product { background: #dbeafe; color: #1d4ed8; }
.actions-cell { display: flex; gap: 12px; align-items: center; }
.action-link { color: #e85d04; text-decoration: none; font-weight: 600; font-size: 13px; background: none; border: none; cursor: pointer; padding: 0; }
.action-link.danger { color: #dc2626; }
.empty-cell { text-align: center; color: #9ca3af; padding: 40px; }
</style>
