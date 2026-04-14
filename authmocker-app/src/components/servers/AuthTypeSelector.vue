<script setup lang="ts">
const emit = defineEmits<{ (e: 'select', type: string): void }>()
defineProps<{ selected: string }>()

const authTypes = [
  { value: 'basic_auth', label: 'Basic Auth', icon: '🔑', description: 'Username & password via Authorization header' },
  { value: 'api_key', label: 'API Key', icon: '🗝️', description: 'Key validation in header, query or body' },
  { value: 'jwt', label: 'JWT Bearer', icon: '🎟️', description: 'JSON Web Tokens with configurable claims' },
  { value: 'oauth2', label: 'OAuth2', icon: '🔐', description: 'Authorization Code & Client Credentials flows' },
  { value: 'session', label: 'Session', icon: '🍪', description: 'Cookie-based session authentication' },
]
</script>

<template>
  <v-row>
    <v-col
      v-for="type in authTypes"
      :key="type.value"
      cols="12"
      sm="6"
      lg="4"
    >
      <v-card
        @click="emit('select', type.value)"
        :color="selected === type.value ? 'primary' : undefined"
        :variant="selected === type.value ? 'outlined' : 'flat'"
        :class="{ 'border-primary': selected === type.value }"
        hover
        class="pa-4"
        style="cursor: pointer;"
      >
        <div class="text-h5 mb-2">{{ type.icon }}</div>
        <div class="text-subtitle-2 font-weight-medium">{{ type.label }}</div>
        <div class="text-caption text-medium-emphasis mt-1">{{ type.description }}</div>
      </v-card>
    </v-col>
  </v-row>
</template>
