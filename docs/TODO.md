# TODO — Backend

Legend: [x] done · [ ] open. Phases per `docs/plan.md`.

## Phase 0 — Plan
- [x] Repos created with CLAUDE.md, plan, workflow
- [x] Tracking docs (BRIDGE, TODO, DAILY_LOG)
- [ ] Answer open questions (rooms/packages, instant booking vs enquiry, payments, domain/hosting)

## Phase 2 — Booking admin MVP (next)
- [ ] Scaffold Laravel + Filament, `.env.example`, Docker/dev setup
- [ ] CI: GitHub Actions (Pint + `php artisan test`)
- [ ] Users and roles (owner, staff)
- [ ] room_types, rooms, rates (seasonal pricing) — flag data model before migrating
- [ ] Booking calendar and availability
- [ ] Manual bookings + booking_items
- [ ] Email/SMS confirmations (queued via Redis/Horizon)
- [ ] `/api/v1` public endpoints: rooms, availability, quote, inquiry; document in `docs/api.md`
- [ ] Seed demo rooms matching the frontend draft

## Phase 3 — Customers
- [ ] Guests, booking history, tags/segments, source tracking
- [ ] Inquiries become leads with source

## Later (design for, don't build)
- [ ] Phase 4: `PaymentGateway` interface; pay at arrival first, then bKash/SSLCommerz/Stripe
- [ ] Phase 5: Meta Lead Ads webhook, leads pipeline, AI post/ad drafts, cost per booking
