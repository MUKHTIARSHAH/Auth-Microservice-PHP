# Auth Microservice PHP

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![JWT](https://img.shields.io/badge/JWT-HS256-black)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-2.x-885630?logo=composer&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

A lightweight PHP authentication microservice with JWT access/refresh tokens, user registration, and profile management. Built for XAMPP/Apache deployments with a flat endpoint structure — no framework overhead.

**Repository:** [github.com/MUKHTIARSHAH/Auth-Microservice-PHP](https://github.com/MUKHTIARSHAH/Auth-Microservice-PHP)

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Runtime | PHP 8.0+ |
| Database | MySQL / MariaDB via PDO |
| DB layer | [ParagonIE EasyDB](https://github.com/paragonie/easydb) |
| Auth | Custom JWT (HS256) |
| Dependencies | Composer |
| Server | Apache (`.htaccess` included) |

---

## Architecture

Each endpoint is a standalone PHP file. Requests flow through shared helpers before hitting the database.

```
Client (Postman / Frontend)
        │
        ▼
   API Endpoint          ←  API/v1/auth/*.php, API/v1/profile/*.php
        │
        ▼
   ResponseHelper         ←  HTTP method check, JSON parsing
        │
        ▼
   Validation             ←  Input rules, password policy
        │
        ▼
   Handler / JwtHelper    ←  Auth logic, token issue & verify
        │
        ▼
   DbHandler (EasyDB)     ←  Prepared statements
        │
        ▼
   JSON Response          ←  Standardized STATUS / MESSAGE / DATA
```

**Auth flow:** Register → Login (access + refresh tokens) → Bearer token on protected routes → Refresh when access token expires.

---

## Features

What's actually in the codebase today:

| Feature | Status |
|---------|--------|
| JWT access & refresh tokens | ✅ Implemented |
| User registration & login | ✅ Implemented |
| Profile read & update | ✅ Implemented |
| Token validation & refresh | ✅ Implemented |
| Password policy validation | ✅ Implemented |
| Prepared statements (SQL injection protection) | ✅ Implemented |
| CORS headers | ✅ Implemented (Apache + PHP) |
| GZIP compression | ✅ Implemented (`.htaccess`) |
| Structured JSON responses | ✅ Implemented |
| Error logging | ✅ Implemented |
| Forgot password | ⚠️ Stub — generates token, no email/DB persistence yet |
| Rate limiting | ⚠️ Config placeholder in `config/config.php`, not enforced |
| CSRF tokens | ⚠️ Config placeholder — not needed for stateless Bearer-token API |

---

## Quick Start

### 1. Clone & install

```bash
git clone https://github.com/MUKHTIARSHAH/Auth-Microservice-PHP.git
cd Auth-Microservice-PHP
composer install
```

### 2. Database

Import the schema:

```bash
mysql -u root -p < database/schema.sql
```

This creates the `first_api` database and the `info` user table. See [`database/schema.sql`](database/schema.sql) for the full DDL.

### 3. Configuration

Set your database credentials and JWT secret in `config/config.php`. You can also set `JWT_SECRET` via environment variable. Password policy, rate-limit defaults, and log paths are configured in the same file.

### 4. Run locally

Place the project in your web root (e.g. `htdocs/Auth-Microservice-PHP`) and hit:

```
http://localhost/Auth-Microservice-PHP/API/v1/auth/login.php
```

Adjust the base URL to match your folder name.

---

## API Overview

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/API/v1/profile/user_registration.php` | — | Register a new user |
| `POST` | `/API/v1/auth/login.php` | — | Authenticate, receive tokens |
| `GET` | `/API/v1/auth/token_validation.php` | Bearer | Validate access token |
| `POST` | `/API/v1/auth/refresh-token.php` | — | Exchange refresh token |
| `POST` | `/API/v1/auth/logout.php` | Bearer | Logout |
| `POST` | `/API/v1/auth/forgot-password.php` | — | Request password reset (stub) |
| `GET` | `/API/v1/profile/profile.php` | Bearer | Get user profile |
| `POST` | `/API/v1/profile/update.php` | Bearer | Update user profile |

Full request/response examples are in [`postman_examples.txt`](postman_examples.txt).

### Response format

All endpoints return the same envelope:

```json
{
  "STATUS": "success",
  "MESSAGE": "Login successful",
  "DATA": { },
  "CODE": 200,
  "TIMESTAMP": "2024-01-01 12:00:00"
}
```

Errors use `"STATUS": "error"`. Validation failures include field-level details in `DATA`.

---

## Project Structure

```
Auth-Microservice-PHP/
├── API/v1/
│   ├── auth/              # login, logout, refresh, token validation, forgot-password
│   └── profile/           # registration, profile, update
├── config/
│   └── config.php         # database, JWT, security settings
├── database/
│   └── schema.sql         # MySQL schema
├── handlers/
│   └── auth_handler.php   # JWT authentication middleware
├── includes/              # JWT, DB, validation, logging helpers
├── tests/
│   └── e2e_test.ps1       # PowerShell smoke tests
├── postman_examples.txt   # Postman-ready examples
└── composer.json
```

---

## Testing

**Postman** — import examples from `postman_examples.txt`.

**E2E script** — update the base URL in `tests/e2e_test.ps1` to match your local path, then:

```powershell
cd tests
.\e2e_test.ps1
```

Suggested manual flow: register → login → validate token → fetch profile → update profile → refresh token → logout.

---

## Security Notes

- Passwords are hashed with `password_hash()` / verified with `password_verify()`.
- All DB queries use prepared statements via EasyDB.
- Set a strong `JWT_SECRET` before any deployment.
- The forgot-password endpoint is a development stub — do not use as-is in production.
- Rate limiting is configured but not yet enforced in middleware.

For production: enable HTTPS, rotate JWT secrets, wire up real email for password reset, and implement rate limiting.

---

## License

MIT License — see [LICENSE](LICENSE) if present, or use freely for learning and portfolio work.
