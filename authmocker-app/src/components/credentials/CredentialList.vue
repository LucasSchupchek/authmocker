<script setup lang="ts">
import { ref } from 'vue'
import { useServersStore } from '../../stores/servers'
import CredentialForm from './CredentialForm.vue'
import type { MockCredential } from '../../types'

const props = defineProps<{
  credentials: MockCredential[]
  authType: string
  serverId: string
}>()

const store = useServersStore()
const deletingId = ref<string | null>(null)
const editingCredential = ref<MockCredential | null>(null)
const showEditDialog = ref(false)

function openEdit(credential: MockCredential) {
  editingCredential.value = credential
  showEditDialog.value = true
}

function onUpdated() {
  showEditDialog.value = false
  editingCredential.value = null
}

async function handleDelete(id: string) {
  if (!confirm('Delete this credential?')) return
  deletingId.value = id
  try {
    await store.deleteCredential(id)
  } finally {
    deletingId.value = null
  }
}
</script>

<template>
  <div v-if="credentials.length === 0" class="text-center py-8 text-medium-emphasis">
    No credentials configured yet. Add one to get started.
  </div>

  <v-list v-else lines="two">
    <v-list-item
      v-for="cred in credentials"
      :key="cred.id"
      class="px-4"
    >
      <v-list-item-title class="text-body-2 font-weight-medium">
        {{ cred.label }}
      </v-list-item-title>

      <v-list-item-subtitle>
        <span v-if="cred.profile?.name">{{ cred.profile.name }}</span>
        <span v-if="cred.profile?.name && cred.profile?.role"> &middot; </span>
        <span v-if="cred.profile?.role">{{ cred.profile.role }}</span>
        <v-chip
          size="x-small"
          :color="cred.is_active ? 'success' : 'grey'"
          variant="tonal"
          class="ml-2"
        >
          {{ cred.is_active ? 'active' : 'inactive' }}
        </v-chip>
      </v-list-item-subtitle>

      <template #append>
        <v-btn
          icon="mdi-pencil"
          size="small"
          variant="text"
          class="mr-1"
          @click="openEdit(cred)"
        />
        <v-btn
          icon="mdi-delete"
          size="small"
          variant="text"
          color="error"
          :loading="deletingId === cred.id"
          @click="handleDelete(cred.id)"
        />
      </template>
    </v-list-item>
  </v-list>

  <!-- Edit Dialog -->
  <v-dialog v-model="showEditDialog" max-width="640">
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between">
        <span>Edit Credential</span>
        <v-btn icon="mdi-close" variant="text" size="small" @click="showEditDialog = false" />
      </v-card-title>
      <v-card-text>
        <CredentialForm
          v-if="editingCredential"
          :server-id="serverId"
          :auth-type="authType"
          :credential="editingCredential"
          @updated="onUpdated"
        />
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
