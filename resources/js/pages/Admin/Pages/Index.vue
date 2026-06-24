<template>
    <AdminLayout page-title="Website Pages">
        <div class="page-header">
            <div>
                <h1 class="page-title">Website Pages</h1>
                <p class="page-subtitle">Edit the pages customers see on your restaurant website.</p>
            </div>
        </div>

        <div class="pages-grid">
            <div v-for="page in pages" :key="page.id" class="page-card">
                <div class="page-image" :style="page.hero ? { backgroundImage: 'url(' + page.hero.permalink + ')' } : {}">
                    <span v-if="!page.hero">{{ page.title.charAt(0) }}</span>
                </div>
                <div class="page-body">
                    <div class="page-meta">/{{ page.slug }}</div>
                    <h2>{{ page.title }}</h2>
                    <p>{{ page.summary || 'No summary yet.' }}</p>
                    <div class="page-actions">
                        <Link :href="route('tenant.admin.pages.edit', page.id)" class="btn-primary">Edit Page</Link>
                        <a :href="page.slug === 'home' ? '/' : '/' + page.slug" target="_blank" class="btn-secondary">View</a>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'

defineProps({ pages: Array })
</script>

<style scoped>
.page-header { margin-bottom: 20px; }
.page-title { font-size: 22px; font-weight: 800; color: #17272b; }
.page-subtitle { color: #6b7c80; margin-top: 4px; }
.pages-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
.page-card { background: #fff; border: 1px solid #e5edf0; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 24px rgba(31, 45, 48, 0.06); }
.page-image { height: 150px; background: linear-gradient(135deg, #ffedd5, #ccfbf1); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; color: #0f766e; font-size: 42px; font-weight: 900; }
.page-body { padding: 18px; }
.page-meta { color: #ef6c3e; font-size: 12px; font-weight: 800; margin-bottom: 8px; }
h2 { font-size: 18px; color: #17272b; margin-bottom: 8px; }
p { color: #657477; min-height: 44px; line-height: 1.5; }
.page-actions { display: flex; gap: 10px; margin-top: 16px; }
.btn-primary, .btn-secondary { border-radius: 7px; padding: 9px 14px; font-weight: 800; font-size: 13px; text-decoration: none; }
.btn-primary { background: #ff7a59; color: #fff; }
.btn-secondary { background: #edf7f4; color: #0f766e; }
</style>