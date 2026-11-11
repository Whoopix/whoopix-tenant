# Agent guide — Whoopix PHP tenant

You are building a **website** inside a Whoopix hybrid tenant. Content and SEO are meant to stay editable in **Whoopix CMS** via `system/api`.

## Canonical docs

https://github.com/Whoopix/whoopix-environments/blob/main/docs/getting-started.md  
https://github.com/Whoopix/whoopix-environments/blob/main/docs/php-website.md  
https://github.com/Whoopix/whoopix-environments/blob/main/docs/build-with-cursor.md  

## Do

- Put layout in `templates/` and features in `modules/`  
- Keep marketing copy in `mod_*` / module fields when possible  
- Preserve HMAC sync under `system/api/`  
- Use `*.example.php` for new config knobs  

## Don’t

- Commit `db.php`, `config.php`, `system/api/config.php`, `.env`  
- Remove or weaken sync signature checks  
- Invent a parallel router that bypasses `modules.php` without need  

## Entry points

| Task | Start |
|------|--------|
| Homepage | `index.php`, `templates/` |
| Modules | `modules.php`, `modules/<name>/` |
| Sync agent | `system/api/read.php`, `operation.php`, `upload.php` |
| Leads | `wpx-lead-submit.php`, `includes/` |
