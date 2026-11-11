<?php
/* Site homepage entry point.
 * ---------------------------------------------------------------------------
 * Renders the template assigned to the homepage row (mod_pages_list) via the
 * same modules.php → templates.php path as inner pages. The self-contained
 * starter (templates/wpx-default.php) is used only when no template is assigned
 * (template id <= 0). */

$wpxDefaultHome = __DIR__ . '/templates/wpx-default.php';
$useStarterHome = false;
$homePageId = '1';

if (is_file($wpxDefaultHome) && is_file(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
    if (function_exists('db_query') && function_exists('db_fetch_array')) {
        $home = db_fetch_array(
            db_query('SELECT id, template FROM mod_pages_list WHERE id = 1 LIMIT 1')
        );
        if (!$home) {
            $home = db_fetch_array(
                db_query('SELECT id, template FROM mod_pages_list ORDER BY id ASC LIMIT 1')
            );
        }
        if ($home) {
            $homePageId = (string) ($home['id'] ?? '1');
        }
        $tplId = (int) ($home['template'] ?? 0);
        $useStarterHome = ($tplId <= 0);
    } else {
        $useStarterHome = true;
    }
} elseif (is_file($wpxDefaultHome)) {
    $useStarterHome = true;
}

if ($useStarterHome) {
    require $wpxDefaultHome;
    return;
}

$get_mod = 'pages';
$get_id = $homePageId;
include 'modules.php';
