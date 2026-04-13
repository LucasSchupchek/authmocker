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
        { key: 'client_id', label: 'Client ID', type: 'text' },
        { key: 'client_secret', label: 'Client Secret', type: 'text' },
        { key: 'redirect_uri', label: 'Redirect URI', type: 'text' },
        { key: 'access_token_ttl', label: 'Access Token TTL (seconds)', type: 'number' },
        { key: 'refresh_token_ttl', label: 'Refresh Token TTL (seconds)', type: 'number' },
      ]
    case 'session':
      return [
        { key: 'username', label: 'Username', type: 'text' },
        { key: 'password', label: 'Password', type: 'text' },
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
  <div class="space-y-4">
    <div v-for="field in visibleFields" :key="field.key">
      <label :for="field.key" class="block text-sm font-medium text-gray-300 mb-1">
        {{ field.label }}
      </label>

      <select
        v-if="field.type === 'select'"
        :id="field.key"
        :value="config[field.key]"
        @change="updateField(field.key, ($event.target as HTMLSelectElement).value)"
        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
      </select>

      <input
        v-else
        :id="field.key"
        :type="field.type"
        :value="config[field.key]"
        @input="updateField(field.key, field.type === 'number' ? Number(($event.target as HTMLInputElement).value) : ($event.target as HTMLInputElement).value)"
        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      />
    </div>
  </div>
</template>
