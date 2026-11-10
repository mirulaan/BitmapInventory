# Docker Deployment Guide for BitmapInventory

This project is fully containerized and production-ready for deployment on any Docker-compatible infrastructure, including Docker Compose, VPS (Coolify, CapRover, Portainer), Kubernetes, AWS ECS, Google Cloud Run, DigitalOcean App Platform, Fly.io, and Railway.

---

## Architecture Overview

The container setup uses a high-performance **multi-stage build**:
1. **Frontend Builder (`node:22-alpine`)**: Builds Tailwind CSS and JavaScript production bundles via Vite.
2. **Composer Builder (`composer:2`)**: Installs production PHP dependencies with optimized autoloader.
3. **Production Runtime (`php:8.4-fpm-alpine`)**:
   - High-performance Alpine-based PHP 8.4 runtime with OPcache and JIT enabled.
   - Built-in **Nginx** reverse proxy to serve static assets and terminate HTTP traffic directly.
   - **Supervisord** managing both PHP-FPM and Nginx within a single container.
   - Built-in health check using Laravel 13's `/up` endpoint.
   - Dynamic port support (`$PORT`) for Cloud Run, Heroku, and Railway.

---

## 1. Quick Start with Docker Compose

The easiest way to run the stack (Laravel application connected to PostgreSQL, Redis, Queue Worker, and Scheduler) is with Docker Compose:

### Step 1: Prepare Environment File
Copy the Docker-specific environment template:
```bash
cp .env.docker.example .env
```

### Step 2: Generate Application Encryption Key
If you don't already have an `APP_KEY`, generate one:
```bash
# On host (if PHP is installed)
php artisan key:generate --show

# Or generate a base64 32-byte key
openssl rand -base64 32
```
Paste this key into `APP_KEY` in your `.env` file (prefixed with `base64:`).

### Step 3: Build and Start Containers
```bash
docker compose up -d --build
```

The application will be available at:
👉 **[http://localhost:8000](http://localhost:8000)**

Healthcheck endpoint:
👉 **[http://localhost:8000/up](http://localhost:8000/up)**

---

## 2. Docker Compose Services

The `docker-compose.yml` file configures the core services connecting to your PostgreSQL database (`192.168.100.4:5432`):

| Service | Container Name | Description | Port |
| :--- | :--- | :--- | :--- |
| `app` | `bitmap_inventory_app` | Web application (Nginx + PHP-FPM + Supervisor) | `8000:80` |
| `redis` | `bitmap_inventory_redis` | Redis 7 in-memory cache, session, and queue driver | `6379:6379` |
| `queue` | `bitmap_inventory_queue` | Laravel background queue worker (`artisan queue:work`) | Internal |
| `scheduler` | `bitmap_inventory_scheduler` | Laravel task scheduler (`artisan schedule:work`) | Internal |

---

## 3. Database Operations & Artisan Commands

Run Artisan commands inside the running `app` container:

### Run Migrations
```bash
docker compose exec app php artisan migrate --force
```

### Run Database Seeders
```bash
docker compose exec app php artisan db:seed --force
```

### Access Laravel Tinker
```bash
docker compose exec app php artisan tinker
```

### Clear / Refresh Caches
```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

### Access Container Shell
```bash
docker compose exec app bash
```

---

## 4. Single-Container Cloud Deployment

If you are deploying to container platforms like **Google Cloud Run**, **AWS ECS**, **DigitalOcean App Platform**, **Fly.io**, or **Render**, you can build and push the single production Docker image:

### Build the Image
```bash
docker build -t your-registry/bitmap-inventory:latest .
```

### Run Standalone (with SQLite or Managed Database)
```bash
docker run -d \
  -p 8080:80 \
  -e APP_NAME=BitmapInventory \
  -e APP_ENV=production \
  -e APP_KEY=base64:YOUR_GENERATED_APP_KEY \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=192.168.100.4 \
  -e DB_PORT=5432 \
  -e DB_DATABASE=mydb \
  -e DB_USERNAME=postgres \
  -e DB_PASSWORD=Abitabit9898 \
  -e RUN_MIGRATIONS=true \
  your-registry/bitmap-inventory:latest
```

### Cloud Run / Dynamic Port Platforms
The entrypoint script automatically binds Nginx to the `$PORT` environment variable provided by Google Cloud Run, Railway, or Heroku without requiring custom configuration.

---

## 5. Local Development Mode (Live Reloading)

To develop locally with code mounted directly into the container and Vite dev server enabled:

```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up
```

This configuration:
- Mounts your local codebase into `/var/www/html` for instantaneous updates.
- Exposes port `5173` for Vite hot module replacement (HMR).
- Sets `APP_ENV=local` and `APP_DEBUG=true`.
- Disables OPcache caching so file modifications are immediately reflected.

---

## 6. Useful Commands & Maintenance

### View Logs
```bash
# View all logs in real-time
docker compose logs -f

# View application logs only
docker compose logs -f app

# View queue worker logs
docker compose logs -f queue
```

### Restart Services
```bash
docker compose restart app
```

### Stop Containers
```bash
# Stop containers (keeps database and volumes intact)
docker compose down

# Stop containers and erase all volume data
docker compose down -v
```

---

## 7. Production Hardening Checklist

- [x] Multi-stage build with minimal attack surface (`alpine` base).
- [x] Static assets compiled directly into image (no node_modules in production).
- [x] OPcache enabled with aggressive preloading and validation disabled for peak speed.
- [x] Nginx gzip compression and browser caching for static assets.
- [x] Nginx security headers (`X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy`).
- [x] Server tokens and PHP version exposure disabled (`expose_php = Off`).
- [x] Healthcheck endpoint configured to Laravel's native `/up`.
- [x] Process supervision with graceful signal forwarding (`SIGQUIT` on shutdown).
- [x] Unprivileged runtime execution for web workers (`www-data`).
