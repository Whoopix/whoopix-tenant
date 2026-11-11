<?php
/**
 * Database Migration Runner
 * Upload to front server, run once via browser, keep for future migrations.
 * Tracks run migrations in _migrations table — safe to run multiple times.
 *
 * ?run=N   Force re-run migration by numeric prefix (e.g. ?run=12)
 * ?run=all Force re-run ALL failed migrations
 */

declare(strict_types=1);

require_once __DIR__ . '/../../db.php';

// ── DB connect ──────────────────────────────────────────────────────────────

try {
    $pdo = new PDO(
        'mysql:host=' . $dbnfo['host'] . ';dbname=' . $dbnfo['name'] . ';charset=utf8mb4',
        $dbnfo['user'],
        $dbnfo['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Exception $e) {
    die('<b>DB connect failed:</b> ' . htmlspecialchars($e->getMessage()));
}

// ── Tracking table ──────────────────────────────────────────────────────────

$pdo->exec("CREATE TABLE IF NOT EXISTS `_migrations` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration`  VARCHAR(255) NOT NULL,
    `ran_at`     DATETIME NOT NULL,
    `status`     VARCHAR(16) NOT NULL DEFAULT 'ok',
    `error`      TEXT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_migration` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ── Load migration files ─────────────────────────────────────────────────────

$dir   = __DIR__ . '/migrations';
$files = glob($dir . '/*.php');
natsort($files);
$files = array_values($files);

// ── ?run param: force re-run specific migration ──────────────────────────────

$force_key = null;
if (isset($_GET['run']) && $_GET['run'] !== '') {
    $run_param = trim($_GET['run']);
    foreach ($files as $f) {
        $base = basename($f);
        // match by numeric prefix (e.g. "12" matches "012_foo.php") or exact filename
        if ($base === $run_param || preg_match('/^0*' . preg_quote($run_param, '/') . '[_.]/', $base)) {
            $force_key = $base;
            // remove from tracking so it re-runs
            $pdo->prepare("DELETE FROM `_migrations` WHERE `migration` = ?")->execute([$base]);
            break;
        }
    }
}

// ── Already-run set ──────────────────────────────────────────────────────────

$ran = [];
foreach ($pdo->query("SELECT `migration`, `status`, `ran_at`, `error` FROM `_migrations`")->fetchAll() as $r) {
    $ran[$r['migration']] = $r;
}

// ── Run ──────────────────────────────────────────────────────────────────────

$results = [];

foreach ($files as $file) {
    $key  = basename($file);
    $def  = require $file;
    $name = $def['name'] ?? $key;

    // In ?run=N mode: show all rows but only execute the targeted one
    if ($force_key !== null && $key !== $force_key) {
        $state = isset($ran[$key]) ? $ran[$key]['status'] : 'pending';
        $results[] = [
            'key'    => $key,
            'name'   => $name,
            'state'  => ($state === 'ok' ? 'skipped' : $state),
            'ran_at' => $ran[$key]['ran_at'] ?? '',
            'errors' => [],
            'sqls'   => [],
            'forced' => false,
        ];
        continue;
    }

    if (isset($ran[$key]) && $ran[$key]['status'] === 'ok') {
        $results[] = [
            'key'    => $key,
            'name'   => $name,
            'state'  => 'skipped',
            'ran_at' => $ran[$key]['ran_at'],
            'errors' => [],
            'sqls'   => [],
            'forced' => false,
        ];
        continue;
    }

    $errors = [];
    $sqls   = [];
    foreach ($def['statements'] as $sql) {
        $trimmed = trim($sql);
        try {
            $affected = $pdo->exec($trimmed);
            $sqls[]   = ['sql' => $trimmed, 'ok' => true, 'affected' => $affected];
        } catch (Exception $e) {
            $sqls[]   = ['sql' => $trimmed, 'ok' => false, 'msg' => $e->getMessage()];
            $errors[] = ['sql' => $trimmed, 'msg' => $e->getMessage()];
        }
    }

    $status   = empty($errors) ? 'ok' : 'error';
    $errorStr = empty($errors) ? null : implode(' | ', array_column($errors, 'msg'));

    $stmt = $pdo->prepare(
        "INSERT INTO `_migrations` (`migration`, `ran_at`, `status`, `error`)
         VALUES (?, NOW(), ?, ?)
         ON DUPLICATE KEY UPDATE `ran_at` = NOW(), `status` = VALUES(`status`), `error` = VALUES(`error`)"
    );
    $stmt->execute([$key, $status, $errorStr]);

    $results[] = [
        'key'    => $key,
        'name'   => $name,
        'state'  => $status,
        'ran_at' => date('Y-m-d H:i:s'),
        'errors' => $errors,
        'sqls'   => $sqls,
        'forced' => ($key === $force_key),
    ];
}

// ── Counts ───────────────────────────────────────────────────────────────────

$total   = count($results);
$ok      = 0; foreach ($results as $r) { if ($r['state'] === 'ok')      $ok++;      }
$skipped = 0; foreach ($results as $r) { if ($r['state'] === 'skipped') $skipped++; }
$failed  = 0; foreach ($results as $r) { if ($r['state'] === 'error')   $failed++;  }

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>DB Migrations</title>
<style>
  body  { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 30px; }
  h1    { color: #a78bfa; margin-bottom: 6px; }
  a     { color: #a78bfa; }
  .summary { display: flex; gap: 20px; margin: 18px 0 28px; flex-wrap: wrap; align-items: center; }
  .badge { padding: 6px 16px; border-radius: 8px; font-size: 14px; font-weight: 700; }
  .badge-ok   { background: #064e3b; color: #6ee7b7; }
  .badge-skip { background: #1e3a5f; color: #93c5fd; }
  .badge-fail { background: #7f1d1d; color: #fca5a5; }
  table { width: 100%; border-collapse: collapse; }
  th    { text-align: left; padding: 10px 14px; background: #1e293b; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; }
  td    { padding: 10px 14px; border-bottom: 1px solid #1e293b; font-size: 13px; vertical-align: top; }
  .num  { color: #64748b; }
  .name { color: #e2e8f0; }
  .ts   { color: #64748b; font-size: 11px; }
  .row-ok    td { border-left: 3px solid #10b981; }
  .row-skip  td { border-left: 3px solid #3b82f6; opacity:.65; }
  .row-error td { border-left: 3px solid #ef4444; }
  .row-forced td { border-left: 3px solid #f59e0b !important; opacity: 1 !important; }
  .pill { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:700; }
  .pill-ok     { background:#065f46; color:#6ee7b7; }
  .pill-skip   { background:#1e40af; color:#93c5fd; }
  .pill-error  { background:#991b1b; color:#fca5a5; }
  .pill-forced { background:#92400e; color:#fde68a; }
  .err-detail  { margin-top:6px; color:#fca5a5; font-size:11px; }
  .err-sql     { color:#94a3b8; font-size:11px; white-space:pre-wrap; word-break:break-all; margin-bottom:4px; }
  .sql-ok      { color:#6ee7b7; font-size:11px; margin-top:4px; }
  .sql-line    { font-size:11px; color:#64748b; white-space:pre-wrap; word-break:break-all; margin:2px 0; padding:4px 8px; background:#1e293b; border-radius:4px; }
  .run-btn     { display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px; background:#312e81; color:#c7d2fe; text-decoration:none; white-space:nowrap; }
  .run-btn:hover { background:#4338ca; }
  .detail-toggle { cursor:pointer; color:#64748b; font-size:11px; user-select:none; }
  .detail-block  { display:none; margin-top:6px; }
</style>
</head>
<body>
<h1>Database Migrations</h1>
<p style="color:#64748b;margin:0 0 4px">
  To force re-run a specific migration: <code>?run=12</code> (numeric prefix) or <code>?run=012_filename.php</code>
</p>

<div class="summary">
  <span class="badge badge-ok"><?=$ok?> Ran</span>
  <span class="badge badge-skip"><?=$skipped?> Skipped</span>
  <?php if ($failed): ?><span class="badge badge-fail"><?=$failed?> Failed</span><?php endif; ?>
  <span class="badge" style="background:#1e293b;color:#94a3b8"><?=$total?> Total</span>
  <?php if ($force_key): ?>
  <span class="badge" style="background:#78350f;color:#fde68a">&#9889; Force-ran: <?=htmlspecialchars($force_key)?></span>
  <?php endif; ?>
</div>

<table>
<thead>
  <tr>
    <th style="width:36px">#</th>
    <th style="width:220px">File</th>
    <th>Description / Result</th>
    <th style="width:70px">Status</th>
    <th style="width:130px">Ran at</th>
    <th style="width:60px">Re-run</th>
  </tr>
</thead>
<tbody>
<?php foreach ($results as $i => $r):
    $num     = preg_match('/^(\d+)/', $r['key'], $m) ? ltrim($m[1], '0') : ($i + 1);
    $rowClass = ($r['forced'] ? 'row-forced' : 'row-' . $r['state']);
?>
<tr class="<?=$rowClass?>">
  <td class="num"><?=str_pad((string)($i+1), 2, '0', STR_PAD_LEFT)?></td>
  <td class="num" style="font-size:11px;"><?=htmlspecialchars($r['key'])?></td>
  <td>
    <span class="name"><?=htmlspecialchars($r['name'])?></span>

    <?php if (!empty($r['sqls'])): ?>
    <span class="detail-toggle" onclick="var b=this.nextElementSibling;b.style.display=b.style.display?'':'block';this.textContent=b.style.display?'▲ hide':'▼ details'"> ▼ details</span>
    <div class="detail-block">
      <?php foreach ($r['sqls'] as $sq): ?>
      <div class="sql-line"><?=htmlspecialchars(substr($sq['sql'], 0, 300))?></div>
      <?php if ($sq['ok']): ?>
      <div class="sql-ok">&#10003; OK<?=($sq['affected'] !== false ? ' — ' . $sq['affected'] . ' row(s) affected' : '')?></div>
      <?php else: ?>
      <div class="err-detail">&#9888; <?=htmlspecialchars($sq['msg'])?></div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php foreach ($r['errors'] as $e): ?>
    <div class="err-detail">&#9888; <?=htmlspecialchars($e['msg'])?></div>
    <?php endforeach; ?>
  </td>
  <td>
    <?php if ($r['forced']): ?>
      <span class="pill pill-forced">FORCED</span>
    <?php elseif ($r['state'] === 'ok'): ?>
      <span class="pill pill-ok">OK</span>
    <?php elseif ($r['state'] === 'skipped'): ?>
      <span class="pill pill-skip">SKIP</span>
    <?php else: ?>
      <span class="pill pill-error">FAIL</span>
    <?php endif; ?>
  </td>
  <td class="ts"><?=htmlspecialchars($r['ran_at'])?></td>
  <td>
    <a class="run-btn" href="?run=<?=htmlspecialchars($num)?>">&#9889; run</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php if ($failed === 0): ?>
<p style="color:#10b981;margin-top:24px;font-size:14px">&#10003; All migrations completed successfully.</p>
<?php else: ?>
<p style="color:#ef4444;margin-top:24px;font-size:14px">&#9888; <?=$failed?> migration(s) failed. Click &#9889; run to retry individual ones.</p>
<?php endif; ?>

</body>
</html>
