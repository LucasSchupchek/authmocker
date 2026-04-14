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

const hint = computed(() => {
  if (props.authType === 'basic_auth') {
    return 'Credentials are managed in the Credentials tab'
  }
  return ''
})

const fields = computed(() => {
  switch (props.authType) {
    case 'basic_auth':
      return []
    case 'api_key':
      return [
        { key: 'location', label: 'Key Location', type: 'select', options: ['header', 'query', 'body'] },
        { key: 'header_name', label: 'Header Name', type: 'text', condition: () => props.modelValue.location === 'header' },
        { key: 'query_param', label: 'Query Param Name', type: 'text', condition: () => props.modelValue.location === 'query' },
        { key: 'body_field', label: 'Body Field Name', type: 'text', condition: () => props.modelValue.location === 'body' },
      ]
    case 'jwt':
      return [
        { key: 'secret', label: 'JWT Secret', type: 'text' },
        { key: 'algorithm', label: 'Algorithm', type: 'select', options: ['HS256', 'HS384', 'HS512'] },
        { key: 'expiration_minutes', label: 'Token Expiration (minutes)', type: 'number' },
      ]
    case 'oauth2':
      return [
        { key: 'access_token_ttl', label: 'Access Token TTL (seconds)', type: 'number' },
        { key: 'refresh_token_ttl', label: 'Refresh Token TTL (seconds)', type: 'number' },
      ]
    case 'session':
      return [
        { key: 'session_ttl_minutes', label: 'Session TTL (minutes)', type: 'number' },
        { key: 'cookie_name', label: 'Cookie Name', type: 'text' },
      ]
    default:
      return []
  }
})

const visibleFields = computed(() =>
  fields.value.filter(f => !f.condition || f.condition())
)
</script>

<template>
  <div>
    <div v-if="hint" class="text-body-2 text-medium-emphasis mb-3">{{ hint }}</div>
    <template v-for="field in visibleFields" :key="field.key">
      <v-select
        v-if="field.type === 'select'"
        :label="field.label"
        :model-value="config[field.key]"
        :items="field.options"
        @update:model-value="updateField(field.key, $event)"
        variant="outlined"
        density="comfortable"
        class="mb-2"
      />

      <v-text-field
        v-else
        :label="field.label"
        :type="field.type"
        :model-value="config[field.key]"
        @update:model-value="updateField(field.key, field.type === 'number' ? Number($event) : $event)"
        variant="outlined"
        density="comfortable"
        class="mb-2"
      />
    </template>
  </div>
</template>
