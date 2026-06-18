<template>
    <AdminLayout page-title="Media Library">
        <div class="media-header">
            <div class="filter-tabs">
                <button v-for="f in folders" :key="f" class="folder-tab" :class="{ active: folder === f }" @click="folder = f">
                    {{ f === 'all' ? 'All' : capitalize(f) }}
                </button>
            </div>
            <button class="btn-primary" @click="$refs.uploadInput.click()">Upload Images</button>
            <input ref="uploadInput" type="file" multiple accept="image/*" hidden @change="upload" />
        </div>

        <div class="upload-progress" v-if="uploading">
            Uploading... {{ uploadProgress }}%
        </div>

        <div class="media-grid">
            <div
                v-for="media in filteredMedia"
                :key="media.id"
                class="media-item"
                :class="{ selected: selected.includes(media.id) }"
                @click="toggleSelect(media.id)"
            >
                <img :src="media.permalink" :alt="media.alt || media.src" class="media-thumb" />
                <div class="media-info">
                    <div class="media-name">{{ media.src }}</div>
                    <div class="media-folder" v-if="media.folder">{{ media.folder }}</div>
                </div>
                <div class="media-check" v-if="selected.includes(media.id)">✓</div>
            </div>
            <div class="empty-state" v-if="filteredMedia.length === 0">
                <div class="empty-icon">🖼</div>
                <p>No media files yet. Upload some images!</p>
            </div>
        </div>

        <div class="media-actions" v-if="selected.length > 0">
            <span>{{ selected.length }} selected</span>
            <button class="btn-danger" @click="deleteSelected">Delete Selected</button>
            <button class="btn-secondary" @click="selected = []">Deselect All</button>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '../../../components/Admin/Layout.vue'
import axios from 'axios'

const props = defineProps({ media: Array })

const folder = ref('all')
const selected = ref([])
const uploading = ref(false)
const uploadProgress = ref(0)

const folders = computed(() => {
    const fs = new Set(['all'])
    for (const m of props.media || []) if (m.folder) fs.add(m.folder)
    return [...fs]
})

const filteredMedia = computed(() => {
    if (folder.value === 'all') return props.media || []
    return (props.media || []).filter(m => m.folder === folder.value)
})

function toggleSelect(id) {
    const i = selected.value.indexOf(id)
    if (i >= 0) selected.value.splice(i, 1)
    else selected.value.push(id)
}

async function upload(e) {
    const files = [...e.target.files]
    if (!files.length) return
    uploading.value = true
    uploadProgress.value = 0
    for (let i = 0; i < files.length; i++) {
        const fd = new FormData()
        fd.append('file', files[i])
        fd.append('folder', folder.value === 'all' ? 'general' : folder.value)
        await axios.post(route('tenant.admin.media.store'), fd)
        uploadProgress.value = Math.round(((i + 1) / files.length) * 100)
    }
    uploading.value = false
    router.reload({ only: ['media'] })
    e.target.value = ''
}

async function deleteSelected() {
    if (!confirm('Delete ' + selected.value.length + ' file(s)?')) return
    for (const id of selected.value) {
        await axios.delete(route('tenant.admin.media.destroy', id))
    }
    selected.value = []
    router.reload({ only: ['media'] })
}

const capitalize = (s) => s.charAt(0).toUpperCase() + s.slice(1)
</script>

<style scoped>
.media-header { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
.filter-tabs { display: flex; gap: 6px; flex: 1; flex-wrap: wrap; }
.folder-tab { background: #fff; border: 1.5px solid #e5e7eb; border-radius: 20px; padding: 5px 16px; font-size: 13px; font-weight: 600; cursor: pointer; color: #6b7280; }
.folder-tab.active { background: #e85d04; color: #fff; border-color: #e85d04; }
.btn-primary { background: #e85d04; color: #fff; border: none; padding: 9px 20px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; }
.btn-danger { background: #dc2626; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
.btn-secondary { background: #f3f4f6; color: #374151; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
.upload-progress { background: #dbeafe; color: #1d4ed8; padding: 10px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-weight: 600; }
.media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
.media-item { border: 2px solid #e5e7eb; border-radius: 10px; overflow: hidden; cursor: pointer; position: relative; transition: border-color 0.15s; }
.media-item:hover { border-color: #e85d04; }
.media-item.selected { border-color: #e85d04; background: #fff7f3; }
.media-thumb { width: 100%; height: 140px; object-fit: cover; display: block; }
.media-info { padding: 8px 10px; }
.media-name { font-size: 11px; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.media-folder { font-size: 10px; color: #9ca3af; text-transform: uppercase; }
.media-check { position: absolute; top: 8px; right: 8px; width: 24px; height: 24px; background: #e85d04; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }
.empty-state { grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #9ca3af; }
.empty-icon { font-size: 48px; margin-bottom: 12px; }
.media-actions { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #1a1a1a; color: #fff; padding: 12px 24px; border-radius: 12px; display: flex; align-items: center; gap: 14px; font-size: 14px; font-weight: 600; box-shadow: 0 4px 20px rgba(0,0,0,.3); z-index: 50; }
</style>
