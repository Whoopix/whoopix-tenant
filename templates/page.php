<?php
/* ============================================================================
 * Whoopix flagship inner-page template (new-site default theme).
 * Renders DB content pages (mod_pages) inside the neutral, world-class chrome
 * with a polished page hero, breadcrumbs and rich-text "prose" styling — so AI
 * onboarding-generated pages look great with no per-page work.
 * Uses includes/header-wpx.php + includes/footer-wpx.php + wpx-theme.css.
 * ==========================================================================*/

$wpx_cfg   = $GLOBALS['GET_SITE_CONFIG_r'] ?? [];
$wpx_site  = trim((string) ($wpx_cfg['name'] ?? '')) !== '' ? (string) $wpx_cfg['name'] : 'האתר שלי';
$wpx_title = trim((string) ($TEMP_INPAGE_TITLE ?? '')) !== '' ? (string) $TEMP_INPAGE_TITLE : $wpx_site;
$wpx_ptit  = trim((string) ($TEMP_PAGE_TITLE ?? '')) !== '' ? (string) $TEMP_PAGE_TITLE : $wpx_title;
?>
<!DOCTYPE html>
<html lang="<?= defined('LANGCODE') ? htmlspecialchars((string) LANGCODE) : 'he' ?>" dir="<?= defined('LANGDIR') ? htmlspecialchars((string) LANGDIR) : 'rtl' ?>">
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
  <title><?= htmlspecialchars((string) $wpx_ptit) ?> | <?= htmlspecialchars((string) $wpx_site) ?></title>
  <meta name="keywords" content="<?= htmlspecialchars((string) ($wpx_cfg['keywords'] ?? '')) ?>">
  <meta name="description" content="<?= htmlspecialchars((string) ($wpx_cfg['description'] ?? '')) ?>">
  <link rel="shortcut icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="resources/design/css/wpx-theme.css" rel="stylesheet">
  <?php include __DIR__ . '/../includes/wpx-theme-vars.php'; ?>
</head>
<body class="wpx">

<?php include __DIR__ . '/../includes/header-wpx.php'; ?>

<section class="wpx-pagehero">
  <div class="wpx-container wpx-pagehero__in">
    <nav class="wpx-crumbs" aria-label="breadcrumbs">
      <a href="/">דף הבית</a>
      <?php echo wpx_icon((wpx_chrome()['ic']['chev'] ?? '<path d="M15 18l-6-6 6-6"/>'), 14); ?>
      <span><?= htmlspecialchars((string) $wpx_title) ?></span>
    </nav>
    <h1><?= htmlspecialchars((string) $wpx_title) ?></h1>
  </div>
</section>

<section class="wpx-section">
  <div class="wpx-container">
    <article class="wpx-prose wpx-reveal" style="margin-inline:auto;">
      <?php if (function_exists('PAGE_TEXT')) { PAGE_TEXT(); } ?>
    </article>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer-wpx.php'; ?>

</body>
</html>
