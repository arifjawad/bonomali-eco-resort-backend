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

## Commands (fill in after scaffold)
- Install: `composer install`
- Test: `php artisan test`
- Style: `./vendor/bin/pint`
- Dev: `php artisan serve`
