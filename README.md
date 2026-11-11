# Bonomali Eco Resort — Backend

Laravel API and Filament admin for managing bookings, guests and (later) payments and Meta ads for Bonomali resort.

- Frontend repo: https://github.com/arifjawad/bonomali-eco-resort
- Plan: [docs/plan.md](docs/plan.md) · Dev workflow: [docs/workflow.md](docs/workflow.md) · API: [docs/api.md](docs/api.md)
- Handoff / current state: [docs/BRIDGE.md](docs/BRIDGE.md) · Todo: [docs/TODO.md](docs/TODO.md) · Log: [docs/DAILY_LOG.md](docs/DAILY_LOG.md)

## Requirements
PHP 8.3+ with the `intl`, `mbstring`, `pdo_sqlite` and `zip` extensions, and Composer 2. No database server needed locally (SQLite).

## Run locally
```bash
composer install
cp .env.example .env
php artisan key:generate
# set ADMIN_EMAIL and ADMIN_PASSWORD in .env for your admin login
php artisan migrate --seed   # creates database/database.sqlite if asked
php artisan serve            # http://localhost:8000
```

- Admin panel: http://localhost:8000/admin (sign in with the `ADMIN_*` values from `.env`)
- API: http://localhost:8000/api/v1/status
- Run the frontend alongside it on http://localhost:3000 (allowed by `FRONTEND_URL`)

## Checks
```bash
php artisan test
./vendor/bin/pint
```

Status: Phase 2 started. Laravel + Filament scaffolded; no resort tables yet.
