import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'
import type { MockServer, MockEndpoint, RequestLog, MockCredential } from '../types'

export const useServersStore = defineStore('servers', () => {
  const servers = ref<MockServer[]>([])
  const currentServer = ref<MockServer | null>(null)
  const endpoints = ref<MockEndpoint[]>([])
  const credentials = ref<MockCredential[]>([])
  const logs = ref<RequestLog[]>([])
  const loading = ref(false)

  async function fetchServers() {
    loading.value = true
    try {
      const { data } = await api.get('/servers')
      servers.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function fetchServer(id: string) {
    loading.value = true
    try {
      const { data } = await api.get(`/servers/${id}`)
      currentServer.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function createServer(payload: Partial<MockServer>) {
    const { data } = await api.post('/servers', payload)
    servers.value.unshift(data.data)
    return data.data
  }

  async function updateServer(id: string, payload: Partial<MockServer>) {
    const { data } = await api.put(`/servers/${id}`, payload)
    const idx = servers.value.findIndex(s => s.id === id)
    if (idx !== -1) servers.value[idx] = data.data
    if (currentServer.value?.id === id) currentServer.value = data.data
    return data.data
  }

  async function deleteServer(id: string) {
    await api.delete(`/servers/${id}`)
    servers.value = servers.value.filter(s => s.id !== id)
    if (currentServer.value?.id === id) currentServer.value = null
  }

  async function fetchEndpoints(serverId: string) {
    const { data } = await api.get(`/servers/${serverId}/endpoints`)
    endpoints.value = data.data
  }

  async function createEndpoint(serverId: string, payload: Partial<MockEndpoint>) {
    const { data } = await api.post(`/servers/${serverId}/endpoints`, payload)
    endpoints.value.push(data.data)
    return data.data
  }

  async function updateEndpoint(id: string, payload: Partial<MockEndpoint>) {
    const { data } = await api.put(`/endpoints/${id}`, payload)
    const idx = endpoints.value.findIndex(e => e.id === id)
    if (idx !== -1) endpoints.value[idx] = data.data
    return data.data
  }

  async function deleteEndpoint(id: string) {
    await api.delete(`/endpoints/${id}`)
    endpoints.value = endpoints.value.filter(e => e.id !== id)
  }

  async function fetchCredentials(serverId: string) {
    const { data } = await api.get(`/servers/${serverId}/credentials`)
    credentials.value = data.data
  }

  async function createCredential(serverId: string, payload: Partial<MockCredential>) {
    const { data } = await api.post(`/servers/${serverId}/credentials`, payload)
    credentials.value.push(data.data)
    return data.data
  }

  async function updateCredential(id: string, payload: Partial<MockCredential>) {
    const { data } = await api.put(`/credentials/${id}`, payload)
    const idx = credentials.value.findIndex(c => c.id === id)
    if (idx !== -1) credentials.value[idx] = data.data
    return data.data
  }

  async function deleteCredential(id: string) {
    await api.delete(`/credentials/${id}`)
    credentials.value = credentials.value.filter(c => c.id !== id)
  }

  async function fetchLogs(serverId: string) {
    const { data } = await api.get(`/servers/${serverId}/logs`)
    logs.value = data.data
  }

  async function clearLogs(serverId: string) {
    await api.delete(`/servers/${serverId}/logs`)
    logs.value = []
  }

  return {
    servers, currentServer, endpoints, credentials, logs, loading,
    fetchServers, fetchServer, createServer, updateServer, deleteServer,
    fetchEndpoints, createEndpoint, updateEndpoint, deleteEndpoint,
    fetchCredentials, createCredential, updateCredential, deleteCredential,
    fetchLogs, clearLogs,
  }
})
