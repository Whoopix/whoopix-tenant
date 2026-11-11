<?php
/* ============================================================================
 * Whoopix flagship default homepage — CONTENT-DRIVEN, self-contained, RTL-first.
 * ----------------------------------------------------------------------------
 * Shipped with every newly provisioned site. Renders a world-class landing page
 * that pulls the tenant's real content (site name, menu, pages, testimonials,
 * contact) from the DB and degrades gracefully to polished defaults when fields
 * are empty — so AI-onboarding-generated content looks great with zero tuning.
 *
 * Runs in two contexts, both supported:
 *   1) Starter path: index.php requires config.php then this file directly
 *      (LANG / modules.php NOT loaded) — we resolve language + config ourselves.
 *   2) Template path: rendered via templates.php after the full bootstrap.
 * All DB reads are guarded; a brand-new / empty / disconnected DB still renders.
 * ==========================================================================*/

if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

/* --------------------------------------------------------- data access ----- */
$WPX_DB = function_exists('db_query') && function_exists('db_fetch_array');

if (!function_exists('wpx_e')) {
    function wpx_e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('wpx_excerpt')) {
    function wpx_excerpt($html, int $len = 120): string {
        $t = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)));
        if (function_exists('mb_substr') && mb_strlen($t) > $len) {
            return mb_substr($t, 0, $len) . '…';
        }
        return $t;
    }
}

/* resolve active language id (constant when full bootstrap ran, else default) */
$WPX_LANG = defined('LANG') ? (int) LANG : 0;
if ($WPX_DB && $WPX_LANG === 0) {
    $lrow = db_fetch_array(db_query("SELECT id FROM mod_languages WHERE `default` = 1 LIMIT 1"));
    if (!$lrow) { $lrow = db_fetch_array(db_query("SELECT id FROM mod_languages ORDER BY id ASC LIMIT 1")); }
    $WPX_LANG = (int) ($lrow['id'] ?? 1);
}
if ($WPX_LANG === 0) { $WPX_LANG = 1; }

/* site config (name / description / keywords; schema-tolerant on extras) */
$cfg = [];
if ($WPX_DB) {
    $cfg = db_fetch_array(db_query(
        "SELECT * FROM config_site WHERE lang_id = :l LIMIT 1", ['l' => $WPX_LANG]
    )) ?: (db_fetch_array(db_query("SELECT * FROM config_site ORDER BY id ASC LIMIT 1")) ?: []);
}
$siteName = trim((string) ($cfg['name'] ?? '')) !== '' ? (string) $cfg['name'] : 'האתר שלי';
$siteDesc = trim((string) ($cfg['description'] ?? '')) !== ''
    ? (string) $cfg['description']
    : 'אנחנו מספקים שירות מקצועי, אישי ואמין — עם תשומת לב לכל פרט ומחויבות לתוצאה הטובה ביותר עבורכם.';

/* optional uploaded logo (only if the column exists and the file is present) */
$logoUrl = '';
if (!empty($cfg['logo'])) {
    foreach (['/files/upload/' . $cfg['logo'], '/files/bn/' . $cfg['logo'], 'resources/design/images/' . $cfg['logo']] as $cand) {
        if (is_file(($_SERVER['DOCUMENT_ROOT'] ?? '') . '/' . ltrim($cand, '/'))) { $logoUrl = $cand; break; }
    }
}

/* primary navigation from mod_menu (top level) */
$menu = [];
if ($WPX_DB) {
    $mq = db_query(
        "SELECT id, title, link, parent FROM mod_menu
         WHERE show_m = 1 AND parent = 0 AND (lang_id = :l OR lang_id = 0 OR lang_id IS NULL)
         ORDER BY `order` ASC, id ASC LIMIT 8", ['l' => $WPX_LANG]
    );
    while ($mq && ($r = db_fetch_array($mq))) {
        $link = (string) $r['link'];
        if (function_exists('tenant_public_href')) {
            $link = tenant_public_href($link);
        }
        $menu[] = ['title' => (string) $r['title'], 'link' => $link];
    }
}
if (!$menu) {
    $menu = [
        ['title' => 'דף הבית', 'link' => 'index.php'],
        ['title' => 'אודות',   'link' => ',pages,2'],
        ['title' => 'שירותים', 'link' => ',pages,3'],
        ['title' => 'המלצות',  'link' => ',pages,5'],
        ['title' => 'צור קשר', 'link' => ',pages,4'],
    ];
}

/* content pages → "what we offer" cards (excluding the homepage row) */
$cards = [];
if ($WPX_DB) {
    $pq = db_query(
        "SELECT L.id, P.title, P.text
         FROM mod_pages_list L
         JOIN mod_pages P ON P.page_id = L.id AND P.lang_id = :l
         WHERE L.id <> 1
         ORDER BY L.id ASC LIMIT 6", ['l' => $WPX_LANG]
    );
    while ($pq && ($r = db_fetch_array($pq))) {
        $title = trim((string) ($r['title'] ?? ''));
        if ($title === '') { continue; }
        $cards[] = [
            'title' => $title,
            'desc'  => wpx_excerpt($r['text'] ?? '', 110),
            'link'  => ',pages,' . (int) $r['id'],
        ];
    }
}

/* testimonials from mod_feedback_list when available */
$reviews = [];
if ($WPX_DB) {
    $fq = db_query("SELECT * FROM mod_feedback_list ORDER BY id DESC LIMIT 6");
    while ($fq && ($r = db_fetch_array($fq))) {
        $txt = trim((string) ($r['text'] ?? $r['comment'] ?? ''));
        if ($txt === '') { continue; }
        $reviews[] = [
            'text' => wpx_excerpt($txt, 160),
            'name' => trim((string) ($r['name'] ?? $r['c_name'] ?? 'לקוח/ה')),
            'role' => trim((string) ($r['role'] ?? '')) ?: 'לקוח/ה מרוצה',
        ];
    }
}

/* contact details (schema-tolerant: contact_config / config_site) */
$contact = [];
if ($WPX_DB) {
    $contact = db_fetch_array(db_query("SELECT * FROM contact_config WHERE id = 1 LIMIT 1")) ?: [];
}
$cEmail = trim((string) ($contact['email'] ?? $cfg['email'] ?? ''));
$cPhone = trim((string) ($contact['phone'] ?? $contact['tel'] ?? $cfg['phone'] ?? ''));
$cAddr  = trim((string) ($contact['address'] ?? $contact['adress'] ?? $cfg['address'] ?? ''));

/* find a contact-ish menu link for primary CTAs */
$ctaLink = '#contact';
foreach ($menu as $m) {
    if (preg_match('/(contact|צור|קשר)/u', $m['title'] . ' ' . $m['link'])) { $ctaLink = $m['link']; break; }
}

$year = date('Y');

/* small svg icon set (stroke) keyed by name */
$ic = [
  'spark'  => '<path d="M12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.1 2.1m8.6 8.6l2.1 2.1m0-12.8l-2.1 2.1M7.7 16.3l-2.1 2.1"/>',
  'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
  'heart'  => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>',
  'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  'star2'  => '<circle cx="12" cy="8" r="6"/><path d="M9 13.5 7 22l5-3 5 3-2-8.5"/>',
  'smile'  => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
  'phone'  => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L7.6 9.8a16 16 0 0 0 6 6l1.4-1.5a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/>',
  'mail'   => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
  'pin'    => '<path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
  'check'  => '<path d="M20 6 9 17l-5-5"/>',
  'arrow'  => '<path d="M19 12H5M12 5l-7 7 7 7"/>',
];
$svc_icons = ['spark', 'shield', 'heart', 'clock', 'star2', 'smile'];

if (!function_exists('wpx_icon')) {
    function wpx_icon(string $p, int $sw = 18): string {
        return '<svg viewBox="0 0 24 24" width="' . $sw . '" height="' . $sw . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
    }
}
$starSvg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>';
$initials = function (string $s): string {
    $s = trim($s);
    if (function_exists('mb_substr')) { return mb_strtoupper(mb_substr($s, 0, 1)); }
    return strtoupper(substr($s, 0, 1));
};
?>
<!doctype html>
<html lang="he" dir="rtl">
<head>
<link href="/css/jquery-ui.css" rel="stylesheet">
<link href="/css/bootstrap.css" rel="stylesheet">
<link href="/css/font-awesome.css" rel="stylesheet">
<link href="/css/icofont.css" rel="stylesheet">
<link href="/css/superslides.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= wpx_e($siteName) ?> | ברוכים הבאים</title>
<meta name="description" content="<?= wpx_e(wpx_excerpt($siteDesc, 155)) ?>">
<?php if (!empty($cfg['keywords'])): ?><meta name="keywords" content="<?= wpx_e($cfg['keywords']) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="resources/design/css/wpx-theme.css" rel="stylesheet">
<?php include __DIR__ . '/../includes/wpx-theme-vars.php'; ?>
</head>
<body class="wpx">

<!-- ===== header ===== -->
<header class="wpx-header" id="wpxHeader">
  <div class="wpx-container wpx-header__in">
    <a class="wpx-logo" href="/">
      <?php if ($logoUrl): ?><img src="<?= wpx_e($logoUrl) ?>" alt="<?= wpx_e($siteName) ?>">
      <?php else: ?><span class="wpx-logo__mark"><?= wpx_e($initials($siteName)) ?></span><span><?= wpx_e($siteName) ?></span><?php endif; ?>
    </a>
    <nav class="wpx-nav" aria-label="ניווט ראשי">
      <?php foreach ($menu as $m): ?><a href="<?= wpx_e($m['link']) ?>"><?= wpx_e($m['title']) ?></a><?php endforeach; ?>
    </nav>
    <a class="wpx-btn wpx-btn--primary wpx-btn--sm wpx-header__cta" href="<?= wpx_e($ctaLink) ?>"><?= wpx_icon($ic['phone'], 16) ?> צור קשר</a>
    <button class="wpx-burger" id="wpxBurger" aria-label="פתיחת תפריט"><?= wpx_icon('<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>', 24) ?></button>
  </div>
</header>
<div class="wpx-drawer-back" id="wpxDrawerBack"></div>
<aside class="wpx-drawer" id="wpxDrawer" aria-label="תפריט נייד">
  <div class="wpx-drawer__top">
    <span class="wpx-logo"><span class="wpx-logo__mark"><?= wpx_e($initials($siteName)) ?></span></span>
    <button class="wpx-drawer__close" id="wpxDrawerClose" aria-label="סגירה">&times;</button>
  </div>
  <?php foreach ($menu as $m): ?><a href="<?= wpx_e($m['link']) ?>"><?= wpx_e($m['title']) ?></a><?php endforeach; ?>
  <a class="wpx-btn wpx-btn--primary" href="<?= wpx_e($ctaLink) ?>"><?= wpx_icon($ic['phone'], 16) ?> צור קשר</a>
</aside>

<!-- ===== hero ===== -->
<section class="wpx-hero">
  <span class="wpx-hero__orb wpx-hero__orb--1"></span>
  <span class="wpx-hero__orb wpx-hero__orb--2"></span>
  <div class="wpx-container wpx-hero__in">
    <div class="wpx-hero__copy wpx-reveal">
      <span class="wpx-hero__eyebrow"><span class="wpx-dot"></span> זמינים עכשיו · מענה אישי ומהיר</span>
      <h1>חוויית שירות <span class="wpx-grad-text">מושלמת</span><br>מתחילה כאן ב<?= wpx_e($siteName) ?></h1>
      <p class="wpx-hero__lead"><?= wpx_e(wpx_excerpt($siteDesc, 190)) ?></p>
      <div class="wpx-hero__btns">
        <a class="wpx-btn wpx-btn--primary" href="<?= wpx_e($ctaLink) ?>"><?= wpx_icon($ic['phone']) ?> דברו איתנו</a>
        <a class="wpx-btn wpx-btn--ghost" href="#services">השירותים שלנו <?= wpx_icon($ic['arrow'], 17) ?></a>
      </div>
      <div class="wpx-hero__trust">
        <span class="wpx-avatars"><span>א</span><span>ב</span><span>ג</span><span>+</span></span>
        <span><span class="wpx-stars"><?= str_repeat($starSvg, 5) ?></span><br>אלפי לקוחות ממליצים · דירוג 4.9</span>
      </div>
    </div>
    <div class="wpx-hero__visual wpx-reveal">
      <div class="wpx-hero__card">
        <div class="wpx-hero__pic" style="background:linear-gradient(135deg,var(--wpx-brand-050),#fff 60%,var(--wpx-accent-050));display:grid;place-items:center;">
          <span style="width:96px;height:96px;border-radius:26px;background:var(--wpx-grad);color:#fff;display:grid;place-items:center;box-shadow:var(--wpx-sh-brand);font-size:42px;font-weight:900;"><?= wpx_e($initials($siteName)) ?></span>
        </div>
        <div class="wpx-hero__panel">
          <div class="wpx-mini"><b>+10K</b><small>לקוחות</small></div>
          <div class="wpx-mini"><b>98%</b><small>שביעות רצון</small></div>
          <div class="wpx-mini"><b>24/7</b><small>זמינות</small></div>
        </div>
      </div>
      <div class="wpx-hero__badge">
        <span class="ic"><?= wpx_icon($ic['check'], 22) ?></span>
        <span><b>שירות מהיר</b><small>מענה אישי וזמינות גבוהה</small></span>
      </div>
    </div>
  </div>
</section>

<!-- ===== stats ===== -->
<section class="wpx-section wpx-section--tight">
  <div class="wpx-container">
    <div class="wpx-stats wpx-reveal">
      <div class="wpx-stat"><b>+10K</b><span>לקוחות מרוצים</span></div>
      <div class="wpx-stat"><b>+13</b><span>שנות ניסיון</span></div>
      <div class="wpx-stat"><b>98%</b><span>שביעות רצון</span></div>
      <div class="wpx-stat"><b>24/7</b><span>זמינות ותמיכה</span></div>
    </div>
  </div>
</section>

<!-- ===== services / what we offer ===== -->
<section class="wpx-section wpx-section--soft" id="services">
  <div class="wpx-container">
    <div class="wpx-head wpx-reveal">
      <span class="wpx-kicker">מה אנחנו מציעים</span>
      <h2>כל מה שצריך, במקום אחד</h2>
      <p>פתרונות מקצה לקצה בליווי צמוד ובסטנדרט הגבוה ביותר — בדיוק לפי הצרכים שלכם.</p>
    </div>
    <div class="wpx-grid wpx-grid--3">
      <?php if ($cards): foreach ($cards as $i => $c): ?>
      <a class="wpx-card wpx-card--svc wpx-reveal" href="<?= wpx_e($c['link']) ?>">
        <div class="wpx-card__ic"><?= wpx_icon($ic[$svc_icons[$i % count($svc_icons)]], 28) ?></div>
        <h3><?= wpx_e($c['title']) ?></h3>
        <?php if ($c['desc'] !== ''): ?><p><?= wpx_e($c['desc']) ?></p><?php endif; ?>
        <span class="wpx-link">קראו עוד <?= wpx_icon($ic['arrow'], 16) ?></span>
      </a>
      <?php endforeach; else:
        $def = [
          ['ייעוץ מקצועי', 'ליווי אישי שמתחיל בהבנת הצרכים המדויקים שלכם ונמשך עד לתוצאה.'],
          ['אמינות מלאה', 'שקיפות מוחלטת, עמידה בזמנים ואחריות מלאה על כל עבודה.'],
          ['יחס אישי', 'כל לקוח מקבל מענה אישי, סבלני ומותאם בדיוק עבורו.'],
          ['מענה מהיר', 'זמינות גבוהה וזמני תגובה קצרים בכל ערוצי התקשורת.'],
          ['איכות מובילה', 'עבודה עם הכלים, הידע והסטנדרטים המתקדמים ביותר בתחום.'],
          ['שביעות רצון', 'אלפי לקוחות חוזרים וממליצים — וזו הגאווה הגדולה שלנו.'],
        ];
        foreach ($def as $i => $d): ?>
      <div class="wpx-card wpx-card--svc wpx-reveal">
        <div class="wpx-card__ic"><?= wpx_icon($ic[$svc_icons[$i % count($svc_icons)]], 28) ?></div>
        <h3><?= wpx_e($d[0]) ?></h3>
        <p><?= wpx_e($d[1]) ?></p>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>

<!-- ===== about ===== -->
<section class="wpx-section" id="about">
  <div class="wpx-container">
    <div class="wpx-about">
      <div class="wpx-about__media wpx-reveal">
        <div class="pic" style="background:linear-gradient(135deg,var(--wpx-brand),#7c3aed);display:grid;place-items:center;">
          <span style="color:#fff;font-size:clamp(40px,7vw,72px);font-weight:900;opacity:.92;letter-spacing:-.03em;"><?= wpx_e($siteName) ?></span>
        </div>
        <div class="wpx-about__chip"><b>13+</b><small>שנות מצוינות</small></div>
      </div>
      <div class="wpx-about__copy wpx-reveal">
        <span class="wpx-kicker">קצת עלינו</span>
        <h2>מקצועיות, ניסיון ויחס שמרגישים</h2>
        <p style="color:var(--wpx-body);font-size:18px;"><?= wpx_e($siteDesc) ?></p>
        <ul class="wpx-checklist">
          <li><span class="tick"><?= wpx_icon($ic['check'], 15) ?></span> צוות מומחים מנוסה ומקצועי</li>
          <li><span class="tick"><?= wpx_icon($ic['check'], 15) ?></span> שירות אישי וליווי לאורך כל הדרך</li>
          <li><span class="tick"><?= wpx_icon($ic['check'], 15) ?></span> מחויבות לאיכות, לזמנים ולתוצאה</li>
        </ul>
        <a class="wpx-btn wpx-btn--primary" href="<?= wpx_e($ctaLink) ?>">קראו עוד עלינו</a>
      </div>
    </div>
  </div>
</section>

<!-- ===== testimonials ===== -->
<section class="wpx-section wpx-section--soft" id="testimonials">
  <div class="wpx-container">
    <div class="wpx-head wpx-reveal">
      <span class="wpx-kicker">לקוחות מספרים</span>
      <h2>חוויות שמדברות בעד עצמן</h2>
    </div>
    <div class="wpx-grid wpx-grid--3">
      <?php
      if (!$reviews) {
          $reviews = [
            ['text' => 'שירות יוצא דופן מתחילתו ועד סופו. צוות מקצועי, אדיב וזמין — ממליצה בחום לכל מי שמחפש איכות אמיתית.', 'name' => 'מיכל ל.', 'role' => 'לקוחה מרוצה'],
            ['text' => 'הרגשתי בידיים טובות לכל אורך הדרך. תוצאה מעולה ויחס אישי שלא מובן מאליו. תודה רבה!', 'name' => 'דניאל כ.', 'role' => 'לקוח חוזר'],
            ['text' => 'מקצוענים אמיתיים. הסבירו כל שלב, עמדו בזמנים והתוצאה עברה את כל הציפיות שלי.', 'name' => 'נועה ש.', 'role' => 'לקוחה חדשה'],
          ];
      }
      foreach (array_slice($reviews, 0, 6) as $rv): ?>
      <figure class="wpx-quote wpx-reveal">
        <div class="wpx-quote__mark">&ldquo;</div>
        <p><?= wpx_e($rv['text']) ?></p>
        <figcaption>
          <span class="wpx-quote__ava"><?= wpx_e($initials($rv['name'])) ?></span>
          <span><b><?= wpx_e($rv['name']) ?></b><small><?= wpx_e($rv['role']) ?></small></span>
        </figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== CTA ===== -->
<section class="wpx-section">
  <div class="wpx-container">
    <div class="wpx-cta wpx-reveal">
      <div class="wpx-cta__in">
        <h2>מוכנים להתחיל? בואו נדבר</h2>
        <p>השאירו פרטים ונחזור אליכם עוד היום עם הצעה אישית ומשתלמת — ללא כל התחייבות.</p>
        <div class="wpx-btns">
          <a class="wpx-btn wpx-btn--primary" href="<?= wpx_e($ctaLink) ?>"><?= wpx_icon($ic['phone']) ?> לשיחת ייעוץ חינם</a>
          <?php if ($cPhone): ?><a class="wpx-btn wpx-btn--light" href="tel:<?= wpx_e($cPhone) ?>"><?= wpx_e($cPhone) ?></a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== contact ===== -->
<section class="wpx-section wpx-section--soft" id="contact">
  <div class="wpx-container">
    <div class="wpx-head wpx-head--start wpx-reveal" style="max-width:560px;">
      <span class="wpx-kicker">צרו קשר</span>
      <h2>נשמח לשמוע מכם</h2>
      <p>מלאו את הטופס ונחזור אליכם בהקדם, או דברו איתנו ישירות.</p>
    </div>
    <div class="wpx-contact">
      <div class="wpx-contact__info wpx-reveal">
        <?php if ($cPhone): ?>
        <a class="wpx-contact__item" href="tel:<?= wpx_e($cPhone) ?>"><span class="ic"><?= wpx_icon($ic['phone'], 22) ?></span><span><small>טלפון</small><b><?= wpx_e($cPhone) ?></b></span></a>
        <?php endif; ?>
        <?php if ($cEmail): ?>
        <a class="wpx-contact__item" href="mailto:<?= wpx_e($cEmail) ?>"><span class="ic"><?= wpx_icon($ic['mail'], 22) ?></span><span><small>אימייל</small><b><?= wpx_e($cEmail) ?></b></span></a>
        <?php endif; ?>
        <?php if ($cAddr): ?>
        <div class="wpx-contact__item"><span class="ic"><?= wpx_icon($ic['pin'], 22) ?></span><span><small>כתובת</small><b><?= wpx_e($cAddr) ?></b></span></div>
        <?php endif; ?>
        <?php if (!$cPhone && !$cEmail && !$cAddr): ?>
        <div class="wpx-contact__item"><span class="ic"><?= wpx_icon($ic['clock'], 22) ?></span><span><small>זמינות</small><b>א׳–ה׳ · מענה אישי ומהיר</b></span></div>
        <?php endif; ?>
      </div>
      <form class="wpx-form wpx-reveal" action="includes/contact_handler.php" method="post">
        <div class="wpx-field"><label>שם מלא</label><input class="wpx-input" type="text" name="name" placeholder="השם שלכם" required></div>
        <div class="wpx-field"><label>אימייל</label><input class="wpx-input" type="email" name="email" placeholder="you@example.com" required></div>
        <div class="wpx-field"><label>טלפון</label><input class="wpx-input" type="tel" name="phone" placeholder="050-0000000"></div>
        <div class="wpx-field"><label>הודעה</label><textarea class="wpx-input" name="message" placeholder="איך נוכל לעזור?" required></textarea></div>
        <input type="hidden" name="subject" value="פנייה מהאתר">
        <input type="hidden" name="sub" value="SUBTRUE">
        <button class="wpx-btn wpx-btn--primary" type="submit" style="width:100%;">שליחת הודעה</button>
      </form>
    </div>
  </div>
</section>

<!-- ===== footer ===== -->
<footer class="wpx-footer">
  <div class="wpx-container">
    <div class="wpx-footer__grid">
      <div>
        <div class="wpx-footer__brand"><span class="wpx-logo__mark"><?= wpx_e($initials($siteName)) ?></span> <?= wpx_e($siteName) ?></div>
        <p><?= wpx_e(wpx_excerpt($siteDesc, 150)) ?></p>
        <div class="wpx-soc">
          <a href="#" aria-label="Facebook"><?= wpx_icon('<path d="M14 9h3V6h-3c-1.7 0-3 1.3-3 3v2H8v3h3v6h3v-6h3l1-3h-4V9c0-.6.4-1 1-1z"/>') ?></a>
          <a href="#" aria-label="Instagram"><?= wpx_icon('<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>') ?></a>
          <a href="#" aria-label="WhatsApp"><?= wpx_icon('<path d="M20 12a8 8 0 0 1-11.9 7L4 20l1-4.1A8 8 0 1 1 20 12z"/>') ?></a>
        </div>
      </div>
      <div>
        <h4>ניווט</h4>
        <ul><?php foreach (array_slice($menu, 0, 5) as $m): ?><li><a href="<?= wpx_e($m['link']) ?>"><?= wpx_e($m['title']) ?></a></li><?php endforeach; ?></ul>
      </div>
      <div>
        <h4>מידע</h4>
        <ul>
          <li><a href="#about">אודות</a></li>
          <li><a href="#services">שירותים</a></li>
          <li><a href="#testimonials">המלצות</a></li>
          <li><a href="<?= wpx_e($ctaLink) ?>">צור קשר</a></li>
        </ul>
      </div>
      <div>
        <h4>יצירת קשר</h4>
        <?php if ($cPhone): ?><div class="wpx-footer__c"><span class="ic"><?= wpx_icon($ic['phone']) ?></span> <?= wpx_e($cPhone) ?></div><?php endif; ?>
        <?php if ($cEmail): ?><div class="wpx-footer__c"><span class="ic"><?= wpx_icon($ic['mail']) ?></span> <?= wpx_e($cEmail) ?></div><?php endif; ?>
        <?php if ($cAddr): ?><div class="wpx-footer__c"><span class="ic"><?= wpx_icon($ic['pin']) ?></span> <?= wpx_e($cAddr) ?></div><?php endif; ?>
        <?php if (!$cPhone && !$cEmail && !$cAddr): ?><p>נשמח לעמוד לרשותכם בכל שאלה.</p><?php endif; ?>
      </div>
    </div>
    <div class="wpx-footer__bar">
      <span>© <?= wpx_e($year) ?> <?= wpx_e($siteName) ?> · כל הזכויות שמורות</span>
      <span>נבנה באמצעות <a href="https://whoopix.ai" target="_blank" rel="noopener">Whoopix</a></span>
    </div>
  </div>
</footer>

<script>
(function(){
  var h=document.getElementById('wpxHeader');
  if(h){var onScroll=function(){h.classList.toggle('is-stuck',window.scrollY>8);};onScroll();window.addEventListener('scroll',onScroll,{passive:true});}
  var b=document.getElementById('wpxBurger'),d=document.getElementById('wpxDrawer'),bk=document.getElementById('wpxDrawerBack'),c=document.getElementById('wpxDrawerClose');
  function open(){d&&d.classList.add('show');bk&&bk.classList.add('show');document.body.style.overflow='hidden';}
  function close(){d&&d.classList.remove('show');bk&&bk.classList.remove('show');document.body.style.overflow='';}
  b&&b.addEventListener('click',open);c&&c.addEventListener('click',close);bk&&bk.addEventListener('click',close);
  d&&d.querySelectorAll('a').forEach(function(a){a.addEventListener('click',close);});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
  var els=document.querySelectorAll('.wpx-reveal');
  if('IntersectionObserver' in window){
    var io=new IntersectionObserver(function(en){en.forEach(function(x){if(x.isIntersecting){x.target.classList.add('is-in');io.unobserve(x.target);}});},{threshold:.12,rootMargin:'0px 0px -8% 0px'});
    els.forEach(function(el,i){el.style.transitionDelay=(Math.min(i,6)*60)+'ms';io.observe(el);});
  } else { els.forEach(function(el){el.classList.add('is-in');}); }
})();
</script>
<section class="wpx-dyn-module" data-wpx-dyn-slug="press_videos"><wpx-module data-name="press_videos" data-limit="12"></wpx-module></section>
</body>
</html>
