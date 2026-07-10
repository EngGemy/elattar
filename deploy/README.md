# Deploying elattar to cPanel (StableServer)

Production target:

| Setting | Value |
|---------|-------|
| Host | `s16386.fra1.stableserver.net` |
| Subdomain | `elattar.gawasem.com` |
| Document root | `/elattar` (not `public_html`) |
| PHP | 8.2 |
| Database | MySQL |
| Repo | `git@github.com:EngGemy/elattar.git` |

Laravel's web root is `public/`. On shared hosting the subdomain document root is `/elattar`, so requests are rewritten into `/elattar/public` via the root `.htaccess` file (`deploy/htaccess-root.txt`).

---

## 1. One-time cPanel setup

### 1.1 Subdomain and document root

1. In cPanel → **Domains** → **Subdomains** (or **Domains**).
2. Create or edit `elattar.gawasem.com`.
3. Set **Document Root** to `/elattar` (not `public_html`).

### 1.2 MySQL database

1. cPanel → **MySQL® Databases**.
2. Create a database (e.g. `username_elattar`).
3. Create a user with a strong password and grant **ALL PRIVILEGES** on that database.
4. Note host (usually `localhost`), database name, username, and password.

### 1.3 FTP account

1. cPanel → **FTP Accounts**.
2. Create an account (or use the main account) with access to `/elattar`.
3. Note:
   - **Server:** `s16386.fra1.stableserver.net`
   - **Username**
   - **Password**
   - **Port:** `21` (FTPS)

Ensure the FTP user can read/write `/elattar` and subfolders.

### 1.4 Root `.htaccess` (first deploy)

The GitHub Actions deploy workflow copies `deploy/htaccess-root.txt` to `.htaccess` before upload.

On the **very first** deploy, verify that `/elattar/.htaccess` exists on the server. If missing, upload manually:

```bash
# From your local clone
cp deploy/htaccess-root.txt .htaccess
# Upload .htaccess to /elattar/ via FTP/File Manager
```

---

## 2. GitHub secrets

Repository → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**.

### Required (FTPS deploy)

| Secret | Example / notes |
|--------|-----------------|
| `FTP_SERVER` | `s16386.fra1.stableserver.net` |
| `FTP_USERNAME` | cPanel FTP username |
| `FTP_PASSWORD` | cPanel FTP password |

### Optional (SSH post-deploy)

If SSH is enabled on the host, add these so the workflow runs `php artisan` after upload. If SSH is **not** available, leave `SSH_HOST` empty — the deploy still succeeds; run artisan manually (see §4).

| Secret | Notes |
|--------|-------|
| `SSH_HOST` | e.g. `s16386.fra1.stableserver.net` |
| `SSH_USERNAME` | cPanel SSH username |
| `SSH_PRIVATE_KEY` | Private key (PEM), no passphrase recommended for CI |
| `SSH_PORT` | Optional, default `22` |

---

## 3. First production `.env` (never commit)

`.env` is **excluded** from FTP deploy. Create it once on the server.

### Option A — cPanel File Manager

1. Copy `.env.example` to `.env` in `/elattar/`.
2. Edit values (database, `APP_KEY`, mail, etc.).

### Option B — SSH

```bash
cd ~/elattar
cp .env.example .env
php artisan key:generate
# Edit .env with nano/vi — set DB_*, APP_URL, APP_DEBUG=false, etc.
```

Minimum production values:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://elattar.gawasem.com
APP_KEY=base64:...   # from php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

---

## 4. Folder permissions

From cPanel **Terminal** or SSH:

```bash
cd ~/elattar

# Directories: 755 (775 for writable Laravel dirs)
find . -type d -exec chmod 755 {} \;
chmod 775 storage bootstrap/cache
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

# Files: 644
find . -type f -exec chmod 644 {} \;
```

If uploads or logs fail, ensure `storage` and `bootstrap/cache` are writable by the web server user.

---

## 5. First-run artisan (manual if no SSH in CI)

Run once after `.env` exists:

```bash
cd ~/elattar
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Optional seed (only on empty database):

```bash
php artisan db:seed --force
```

---

## 6. Automated deploy

### Trigger

- **Push to `main`** — automatic deploy.
- **Manual** — GitHub → **Actions** → **Deploy to Production** → **Run workflow**.

### What the workflow does

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build` (Vite → `public/build`)
3. Copies `deploy/htaccess-root.txt` → `.htaccess`
4. Uploads via **FTPS** to `/elattar/`
5. If `SSH_HOST` is set: `migrate --force`, `config:cache`, `route:cache`, `view:cache`, `storage:link`

### Excluded from upload

- `.env`, `.git`, `node_modules`, `tests`
- `storage/logs`, `storage/framework` cache/session/view files

Server-side `.env` and runtime logs are preserved across deploys.

---

## 7. Rollback

Deployments are file-based (no atomic releases). To roll back application code:

1. Identify the bad commit on `main`.
2. Revert locally and push:

   ```bash
   git checkout main
   git pull
   git revert <bad-commit-sha> --no-edit
   git push origin main
   ```

3. The **Deploy to Production** workflow runs automatically on push to `main`.

4. If the bad deploy included a **database migration**, restore from backup or write a down migration — `git revert` does not undo SQL.

5. Re-run manually if needed: **Actions** → **Deploy to Production** → **Run workflow**.

6. After rollback, on SSH (or Terminal):

   ```bash
   cd ~/elattar
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 8. CI (pull requests)

Workflow `.github/workflows/ci.yml` runs on PRs to `main`:

- `composer validate --strict`
- `php -l` on all files in `app/`
- `php artisan test` (SQLite `:memory:`)
- `npm ci && npm run build`

Merge only when CI is green.

---

## 9. Troubleshooting

| Symptom | Check |
|---------|--------|
| 500 error | `storage/logs/laravel.log`, permissions on `storage` / `bootstrap/cache` |
| `.env` exposed | Root `.htaccess` present; `APP_DEBUG=false` |
| CSS/JS missing | `public/build` deployed; run `npm run build` locally and redeploy |
| Mixed content | `APP_URL` must be `https://elattar.gawasem.com` |
| 403 on `/` | Document root is `/elattar`, not `/elattar/public`; root `.htaccess` rewrites to `public/` |
| FTP fails | FTPS on port 21, correct `server-dir: /elattar/` |

---

## 10. Security reminders

- Never commit `.env` or `storage/*.key`.
- Keep `APP_DEBUG=false` in production.
- Use strong DB and FTP passwords.
- Restrict FTP/SSH to trusted IPs in cPanel when possible.
