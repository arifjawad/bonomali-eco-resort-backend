# Development Workflow

## Repos
- `bonomali-eco-resort` — Next.js public site
- `bonomali-eco-resort-backend` — Laravel API + Filament admin (this repo)

## Branching
- `main` — always deployable. Protected; changes arrive by PR.
- `staging` — pre-production checks.
- `feature/<short-name>` — one module or fix per branch; fix branches use `fix/<short-name>`.

## Commits and PRs
- Small, focused commits with clear messages.
- PR description: what changed, which phase, how to test, any data model change.
- CI must pass before merge.

## CI (GitHub Actions, to be added at scaffold)
- Install dependencies, run Pint (style) and `php artisan test`.
- Frontend repo: lint, type-check, build.

## Environments
| Env | Purpose | Notes |
|-----|---------|-------|
| local | Development | `.env` from `.env.example`; seeded demo rooms |
| staging | Review before release | Auto-deploy from `staging` |
| production | Live site | Deploy from `main`, manual approval |

## Rules
- Secrets live in environment variables only, never in git.
- Every schema change is a migration; take a database backup before production migrations.
- API changes that affect the frontend are noted in both repos' PRs.
- Payments sit behind a gateway interface so providers can be swapped.

## Release checklist
1. CI green on `main`.
2. Database backup taken.
3. Deploy, run migrations, smoke-test booking flow.
4. Roll back to the previous release if the smoke test fails.
