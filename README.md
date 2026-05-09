<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-2088FF?style=for-the-badge&logo=github-actions&logoColor=white" alt="CI/CD">
  <img src="https://img.shields.io/badge/Monitoring-Grafana-F46800?style=for-the-badge&logo=grafana&logoColor=white" alt="Grafana">
</p>

<h1 align="center">🏙️ Smart City Waste Management System</h1>

<p align="center">
  A production-ready Laravel-based waste management platform with full DevOps integration — CI/CD pipelines, containerization, monitoring, centralized logging, automated backups, and security scanning.
</p>

<p align="center">
  <a href="https://waste-management-tu84.onrender.com/">🌐 Live Demo</a> •
  <a href="#-quick-start">🚀 Quick Start</a> •
  <a href="#-architecture">📐 Architecture</a> •
  <a href="#-devops-features">⚙️ DevOps Features</a>
</p>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Tech Stack](#-tech-stack)
- [Architecture](#-architecture)
- [Quick Start](#-quick-start)
- [DevOps Features](#-devops-features)
  - [CI/CD Pipeline](#1--cicd-pipeline)
  - [Docker Integration](#2--docker-integration)
  - [Nginx Reverse Proxy](#3--nginx-reverse-proxy)
  - [Monitoring & Observability](#4--monitoring--observability)
  - [Centralized Logging](#5--centralized-logging)
  - [Automated Backups](#6--automated-backups)
  - [Security Scanning](#7--security-scanning)
  - [SSL / HTTPS](#8--ssl--https)
- [Project Structure](#-project-structure)
- [Deployment](#-deployment)
- [Troubleshooting](#-troubleshooting)
- [License](#-license)

---

## 🌟 Overview

The **Smart City Waste Management System** is a full-stack web application for managing urban waste collection. It provides:

- **Citizen Portal**: Submit waste pickup requests, track status, earn rewards
- **Driver Dashboard**: View assigned pickups, update collection status, route navigation
- **Admin Panel**: Monitor operations, manage users, view analytics and reports
- **API Endpoints**: RESTful API for mobile/IoT integration

### What Makes This Special?

This project goes beyond a typical college project by integrating **professional-grade DevOps practices**:

| Feature | Technology | Status |
|---------|-----------|--------|
| CI/CD Pipeline | GitHub Actions | ✅ Active |
| Containerization | Docker + Docker Compose | ✅ Ready |
| Reverse Proxy | Nginx | ✅ Configured |
| Monitoring | Prometheus + Grafana | ✅ Ready |
| Logging | Grafana Loki + Promtail | ✅ Ready |
| Backups | Laravel Scheduler + Cron | ✅ Active |
| Security | Dependabot + Audits | ✅ Active |
| SSL/HTTPS | Render SSL | ✅ Active |

---

## 🛠️ Tech Stack

### Application
| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2) |
| Frontend | Blade Templates + Bootstrap + JavaScript |
| Database | SQLite (default) / MySQL / PostgreSQL |
| Cache | Redis (Docker) / Array (production) |
| Authentication | Laravel Breeze |

### DevOps
| Tool | Purpose |
|------|---------|
| GitHub Actions | CI/CD pipeline automation |
| Docker | Application containerization |
| Docker Compose | Multi-service orchestration |
| Nginx | Reverse proxy, static files, security |
| Prometheus | Metrics collection & alerting |
| Grafana | Monitoring dashboards & log viewer |
| Grafana Loki | Centralized log aggregation |
| Promtail | Log shipping agent |
| Dependabot | Dependency vulnerability scanning |

---

## 📐 Architecture

### DevOps Pipeline Flow

```
┌──────────────────────────────────────────────────────────────────────┐
│                        DEVELOPER WORKFLOW                            │
│                                                                      │
│   Local Dev ──► Git Push ──► GitHub Actions (CI/CD)                  │
│                                  │                                   │
│                          ┌───────┴───────┐                           │
│                          │  Test & Build  │                          │
│                          │  ✓ PHP Setup   │                          │
│                          │  ✓ Composer    │                          │
│                          │  ✓ npm Build   │                          │
│                          │  ✓ PHPUnit     │                          │
│                          └───────┬───────┘                           │
│                                  │ (on main branch)                  │
│                          ┌───────▼───────┐                           │
│                          │    Deploy to   │                          │
│                          │     Render     │                          │
│                          └───────┬───────┘                           │
│                                  │                                   │
│                          ┌───────▼───────┐                           │
│                          │   Production  │                           │
│                          │  Application  │                          │
│                          └───────────────┘                           │
└──────────────────────────────────────────────────────────────────────┘
```

### Docker Compose Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                    Docker Compose Network                            │
│                                                                     │
│  ┌──────────┐     ┌──────────┐     ┌──────────┐                    │
│  │  Nginx   │────►│ Laravel  │────►│  Redis   │                    │
│  │  :8080   │     │ PHP-FPM  │     │  :6379   │                    │
│  └──────────┘     │  :9000   │     └──────────┘                    │
│                   └──────────┘                                      │
│                        │                                            │
│                   ┌────▼────┐                                       │
│                   │ SQLite  │                                       │
│                   │   DB    │                                       │
│                   └─────────┘                                       │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────┐       │
│  │              Monitoring Stack                             │       │
│  │  ┌────────────┐  ┌────────────┐  ┌────────────────────┐ │       │
│  │  │ Prometheus │  │  Grafana   │  │ Loki + Promtail    │ │       │
│  │  │   :9090    │  │   :3000    │  │ :3100              │ │       │
│  │  └────────────┘  └────────────┘  └────────────────────┘ │       │
│  └──────────────────────────────────────────────────────────┘       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🚀 Quick Start

### Option 1: Docker Compose (Recommended)

```bash
# 1. Clone the repository
git clone https://github.com/vikashgupta9752/Waste-management.git
cd Waste-management

# 2. Copy Docker environment file
cp .env.docker .env

# 3. Start all services
docker-compose up -d

# 4. Install dependencies (first time only)
docker exec waste-mgmt-app composer install
docker exec waste-mgmt-app php artisan key:generate
docker exec waste-mgmt-app php artisan migrate --seed

# 5. Access the application
# App:       http://localhost:8080
# Grafana:   http://localhost:3000 (admin/admin)
# Prometheus: http://localhost:9090
```

### Option 2: Local Development

```bash
# 1. Clone and install dependencies
git clone https://github.com/vikashgupta9752/Waste-management.git
cd Waste-management
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed

# 3. Start development server
composer dev
# Or: php artisan serve & npm run dev
```

### Default Login Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@waste.com | password |
| Driver | driver@waste.com | password |
| Citizen | citizen@waste.com | password |

---

## ⚙️ DevOps Features

### 1. 🔄 CI/CD Pipeline

**File**: `.github/workflows/laravel.yml`

The CI/CD pipeline runs automatically on every push to GitHub:

```
Push to GitHub ──► Install Dependencies ──► Build Assets ──► Run Tests ──► Deploy to Render
```

**What it does:**
1. Sets up PHP 8.2 and Node.js 20
2. Installs Composer and npm dependencies
3. Generates Laravel application key
4. Builds frontend assets with Vite
5. Runs PHPUnit test suite
6. On `main` branch: triggers Render deployment

**Setup Render Deploy Hook:**
1. Go to Render Dashboard → Your Service → Settings
2. Copy the **Deploy Hook URL**
3. Go to GitHub → Repository → Settings → Secrets → Actions
4. Add secret: `RENDER_DEPLOY_HOOK_URL` = your URL

**Security Scan** (`.github/workflows/security-scan.yml`):
- Runs weekly on Mondays
- Checks PHP (`composer audit`) and JS (`npm audit`) vulnerabilities
- Also runs on pull requests

---

### 2. 🐳 Docker Integration

**Files**: `Dockerfile.dev`, `docker-compose.yml`, `.env.docker`

| Service | Port | Description |
|---------|------|-------------|
| `app` | 9000 | Laravel PHP-FPM |
| `nginx` | 8080 | Web server |
| `redis` | 6379 | Cache & sessions |
| `prometheus` | 9090 | Metrics collection |
| `grafana` | 3000 | Dashboards |
| `loki` | 3100 | Log aggregation |
| `promtail` | — | Log shipper |

**Commands:**
```bash
docker-compose up -d              # Start all services
docker-compose down               # Stop all services
docker-compose logs -f app        # View Laravel logs
docker-compose ps                 # Check service status
docker-compose restart app        # Restart a service
docker-compose down -v            # Stop and remove all data
```

---

### 3. 🌐 Nginx Reverse Proxy

**File**: `docker/nginx-docker.conf`

Features:
- Rate limiting (10 req/sec per IP with burst of 20)
- Security headers (HSTS, X-Frame-Options, X-Content-Type-Options)
- Gzip compression for all text-based content
- 1-year cache for static assets
- FastCGI proxy to PHP-FPM
- Hidden file protection (.env, .git, etc.)
- Nginx stub_status for monitoring

---

### 4. 📊 Monitoring & Observability

**Access**: http://localhost:3000 (Grafana, login: admin/admin)

**Prometheus** scrapes metrics from:
- Application health endpoint (`/api/devops/health`)
- Laravel custom metrics (`/api/devops/metrics`)
- Nginx connection stats (`/nginx_status`)

**Grafana Dashboard** includes:
- 🟢 Service health status (UP/DOWN indicators)
- 📈 Prometheus performance graphs
- 📊 Scrape target monitoring
- 📝 Laravel application logs (via Loki)
- 🌐 Nginx access & error logs (via Loki)

**Laravel Metrics Endpoint** (`GET /api/devops/metrics`):
- Application info and uptime
- Database connection status
- Cache connection status
- Storage disk space
- Log file size

---

### 5. 📝 Centralized Logging

**Stack**: Promtail → Loki → Grafana

```
Laravel Logs (storage/logs/)  ──┐
                                 ├──► Promtail ──► Loki ──► Grafana
Nginx Logs (/var/log/nginx/) ──┘
```

**What's tracked:**
- Laravel application errors and warnings
- HTTP request access logs
- Nginx error logs
- User and driver activities (via Laravel logs)

**View Logs in Grafana:**
1. Open http://localhost:3000
2. Go to **Explore** → Select **Loki** data source
3. Query: `{app="waste-management",service="laravel"}`

---

### 6. 💾 Automated Backups

**Artisan Command**: `php artisan backup:database`

```bash
# Create a backup manually
php artisan backup:database

# Create backup and cleanup old ones
php artisan backup:database --cleanup

# Docker:
docker exec waste-mgmt-app php artisan backup:database
```

**Schedule**: Runs automatically daily at 2:00 AM
**Retention**: 7 days (configurable)
**Location**: `storage/backups/`
**Supports**: SQLite, MySQL, PostgreSQL

📖 See [BACKUP_RESTORE_GUIDE.md](BACKUP_RESTORE_GUIDE.md) for full restore instructions.

---

### 7. 🔒 Security Scanning

**File**: `.github/dependabot.yml`

Dependabot monitors:
- PHP packages (Composer) — weekly
- JavaScript packages (npm) — weekly
- Docker base images — weekly
- GitHub Actions versions — weekly

**Automated security scan** (`.github/workflows/security-scan.yml`):
- Runs `composer audit` for PHP vulnerabilities
- Runs `npm audit` for JavaScript vulnerabilities
- Triggers weekly and on pull requests

**Manual security check:**
```bash
composer audit            # Check PHP packages
npm audit                 # Check JavaScript packages
```

---

### 8. 🔐 SSL / HTTPS

**Production (Render)**: ✅ Automatic SSL via Render's built-in HTTPS

**Nginx Security Headers** (always active):
- `Strict-Transport-Security` (HSTS)
- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`

📖 See [docker/nginx/ssl/README.md](docker/nginx/ssl/README.md) for self-hosted SSL setup.

---

## 📁 Project Structure

```
waste-management/
├── .github/
│   ├── workflows/
│   │   ├── laravel.yml              # CI/CD pipeline
│   │   └── security-scan.yml       # Security scanning
│   └── dependabot.yml              # Dependency updates
├── app/
│   ├── Console/Commands/
│   │   └── BackupDatabase.php      # Backup command
│   ├── Http/Controllers/           # Application controllers
│   ├── Models/                     # Eloquent models
│   └── Services/                   # Business logic services
├── docker/
│   ├── backup/
│   │   └── backup.sh               # Shell backup script
│   ├── grafana/
│   │   ├── dashboards/
│   │   │   └── waste-management-overview.json
│   │   └── provisioning/
│   │       ├── dashboards/dashboard.yml
│   │       └── datasources/datasources.yml
│   ├── loki/
│   │   └── loki-config.yml         # Loki log aggregation config
│   ├── nginx/
│   │   └── ssl/README.md           # SSL setup guide
│   ├── prometheus/
│   │   └── prometheus.yml          # Metrics scrape config
│   ├── promtail/
│   │   └── promtail-config.yml     # Log shipping config
│   ├── entrypoint.sh               # Production Docker entrypoint
│   ├── nginx.conf                  # Production Nginx config
│   ├── nginx-docker.conf           # Docker Compose Nginx config
│   ├── php-production.ini          # PHP production settings
│   └── supervisord.conf            # Process manager config
├── Dockerfile                       # Production Dockerfile
├── Dockerfile.dev                   # Development Dockerfile
├── docker-compose.yml               # Multi-service orchestration
├── .env.docker                      # Docker environment variables
├── .env.example                     # Environment template
├── BACKUP_RESTORE_GUIDE.md         # Backup/restore documentation
└── README.md                        # This file
```

---

## 🚢 Deployment

### Production (Render)

The application is deployed at: **https://waste-management-tu84.onrender.com/**

**Automatic deployment** is triggered when:
1. Code is pushed to `main` branch
2. GitHub Actions tests pass
3. Render Deploy Hook is called

**Environment variables** on Render:
| Variable | Value |
|----------|-------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | (generated) |
| `DB_CONNECTION` | `sqlite` |
| `LOG_CHANNEL` | `stack` |

---

## 🔧 Troubleshooting

| Issue | Solution |
|-------|---------|
| Docker containers won't start | Run `docker-compose down -v` then `docker-compose up -d` |
| Permission denied on storage | `chmod -R 775 storage bootstrap/cache` |
| Grafana shows no data | Wait 30s for Prometheus to start scraping |
| Loki logs empty | Check Promtail: `docker logs waste-mgmt-promtail` |
| CI/CD fails | Check GitHub Actions tab for error details |
| Backup fails | Ensure `storage/backups/` directory exists |
| Redis connection refused | Verify Redis is running: `docker-compose ps redis` |

---

## 📄 License

This project is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

<p align="center">
  Built with ❤️ using Laravel • Docker • GitHub Actions • Prometheus • Grafana
</p>
