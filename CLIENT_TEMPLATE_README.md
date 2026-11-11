Whoopix client / tenant template
================================

Deployable front-end stack for a Whoopix site. Developers clone this repo, point
Cursor at it, and build the website while keeping the CMS sync API intact.

Per-environment configuration (never commit real values):

  - db.php                     <- copy from db.example.php
  - config.php                 <- copy from config.example.php
  - system/api/config.php      <- copy from system/api/config.example.php
                                 (SYNC_CLIENT_ID / SYNC_CLIENT_SECRET + DB)

Excluded from the public kit: live uploads, tenant diagnostics, SQL dumps,
and production secrets.

See README.md for the hybrid architecture and Cursor workflow.
