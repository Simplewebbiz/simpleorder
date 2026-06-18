<template>
    <div class="category-view">
        <div class="container">
            <div class="cat-header">
                <button class="back-btn" @click="$emit('back')">← Back</button>
                <div class="cat-hero" v-if="category.image">
                    <img :src="category.image.url" :alt="category.name" class="cat-hero-img" />
                </div>
                <div class="cat-title-wrap">
                    <h1 class="cat-title">{{ category.name }}</h1>
                    <p class="cat-desc" v-if="category.description" v-html="category.description"></p>
                </div>
            </div>

            <div class="items-grid">
                <button
                    v-for="item in category.items"
                    :key="item.id"
                    class="item-card"
                    :class="{ 'out-of-stock': !item.is_active }"
                    @click="selectItem(item)"
                    :disabled="!item.is_active"
                >
                    <div class="item-image-wrap" v-if="item.image">
                        <img :src="item.image.permalink" :alt="item.name" class="item-image" />
                    </div>
                    <div class="item-image-placeholder" v-else>
                        <span>🍽</span>
                    </div>

                    <div class="item-info">
                        <div class="item-name">{{ item.name }}</div>
                        <div class="item-desc" v-if="item.description">{{ stripTags(item.description) }}</div>
                        <div class="item-footer">
                            <span class="item-price">${{ Number(item.price).toFixed(2) }}</span>
                            <span class="item-add" v-if="item.is_active">+ Add</span>
                            <span class="item-unavailable" v-else>Unavailable</span>
                        </div>
                    </div>
                </button>
            </div>

            <div class="empty-state" v-if="!category.items || category.items.length === 0">
                <div class="empty-icon">🍽</div>
                <p>No items in this category yet.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useCartStore } from '../../stores/cart'

const props = defineProps({
    category: Object,
    settings: Object,
})

const emit = defineEmits(['back', 'add-item'])
const cart = useCartStore()

function selectItem(item) {
    if (!item.is_active) return
    if (item.options && item.options.length > 0) {
        cart.openAddItem(item, props.settings)
    } else {
        emit('add-item', item, null)
    }
}

function stripTags(html) {
    if (!html) return ''
    const s = html.replace(/<[^>]*>/g, '')
    return s.length > 100 ? s.substring(0, 100) + '…' : s
}
</script>

<style scoped>
.category-view { padding: 32px 0 64px; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.cat-header { margin-bottom: 32px; }
.back-btn { background: none; border: none; color: #e85d04; font-weight: 700; font-size: 14px; cursor: pointer; padding: 0 0 16px; }
.cat-hero-img { width: 100%; max-height: 240px; object-fit: cover; border-radius: 12px; margin-bottom: 16px; }
.cat-title { font-size: 28px; font-weight: 800; margin-bottom: 6px; }
.cat-desc { color: #6b7280; font-size: 15px; line-height: 1.6; }
.items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.item-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; text-align: left; cursor: pointer; padding: 0; transition: all 0.2s; }
.item-card:not(.out-of-stock):hover { border-color: #e85d04; box-shadow: 0 4px 16px rgba(232,93,4,.12); transform: translateY(-2px); }
.item-card.out-of-stock { opacity: 0.6; cursor: default; }
.item-image-wrap { height: 160px; overflow: hidden; }
.item-image { width: 100%; height: 100%; object-fit: cover; }
.item-image-placeholder { height: 100px; background: #f9fafb; display: flex; align-items: center; justify-content: center; font-size: 36px; color: #d1d5db; }
.item-info { padding: 16px; }
.item-name { font-size: 16px; font-weight: 700; margin-bottom: 4px; color: #1a1a1a; }
.item-desc { font-size: 13px; color: #6b7280; line-height: 1.4; margin-bottom: 10px; }
.item-footer { display: flex; align-items: center; justify-content: space-between; }
.item-price { font-size: 16px; font-weight: 800; color: #1a1a1a; }
.item-add { background: #e85d04; color: #fff; border-radius: 6px; padding: 5px 14px; font-size: 13px; font-weight: 700; }
.item-unavailable { font-size: 12px; color: #9ca3af; }
.empty-state { text-align: center; padding: 80px 20px; color: #9ca3af; }
.empty-icon { font-size: 48px; margin-bottom: 12px; }
</style>
