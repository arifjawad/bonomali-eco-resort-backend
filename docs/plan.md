# Bonomali Project Plan

Goal: a travel-style landing site for Bonomali resort, plus a backend to manage bookings, guests, and later payments and Meta ads.

## Phases
| # | Phase | Scope | Status |
|---|-------|-------|--------|
| 0 | Plan | Scope, rooms and packages, brand and photos, data model | Build now |
| 1 | Landing site | Hero, gallery, rooms/packages, enquiry/WhatsApp, Meta Pixel + Conversions API hooks | Build now |
| 2 | Booking admin MVP | Room types, calendar, seasonal pricing, manual bookings, email/SMS confirmations | Build now |
| 3 | Customers | Guest profiles, booking history, tags/segments, source tracking | Build now |
| 4 | Payments | Pay at arrival first, gateway interface, then bKash/SSLCommerz/Stripe | Later |
| 5 | Meta ads | Lead Ads webhook, leads pipeline, AI post/ad drafts, cost per booking | Later |

## Stack
- Public site: Next.js (SEO, mobile-first, English + Bangla)
- Backend: Laravel API + Filament admin
- Data: MySQL, Redis (queues), S3-compatible storage

## Core data model (draft)
users and roles, room_types, rooms, rates, bookings, booking_items, guests, payments, leads, campaigns, activity_log.

## Open questions
- Room types/count, and day-out or event packages?
- Instant online booking vs enquiry-only at launch?
- Payment methods guests actually use?
- Domain and hosting?
