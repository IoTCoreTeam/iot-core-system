# Auth Service (Laravel Passport)

This project is the centralized authentication server for the IoT-core platform. It exposes OAuth2 / Passport endpoints that issue JWT access tokens and refresh tokens for the rest of the stack (frontend, control-module, etc.). Follow the steps below whenever you clone or pull this repository onto a new machine.

## 1. Requirements

- PHP 8.2+
- Composer
- Node.js 18+ (needed for Vite assets)
- MySQL (or another database supported via `.env`)

## 2. Fresh Clone / Pull Setup

1. **Install global tooling (optional but recommended)**
   ```bash
   composer global require laravel/installer
   npm install --global npm@latest
   ```
2. **Install project dependencies**
   ```bash
   composer install
   npm install
   ```
3. **Bootstrap environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Configure the database connection in `.env`**
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=iot_auth
   DB_USERNAME=...
   DB_PASSWORD=...
   ```
5. **Run database migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

## 3. Passport Initialization

Run these commands once per environment (or whenever you rotate keys):

```bash
php artisan install:api --passport
php artisan passport:keys --force
php artisan vendor:publish --tag=passport-config
```

The commands above will generate fresh RSA keys at `storage/oauth-private.key` and `storage/oauth-public.key`. Keep the private key secure; other services only need the public key (see the Control Module README for how it consumes the public key).

### Personal Access Client

If you need a dedicated personal access client (for Nuxt/frontend in local dev):

```bash
php artisan passport:client --personal
```

Accept the defaults or provide a descriptive client name (for example `Nuxt-App`). Record the generated client ID/secret if you plan to use password or client credentials flows.

## 4. Useful Scripts

- `composer setup`: Installs dependencies, generates `.env`, runs migrations, and builds assets (see `composer.json`).
- `php artisan test`: Runs the automated test suite.
- `php artisan serve`: Local HTTP server (use `php artisan queue:listen` in another terminal if you rely on queues).

## 5. Regenerating Keys After Pulls

Whenever the repo changes include Passport config updates, rerun:

```bash
php artisan migrate
php artisan passport:keys --force
```

Then copy `storage/oauth-public.key` to downstream services (or share it via secrets management) so that middleware like `verify.central.token` can validate JWT signatures.

---

If you hit issues during setup, check `docs/Setup_tutorial.txt` for quick reference commands or reach out to the platform team.
