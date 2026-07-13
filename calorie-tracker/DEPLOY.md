# Deploying Mealo to Railway

The app ships as a **Dockerfile** (FrankenPHP + PHP 8.4). Railway auto-detects
it and builds it — no build/start commands to configure by hand.

## What runs automatically on each deploy

`docker-entrypoint.sh` runs before the server starts:

- creates the SQLite file on the volume if it doesn't exist yet
- `php artisan migrate --force`
- caches config, routes, and views
- `php artisan storage:link`

So deploys are just `git push` → Railway rebuilds → migrations apply.

---

## Your side on Railway (one-time setup)

1. **New Project → Deploy from GitHub repo** → pick this repo.
2. **Service → Settings → Root Directory: `calorie-tracker`** (the app lives in
   this subfolder, not the repo root). Railway then finds the Dockerfile.
3. **Settings → Region: `Europe West`** — lowest latency to Saudi Arabia.
4. **Add a Volume** (Service → Variables/Settings → Volumes): mount path
   **`/data`**. This is where the SQLite database lives so data survives
   redeploys. Without it, every deploy wipes user accounts and entries.
5. **Add the environment variables** below (Service → Variables).
6. Deploy. Railway gives you a `*.up.railway.app` URL — open it.

### Environment variables to set

| Key | Value |
|-----|-------|
| `APP_NAME` | `Mealo` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | generate locally with `php artisan key:generate --show` and paste it (starts with `base64:`) |
| `APP_URL` | your Railway URL, e.g. `https://mealo.up.railway.app` |
| `DB_CONNECTION` | `sqlite` |
| `DB_DATABASE` | `/data/database.sqlite` (must match the volume mount) |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `LOG_CHANNEL` | `stderr` (so logs show in Railway's log viewer) |
| `OPENAI_API_KEY` | your OpenAI key |
| `AI_MONTHLY_CALL_CAP` | `5000` (or lower for a tighter spend guard) |

> Generate `APP_KEY` once and keep it stable. Changing it later invalidates all
> sessions **and makes existing encrypted `health_notes` unreadable.**

---

## Cost guards (two layers)

1. **OpenAI dashboard → Billing → Usage limits:** set a hard monthly cap. This
   is the real guarantee — OpenAI stops serving once hit.
2. **`AI_MONTHLY_CALL_CAP`** (this app): a call-count ceiling that fails
   gracefully (users just stop getting AI features until next month) well before
   the bill gets interesting.

## Optional later: Cloudflare for Saudi edge caching

Point a cheap domain at the app through Cloudflare (free plan) to cache static
assets closer to users and hide the origin. Not required to launch.
