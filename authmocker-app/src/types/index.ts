export interface MockServer {
  id: string
  name: string
  slug: string
  auth_type: string
  auth_type_label: string
  config: Record<string, any>
  is_active: boolean
  description: string | null
  mock_url: string
  endpoints_count?: number
  endpoints?: MockEndpoint[]
  credentials_count?: number
  credentials?: MockCredential[]
  created_at: string
  updated_at: string
}

export interface MockEndpoint {
  id: string
  mock_server_id: string
  method: string
  path: string
  response_status: number
  response_body: Record<string, any> | null
  response_headers: Record<string, string> | null
  delay_ms: number
  is_active: boolean
  description: string | null
  full_url?: string
  created_at: string
  updated_at: string
}

export interface RequestLog {
  id: string
  mock_server_id: string
  mock_endpoint_id: string | null
  mock_credential_id: string | null
  credential_label: string | null
  method: string
  path: string
  headers: Record<string, string> | null
  body: Record<string, any> | null
  query_params: Record<string, string> | null
  ip: string | null
  auth_status: 'success' | 'failed' | 'skipped'
  response_status: number
  created_at: string
}

export interface AuthType {
  value: string
  label: string
  default_server_config: Record<string, any>
  default_credential: Record<string, any>
}

export interface CredentialProfile {
  name?: string
  email?: string
  role?: string
  custom?: Record<string, any>
}

export interface MockCredential {
  id: string
  mock_server_id: string
  label: string
  is_active: boolean
  credentials: Record<string, any>
  profile: CredentialProfile
  created_at: string
  updated_at: string
}

export interface PaginatedResponse<T> {
  data: T[]
  links: { first: string; last: string; prev: string | null; next: string | null }
  meta: { current_page: number; last_page: number; per_page: number; total: number }
}
