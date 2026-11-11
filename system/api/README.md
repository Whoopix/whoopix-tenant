# CMS Sync API — Front Server

Deployed on the **front server** (e.g. `www.site.thost.co.il/api/`).  
Receives authenticated requests from the CMS and executes them against the front site's MySQL database.

---

## Authentication

Every request must include three HTTP headers:

| Header | Value |
|--------|-------|
| `X-Sync-Client` | Client ID (matches `sync_clients.client_id` in CMS DB) |
| `X-Sync-Timestamp` | Unix timestamp (seconds). Rejected if > 300s old |
| `X-Sync-Signature` | `hash_hmac('sha256', rawBody . '.' . timestamp, secret)` |

Upload requests also require `X-Sync-File-Hash`: `sha256` of the file content.

---

## Endpoints

### `POST /api/read.php` — Query data

**Request envelope:**
```json
{
  "protocol":   "cms-sync-v1",
  "client_id":  "example",
  "request_id": "unique-uuid-per-request",
  "timestamp":  1714567890,
  "read": {
    "table":  "mod_orders",
    "action": "list",
    ...
  }
}
```

**Actions:**

#### `list` — paginated rows
```json
{
  "table":        "mod_orders",
  "action":       "list",
  "columns":      ["id", "total", "time"],
  "filters":      [{"field": "status", "operator": "=", "value": 1}],
  "filter_logic": "AND",
  "sort":         {"field": "id", "direction": "DESC"},
  "limit":        50,
  "page":         1
}
```

#### `get` — single row (limit 1)
```json
{"table": "mod_orders", "action": "get", "filters": [{"field": "id", "operator": "=", "value": 42}]}
```

#### `count` — row count
```json
{"table": "mod_orders", "action": "count", "filters": [...]}
```

#### `aggregate` — SUM / COUNT / AVG / MIN / MAX
```json
{
  "table":          "mod_orders",
  "action":         "aggregate",
  "aggregate_fn":   "SUM",
  "aggregate_field": "total",
  "filters":        [{"field": "status", "operator": "=", "value": 1}],
  "group_by":       "status"
}
```
`group_by` supports `"unix:colname"` to group a Unix-timestamp column by calendar date:
```json
{"group_by": "unix:time"}
```
Returns rows with key `date` (formatted `YYYY-MM-DD`) and `result`.

#### `join` — JOIN two tables
```json
{
  "table":       "mod_orders",
  "action":      "join",
  "joins":       [{"table": "mod_orders_pords", "on": "mod_orders.id = mod_orders_pords.order_id", "type": "LEFT"}],
  "select_expr": "mod_orders.id, mod_orders.total, mod_orders_pords.product_id",
  "filters":     [],
  "limit":       100,
  "page":        1
}
```

**Filter operators:** `=` `!=` `>` `>=` `<` `<=` `LIKE` `IN` `NOT IN` `BETWEEN` `IS NULL` `IS NOT NULL`

**Response:**
```json
{"success": true, "action": "list", "table": "mod_orders", "data": [...], "total": 42, "page": 1, "limit": 50}
```

---

### `POST /api/operation.php` — Write data

**Request envelope:**
```json
{
  "protocol":   "cms-sync-v1",
  "client_id":  "example",
  "request_id": "unique-uuid-per-request",
  "timestamp":  1714567890,
  "operation": {
    "action":      "upsert",
    "table":       "mod_orders",
    "primary_key": "id",
    "rows":        [{"id": 0, "total": 250, "status": 1}]
  }
}
```

**Actions:**

| Action | Description |
|--------|-------------|
| `upsert` | INSERT or UPDATE by primary key. `id=0` or missing → INSERT |
| `update` | UPDATE rows matching primary key |
| `delete` | DELETE rows by primary key values in `rows` |

**Response:**
```json
{"success": true, "action": "upsert", "table": "mod_orders", "affected": 1, "last_insert_id": 99}
```

`request_id` is stored — duplicate requests return `409 Duplicate request_id` (idempotency).

---

### `POST /api/upload.php` — Upload file

`multipart/form-data` POST (not JSON):

| Field | Value |
|-------|-------|
| `type` | `images` or `templates` |
| `file` | The file to upload |

Headers: `X-Sync-Client`, `X-Sync-Timestamp`, `X-Sync-Signature` (HMAC of `type.filename.timestamp`), `X-Sync-File-Hash` (sha256 of file content).

**Response:**
```json
{"success": true, "filename": "abc123.jpg", "url": "/files/upload/abc123.jpg", "size": 48200}
```

**`GET /api/upload.php?action=list&type=images`** — list uploaded files  
**`DELETE /api/upload.php`** — delete a file (JSON body: `{"type":"images","filename":"abc123.jpg"}`)

---

### `GET /api/schema.php?table=tablename` — Table schema

Returns column names and types for any table. Used by CMS to discover available columns before writing.

**Response:**
```json
{"success": true, "table": "mod_orders", "columns": [{"Field": "id", "Type": "int(11)", ...}]}
```

---

## Configuration (`config.php`)

```php
define('SYNC_CLIENT_ID',     'example');      // Must match CMS sync_clients.client_id
define('SYNC_CLIENT_SECRET', 'hex64chars');       // Shared secret — never expose
define('SYNC_MAX_AGE',       300);               // Request age limit (seconds)
define('AGENT_DB_HOST',      'localhost');
define('AGENT_DB_USER',      '...');
define('AGENT_DB_PASS',      '...');
define('AGENT_DB_NAME',      '...');
define('UPLOAD_IMAGES_PATH', '/path/to/files/upload');
```

Can be overridden via environment variables (`SYNC_CLIENT_ID`, `DB_HOST`, etc.).

---

## Required DB Tables (in front DB)

```sql
CREATE TABLE cms_sync_requests (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  request_id VARCHAR(64) UNIQUE NOT NULL,
  client_id  VARCHAR(64),
  type       VARCHAR(16),
  received_at DATETIME,
  status     VARCHAR(16),
  error      TEXT
);

CREATE TABLE cms_sync_logs (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  request_id    VARCHAR(64),
  client_id     VARCHAR(64),
  type          VARCHAR(16),
  action        VARCHAR(32),
  resource_name VARCHAR(128),
  rows_affected INT,
  status        VARCHAR(16),
  error         TEXT,
  created_at    DATETIME
);
```
