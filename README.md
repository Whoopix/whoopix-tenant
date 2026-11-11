# Whoopix Tenant

**PHP hybrid website environment** connected to [Whoopix CMS](https://www.cms.whoopix.ai/admin.php).

This folder is the deployable site + sync agent. Whoopix CMS edits content and syncs it here over **cms-sync-v1**. Use Cursor (or any IDE) to build templates and modules without losing CMS control.

> **Full developer documentation (all stacks):**  
> https://github.com/Whoopix/whoopix-environments#readme  
> Especially: [Getting started](https://github.com/Whoopix/whoopix-environments/blob/main/docs/getting-started.md) · [PHP website guide](https://github.com/Whoopix/whoopix-environments/blob/main/docs/php-website.md) · [Build with Cursor](https://github.com/Whoopix/whoopix-environments/blob/main/docs/build-with-cursor.md)

---

## Architecture

```text
Developer host                         Whoopix cloud
┌──────────────────────────┐           ┌────────────────────┐
│  whoopix-tenant (this)   │  HMAC     │  cms.whoopix.ai    │
│  templates · modules     │◄─────────►│  editors · SEO· AI │
│  system/api (sync agent) │           │                    │
│  MySQL mod_*             │           └────────────────────┘
└──────────────────────────┘
```

| Piece | Role |
|--------|------|
| `index.php`, `templates/`, `modules/` | Public website you customize |
| `system/api/` | Sync agent — CMS read/write/upload |
| `_sync_bridge_*.php` | Bridge helpers for pull/push |
| MySQL | Source of truth for this tenant’s content |

---

## Quick start

```bash
git clone https://github.com/Whoopix/whoopix-tenant.git my-site
cd my-site

cp db.example.php db.php
cp config.example.php config.php
cp system/api/config.example.php system/api/config.php
```

1. Create a MySQL database (utf8mb4).  
2. Put DB credentials in `db.php` **and** `system/api/config.php`.  
3. Set `SYNC_CLIENT_ID` / `SYNC_CLIENT_SECRET` (same values in Whoopix CMS).  
4. Serve this folder with PHP (Apache, nginx, or `php -S 127.0.0.1:8080 -t .`).  
5. Point CMS agent URL to `https://YOUR_HOST/system/api` (use a tunnel for local).  
6. Open the folder in **Cursor** and build the site — see [Build with Cursor](https://github.com/Whoopix/whoopix-environments/blob/main/docs/build-with-cursor.md).

Detailed steps: [PHP website documentation](https://github.com/Whoopix/whoopix-environments/blob/main/docs/php-website.md)

---

## Cursor prompt (copy/paste)

```text
You are working in a Whoopix PHP tenant (read AGENTS.md).
Build a polished marketing website (home, about, services, blog, contact).
Use templates/ and modules/. Keep copy CMS-editable via mod_* tables.
Do not break system/api HMAC sync. Do not commit secrets.
```

---

## Sync API

| Endpoint | Purpose |
|----------|---------|
| `POST system/api/read.php` | list / get / count / aggregate / join |
| `POST system/api/operation.php` | upsert / update / delete |
| `POST system/api/upload.php` | images / templates / videos |
| `GET system/api/schema.php` | column discovery |

Auth: `X-Sync-Client`, `X-Sync-Timestamp`, `X-Sync-Signature`  
Details: [system/api/README.md](system/api/README.md) · [cms-sync-v1](https://github.com/Whoopix/whoopix-environments/blob/main/docs/cms-sync-v1.md)

---

## Layout

```text
├── index.php
├── templates/           # page layouts (CMS-assignable)
├── modules/             # features (pages, blog, products, …)
├── system/api/          # CMS sync agent
├── includes/
├── files/upload/
├── config.example.php
├── db.example.php
├── AGENTS.md            # rules for AI coding agents
└── README.md
```

---

## Security

- Never commit `db.php`, `config.php`, `system/api/config.php`, or `.env`  
- Never expose `SYNC_CLIENT_SECRET`  
- Use HTTPS for any agent URL reachable by CMS  

---

## Related

| Resource | Link |
|----------|------|
| Multi-stack hub + docs | https://github.com/Whoopix/whoopix-environments |
| Connect CMS | https://github.com/Whoopix/whoopix-environments/blob/main/docs/connect-cms.md |
| Product developers page | https://www.whoopix.ai/developers |
| CMS admin | https://www.cms.whoopix.ai/admin.php |
