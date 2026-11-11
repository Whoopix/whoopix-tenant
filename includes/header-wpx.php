<?php
/* ============================================================================
 * Whoopix flagship NEUTRAL header (new-site default theme).
 * Content-driven: logo/site-name + nav from the tenant DB, no brand hardcoding.
 * Emits the sticky site header + mobile drawer. The host document (page.php)
 * owns <head>/<body> and loads resources/design/css/wpx-theme.css + Heebo.
 * Shared chrome helpers/data live here and are reused by footer-wpx.php.
 * The original includes/header.php (Site) is left untouched.
 * ==========================================================================*/

if (!function_exists('wpx_e')) {
    function wpx_e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('wpx_icon')) {
    function wpx_icon(string $p, int $sw = 18): string {
        return '<svg viewBox="0 0 24 24" width="' . $sw . '" height="' . $sw . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
    }
}
if (!function_exists('wpx_chrome')) {
    /** Resolve site name, nav, contact + icon set once; cached for the request. */
    function wpx_chrome(): array {
        static $c = null;
        if ($c !== null) { return $c; }

        $hasDb = function_exists('db_query') && function_exists('db_fetch_array');
        $lang  = defined('LANG') ? (int) LANG : 0;
        if ($hasDb && $lang === 0) {
            $lr = db_fetch_array(db_query("SELECT id FROM mod_languages WHERE `default` = 1 LIMIT 1"))
               ?: db_fetch_array(db_query("SELECT id FROM mod_languages ORDER BY id ASC LIMIT 1"));
            $lang = (int) ($lr['id'] ?? 1);
        }
        if ($lang === 0) { $lang = 1; }

        $cfg = $GLOBALS['GET_SITE_CONFIG_r'] ?? [];
        if ((!is_array($cfg) || !$cfg) && $hasDb) {
            $cfg = db_fetch_array(db_query("SELECT * FROM config_site WHERE lang_id = :l LIMIT 1", ['l' => $lang])) ?: [];
        }
        $name = trim((string) ($cfg['name'] ?? '')) !== '' ? (string) $cfg['name'] : 'האתר שלי';

        $logo = '';
        if (!empty($cfg['logo'])) {
            foreach (['/files/upload/' . $cfg['logo'], '/files/bn/' . $cfg['logo'], 'resources/design/images/' . $cfg['logo']] as $cand) {
                if (is_file(($_SERVER['DOCUMENT_ROOT'] ?? '') . '/' . ltrim($cand, '/'))) { $logo = $cand; break; }
            }
        }

        $menu = [];
        if ($hasDb) {
            $mq = db_query(
                "SELECT title, link FROM mod_menu
                 WHERE show_m = 1 AND parent = 0 AND (lang_id = :l OR lang_id = 0 OR lang_id IS NULL)
                 ORDER BY `order` ASC, id ASC LIMIT 8", ['l' => $lang]
            );
            while ($mq && ($r = db_fetch_array($mq))) {
                $menu[] = ['title' => (string) $r['title'], 'link' => (string) $r['link']];
            }
        }
        if (!$menu) {
            $menu = [['title' => 'דף הבית', 'link' => 'index.php'], ['title' => 'צור קשר', 'link' => 'index.php#contact']];
        }

        $contact = $hasDb ? (db_fetch_array(db_query("SELECT * FROM contact_config WHERE id = 1 LIMIT 1")) ?: []) : [];
        $phone = trim((string) ($contact['phone'] ?? $contact['tel'] ?? $cfg['phone'] ?? ''));
        $email = trim((string) ($contact['email'] ?? $cfg['email'] ?? ''));
        $addr  = trim((string) ($contact['address'] ?? $contact['adress'] ?? $cfg['address'] ?? ''));

        $cta = 'index.php#contact';
        foreach ($menu as $m) {
            if (preg_match('/(contact|צור|קשר)/u', $m['title'] . ' ' . $m['link'])) { $cta = $m['link']; break; }
        }

        $initial = function_exists('mb_substr') ? mb_strtoupper(mb_substr($name, 0, 1)) : strtoupper(substr($name, 0, 1));

        $ic = [
            'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L7.6 9.8a16 16 0 0 0 6 6l1.4-1.5a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/>',
            'mail'  => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
            'pin'   => '<path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
            'home'  => '<path d="M3 11l9-8 9 8M5 10v10h14V10"/>',
            'chev'  => '<path d="M15 18l-6-6 6-6"/>',
            'burger'=> '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
        ];

        return $c = compact('name', 'logo', 'menu', 'phone', 'email', 'addr', 'cta', 'initial', 'ic');
    }
}

$W = wpx_chrome();
?>
<header class="wpx-header" id="wpxHeader">
  <div class="wpx-container wpx-header__in">
    <a class="wpx-logo" href="index.php">
      <?php if ($W['logo']): ?><img src="<?= wpx_e($W['logo']) ?>" alt="<?= wpx_e($W['name']) ?>">
      <?php else: ?><span class="wpx-logo__mark"><?= wpx_e($W['initial']) ?></span><span><?= wpx_e($W['name']) ?></span><?php endif; ?>
    </a>
    <nav class="wpx-nav" aria-label="ניווט ראשי">
      <?php foreach ($W['menu'] as $m): ?><a href="<?= wpx_e($m['link']) ?>"><?= wpx_e($m['title']) ?></a><?php endforeach; ?>
    </nav>
    <a class="wpx-btn wpx-btn--primary wpx-btn--sm wpx-header__cta" href="<?= wpx_e($W['cta']) ?>"><?= wpx_icon($W['ic']['phone'], 16) ?> צור קשר</a>
    <button class="wpx-burger" id="wpxBurger" aria-label="פתיחת תפריט"><?= wpx_icon($W['ic']['burger'], 24) ?></button>
  </div>
</header>
<div class="wpx-drawer-back" id="wpxDrawerBack"></div>
<aside class="wpx-drawer" id="wpxDrawer" aria-label="תפריט נייד">
  <div class="wpx-drawer__top">
    <span class="wpx-logo"><span class="wpx-logo__mark"><?= wpx_e($W['initial']) ?></span></span>
    <button class="wpx-drawer__close" id="wpxDrawerClose" aria-label="סגירה">&times;</button>
  </div>
  <?php foreach ($W['menu'] as $m): ?><a href="<?= wpx_e($m['link']) ?>"><?= wpx_e($m['title']) ?></a><?php endforeach; ?>
  <a class="wpx-btn wpx-btn--primary" href="<?= wpx_e($W['cta']) ?>"><?= wpx_icon($W['ic']['phone'], 16) ?> צור קשר</a>
</aside>
