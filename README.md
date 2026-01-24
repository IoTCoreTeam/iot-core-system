# IoT Core Backend

This repository serves as the central backend service for the IoT-core platform, handling authentication, user management, and IoT device control through a modular architecture built on the Laravel framework.

## Core Features

### 1. Centralized Authentication (Laravel Passport)
- Managed OAuth2 server for issuing JWT Access Tokens and Refresh Tokens.
- Provides authentication flows for the Frontend (Nuxt), Control Module, and other microservices.
- Management of Personal Access and Password Grant clients.

### 2. User and Account Management
- Comprehensive management of users, roles (Admin, User), and company associations.
- Advanced filtering and search capabilities for user administration.

### 3. Modular Architecture
- Leverages `nwidart/laravel-modules` for strict separation of business logic.
- Designed for high scalability and maintainable code structure.

### 4. Control Module (IoT Management)
- Administration of IoT Gateways and End-Nodes.
- Registration and deactivation workflows for Gateways.
- API endpoints for querying available nodes and device status.

### 5. System Logging and Audit
- Integrated system logging for audit trails and troubleshooting.

---

## Module Structure

The backend follows a modular architectural pattern:

- `app/`: Contains core Laravel application logic (Models, Controllers, Providers).
  - Includes global models such as `User.php`, `Company.php`, and `SystemLog.php`.
- `Modules/`: Directory for independent business modules.
  - `ControlModule/`: Dedicated module for IoT logic.
    - `app/Http/Controllers/`: Controllers for Gateway and Node management.
    - `database/`: Module-specific migrations, factories, and seeders.
    - `routes/api.php`: API endpoints for device interaction.
- `database/`: System-wide migrations and seeders for core entities.
- `routes/`: Primary application routes (authentication, web).

---

## Technical Setup

### 1. Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18 or higher (for asset compilation)
- MySQL or a compatible relational database

### 2. Installation Steps

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Configure your database connection details in the newly created `.env` file.*

3. **Database Initialization**
   ```bash
   php artisan migrate --seed
   ```

4. **Passport Initialization**
   ```bash
   php artisan install:api --passport
   php artisan passport:keys --force
   ```
   *This generates the RSA keys required for secure token signing in the `storage/` directory.*

5. **Personal Access Client Setup**
   ```bash
   php artisan passport:client --personal
   ```

### 3. Running the Application

- **Development Server:**
  ```bash
  php artisan serve
  ```

- **Full Stack Concurrency (Server, Queue, Vite):**
  ```bash
  composer dev
  ```

---

## Utility Commands

- `composer setup`: Orchestrates dependency installation, environment setup, migrations, and builds.
- `php artisan test`: Executes the automated test suite.
- `php artisan storage:link`: Establishes the symbolic link for public file storage.

For detailed information on the setup process, refer to `docs/Setup_tutorial.txt` or contact the development team.
