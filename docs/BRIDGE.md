# BRIDGE — Backend (bonomali-eco-resort-backend)

Handoff file between Claude chat (planning) and Claude Code (building). **Read this first, update it last.**
Last updated: 2026-11-11 · Active phase overall: 1 (Landing site, in the frontend repo). Backend work starts at Phase 2.

## Where things are
- Backend repo (this): https://github.com/arifjawad/bonomali-eco-resort-backend — plan in `docs/plan.md`, workflow in `docs/workflow.md`
- Frontend repo: https://github.com/arifjawad/bonomali-eco-resort — branch `feature/phase1-landing-port` has the ported landing page; see its `docs/BRIDGE.md`
- Plan whiteboard: https://claude.ai/artifact/Kcxn88DZ6hzcxvLXzx6Ln6

## Stack
Laravel API + Filament admin, MySQL, Redis (Horizon), S3-compatible storage. Pest/PHPUnit, Pint.

## Current state
- Docs and CLAUDE.md only. **Laravel app not scaffolded yet.**
- Frontend draft landing page is done and awaiting Arif's review. It currently sends enquiries via WhatsApp only; rooms/prices are hard-coded in the frontend's `src/data/content.ts`.

## What the frontend expects from this API (from the draft)
- Room types: name, category (ac / non-ac), vibe tag, image, tag line, description, amenities, nightly price. Draft data: Premium Riverview AC Suite 7,500, Traditional Mangrove Eco 5,500, Family Eco Grand Suite 9,000 (BDT/night).
- Pricing rules in draft calculator: extra guest 1,200 BDT/guest/night beyond 2; candlelit BBQ upgrade 800/guest/night; speedboat transfer 1,500 round trip. Packages: 1, 2, 3 nights.
- Enquiry payload: name, phone, dates, guests, cottage preference, message (becomes a lead with source).

## Decisions made
- Backend scaffold waits for Arif's review of the landing page and answers to open questions below.
- No data model changes made yet. Any schema change is flagged first and ships as a migration.

## Open questions (from plan.md)
Room types/count, day-out/event packages, instant booking vs enquiry-only at launch, guest payment methods, domain and hosting.

## Next steps
1. Arif answers open questions.
2. Scaffold Laravel + Filament on `feature/scaffold` (Phase 2): auth/roles, room_types, rooms, rates, bookings, guests, calendar.
3. Define `/api/v1` contract in `docs/api.md`; add CI (Pint + tests).
