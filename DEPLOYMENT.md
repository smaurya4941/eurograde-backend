# Deploying the EuroRank API to Plesk

Staging: **https://eurogradeapi.ureligible.com** (website: https://eurograde.ureligible.com).

## How it works

1. You push to `main` on GitHub.
2. GitHub Actions (`.github/workflows/deploy.yml`) runs Pint, Larastan and the test suite. If they
   pass, it installs production dependencies and commits a ready-to-run copy (including `vendor/`)
   to the **`deploy`** branch.
3. Plesk's Git extension pulls the `deploy` branch into the site folder (GitHub webhook), then runs
   the deployment actions (migrations, caches).

The server never needs Composer, Node or SSH. Never push to `deploy` by hand.

## One-time setup

### 1. GitHub

- **Settings → Actions → General → Workflow permissions**: "Read and write permissions" (the
  workflow commits to the `deploy` branch).
- Push to `main` once (or run the workflow manually: **Actions → Test and publish deploy branch →
  Run workflow**) so the `deploy` branch exists.

### 2. Plesk: subdomain, PHP, SSL

1. **Websites & Domains → Add Subdomain** `eurogradeapi` on `ureligible.com`.
   Document root: **`eurogradeapi.ureligible.com/public`** (Laravel's `public/` folder).
2. **PHP Settings**: PHP **8.2 or newer** (FPM served by Apache). Extensions: mbstring, intl,
   pdo_mysql, dom, fileinfo, openssl, tokenizer, ctype, curl (all enabled by default).
3. **SSL/TLS Certificates**: Let's Encrypt for the subdomain, and turn on **"Permanent SEO-safe 301
   redirect from HTTP to HTTPS"** in Hosting Settings.
4. **Apache & nginx Settings**: Apache must stay enabled (Laravel's `public/.htaccess` does the
   routing).

### 3. Plesk: database

**Databases → Add Database** (MySQL/MariaDB), e.g. `eurograde` with its own user. Note the name,
user and password for `.env`.

### 4. Plesk: Git

1. **Git → Add Repository → Remote Git hosting**.
   - Repository URL: `git@github.com:smaurya4941/eurograde-backend.git` (SSH).
   - Plesk shows an **SSH public key**: add it in GitHub under **repo → Settings → Deploy keys →
     Add deploy key** (read-only is enough).
2. Repository settings:
   - Branch: **`deploy`**
   - Deployment mode: **Automatic**
   - Deployment path: **`eurogradeapi.ureligible.com`** (the site root, *not* `public`).
3. Copy the **Webhook URL** Plesk shows into GitHub: **repo → Settings → Webhooks → Add webhook**
   (content type `application/json`, "Just the push event").
4. **Enable additional deployment actions** and enter (adjust the PHP version in the path):

   ```bash
   /opt/plesk/php/8.2/bin/php artisan migrate --force
   /opt/plesk/php/8.2/bin/php artisan optimize
   /opt/plesk/php/8.2/bin/php artisan queue:restart
   ```

   If your plan doesn't offer deployment actions, run these three commands after each deploy from
   **Laravel Toolkit** (Websites & Domains → Laravel) or ask your host to enable them.

### 5. `.env` on the server

In **File Manager**, inside `eurogradeapi.ureligible.com/`, create `.env` from
`.env.production.example` and fill in the database and mail settings. `.env` is not in Git and
survives every deploy.

### 6. First deploy (once)

After the first pull, run once (Laravel Toolkit → Artisan, or deployment actions temporarily):

```bash
php artisan key:generate --force
php artisan migrate --force --seed      # tables + site content, copies seeded images to public/storage
# storage:link is not needed (and fails on this host): FILESYSTEM_PUBLIC_IN_WEBROOT=true in .env
# stores media directly in public/storage.
php artisan optimize
```

Check https://eurogradeapi.ureligible.com/api/v1/health and
https://eurogradeapi.ureligible.com/api/v1/services.

### 7. Queue worker (contact form and newsletter e-mails)

**Scheduled Tasks → Add Task**:

- Task type: **Run a PHP script**
- Script path: `eurogradeapi.ureligible.com/artisan`
- Arguments: `queue:work --stop-when-empty --tries=3 --max-time=50`
- PHP version: same as the site
- Run: **every minute** (cron `* * * * *`)

Each run sends any queued e-mails and exits, so there is no long-running process to babysit.

## Everyday use

- **Deploy**: push to `main`. Watch **GitHub → Actions**; when it's green, Plesk pulls within
  seconds and runs the deployment actions.
- **Update site content from the seeder** (after changing `database/seeders/ContentSeeder.php` or
  `content/`): `php artisan db:seed --class=ContentSeeder --force`. It is idempotent and only adds
  or updates seeded rows. Replaced images need a **new filename** (media is copied only when
  missing).
- **Approve blog comments**: set `post_comments.status = 'approved'` (phpMyAdmin).
- **Logs**: `storage/logs/laravel-YYYY-MM-DD.log` (File Manager).

## Moving to the main domain later

1. Add the new (sub)domain in Plesk with the same document root setup, or rename the subdomain.
2. In `.env`: `APP_URL`, `FRONTEND_URL`, `SESSION_DOMAIN` (e.g. `.eurogrades.com`) and
   `SANCTUM_STATEFUL_DOMAINS` (e.g. `eurogrades.com,www.eurogrades.com`).
3. `php artisan optimize` (refreshes the cached config).
4. Rebuild the frontend with the new API URL (see the frontend's DEPLOYMENT.md).

## Notes

- The website and API must share a parent domain (`*.ureligible.com` now): the CSRF cookie is set
  for `SESSION_DOMAIN` so the website can read it for form submissions.
- `APP_DEBUG` must stay `false` in production.
- `public/robots.txt` keeps the JSON API out of search engines; website images under `/storage/`
  remain indexable.
