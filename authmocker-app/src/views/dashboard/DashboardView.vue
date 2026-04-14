<script setup lang="ts">
import { onMounted } from 'vue'
import { useServersStore } from '../../stores/servers'
import ServerCard from '../../components/servers/ServerCard.vue'

const store = useServersStore()

onMounted(() => {
  store.fetchServers()
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-8">
      <div>
        <h1 class="text-h4 font-weight-bold">Mock Servers</h1>
        <p class="text-subtitle-2 text-medium-emphasis mt-1">Manage your authentication mock servers</p>
      </div>
      <v-btn color="primary" to="/servers/create" prepend-icon="mdi-plus">
        New Server
      </v-btn>
    </div>

    <v-progress-linear v-if="store.loading" indeterminate color="primary" class="mb-4" />

    <div v-else-if="store.servers.length === 0" class="text-center py-16">
      <v-icon size="64" color="grey" class="mb-4">mdi-server-off</v-icon>
      <h3 class="text-h6">No mock servers yet</h3>
      <p class="text-medium-emphasis mt-1">Create your first mock authentication server to get started.</p>
      <v-btn color="primary" to="/servers/create" class="mt-4">
        Create Server
      </v-btn>
    </div>

    <v-row v-else>
      <v-col
        v-for="server in store.servers"
        :key="server.id"
        cols="12"
        md="6"
        lg="4"
      >
        <ServerCard :server="server" />
      </v-col>
    </v-row>
  </div>
</template>
