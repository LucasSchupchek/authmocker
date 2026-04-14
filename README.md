# AuthMocker

**Mock Authentication API for Developers** - Create and manage mock authentication servers to test your clients in development.

AuthMocker lets you spin up fully-functional mock auth endpoints in seconds. Configure the authentication type, create multiple credentials with custom profiles, set up endpoints with mock responses, and test your client applications without needing a real auth server.

## Features

- **5 Auth Types**: Basic Auth, API Key, JWT Bearer, OAuth2, Session/Cookie
- **Multi-Credential Support**: Create multiple users/clients per server with custom profiles (name, email, role, custom claims)
- **OAuth2 Multi-Client**: Configure multiple OAuth2 applications with independent scopes, secrets and redirect URIs
- **Configurable Endpoints**: Define custom mock responses for any HTTP method and path
- **Request Logging**: See every request with full details, including which credential was used
- **Code Snippets**: Auto-generated usage examples in cURL, JavaScript, and Python
- **API Documentation**: Auto-generated OpenAPI docs from code (Scramble)
- **Redis Cache**: Smart caching with automatic invalidation on writes
- **Dark/Light Theme**: Toggle between themes with persistent preference
- **Docker Ready**: One command to spin up the entire stack
- **User Isolation**: Each user only sees and manages their own mock servers

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 12 (PHP 8.2) |
| **Frontend** | Vue 3 + TypeScript + Vuetify 3 |
| **Database** | PostgreSQL 16 |
| **Cache** | Redis |
| **Auth** | JWT (self-managed, no third-party dependency) |
| **UI** | Vuetify 3 + Material Design Icons |
| **Docs** | Scramble (auto-generated OpenAPI) |
| **Infra** | Docker + Docker Compose |

## Architecture

```
Controllers (input/output only)
    -> FormRequests (validation)
    -> Services (business logic)
        -> Strategy Pattern (auth type handling)
        -> HasCache Trait (Redis caching)
    -> Resources (JSON responses)
```

- **Controllers** are thin - they only handle request validation (FormRequest) and response formatting (Resource). Zero business logic.
- **Services** contain all business logic. Authentication mock handling uses the **Strategy Pattern** with a dedicated strategy for each auth type.
- **Multi-Credential Model**: Server-level config (algorithm, TTLs, key location) is separated from credential-level config (username/password, API keys, client secrets). Each server supports N credentials with custom user profiles.
- **HasCache Trait** provides reusable cache methods across services. Cache is automatically invalidated on create/update/delete operations.
- **User isolation** is enforced at the service layer - queries are always scoped by `user_id`.

## Prerequisites

- [Docker](https://www.docker.com/) & Docker Compose
- [PHP 8.2+](https://www.php.net/) (for local development)
- [Node.js 20+](https://nodejs.org/) (for frontend)
- [Composer](https://getcomposer.org/)

## Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/LucasSchupchek/authmocker.git
cd authmocker
```

### 2. Start infrastructure (PostgreSQL + Redis)

```bash
docker-compose up postgres redis -d
```

### 3. Configure the API

```bash
cd authmocker-api
cp .env.example .env
php artisan key:generate
```

### 4. Install dependencies and run migrations

```bash
composer install
php artisan migrate
```

### 5. Start the API

```bash
php artisan serve --port=8080
```

### 6. Start the Frontend (new terminal)

```bash
cd authmocker-app
cp .env.example .env
npm install
npm run dev
```

### 7. Access the application

- **Frontend**: http://localhost:3000
- **API**: http://localhost:8080
- **API Docs**: http://localhost:8080/docs/api

## Running Everything with Docker

If you prefer to run all services in Docker:

```bash
cp authmocker-api/.env.example authmocker-api/.env
cp authmocker-app/.env.example authmocker-app/.env
docker-compose up --build -d
docker exec authmocker-api composer install
docker exec authmocker-api php artisan key:generate
docker exec authmocker-api php artisan migrate --force
```

> **Note**: When running the API inside Docker, set `REDIS_HOST=authmocker-redis` and `DB_HOST=authmocker-postgres` in your `.env` file instead of `127.0.0.1`.

## Environment Variables

### API (`authmocker-api/.env`)

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | PostgreSQL host | `127.0.0.1` |
| `DB_PORT` | PostgreSQL port | `5432` |
| `DB_DATABASE` | Database name | `authmocker` |
| `DB_USERNAME` | Database user | `authmocker` |
| `DB_PASSWORD` | Database password | `secret` |
| `REDIS_HOST` | Redis host | `127.0.0.1` |
| `REDIS_PORT` | Redis port | `6379` |
| `AUTH_TOKEN_TTL` | Access token lifetime (seconds) | `3600` |
| `AUTH_REFRESH_TTL` | Refresh token lifetime (seconds) | `604800` |

### Frontend (`authmocker-app/.env`)

| Variable | Description | Default |
|----------|-------------|---------|
| `VITE_API_URL` | Backend API URL | `http://localhost:8080/api` |

## Usage

### 1. Create an account
Register via the frontend at http://localhost:3000/register

### 2. Create a mock server
Choose an auth type, give it a name and slug. The slug determines your mock URL: `/mock/{slug}/...`. A default credential is created automatically.

### 3. Add credentials
Go to the **Credentials** tab on your server to create multiple users or clients. Each credential has:
- **Label**: Friendly name (e.g., "Admin User", "Read-Only Client")
- **Credentials**: Auth-type specific data (username/password, API key, client_id/secret, etc.)
- **Profile**: Identity data included in tokens/responses (name, email, role, custom claims)

### 4. Add endpoints
Configure endpoints with:
- HTTP method (GET, POST, PUT, PATCH, DELETE)
- Path (e.g., `users`, `products/1`)
- Response status code
- Response body (JSON)
- Optional delay (simulate latency)

### 5. Test your client

```bash
# API Key example
curl http://localhost:8080/mock/my-api/users \
  -H "X-API-Key: your-configured-key"

# JWT example - get token for a specific user
curl -X POST http://localhost:8080/mock/my-api/token \
  -d '{"sub": "admin"}'

# Then use the token (includes the user's profile in claims)
curl http://localhost:8080/mock/my-api/users \
  -H "Authorization: Bearer <token>"

# OAuth2 - client credentials (per-client scopes)
curl -X POST http://localhost:8080/mock/my-api/token \
  -d "grant_type=client_credentials&client_id=xxx&client_secret=yyy"
```

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Create a new account |
| POST | `/api/auth/login` | Login and get JWT tokens |
| POST | `/api/auth/refresh` | Refresh access token |
| GET | `/api/auth/me` | Get authenticated user info |

### Mock Servers (requires auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/servers` | List your mock servers |
| POST | `/api/servers` | Create a mock server |
| GET | `/api/servers/{id}` | Get server details |
| PUT | `/api/servers/{id}` | Update a server |
| DELETE | `/api/servers/{id}` | Delete a server |

### Mock Credentials (requires auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/servers/{id}/credentials` | List credentials |
| POST | `/api/servers/{id}/credentials` | Create credential |
| GET | `/api/credentials/{id}` | Get credential details |
| PUT | `/api/credentials/{id}` | Update credential |
| DELETE | `/api/credentials/{id}` | Delete credential |

### Mock Endpoints (requires auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/servers/{id}/endpoints` | List endpoints |
| POST | `/api/servers/{id}/endpoints` | Create endpoint |
| PUT | `/api/endpoints/{id}` | Update endpoint |
| DELETE | `/api/endpoints/{id}` | Delete endpoint |

### Request Logs (requires auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/servers/{id}/logs` | View request logs |
| DELETE | `/api/servers/{id}/logs` | Clear request logs |

### Mock Handler (public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| ANY | `/mock/{slug}/{path}` | Handle mock request |
| POST | `/mock/{slug}/token` | Get token (JWT/OAuth2) |
| POST | `/mock/{slug}/authorize` | OAuth2 authorize |
| POST | `/mock/{slug}/login` | Session login |

### Other

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/auth-types` | List available auth types with default configs |

## Project Structure

```
AuthMocker/
├── docker-compose.yml
├── README.md
├── LICENSE
├── authmocker-api/                  # Laravel Backend
│   ├── app/
│   │   ├── Enums/                   # AuthType, HttpMethod, AuthStatus
│   │   ├── Http/
│   │   │   ├── Controllers/         # Thin controllers (input/output only)
│   │   │   ├── Requests/            # FormRequest validation
│   │   │   ├── Resources/           # API Resource responses
│   │   │   └── Middleware/           # JWT Auth middleware
│   │   ├── Models/                  # User, MockServer, MockEndpoint, MockCredential, RequestLog
│   │   ├── Services/                # Business logic
│   │   │   └── MockHandler/         # Strategy Pattern (5 auth strategies)
│   │   └── Traits/                  # HasCache trait
│   ├── database/migrations/
│   ├── routes/
│   └── config/
└── authmocker-app/                  # Vue.js Frontend
    └── src/
        ├── components/
        │   ├── credentials/         # CredentialList, CredentialForm, CredentialConfigFields
        │   ├── endpoints/           # EndpointList, EndpointForm
        │   ├── layout/              # AppLayout (sidebar, app bar, theme toggle)
        │   ├── logs/                # RequestLogTable
        │   ├── servers/             # ServerCard, AuthTypeSelector, AuthConfigForm
        │   └── ui/                  # CodeSnippet
        ├── plugins/                 # Vuetify configuration
        ├── views/                   # Login, Register, Dashboard, Server CRUD, Docs
        ├── stores/                  # Pinia stores (auth, servers, theme)
        ├── services/                # API client (Axios)
        ├── router/                  # Vue Router with auth guards
        └── types/                   # TypeScript interfaces
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
