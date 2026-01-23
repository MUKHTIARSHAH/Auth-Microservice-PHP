# 🔐 PHP Authentication Microservice

This is a standalone microservice extracted from my large-scale API deployments. It focuses strictly on secure identity management.

## 🚀 Key Features
- **Stateless Auth:** Uses JWT (JSON Web Tokens).
- **Dual Token System:** Implements both Access and Refresh tokens for better security.
- **Microservice Ready:** Designed to be called via internal cURL or Guzzle requests from other services.
- **PSR-4 Compliant:** Follows modern PHP namespacing standards.

## 🛠️ Security Implemented
- **Password Hashing:** Uses `PASSWORD_DEFAULT` (Bcrypt).
- **Token Rotation:** Logic included for secure session refreshing.
- **Input Filtering:** Strict sanitization on all auth endpoints.
-
