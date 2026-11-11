<?php

declare(strict_types=1);

const WPX_TEMPLATE_LOADER_CONTRACT_VERSION = '20260809a';

$moduleTagsRuntime = __DIR__ . '/wpx-module-tags.php';
if (is_file($moduleTagsRuntime)) {
    require_once $moduleTagsRuntime;
}

if (empty($TEMP_TEMPLATE)) {
    $TEMP_TEMPLATE   = 2;
    $TEMP_PAGE_TITLE = 'System Error';
}

// Lab routes always use the same inner-page Xleb shell as /contact and /about — never homepage template.
$isLabModuleRoute = (($get_mod ?? '') === 'lab');
if ($isLabModuleRoute) {
    $labInnerTplRow = db_fetch_array(
        db_query('SELECT template FROM mod_pages_list WHERE id = 22 LIMIT 1')
    ) ?: db_fetch_array(
        db_query('SELECT template FROM mod_pages_list WHERE id = 2 LIMIT 1')
    ) ?: [];
    $labInnerTplId = (int) ($labInnerTplRow['template'] ?? 0);
    if ($labInnerTplId > 0) {
        $TEMP_TEMPLATE = $labInnerTplId;
    }
}

$GET_TEMP_r = db_fetch_array(
    db_query("SELECT * FROM templates WHERE id = :id", ['id' => $TEMP_TEMPLATE])
) ?: [];

$selectedTemplateActive = $GET_TEMP_r
    && (!isset($GET_TEMP_r['archived']) || (int) $GET_TEMP_r['archived'] === 0);
$baseTemplateFile = $selectedTemplateActive
    ? trim(str_replace('\\', '/', (string) ($GET_TEMP_r['file'] ?? '')))
    : '';
if ($baseTemplateFile === ''
    || basename($baseTemplateFile) !== $baseTemplateFile
    || strpos($baseTemplateFile, '..') !== false
    || !preg_match('/\.php$/i', $baseTemplateFile)
) {
    $baseTemplateFile = '';
}
$templateFile = 'templates/' . $baseTemplateFile;

// Per-language template files only for non-default langs (matches CMS tpl_lang_file_path).
$useLangSuffix = !(defined('LANDDEFAULT') && LANDDEFAULT);
if ($useLangSuffix && $baseTemplateFile !== '') {
    $langSuffixFile = 'templates/' . preg_replace('/\.php$/i', '', $baseTemplateFile) . '_' . (int) LANG . '.php';
    if (is_file($langSuffixFile)) {
        $templateFile = $langSuffixFile;
    }
}

if (!is_file($templateFile)) {
    // Missing synced template must not hard-500 the whole site (bridge apply lag / free path).
    // Prefer page.php for content pages — never fall back to the homepage shell.
    $fallbacks = (($get_mod ?? '') === 'pages')
        ? array('templates/page.php', 'templates/wpx-default.php', 'templates/index.php')
        : array('templates/wpx-default.php', 'templates/page.php', 'templates/index.php');
    foreach ($fallbacks as $fallback) {
        if (is_file($fallback)) {
            $templateFile = $fallback;
            break;
        }
    }
}
if (!is_file($templateFile)) {
    http_response_code(500);
    exit;
}

ob_start();
require $templateFile;
$templateContent = ob_get_clean();
if (function_exists('wpx_module_tags_expand')) {
    $templateContent = wpx_module_tags_expand($templateContent, array(
        'lang_id' => defined('LANG') ? (int) LANG : 1,
    ));
}

$GET_SITE_CONFIG_r = db_fetch_array(
    db_query("SELECT * FROM config_site WHERE lang_id = :lid", ['lid' => LANG])
) ?: [];

if (!empty($GET_keywords)) {
    $GET_SITE_CONFIG_r['keywords'] = $GET_keywords;
}

if (!empty($GET_description)) {
    $GET_SITE_CONFIG_r['description'] = $GET_description;
}

$SEO = is_array($SEO ?? null) ? $SEO : [];

// Prefer module-provided SEO; otherwise load from mod_seo (page id, or homepage id=1).
if (trim((string) ($SEO['ptitle'] ?? '')) === '' && function_exists('db_query')) {
    $seoLookupId = 0;
    if (!empty($get_id) && is_numeric((string) $get_id)) {
        $seoLookupId = (int) $get_id;
    } elseif (($get_mod ?? '') === '' || (($get_mod ?? '') === 'pages' && (string) ($get_id ?? '') === '')) {
        $seoLookupId = 1;
    }
    if ($seoLookupId > 0) {
        $seoLid = defined('LANG') ? (int) LANG : 1;
        $seoRow = db_fetch_array(db_query(
            "SELECT * FROM `mod_seo` WHERE `mod` = 'pages' AND `id` = :id AND `lang_id` = :lid ORDER BY `uniqe_id` DESC LIMIT 1",
            ['id' => $seoLookupId, 'lid' => $seoLid]
        )) ?: [];
        if (!$seoRow) {
            $seoRow = db_fetch_array(db_query(
                "SELECT * FROM `mod_seo` WHERE `id` = :id AND `lang_id` = :lid ORDER BY `uniqe_id` DESC LIMIT 1",
                ['id' => $seoLookupId, 'lid' => $seoLid]
            )) ?: [];
        }
        if ($seoRow) {
            $SEO = $seoRow;
        }
    }
}

$P_TITLE = trim((string) ($SEO['ptitle'] ?? '')) !== ''
    ? (string) $SEO['ptitle']
    : (string) ($TEMP_PAGE_TITLE ?? '');
$P_TITLE = trim(html_entity_decode(strip_tags($P_TITLE), ENT_QUOTES, 'UTF-8'));
if (strcasecmp($P_TITLE, 'System Error') === 0) {
    $P_TITLE = '';
}

$P_KEYWORDS = trim((string) ($SEO['keywords'] ?? '')) !== ''
    ? (string) $SEO['keywords']
    : htmlspecialchars((string) ($GET_SITE_CONFIG_r['keywords'] ?? ''));

$P_DESCRIPTION = trim((string) ($SEO['description'] ?? '')) !== ''
    ? (string) $SEO['description']
    : htmlspecialchars((string) ($GET_SITE_CONFIG_r['description'] ?? ''));

if ($SEO === [] && isset($sss) && is_array($sss) && $sss !== []) {
    $SEO = $sss;
}

/**
 * Absolute public canonical URL for the current front-end request.
 * Prefers pretty plink (/about) over comma routes (,pages,3); homepage → /.
 */
if (!function_exists('wpx_front_canonical_url')) {
    function wpx_front_canonical_url(array $seo = [], string $getMod = '', $getId = '', string $getAct = ''): string
    {
        $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        if ($host === '' && defined('SITEDOMAIN')) {
            $host = strtolower((string) SITEDOMAIN);
        }
        // Free-plan tenants live under /user/<slug>/ on hoops.site. Canonicals must
        // keep that path prefix — never emit https://www.hoops.site/<plink> (404).
        $basePath = '';
        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $requestUri = str_replace('\\', '/', (string) ($_SERVER['REQUEST_URI'] ?? ''));
        if (preg_match('~^(.*?/user/[^/]+)~i', $scriptName, $bm)) {
            $basePath = rtrim($bm[1], '/');
        } elseif (preg_match('~^(.*?/user/[^/]+)~i', $requestUri, $bm)) {
            $basePath = rtrim($bm[1], '/');
        }
        $isHoopsFree = ($basePath !== '' && preg_match('~(^|\.)hoops\.site$~i', $host));
        if ($isHoopsFree) {
            $host = preg_replace('~^www\.~i', '', $host);
        } elseif ($host !== '' && strpos($host, 'www.') !== 0
            && preg_match('/\.(co\.il|com|net|org|ai|site)$/i', $host)
        ) {
            $host = 'www.' . $host;
        }
        if ($host === '') {
            return '';
        }

        $isHomePlink = static function (string $plink): bool {
            return $plink === '' || (bool) preg_match('/^(home|index)$/i', $plink);
        };

        $path = '';
        $query = trim((string) ($_GET['query'] ?? ''));

        // Homepage (index.php → pages id=1) is always site root — ignore stray SEO plinks.
        if ($getMod === 'pages' && (string) $getId === '1' && $query === '' && empty($_GET['mod'])) {
            return 'https://' . $host . ($basePath !== '' ? $basePath . '/' : '/');
        }

        if ($query !== '' && strpos($query, ',') === false && !$isHomePlink($query)) {
            $path = '/' . ltrim($query, '/');
        }

        if ($path === '') {
            $plink = trim((string) ($seo['plink'] ?? ''));
            if ($plink === '' && isset($GLOBALS['sss']) && is_array($GLOBALS['sss'])) {
                $plink = trim((string) ($GLOBALS['sss']['plink'] ?? ''));
            }
            if ($plink !== '' && !$isHomePlink($plink)) {
                $path = '/' . ltrim($plink, '/');
            } elseif ($plink !== '' && $isHomePlink($plink)) {
                $path = '/';
            }
        }

        if ($path === '' && $getMod !== '' && function_exists('db_query')) {
            $lid = defined('LANG') ? (int) LANG : 1;
            $row = [];
            if ($getId !== '' && is_numeric((string) $getId)) {
                $row = db_fetch_array(db_query(
                    "SELECT `plink` FROM `mod_seo` WHERE `mod` = :m AND `id` = :id AND `lang_id` = :lid ORDER BY `uniqe_id` DESC LIMIT 1",
                    ['m' => $getMod, 'id' => (int) $getId, 'lid' => $lid]
                )) ?: [];
            }
            if ($row === [] || trim((string) ($row['plink'] ?? '')) === '') {
                $row = db_fetch_array(db_query(
                    "SELECT `plink` FROM `mod_seo` WHERE `mod` = :m AND `lang_id` = :lid AND TRIM(IFNULL(`plink`,'')) <> '' ORDER BY `uniqe_id` DESC LIMIT 1",
                    ['m' => $getMod, 'lid' => $lid]
                )) ?: [];
            }
            if ($row === [] || trim((string) ($row['plink'] ?? '')) === '') {
                $row = db_fetch_array(db_query(
                    "SELECT `plink` FROM `mod_seo` WHERE LOWER(`plink`) = LOWER(:p) AND `lang_id` = :lid ORDER BY `uniqe_id` DESC LIMIT 1",
                    ['p' => $getMod, 'lid' => $lid]
                )) ?: [];
            }
            $plink = trim((string) ($row['plink'] ?? ''));
            if ($plink !== '' && !$isHomePlink($plink)) {
                $path = '/' . ltrim($plink, '/');
            }
        }

        if ($path === '') {
            $modEmpty = ($getMod === '' || $getMod === null);
            $isHomePage = $modEmpty
                || ($getMod === 'pages' && ((string) $getId === '' || (string) $getId === '1'));
            if ($isHomePage && $query === '' && empty($_GET['mod'])) {
                $path = '/';
            }
        }

        if ($path === '' && $getMod !== '') {
            if ($getMod === 'pages' && $getId !== '' && is_numeric((string) $getId)) {
                $path = ',pages,' . (int) $getId;
            } elseif ($getAct !== '' && $getId !== '') {
                $path = ',' . $getMod . ',' . $getAct . '_' . $getId;
            } elseif ($getId !== '' && is_numeric((string) $getId)) {
                $path = ',' . $getMod . ',' . (int) $getId;
            } else {
                $path = ',' . $getMod . ',';
            }
        }

        if ($path === '') {
            $path = '/';
        }

        if ($path === '/') {
            return 'https://' . $host . ($basePath !== '' ? $basePath . '/' : '/');
        }
        if (isset($path[0]) && $path[0] === ',') {
            return 'https://' . $host . ($basePath !== '' ? $basePath . '/' : '/') . $path;
        }
        return 'https://' . $host . ($basePath !== '' ? $basePath : '') . $path;
    }
}

$P_CANONICAL = wpx_front_canonical_url(
    $SEO,
    (string) ($get_mod ?? ''),
    $get_id ?? '',
    (string) (($get_act ?? '') !== '' ? $get_act : ($get_acts ?? ''))
);

$replacements = [
    '<% SITE_NAME %>'        => htmlspecialchars((string) ($GET_SITE_CONFIG_r['name'] ?? '')),
    '<% SITE_KEYWORDS %>'    => $P_KEYWORDS,
    '<% SITE_DESCRIPTION %>' => $P_DESCRIPTION,
    '<% INPAGE_TITLE %>'     => stripslashes((string) ($TEMP_INPAGE_TITLE ?? '')),
    // Always prefer resolved SEO title ($P_TITLE), not the raw TEMP fallback alone.
    '<% PAGE_TITLE %>'       => htmlspecialchars($P_TITLE !== '' ? $P_TITLE : (string) ($TEMP_PAGE_TITLE ?? ''), ENT_QUOTES, 'UTF-8'),
    '<% PAGE_CANONICAL %>'   => htmlspecialchars($P_CANONICAL, ENT_QUOTES, 'UTF-8'),
];

$templateContent = strtr($templateContent, $replacements);

// Force document <title> from SEO even when templates/includes bake a static brand-only title.
$siteNameRaw = trim((string) ($GET_SITE_CONFIG_r['name'] ?? ''));
$pageTitleRaw = $P_TITLE;
$docTitle = $siteNameRaw;
if ($pageTitleRaw !== '') {
    $pageHasBrand = $siteNameRaw !== '' && (
        (function_exists('mb_stripos') && mb_stripos($pageTitleRaw, $siteNameRaw, 0, 'UTF-8') !== false)
        || (!function_exists('mb_stripos') && stripos($pageTitleRaw, $siteNameRaw) !== false)
    );
    if ($pageHasBrand) {
        $docTitle = $pageTitleRaw;
    } elseif ($siteNameRaw !== '') {
        $docTitle = $siteNameRaw . ' | ' . $pageTitleRaw;
    } else {
        $docTitle = $pageTitleRaw;
    }
}
if ($docTitle !== '') {
    $titleHtml = '<title>' . htmlspecialchars($docTitle, ENT_QUOTES, 'UTF-8') . '</title>';
    $replacedTitle = preg_replace('/<title\b[^>]*>.*?<\/title>/is', $titleHtml, $templateContent, 1);
    if (is_string($replacedTitle) && $replacedTitle !== '') {
        $templateContent = $replacedTitle;
    }
}

// Force <link rel="canonical"> to this page's public URL (templates often bake homepage).
if ($P_CANONICAL !== '') {
    $canonicalTag = '<link rel="canonical" href="' . htmlspecialchars($P_CANONICAL, ENT_QUOTES, 'UTF-8') . '">';
    $replacedCanon = preg_replace(
        '/<link\b[^>]*rel\s*=\s*["\']canonical["\'][^>]*>/i',
        $canonicalTag,
        $templateContent,
        1
    );
    if (is_string($replacedCanon) && $replacedCanon !== '') {
        $templateContent = $replacedCanon;
    } elseif (stripos($templateContent, 'rel="canonical"') === false
        && stripos($templateContent, "rel='canonical'") === false
    ) {
        if (preg_match('/<head\b[^>]*>/i', $templateContent)) {
            $templateContent = preg_replace(
                '/(<head\b[^>]*>)/i',
                '$1' . "\n\t" . $canonicalTag,
                $templateContent,
                1
            ) ?? ($canonicalTag . $templateContent);
        } else {
            $templateContent = $canonicalTag . $templateContent;
        }
    }
}

// Inject description + Open Graph / Twitter cards when composed HTML omitted them
// (common for section-composed homepages that only bake <title>).
$P_OG_TITLE = $docTitle !== '' ? $docTitle : $P_TITLE;
$seoHeadBits = '';
if ($P_DESCRIPTION !== '') {
    if (!preg_match('/<meta\b[^>]*name\s*=\s*["\']description["\']/i', $templateContent)) {
        $seoHeadBits .= '<meta name="description" content="'
            . htmlspecialchars($P_DESCRIPTION, ENT_QUOTES, 'UTF-8') . '">' . "\n\t";
    }
    if (!preg_match('/<meta\b[^>]*property\s*=\s*["\']og:description["\']/i', $templateContent)) {
        $seoHeadBits .= '<meta property="og:description" content="'
            . htmlspecialchars($P_DESCRIPTION, ENT_QUOTES, 'UTF-8') . '">' . "\n\t";
    }
    if (!preg_match('/<meta\b[^>]*name\s*=\s*["\']twitter:description["\']/i', $templateContent)) {
        $seoHeadBits .= '<meta name="twitter:description" content="'
            . htmlspecialchars($P_DESCRIPTION, ENT_QUOTES, 'UTF-8') . '">' . "\n\t";
    }
}
if ($P_KEYWORDS !== '' && !preg_match('/<meta\b[^>]*name\s*=\s*["\']keywords["\']/i', $templateContent)) {
    $seoHeadBits .= '<meta name="keywords" content="'
        . htmlspecialchars($P_KEYWORDS, ENT_QUOTES, 'UTF-8') . '">' . "\n\t";
}
if ($P_OG_TITLE !== '') {
    if (!preg_match('/<meta\b[^>]*property\s*=\s*["\']og:title["\']/i', $templateContent)) {
        $seoHeadBits .= '<meta property="og:title" content="'
            . htmlspecialchars($P_OG_TITLE, ENT_QUOTES, 'UTF-8') . '">' . "\n\t";
    }
    if (!preg_match('/<meta\b[^>]*name\s*=\s*["\']twitter:title["\']/i', $templateContent)) {
        $seoHeadBits .= '<meta name="twitter:title" content="'
            . htmlspecialchars($P_OG_TITLE, ENT_QUOTES, 'UTF-8') . '">' . "\n\t";
    }
}
if (!preg_match('/<meta\b[^>]*property\s*=\s*["\']og:type["\']/i', $templateContent)) {
    $seoHeadBits .= '<meta property="og:type" content="website">' . "\n\t";
}
if (!preg_match('/<meta\b[^>]*name\s*=\s*["\']twitter:card["\']/i', $templateContent)) {
    $seoHeadBits .= '<meta name="twitter:card" content="summary_large_image">' . "\n\t";
}
if ($P_CANONICAL !== '') {
    if (!preg_match('/<meta\b[^>]*property\s*=\s*["\']og:url["\']/i', $templateContent)) {
        $seoHeadBits .= '<meta property="og:url" content="'
            . htmlspecialchars($P_CANONICAL, ENT_QUOTES, 'UTF-8') . '">' . "\n\t";
    }
}
if ($seoHeadBits !== '' && preg_match('/<head\b[^>]*>/i', $templateContent)) {
    $templateContent = preg_replace(
        '/(<head\b[^>]*>)/i',
        '$1' . "\n\t" . rtrim($seoHeadBits),
        $templateContent,
        1
    ) ?? $templateContent;
}

$GET_BLOCKS_q = db_query(
    "SELECT L.* FROM mod_blocks L 
     JOIN mod_blocks_list T ON L.block_id = T.id 
     WHERE L.lang_id = :lid",
    ['lid' => LANG]
);

while ($row = db_fetch_array($GET_BLOCKS_q)) {
    $bid = (string) ($row['block_id'] ?? '');

    $templateContent = str_replace(
        ["<% BLOCK_{$bid} %>", "<% BLOCK_TITLE_{$bid} %>"],
        [
            (string) ($row['text'] ?? ''),
            (string) ($row['title'] ?? ''),
        ],
        $templateContent
    );
}

$templateContent = preg_replace_callback(
    '/<% LANG_([A-Za-z0-9]+) %>/',
    static fn($m) => (string) ($lang[$m[1]] ?? ''),
    $templateContent
);

// Lab portal only: wrap PAGE_TEXT into the lab shell once.
// The inner-page template already calls PAGE_TEXT() during require — reusing that
// HTML avoids a second render (duplicate login/forms on ,lab,* routes).
if ($isLabModuleRoute && function_exists('PAGE_TEXT') && stripos($templateContent, 'lab-module-content') === false) {
    $labPortalHtml = '';

    // Prefer HTML already emitted by the template's PAGE_TEXT() call.
    if (preg_match('/<!-- Title Bar End -->([\s\S]*?)(?=<!-- footer -->|<footer\b)/i', $templateContent, $labChunkMatch)) {
        $labChunk = (string) ($labChunkMatch[1] ?? '');
        if (preg_match('/<div class="page-content(?! client-inner-page client-layout-lab)[^"]*">([\s\S]*)<\/div>\s*$/i', $labChunk, $labInnerMatch)) {
            $labPortalHtml = trim((string) $labInnerMatch[1]);
        } else {
            $labPortalHtml = trim($labChunk);
        }
        $looksLikeLabBody = $labPortalHtml !== ''
            && (
                stripos($labPortalHtml, '<form') !== false
                || stripos($labPortalHtml, 'lab-') !== false
                || stripos($labPortalHtml, 'client-lab') !== false
                || strlen(trim(strip_tags($labPortalHtml))) > 40
            );
        if ($looksLikeLabBody) {
            $templateContent = preg_replace(
                '/(<!-- Title Bar End -->)([\s\S]*?)(?=<!-- footer -->|<footer\b)/i',
                '$1',
                $templateContent,
                1
            ) ?? $templateContent;
        } else {
            $labPortalHtml = '';
        }
    }

    // Templates without an inline PAGE_TEXT() slot still need an explicit render.
    if ($labPortalHtml === '') {
        ob_start();
        PAGE_TEXT();
        $labPortalHtml = (string) ob_get_clean();
    }

    if ($labPortalHtml !== '') {
        if (!empty($TEMP_PAGE_TITLE)) {
            $templateContent = preg_replace(
                '/<title>.*?<\/title>/is',
                '<title>' . htmlspecialchars((string) $TEMP_PAGE_TITLE, ENT_QUOTES, 'UTF-8') . '</title>',
                $templateContent,
                1
            );
        }

        // Normalize header: inner-page chrome, no homepage slider in header.
        if (stripos($templateContent, 'client-header-inner') === false) {
            $templateContent = preg_replace(
                '/(<header class="site-header header-style-2)(?!\s+client-header-inner)/',
                '$1 client-header-inner',
                $templateContent,
                1
            );
        }
        $templateContent = preg_replace(
            '/\s*<div class="pbmit-slider-area pbmit-slider-two">[\s\S]*?(?=<!-- Header Main Area End Here -->|<\/header>)/i',
            '',
            $templateContent,
            1
        );

        // Fallback title bar when a homepage-style template slipped through.
        if (stripos($templateContent, 'client-title-bar') === false) {
            $labHeroTitle = trim(stripslashes((string) ($TEMP_INPAGE_TITLE ?? '')));
            if ($labHeroTitle === '') {
                $labHeroTitle = trim((string) ($TEMP_PAGE_TITLE ?? 'אזור לקוחות'));
            }
            $labHeroTitleEsc = htmlspecialchars($labHeroTitle, ENT_QUOTES, 'UTF-8');
            $siteNameEsc = htmlspecialchars((string) ($GET_SITE_CONFIG_r['name'] ?? 'Lab'), ENT_QUOTES, 'UTF-8');
            $titleBar = '<!-- Title Bar -->'
                . '<div class="pbmit-title-bar-wrapper client-title-bar"><div class="container"><div class="pbmit-title-bar-content">'
                . '<div class="pbmit-title-bar-content-inner"><div class="pbmit-tbar"><div class="pbmit-tbar-inner container">'
                . '<h1 class="pbmit-tbar-title">' . $labHeroTitleEsc . '</h1></div></div>'
                . '<div class="pbmit-breadcrumb"><div class="pbmit-breadcrumb-inner">'
                . '<span><a title="' . $siteNameEsc . '" href="/" class="home"><span>' . $siteNameEsc . '</span></a></span>'
                . '<span class="sep"><i class="pbmit-base-icon-angle-right"></i></span>'
                . '<span><span class="post-root post post-post current-item">' . $labHeroTitleEsc . '</span></span>'
                . '</div></div></div></div></div></div>'
                . '<!-- Title Bar End -->';
            $templateContent = preg_replace(
                '/(<!-- Header Main Area End Here -->)/i',
                '$1' . "\n" . $titleBar,
                $templateContent,
                1
            );
        }

        // Do not inherit About/current-menu active state from contact/about templates.
        $templateContent = preg_replace(
            '/<li([^>]*)\sclass="([^"]*\bcurrent-menu-item\b[^"]*)"([^>]*)>\s*<a([^>]*)\shref="\/about"/i',
            '<li$1$3><a$4 href="/about"',
            $templateContent
        );

        $labShell = '<div class="page-content client-inner-page client-layout-lab"><div class="lab-module-content">'
            . $labPortalHtml . '</div></div>';

        $injected = false;
        foreach (['<!-- Title Bar End -->', '<!-- Title Bar End'] as $marker) {
            $pos = stripos($templateContent, $marker);
            if ($pos !== false) {
                $insertAt = $pos + strlen($marker);
                $templateContent = substr($templateContent, 0, $insertAt) . $labShell . substr($templateContent, $insertAt);
                $injected = true;
                break;
            }
        }
        if (!$injected) {
            $templateContent = preg_replace(
                '/(<!-- Header Main Area End Here -->)/i',
                '$1' . "\n" . $labShell,
                $templateContent,
                1
            ) ?: ($templateContent . $labShell);
        }

        // Strip leftover static contact/about page body outside the lab shell.
        $templateContent = preg_replace(
            '/<div class="page-content(?! client-inner-page client-layout-lab)[^"]*">[\s\S]*?(?=<!-- footer -->|<footer\b)/i',
            '',
            $templateContent,
            1
        );
    }
}

echo $templateContent;