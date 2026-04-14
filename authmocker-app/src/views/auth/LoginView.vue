<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useTheme } from 'vuetify'

const auth = useAuthStore()
const router = useRouter()
const theme = useTheme()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)
const form = ref<any>(null)

const isDark = theme.global.current.value.dark

const emailRules = [
  (v: string) => !!v || 'Email is required',
  (v: string) => /.+@.+\..+/.test(v) || 'Enter a valid email',
]

const passwordRules = [
  (v: string) => !!v || 'Password is required',
]

async function handleLogin() {
  const { valid } = await form.value.validate()
  if (!valid) return

  error.value = ''
  loading.value = true
  try {
    await auth.signIn(email.value, password.value)
    router.push('/')
  } catch (e: any) {
    error.value = e.response?.data?.message || e.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-wrapper">
    <!-- Animated background -->
    <div class="bg-animation">
      <div class="orb orb-1" />
      <div class="orb orb-2" />
      <div class="orb orb-3" />
    </div>

    <v-container class="fill-height position-relative" style="z-index: 1">
      <v-row justify="center" align="center">
        <v-col cols="12" sm="8" md="5" lg="4" xl="3">
          <!-- Logo & Title -->
          <div class="text-center mb-8">
            <div class="logo-container mb-5">
              <div class="logo-glow" />
              <v-avatar color="primary" size="72" rounded="xl" class="logo-avatar elevation-8">
                <v-icon size="36" icon="mdi-shield-lock" />
              </v-avatar>
            </div>
            <h1 class="text-h3 font-weight-bold mb-1" style="letter-spacing: -0.5px">
              Auth<span class="text-primary">Mocker</span>
            </h1>
            <p class="text-body-1 text-medium-emphasis">
              Mock authentication for developers
            </p>
          </div>

          <!-- Login Card -->
          <v-card class="login-card" variant="outlined" rounded="xl">
            <v-card-text class="pa-8">
              <v-form ref="form" @submit.prevent="handleLogin">
                <v-fade-transition>
                  <v-alert
                    v-if="error"
                    type="error"
                    variant="tonal"
                    density="compact"
                    closable
                    class="mb-6"
                    @click:close="error = ''"
                  >
                    {{ error }}
                  </v-alert>
                </v-fade-transition>

                <v-text-field
                  v-model="email"
                  label="Email"
                  type="email"
                  placeholder="you@example.com"
                  prepend-inner-icon="mdi-email-outline"
                  :rules="emailRules"
                  variant="outlined"
                  density="comfortable"
                  class="mb-1"
                  color="primary"
                />

                <v-text-field
                  v-model="password"
                  label="Password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Enter your password"
                  prepend-inner-icon="mdi-lock-outline"
                  :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  :rules="passwordRules"
                  @click:append-inner="showPassword = !showPassword"
                  variant="outlined"
                  density="comfortable"
                  class="mb-2"
                  color="primary"
                />

                <v-btn
                  type="submit"
                  color="primary"
                  block
                  size="large"
                  rounded="lg"
                  :loading="loading"
                  :disabled="loading || !email || !password"
                  class="mt-4 text-none font-weight-bold"
                  style="letter-spacing: 0.5px"
                >
                  <v-icon start icon="mdi-login" />
                  Sign In
                </v-btn>
              </v-form>
            </v-card-text>

            <v-divider />

            <v-card-actions class="justify-center pa-4">
              <span class="text-body-2 text-medium-emphasis">
                Don't have an account?
              </span>
              <v-btn
                variant="text"
                color="primary"
                size="small"
                to="/register"
                class="text-none font-weight-bold ml-1"
              >
                Create Account
              </v-btn>
            </v-card-actions>
          </v-card>

          <!-- Footer -->
          <p class="text-center text-caption text-medium-emphasis mt-6">
            Open-source mock authentication API
          </p>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

.bg-animation {
  position: fixed;
  inset: 0;
  z-index: 0;
  overflow: hidden;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.15;
  animation: float 20s infinite ease-in-out;
}

.orb-1 {
  width: 600px;
  height: 600px;
  background: rgb(var(--v-theme-primary));
  top: -200px;
  right: -100px;
  animation-delay: 0s;
}

.orb-2 {
  width: 400px;
  height: 400px;
  background: rgb(var(--v-theme-secondary));
  bottom: -100px;
  left: -100px;
  animation-delay: -7s;
}

.orb-3 {
  width: 300px;
  height: 300px;
  background: rgb(var(--v-theme-primary));
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation-delay: -14s;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  25% { transform: translate(30px, -40px) scale(1.05); }
  50% { transform: translate(-20px, 20px) scale(0.95); }
  75% { transform: translate(15px, 30px) scale(1.02); }
}

.logo-container {
  position: relative;
  display: inline-block;
}

.logo-glow {
  position: absolute;
  inset: -12px;
  border-radius: 20px;
  background: rgb(var(--v-theme-primary));
  opacity: 0.2;
  filter: blur(20px);
  animation: pulse-glow 3s infinite ease-in-out;
}

@keyframes pulse-glow {
  0%, 100% { opacity: 0.15; transform: scale(1); }
  50% { opacity: 0.3; transform: scale(1.1); }
}

.logo-avatar {
  position: relative;
}

.login-card {
  backdrop-filter: blur(20px);
  border-color: rgba(var(--v-theme-primary), 0.15) !important;
  background: rgba(var(--v-theme-surface), 0.8) !important;
}
</style>
