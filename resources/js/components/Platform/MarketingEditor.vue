<template>
    <div class="wysiwyg">
        <div class="toolbar">
            <button type="button" @click="format('bold')">B</button>
            <button type="button" @click="format('italic')">I</button>
            <button type="button" @click="format('underline')">U</button>
            <button type="button" @click="block('h2')">H2</button>
            <button type="button" @click="block('h3')">H3</button>
            <button type="button" @click="format('insertUnorderedList')">List</button>
            <button type="button" @click="format('insertOrderedList')">1. List</button>
            <button type="button" @click="addLink">Link</button>
            <button type="button" @click="addImage">Image URL</button>
            <button type="button" @click="format('removeFormat')">Clear</button>
        </div>

        <div ref="editor" class="editor" contenteditable="true" @input="sync" @blur="sync"></div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'

const props = defineProps({ modelValue: { type: String, default: '' } })
const emit = defineEmits(['update:modelValue'])
const editor = ref(null)
const isSettingHtml = ref(false)

onMounted(() => setHtml(props.modelValue || ''))

watch(() => props.modelValue, value => {
    if (!editor.value || editor.value.innerHTML === value || isSettingHtml.value) return
    setHtml(value || '')
})

function setHtml(html) {
    if (!editor.value) return
    isSettingHtml.value = true
    editor.value.innerHTML = html
    isSettingHtml.value = false
}

function sync() {
    if (!editor.value) return
    emit('update:modelValue', editor.value.innerHTML)
}

function format(command, value = null) {
    editor.value?.focus()
    document.execCommand(command, false, value)
    sync()
}

function block(tag) {
    format('formatBlock', tag)
}

function addLink() {
    const url = window.prompt('Paste the website address')
    if (!url) return
    format('createLink', url)
}

function addImage() {
    const url = window.prompt('Paste the image address')
    if (!url) return
    editor.value?.focus()
    document.execCommand('insertImage', false, url)
    sync()
}
</script>

<style scoped>
.wysiwyg { border: 1.5px solid #dbe4e8; border-radius: 8px; overflow: hidden; background: #fff; }
.toolbar { display: flex; flex-wrap: wrap; gap: 6px; padding: 8px; border-bottom: 1px solid #edf2f4; background: #f8fbfb; }
.toolbar button { border: 1px solid #dbe4e8; background: #fff; color: #243b3f; border-radius: 6px; padding: 6px 10px; font-size: 12px; font-weight: 700; cursor: pointer; }
.toolbar button:hover { border-color: #ff7a59; color: #d94b26; }
.editor { min-height: 220px; padding: 16px; color: #243b3f; line-height: 1.7; outline: none; }
.editor :deep(h2) { font-size: 24px; margin: 0 0 12px; }
.editor :deep(h3) { font-size: 19px; margin: 18px 0 8px; }
.editor :deep(p) { margin: 0 0 14px; }
.editor :deep(img) { max-width: 100%; border-radius: 8px; margin: 12px 0; }
.editor :deep(a) { color: #0f766e; font-weight: 700; }
</style>