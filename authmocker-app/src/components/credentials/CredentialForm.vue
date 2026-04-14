<script setup lang="ts">
import { ref, computed } from 'vue'
import { useServersStore } from '../../stores/servers'
import CredentialConfigFields from './CredentialConfigFields.vue'
import type { MockCredential, CredentialProfile } from '../../types'

const props = defineProps<{
  serverId: string
  authType: string
  credential?: MockCredential
}>()

const emit = defineEmits<{
  (e: 'created'): void
  (e: 'updated'): void
}>()

const store = useServersStore()

const isEdit = computed(() => !!props.credential)

const label = ref(props.credential?.label || '')
const isActive = ref(props.credential?.is_active ?? true)
const credentialData = ref<Record<string, any>>(props.credential?.credentials ? { ...props.credential.credentials } : {})
const profileName = ref(props.credential?.profile?.name || '')
const profileEmail = ref(props.credential?.profile?.email || '')
const profileRole = ref(props.credential?.profile?.role || '')
const profileCustom = ref(
  props.credential?.profile?.custom ? JSON.stringify(props.credential.profile.custom, null, 2) : ''
)

const error = ref('')
const loading = ref(false)

async function handleSubmit() {
  error.value = ''
  loading.value = true

  let customParsed: Record<string, any> | undefined
  if (profileCustom.value.trim()) {
    try {
      customParsed = JSON.parse(profileCustom.value)
    } catch {
      error.value = 'Custom Claims must be valid JSON'
      loading.value = false
      return
    }
  }

  const profile: CredentialProfile = {}
  if (profileName.value) profile.name = profileName.value
  if (profileEmail.value) profile.email = profileEmail.value
  if (profileRole.value) profile.role = profileRole.value
  if (customParsed) profile.custom = customParsed

  const payload = {
    label: label.value,
    is_active: isActive.value,
    credentials: credentialData.value,
    profile,
  }

  try {
    if (isEdit.value && props.credential) {
      await store.updateCredential(props.credential.id, payload)
      emit('updated')
    } else {
      await store.createCredential(props.serverId, payload)
      // Reset form
      label.value = ''
      isActive.value = true
      credentialData.value = {}
      profileName.value = ''
      profileEmail.value = ''
      profileRole.value = ''
      profileCustom.value = ''
      emit('created')
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || e.message || 'Failed to save credential'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <v-card class="mb-4">
    <v-card-text>
      <v-form @submit.prevent="handleSubmit">
        <v-alert v-if="error" type="error" variant="tonal" density="compact" closable class="mb-4" @click:close="error = ''">
          {{ error }}
        </v-alert>

        <v-text-field
          v-model="label"
          label="Label"
          placeholder="e.g. Admin User, Test Client"
          variant="outlined"
          density="comfortable"
          required
          class="mb-2"
        />

        <v-switch
          v-model="isActive"
          label="Active"
          color="success"
          density="comfortable"
          class="mb-2"
        />

        <!-- Credential fields -->
        <div class="text-subtitle-2 font-weight-medium mb-3">Credential Data</div>
        <CredentialConfigFields
          :auth-type="authType"
          v-model="credentialData"
        />

        <!-- Profile section -->
        <v-card variant="outlined" class="mb-4 pa-4">
          <div class="text-subtitle-2 font-weight-medium mb-3">Profile</div>
          <v-text-field
            v-model="profileName"
            label="Name"
            variant="outlined"
            density="comfortable"
            class="mb-2"
          />
          <v-text-field
            v-model="profileEmail"
            label="Email"
            variant="outlined"
            density="comfortable"
            class="mb-2"
          />
          <v-text-field
            v-model="profileRole"
            label="Role"
            variant="outlined"
            density="comfortable"
            class="mb-2"
          />
          <v-textarea
            v-model="profileCustom"
            label="Custom Claims (JSON)"
            variant="outlined"
            density="comfortable"
            rows="3"
            style="font-family: monospace"
          />
        </v-card>

        <v-btn
          type="submit"
          color="primary"
          :loading="loading"
          :disabled="loading || !label"
        >
          {{ isEdit ? 'Update Credential' : 'Add Credential' }}
        </v-btn>
      </v-form>
    </v-card-text>
  </v-card>
</template>
