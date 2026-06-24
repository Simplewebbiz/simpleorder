<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  user: Object,
  tenant: Object,
  stats: Object,
  tenantUrl: String,
  tenantAdminUrl: String,
})

// Safe defaults so the page doesn't break
const stats = computed(() => props.stats || {
  total_orders: 0,
  total_revenue: 0,
  menu_items: 0,
  is_stripe_connected: false
})

const tenantUrl = computed(() => props.tenantUrl || '#')
const tenantAdminUrl = computed(() => props.tenantAdminUrl || '#')
</script>

<template>
  <div class="platform-dashboard">
    <!-- Header -->
    <header class="flex justify-between items-center p-6 border-b">
      <div class="flex items-center gap-4">
        <h1 class="text-2xl font-bold">SimpleOrder</h1>
        <nav class="flex gap-4">
          <Link :href="route('platform.dashboard')" class="text-gray-600 hover:text-black">Dashboard</Link>
          <Link :href="route('platform.billing.index')" class="text-gray-600 hover:text-black">Billing</Link>
          <a v-if="tenantUrl" :href="tenantUrl" target="_blank" class="text-gray-600 hover:text-black">View Store</a>
        </nav>
      </div>

      <div class="flex items-center gap-4">
        <span>{{ user?.name }}</span>
        <form method="POST" :action="route('platform.logout')">
          <button type="submit" class="text-red-600 hover:underline">Logout</button>
        </form>
      </div>
    </header>

    <div class="p-6">
      <!-- Welcome -->
      <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h2 class="text-xl font-semibold">Welcome back, {{ user?.name }}!</h2>
        <p v-if="tenant" class="text-gray-600 mt-1">
          {{ tenant.name || 'Your store' }}
        </p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl shadow">
          <div class="text-sm text-gray-500">Total Orders</div>
          <div class="text-3xl font-bold">{{ stats.total_orders }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow">
          <div class="text-sm text-gray-500">Revenue</div>
          <div class="text-3xl font-bold">${{ stats.total_revenue }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow">
          <div class="text-sm text-gray-500">Menu Items</div>
          <div class="text-3xl font-bold">{{ stats.menu_items }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow">
          <div class="text-sm text-gray-500">Stripe</div>
          <div class="text-lg font-semibold" :class="stats.is_stripe_connected ? 'text-green-600' : 'text-orange-600'">
            {{ stats.is_stripe_connected ? 'Connected' : 'Not Connected' }}
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <a :href="tenantAdminUrl" class="p-4 border rounded-lg hover:bg-gray-50">
            Go to Admin Panel
          </a>
          <!-- Add more links as needed -->
        </div>
      </div>
    </div>
  </div>
</template>
