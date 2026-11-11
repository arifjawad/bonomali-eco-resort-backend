# BRIDGE — Backend (bonomali-eco-resort-backend)

Handoff file between Claude chat (planning) and Claude Code (building). **Read this first, update it last.**
Last updated: 2026-11-12 · Active phase: 1 (landing site, frontend repo) and 2 (backend scaffold, this repo).

## Where things are
- Backend repo (this): https://github.com/arifjawad/bonomali-eco-resort-backend — plan in `docs/plan.md`, workflow in `docs/workflow.md`, API contract in `docs/api.md`
- Frontend repo: https://github.com/arifjawad/bonomali-eco-resort — branch `feature/phase1-landing-port` has the ported landing page; see its `docs/BRIDGE.md`
- Plan whiteboard: https://claude.ai/artifact/Kcxn88DZ6hzcxvLXzx6Ln6
- Branch with latest work: `feature/scaffold` (stacked on `docs/tracking-files`, PR #1; merge that first)

## Stack
Laravel 13 (PHP 8.3) API + Filament 5 admin (Livewire 4). MySQL in staging/prod, SQLite locally. Redis (Horizon) planned for queues; database queue locally. S3-compatible storage later. PHPUnit tests, Pint.

## Current state
- Laravel app scaffolded with Filament admin panel at `/admin` ("Bonomali Admin", emerald theme, login only, no public sign-up).
- `User` implements `FilamentUser`; `canAccessPanel()` returns true for every user until roles exist.
- Owner login is seeded from `ADMIN_NAME` / `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env` (read via `config/bonomali.php`); seeding skips it if unset, so no default password ships.
- `/api/v1` route group in `routes/api.php` with `GET /api/v1/status`. CORS limited to `FRONTEND_URL` (default `http://localhost:3000`).
- CI: `.github/workflows/ci.yml` runs Pint (`--test`) and `php artisan test` on PHP 8.3. Not yet seen running on GitHub.
- Only Laravel's default tables (users, cache, jobs, sessions). **No resort tables yet.**
- 10 feature tests pass locally; Pint clean.

## Code map
- `app/Providers/Filament/AdminPanelProvider.php` — panel config (path, brand, colours, discovery)
- `app/Filament/{Resources,Pages,Widgets}` — auto-discovered admin screens (none yet)
- `routes/api.php` + `app/Http/Controllers/Api/V1/*` — public API
- `config/bonomali.php` — resort-specific settings (admin seed)
- `database/seeders/DatabaseSeeder.php` — owner user
- `tests/Feature/{Api,Admin}` — API, CORS, panel access, seeder tests

## What the frontend expects from this API (from the draft)
- Room types: name, category (ac / non-ac), vibe tag, image, tag line, description, amenities, nightly price. Draft data: Premium Riverview AC Suite 7,500, Traditional Mangrove Eco 5,500, Family Eco Grand Suite 9,000 (BDT/night).
- Pricing rules in draft calculator: extra guest 1,200 BDT/guest/night beyond 2; candlelit BBQ upgrade 800/guest/night; speedboat transfer 1,500 round trip. Packages: 1, 2, 3 nights.
- Enquiry payload: name, phone, dates, guests, cottage preference, message (becomes a lead with source).

## Decisions made
- Scaffolded ahead of the open questions (Arif's call, 2026-11-12); the scaffold doesn't depend on them, the data model does.
- SQLite for local dev (no MySQL/Docker on the dev machine yet); MySQL for staging/prod via `.env`.
- No Sanctum yet: public endpoints need no auth and the admin uses Filament's session login. Add Sanctum if the frontend ever needs authenticated calls.
- Admin seed values go through `config/bonomali.php`, never `env()` in app code (keeps `config:cache` safe).
- No data model changes made yet. Any schema change is flagged first and ships as a migration.

## Open questions (from plan.md)
Room types/count, day-out/event packages, instant booking vs enquiry-only at launch, guest payment methods, domain and hosting.

## Next steps
1. Merge backend PR #1 (`docs/tracking-files`), then open a PR for `feature/scaffold`; confirm CI goes green on GitHub.
2. Arif answers open questions.
3. Propose the Phase 2 data model (roles, room_types, rooms, rates, bookings, booking_items, guests) for sign-off, then migrate.
4. Filament resources for room types, rooms, rates; seed demo rooms matching the frontend draft.
5. `/api/v1` rooms, availability, quote, inquiry endpoints; document each in `docs/api.md`.
