import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: localStorage.getItem('theme') || 'dark',
    themes: {
      light: {
        colors: {
          primary: '#6366f1',
          secondary: '#8b5cf6',
          surface: '#ffffff',
          background: '#f5f5f5',
          error: '#ef4444',
          success: '#22c55e',
          warning: '#f59e0b',
          info: '#3b82f6',
        },
      },
      dark: {
        colors: {
          primary: '#818cf8',
          secondary: '#a78bfa',
          surface: '#1e1e1e',
          background: '#121212',
          error: '#f87171',
          success: '#4ade80',
          warning: '#fbbf24',
          info: '#60a5fa',
        },
      },
    },
  },
  defaults: {
    VTextField: { variant: 'outlined', density: 'comfortable' },
    VSelect: { variant: 'outlined', density: 'comfortable' },
    VTextarea: { variant: 'outlined', density: 'comfortable' },
    VBtn: { variant: 'flat' },
    VCard: { rounded: 'lg' },
  },
})
