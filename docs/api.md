# API contract — `/api/v1`

Public JSON API consumed by the Next.js site. Base URL locally: `http://localhost:8000/api/v1`.
Browser calls are allowed only from origins listed in `FRONTEND_URL`. Errors on `api/*` are always JSON.
Any change here that affects the frontend is noted in both repos' PRs.

## Endpoints

### `GET /status`
Checks the API is reachable. No auth.

```json
{ "name": "Bonomali Eco Resort", "api": "v1", "status": "ok" }
```

## Planned (Phase 2, not built)
- `GET /room-types`: room types with category, vibe tag, images, amenities, base nightly price
- `GET /availability?check_in&check_out&guests`: available room types for dates
- `POST /quote`: price for room type, nights, guests and add-ons (extra guest, BBQ, speedboat)
- `POST /inquiries`: name, phone, dates, guests, cottage preference, message, source; becomes a lead
