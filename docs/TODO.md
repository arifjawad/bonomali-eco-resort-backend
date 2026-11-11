# TODO — Backend

Legend: [x] done · [ ] open. Phases per `docs/plan.md`.

## Phase 0 — Plan
- [x] Repos created with CLAUDE.md, plan, workflow
- [x] Tracking docs (BRIDGE, TODO, DAILY_LOG)
- [ ] Answer open questions (rooms/packages, instant booking vs enquiry, payments, domain/hosting)
- [ ] Confirm room names/counts and max guests per type (seeded placeholders)
- [ ] Decide extra-guest charge and manual discounts for bookings

## Phase 2 — Booking admin MVP (in progress)
- [x] Scaffold Laravel 13 + Filament 5, `.env.example` (SQLite local, MySQL prod)
- [x] Owner admin user seeded from `.env`
- [x] `/api/v1` route group + status endpoint, CORS limited to `FRONTEND_URL`, `docs/api.md`
- [x] CI: GitHub Actions (Pint + `php artisan test`)
- [ ] Confirm CI green on GitHub once the PR is open
- [ ] Docker/dev setup (optional: MySQL + Redis locally)
- [x] Users and roles (owner, staff); `canAccessPanel()` = active users; policies
- [x] Data model proposed in `docs/data-model.md` — [ ] Arif signs it off on the PR
- [x] room_types, rooms, rates (seasonal pricing) + admin screens
- [x] PricingService, AvailabilityService, BookingService (manual bookings, status flow)
- [x] Manual bookings screen with live total and status actions
- [x] Dashboard: today's stats, bookings per month, upcoming arrivals
- [x] Seed demo rooms matching the frontend draft (+ local demo guests/bookings/inquiries)
- [ ] `/api/v1` public endpoints: rooms, availability, quote, inquiry; document in `docs/api.md`
- [ ] Pricing extras (extra guest, BBQ, speedboat) and manual discount — after decision

## Phase 3 — Customers
- [x] Guests, booking history, tags, source tracking
- [x] Inquiries as leads with source; convert to booking (InquiryService)
- [ ] Segments / saved filters on guests (if needed)

## Later — heavy features (design for, don't build yet)
- [ ] Visual booking calendar (Filament calendar plugin or custom page)
- [ ] booking_items / multi-room bookings and extras as line items
- [ ] Email/SMS confirmations, queued via Redis/Horizon
- [ ] Queues/Horizon setup
- [ ] Photo uploads to S3-compatible storage (room types use an image path for now)
- [ ] Activity log (who changed what)
- [ ] Phase 4: `PaymentGateway` interface; pay at arrival first, then bKash/SSLCommerz/Stripe
- [ ] Phase 5: Meta Lead Ads webhook, leads pipeline, AI post/ad drafts, cost per booking
