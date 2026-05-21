---
name: feedback-no-build-no-migrate
description: Never run npm build or php artisan migrate without explicit user permission — production environment
metadata:
  type: feedback
---

Never run `npm run build`, `npm run dev`, `php artisan migrate`, or any command that modifies the production database or assets without the user explicitly asking for it.

**Why:** The project runs in production (Laragon local mirrors prod config). Running builds or migrations unexpectedly can break the live site.

**How to apply:** After making code changes, stop. Do not run build or migrate commands. Just commit and push. If a migration is needed, mention it clearly so the user can run it themselves when ready.
