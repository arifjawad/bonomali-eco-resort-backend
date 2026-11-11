# BRIDGE — Backend (bonomali-eco-resort-backend)

Handoff file between Claude chat (planning) and Claude Code (building). **Read this first, update it last.**
Last updated: 2026-11-12 · Active phase: 2 (booking admin MVP) + 3 (CRM-lite) in this repo; 1 (landing site) in the frontend repo.

## Where things are
- Backend repo (this): https://github.com/arifjawad/bonomali-eco-resort-backend — plan in `docs/plan.md`, workflow in `docs/workflow.md`, API contract in `docs/api.md`, **data model in `docs/data-model.md`**
- Frontend repo: https://github.com/arifjawad/bonomali-eco-resort — branch `feature/phase1-landing-port` has the ported landing page; see its `docs/BRIDGE.md`
- Plan whiteboard: https://claude.ai/artifact/Kcxn88DZ6hzcxvLXzx6Ln6
- Branch stack (none pushed yet): `docs/tracking-files` (PR #1) → `feature/scaffold` → **`feature/admin-dashboard`** (latest work)

## Stack
Laravel 13 (PHP 8.3) API + Filament 5 admin (Livewire 4). MySQL in staging/prod, SQLite locally. Redis (Horizon) planned for queues; database queue locally. S3-compatible storage later. PHPUnit tests, Pint.

## Current state
- **Data model proposed and migrated** (`docs/data-model.md`): users.role/is_active, room_types, rooms, rates, guests, bookings, inquiries. Money is integer BDT. Waiting for Arif's sign-off on the `feature/admin-dashboard` PR.
- **Admin panel** at `/admin` (Bonomali Admin):
  - Bookings group: Bookings (create/edit form with guest search + "new guest", room, dates, live total; table with status badges, filters by status/room/stay dates; Confirm / Check in / Check out / Cancel actions), Inquiries (status badges, new-count badge in nav, "Convert to booking").
  - Guests group: Guests with booking history relation manager, tags, source.
  - Rooms group: Room types (drag to reorder), Rooms, Seasonal rates.
  - Settings group: Staff accounts (owner only).
  - Dashboard: today's stats (arrivals, departures, in-house, occupancy tonight, new inquiries, this month's confirmed revenue), bookings-per-month chart, arrivals in the next 7 days. FilamentInfoWidget removed.
- **Access**: owner does everything. Staff create/edit bookings, guests, inquiries and rooms, and can see room types. Owner-only: staff accounts, seasonal rates, changing room types (they hold base prices), and deleting anything. Inactive users cannot sign in. Finished bookings (checked out / cancelled) are read-only (view page).
- Seeders: owner from `.env` (role owner), room catalogue from the frontend draft (3 types, 10 rooms, one "Winter peak" rate) in every env; demo guests/bookings/inquiries in `local` only. Local DB has been `migrate:fresh --seed`ed.
- App timezone is `Asia/Dhaka` (`APP_TIMEZONE`), so "today" on the dashboard is resort time.
- `/api/v1/status` only; no public booking endpoints yet.
- 66 tests pass; Pint clean. CI not yet seen running on GitHub.

## Code map
- `app/Enums` — `UserRole`, `RoomCategory`, `Source`, `BookingStatus` (allowed transitions live here), `InquiryStatus`
- `app/Models` — `RoomType`, `Room`, `Rate`, `Guest`, `Booking` (scopes: `notCancelled`, `overlapping`, `staying`), `Inquiry`, `User` (`isOwner()`, `canAccessPanel()`)
- `app/Services`
  - `PricingService` — nightly price (seasonal rate overrides base), stay total
  - `AvailabilityService` — is a room free for check-in..check-out, occupancy % for a night
  - `BookingService` — create/update manual bookings (availability, guest count, total, reference `BNM-YYYY-NNNN`), quote for the form preview, confirm / checkIn / checkOut / cancel
  - `InquiryService` — convert an inquiry into guest (matched by phone) + booking
- `app/Exceptions/BookingException.php` — rule broken; message shown to staff as a notification
- `app/Policies/*Policy.php` — access rules above (auto-discovered)
- `app/Filament/Resources/{Bookings,Inquiries,Guests,RoomTypes,Rooms,Rates,Users}` — admin screens; Booking create/edit pages call `BookingService`
- `app/Filament/Widgets` — `TodayStats`, `BookingsPerMonthChart`, `UpcomingArrivals`
- `database/seeders` — `DatabaseSeeder` (owner), `RoomCatalogSeeder`, `DemoDataSeeder` (local only)
- `tests/Feature/Services` — service rules; `tests/Feature/Admin` — panel access, policies, screens, dashboard, seeders

## What the frontend expects from this API (from the draft)
- Room types: name, category (ac / non-ac), vibe tag, image, tag line, description, amenities, nightly price. Now in `room_types` (seeded from `src/data/content.ts`).
- Pricing rules in draft calculator: extra guest 1,200 BDT/guest/night beyond 2; candlelit BBQ upgrade 800/guest/night; speedboat transfer 1,500 round trip. Packages: 1, 2, 3 nights. **Not priced in the backend yet.**
- Enquiry payload: name, phone, dates, guests, cottage preference, message → maps 1:1 to `inquiries`.

## Decisions made
- Scaffolded ahead of the open questions (Arif's call, 2026-11-12); the data model is kept minimal so answers can still reshape it.
- SQLite for local dev; MySQL for staging/prod via `.env`.
- No Sanctum yet: public endpoints need no auth and the admin uses Filament's session login.
- Admin seed values go through `config/bonomali.php`, never `env()` in app code.
- Money is integer BDT everywhere. Statuses/sources are PHP enums stored as strings.
- One room per booking for now; `booking_items` later.
- Pricing: base price per night unless a seasonal rate covers the night (inclusive dates); overlapping rates → the latest-starting one wins. No extras, no extra-guest charge, no manual discount/override yet; total recalculates on every save.
- Availability: a non-cancelled booking blocks its nights; the check-out day is free (back-to-back stays allowed). Inactive rooms can't be booked. Guests (adults + children) must fit `max_guests`.
- Status flow: pending → confirmed → checked in → checked out; pending/confirmed → cancelled. No date check on check-in (staff may check in early/late).
- Booking reference: `BNM-<year booked>-<4-digit running number>`, restarts each January.
- "Revenue this month" = total of confirmed/in-house/checked-out bookings with check-in this month (expected revenue, not cash; payments are Phase 4).
- Inquiry conversion reuses a guest with the same phone number, else creates one.
- Timezone Asia/Dhaka for the app.

## Open questions (from plan.md, plus new)
- Room types/count, day-out/event packages, instant booking vs enquiry-only at launch, guest payment methods, domain and hosting.
- New: real room names/numbers and max guests per type (seeded placeholders: 4 Riverview, 4 Eco, 2 Family; 3/3/5 guests).
- New: should the extra-guest charge (1,200/night beyond 2) apply to manual bookings? Should staff be able to override a total (discounts)?

## Next steps
1. Merge PR #1, then PR `feature/scaffold`, then PR `feature/admin-dashboard` (Arif signs off the data model there). Confirm CI green.
2. Arif tries the admin locally (`php artisan migrate:fresh --seed`, log in with the `.env` owner) and answers the new open questions.
3. `/api/v1` public endpoints: rooms, availability, quote, inquiry (Form Requests + API Resources, reusing the services); document in `docs/api.md`.
4. Pricing extras / discounts once decided.
5. Later (heavy, see TODO): email/SMS confirmations via queues, calendar view, booking_items, payments, Meta leads, uploads, activity log.
