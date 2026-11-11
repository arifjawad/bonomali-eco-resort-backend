# Bonomali Eco Resort — Backend

Laravel API + Filament admin for the Bonomali resort: bookings, guests, payments (later), Meta leads/ads (later).
Frontend lives in the separate repo `arifjawad/bonomali-eco-resort` (Next.js).

## Stack
- Laravel (PHP), Filament admin panel
- MySQL, Redis (queues via Horizon), S3-compatible storage
- Pest/PHPUnit for tests, Laravel Pint for code style

## Phases
0 Plan -> 1 Landing site -> 2 Booking admin MVP -> 3 Customers (CRM-lite) -> 4 Payments -> 5 Meta leads/ads + AI drafts.
Phases 0-3 are build-now. 4-5 are later: design for them, don't build them yet.
Full plan: `docs/plan.md`. Workflow: `docs/workflow.md`.

## Start here
Read `docs/BRIDGE.md` first (current state, decisions, next steps), then `docs/TODO.md`.
At the end of every working session, append to `docs/DAILY_LOG.md`, tick `docs/TODO.md`, refresh `docs/BRIDGE.md`, and push.

## Rules
- Work one phase/module at a time; say which phase a change belongs to.
- Feature branches off `main`; small commits; PR into `main` (use `staging` for pre-prod checks).
- Flag data model changes before making them. Every schema change is a migration.
- Payments go through a gateway interface (`PaymentGateway`); no gateway-specific code in controllers.
- Public API is versioned under `/api/v1`. Keep the contract documented in `docs/api.md` once it exists.
- Never commit secrets. Use `.env`; keep `.env.example` current.
- Prefer simple and shippable over clever. State assumptions.

## Code style
- Readable over clever: small methods, clear names, no extra abstraction layers. Someone new should follow it at a glance.
- Service pattern: business logic lives in `app/Services/*Service.php` (e.g. `BookingService`, `PricingService`). Controllers, Filament resources, jobs and seeders stay thin and call a service.
- Validation in Form Requests; JSON output through API Resources.
- Don't create a service for trivial code (a one-line response or plain CRUD Filament handles itself).
- Short comments only where the "why" isn't obvious.

## Commands
- Install: `composer install`, then `cp .env.example .env`, `php artisan key:generate`, `php artisan migrate --seed`
- Test: `php artisan test`
- Style: `./vendor/bin/pint` (CI runs `--test`)
- Dev: `php artisan serve` (admin at `/admin`, API at `/api/v1`)
- Stack versions: Laravel 13, Filament 5, Livewire 4. Check `vendor/` source or the package docs before using unfamiliar APIs; Filament 5 differs from older tutorials.
