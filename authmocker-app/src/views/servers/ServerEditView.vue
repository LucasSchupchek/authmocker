<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useServersStore } from '../../stores/servers'
import AuthConfigForm from '../../components/servers/AuthConfigForm.vue'

const route = useRoute()
const router = useRouter()
const store = useServersStore()

const name = ref('')
const slug = ref('')
const config = ref<Record<string, any>>({})
const description = ref('')
const isActive = ref(true)
const error = ref('')
const loading = ref(false)

const serverId = route.params.id as string

onMounted(async () => {
  await store.fetchServer(serverId)
  if (store.currentServer) {
    name.value = store.currentServer.name
    slug.value = store.currentServer.slug
    config.value = { ...store.currentServer.config }
    description.value = store.currentServer.description || ''
    isActive.value = store.currentServer.is_active
  }
})

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    await store.updateServer(serverId, {
      name: name.value,
      slug: slug.value,
      config: config.value,
      description: description.value || undefined,
      is_active: isActive.value,
    })
    router.push(`/servers/${serverId}`)
  } catch (e: any) {
    error.value = e.response?.data?.message || e.message || 'Failed to update server'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl" v-if="store.currentServer">
    <div class="mb-8">
      <RouterLink :to="`/servers/${serverId}`" class="text-gray-400 hover:text-white text-sm transition-colors">
        &larr; Back to Server
      </RouterLink>
      <h1 class="text-2xl font-bold text-white mt-2">Edit Server</h1>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div v-if="error" class="p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm">
        {{ error }}
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-1">Server Name</label>
          <input
            v-model="name"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-1">Slug</label>
          <input
            v-model="slug"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
        <textarea
          v-model="description"
          rows="2"
          class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
      </div>

      <div class="flex items-center gap-3">
        <input
          id="active"
          v-model="isActive"
          type="checkbox"
          class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-indigo-500 focus:ring-indigo-500"
        />
        <label for="active" class="text-sm text-gray-300">Server is active</label>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-3">Auth Configuration</label>
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5">
          <AuthConfigForm :auth-type="store.currentServer.auth_type" v-model="config" />
        </div>
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full px-4 py-2.5 bg-indigo-500 hover:bg-indigo-600 disabled:opacity-50 text-white font-medium rounded-lg transition-colors"
      >
        {{ loading ? 'Saving...' : 'Save Changes' }}
      </button>
    </form>
  </div>
</template>
