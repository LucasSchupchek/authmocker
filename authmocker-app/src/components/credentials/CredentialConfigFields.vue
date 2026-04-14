<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ authType: string; modelValue: Record<string, any> }>()
const emit = defineEmits<{ (e: 'update:modelValue', value: Record<string, any>): void }>()

const config = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
})

function updateField(key: string, value: any) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}

const fields = computed(() => {
  switch (props.authType) {
    case 'basic_auth':
      return [
        { key: 'username', label: 'Username', type: 'text' },
        { key: 'password', label: 'Password', type: 'text' },
      ]
    case 'api_key':
      return [
        { key: 'key', label: 'API Key', type: 'text' },
      ]
    case 'jwt':
      return [
        { key: 'sub', label: 'Subject Identifier', type: 'text' },
      ]
    case 'oauth2':
      return [
        { key: 'client_id', label: 'Client ID', type: 'text' },
        { key: 'client_secret', label: 'Client Secret', type: 'text' },
        { key: 'redirect_uri', label: 'Redirect URI', type: 'text' },
        { key: 'scopes', label: 'Scopes', type: 'text', hint: 'Comma separated' },
      ]
    case 'session':
      return [
        { key: 'username', label: 'Username', type: 'text' },
        { key: 'password', label: 'Password', type: 'text' },
      ]
    default:
      return []
  }
})
</script>

<template>
  <div>
    <template v-for="field in fields" :key="field.key">
      <v-text-field
        :label="field.label"
        :type="field.type"
        :model-value="config[field.key]"
        :hint="field.hint"
        :persistent-hint="!!field.hint"
        @update:model-value="updateField(field.key, $event)"
        variant="outlined"
        density="comfortable"
        class="mb-2"
      />
    </template>
  </div>
</template>
