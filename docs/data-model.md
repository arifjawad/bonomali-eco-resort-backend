# Data model — Phase 2/3 (booking admin MVP + CRM-lite)

Status: **proposed on `feature/admin-dashboard`, waiting for Arif's sign-off.** Everything below ships as migrations, so it is cheap to change before it reaches staging.

Conventions
- Money is an **integer number of BDT** (no paisa, no floats). `7500` means 7,500 BDT.
- Dates without time (`date`) for stays. A stay is `check_in` (first night) to `check_out` (departure morning); the `check_out` day itself is free for the next guest.
- Statuses and sources are PHP enums in `app/Enums`, stored as short strings.
- Every table has `id` and `created_at` / `updated_at`.

## users (Laravel default + 2 columns)
| column | type | notes |
|---|---|---|
| role | string, default `staff` | `App\Enums\UserRole`: `owner` / `staff` |
| is_active | boolean, default true | inactive users cannot sign in to the admin |

The owner seeded from `.env` (`ADMIN_*`) always gets `role = owner`.

## room_types
What guests book and what the website shows ("Premium Riverview AC Suite").

| column | type | notes |
|---|---|---|
| name | string | |
| slug | string, unique | for URLs and the API |
| category | string | `App\Enums\RoomCategory`: `ac` / `non_ac` |
| vibe_tag | string, nullable | short label on the card ("Riverview", "Eco") |
| tagline | string, nullable | |
| description | text, nullable | |
| amenities | json | list of strings |
| base_price | unsigned int | BDT per night when no seasonal rate applies |
| max_guests | unsigned tinyint | adults + children allowed per room |
| image_path | string, nullable | path/URL for now; S3 uploads later |
| is_active | boolean | hidden from booking/site when false |
| sort_order | unsigned int | display order |

## rooms
The physical cottages/rooms of a type. A booking is for one room.

| column | type | notes |
|---|---|---|
| room_type_id | FK room_types (restrict delete) | |
| name | string, unique | e.g. `R-101`, "Kewra cottage" |
| is_active | boolean | out of service when false (no new bookings) |
| notes | text, nullable | |

## rates (seasonal pricing)
| column | type | notes |
|---|---|---|
| room_type_id | FK room_types (cascade delete) | |
| name | string | "Eid peak", "Winter season" |
| starts_on | date | first night covered (inclusive) |
| ends_on | date | last night covered (inclusive) |
| price_per_night | unsigned int | BDT, replaces `base_price` for those nights |

If two rates of the same room type cover the same night, the one that **starts latest** wins (a short "Eid peak" inside a long "Winter season" overrides it).

## guests (CRM-lite)
| column | type | notes |
|---|---|---|
| name | string | |
| phone | string, indexed | main contact (WhatsApp); not unique, families share numbers |
| email | string, nullable | |
| address | string, nullable | |
| notes | text, nullable | |
| source | string | `App\Enums\Source`: `whatsapp` / `walk_in` / `phone` / `website` / `facebook` / `other` |
| tags | json | simple list of strings ("VIP", "corporate") |

## bookings
| column | type | notes |
|---|---|---|
| reference | string, unique | `BNM-2026-0001` (year booked + running number per year) |
| guest_id | FK guests (restrict delete) | |
| room_id | FK rooms (restrict delete) | one room per booking for now |
| check_in / check_out | date | `check_out` > `check_in` |
| adults / children | unsigned tinyint | |
| status | string, indexed | `App\Enums\BookingStatus`: `pending` / `confirmed` / `checked_in` / `checked_out` / `cancelled` |
| source | string | `App\Enums\Source` |
| total_amount | unsigned int | BDT, computed by `PricingService` when saved |
| notes | text, nullable | |
| created_by | FK users, nullable (null on delete) | staff member who entered it |

Index on (`room_id`, `check_in`, `check_out`) for the availability check.

Status flow: `pending → confirmed → checked_in → checked_out`; `pending` or `confirmed` can be `cancelled`. `checked_out` and `cancelled` are final.

## inquiries (leads)
| column | type | notes |
|---|---|---|
| name, phone | string | |
| check_in / check_out | date, nullable | what the guest asked for |
| guests | unsigned tinyint, nullable | |
| cottage_preference | string, nullable | free text from the enquiry form |
| message | text, nullable | |
| source | string | `App\Enums\Source` |
| status | string, indexed | `App\Enums\InquiryStatus`: `new` / `contacted` / `converted` / `lost` |
| booking_id | FK bookings, nullable (null on delete) | set when converted |

## Relations
- RoomType hasMany Room, hasMany Rate
- Room belongsTo RoomType, hasMany Booking
- Guest hasMany Booking
- Booking belongsTo Guest, Room, User (`created_by`); hasOne Inquiry (the lead it came from)
- Inquiry belongsTo Booking

## Deliberately not in this model yet
- `booking_items` (several rooms/extras per booking): one room per booking keeps availability and pricing simple. When needed, `bookings.room_id` + `total_amount` move into items.
- Extras from the frontend calculator (extra guest 1,200/night beyond 2, BBQ 800/guest/night, speedboat 1,500): not priced yet; add as booking items or a pricing rule.
- `payments`, `campaigns`, `activity_log`: Phases 4–5.
