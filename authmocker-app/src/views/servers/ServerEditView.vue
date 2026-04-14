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
  <div style="max-width: 720px;" v-if="store.currentServer">
    <div class="mb-8">
      <v-btn variant="text" :to="`/servers/${serverId}`" prepend-icon="mdi-arrow-left" size="small" class="mb-2">
        Back to Server
      </v-btn>
      <h1 class="text-h4 font-weight-bold">Edit Server</h1>
    </div>

    <v-form @submit.prevent="handleSubmit">
      <v-alert v-if="error" type="error" variant="tonal" closable class="mb-6" @click:close="error = ''">
        {{ error }}
      </v-alert>

      <v-row class="mb-2">
        <v-col cols="12" sm="6">
          <v-text-field
            v-model="name"
            label="Server Name"
            variant="outlined"
            density="comfortable"
            required
          />
        </v-col>
        <v-col cols="12" sm="6">
          <v-text-field
            v-model="slug"
            label="Slug"
            variant="outlined"
            density="comfortable"
            required
          />
        </v-col>
      </v-row>

      <v-textarea
        v-model="description"
        label="Description"
        variant="outlined"
        density="comfortable"
        rows="2"
        no-resize
        class="mb-2"
      />

      <v-switch
        v-model="isActive"
        label="Server is active"
        color="primary"
        class="mb-4"
      />

      <v-card variant="outlined" class="mb-6 pa-5">
        <div class="text-subtitle-2 font-weight-medium mb-3">Auth Configuration</div>
        <AuthConfigForm :auth-type="store.currentServer.auth_type" v-model="config" />
      </v-card>

      <v-btn
        type="submit"
        color="primary"
        block
        size="large"
        :loading="loading"
      >
        Save Changes
      </v-btn>
    </v-form>
  </div>
</template>
