<?php
/**
 * Generic public runtime for builder-created content types.
 * Used when modules/{slug}/index.php is absent: modules.php falls back here
 * with $get_mod = the content-type slug.
 *
 * Routes:
 *   ,{slug}       — listing
 *   ,{slug},{id}  — single item detail
 */
global $get_mod, $get_id, $TEMP_TEMPLATE, $TEMP_PAGE_TITLE, $TEMP_INPAGE_TITLE, $SEO;

$wpxDynSlug = preg_replace('/[^a-z0-9_]/', '', strtolower((string) ($get_mod ?? '')));
if ($wpxDynSlug === '' || $wpxDynSlug === '_dynamic') {
    return;
}

$wpxDynMod = db_fetch_array(db_query(
    "SELECT * FROM `mod_modules` WHERE `module`='" . addslashes($wpxDynSlug) . "' AND `status`=1 LIMIT 1"
));
if (!$wpxDynMod) {
    echo '<!-- WPX_DYN_MODULE missing slug=' . htmlspecialchars($wpxDynSlug, ENT_QUOTES, 'UTF-8') . ' -->';
    return;
}

$wpxDynId = isset($get_id) ? (int) $get_id : 0;
if ($wpxDynId <= 0 && preg_match('~(?:^|,)' . preg_quote($wpxDynSlug, '~') . ',(\d+)(?:$|[/?#])~', (string) ($_SERVER['REQUEST_URI'] ?? ''), $m)) {
    $wpxDynId = (int) $m[1];
}

$wpxDynListTable = 'mod_' . $wpxDynSlug . '_list';
$wpxDynLangTable = 'mod_' . $wpxDynSlug;
$wpxDynFk = $wpxDynSlug . '_id';
$wpxDynLangId = defined('LANG') ? (int) LANG : 1;
$wpxDynTitle = trim((string) ($wpxDynMod['name_he'] ?? $wpxDynMod['name_en'] ?? $wpxDynSlug));
if (defined('LANGCODE') && LANGCODE === 'en' && trim((string) ($wpxDynMod['name_en'] ?? '')) !== '') {
    $wpxDynTitle = trim((string) $wpxDynMod['name_en']);
}

// Prefer AGA (or site) inner chrome over Whoopix shell page.php when available.
$wpxDynInner = db_fetch_array(db_query(
    "SELECT `id` AS `template` FROM `templates` WHERE `file`='page.php' LIMIT 1"
));
if (!$wpxDynInner) {
    $wpxDynInner = db_fetch_array(db_query(
        "SELECT `id` AS `template` FROM `templates` WHERE `file` LIKE 'wpx-inner-%' ORDER BY `id` DESC LIMIT 1"
    ));
}
if (!$wpxDynInner) {
    $wpxDynInner = db_fetch_array(db_query("SELECT `id` AS `template` FROM `templates` WHERE `id`=1 LIMIT 1"));
}
if (!empty($wpxDynInner['template'])) {
    $TEMP_TEMPLATE = (int) $wpxDynInner['template'];
}

$TEMP_PAGE_TITLE = htmlspecialchars($wpxDynTitle, ENT_QUOTES, 'UTF-8');
$TEMP_INPAGE_TITLE = $TEMP_PAGE_TITLE;
$SEO = array(
    'ptitle' => $wpxDynTitle,
    'description' => $wpxDynTitle,
    'mod' => $wpxDynSlug,
    'id' => (string) $wpxDynId,
);

if (!function_exists('PAGE_TEXT')) {
    function PAGE_TEXT() {
        global $wpxDynSlug, $wpxDynId, $wpxDynListTable, $wpxDynLangTable, $wpxDynFk, $wpxDynLangId, $wpxDynTitle, $wpxDynMod;

        $isEn = defined('LANGCODE') && strtolower((string) LANGCODE) === 'en';
        $esc = function ($s) {
            return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        };
        $assocOnly = function ($row) {
            $out = array();
            if (!is_array($row)) {
                return $out;
            }
            foreach ($row as $k => $v) {
                if (is_int($k) || ctype_digit((string) $k)) {
                    continue;
                }
                $out[$k] = $v;
            }
            return $out;
        };
        $mergeItem = function ($listRow, $langRow) use ($assocOnly) {
            $out = $assocOnly($listRow);
            foreach ($assocOnly($langRow) as $k => $v) {
                if ($k === 'id') {
                    continue;
                }
                // Prefer non-empty lang values; keep list media if lang empty.
                if ($v === null || $v === '') {
                    if (!isset($out[$k])) {
                        $out[$k] = $v;
                    }
                    continue;
                }
                $out[$k] = $v;
            }
            return $out;
        };

        // col_name => col_type from module builder defs
        $colTypes = array();
        $colTitles = array();
        $modNum = (int) ($wpxDynMod['id'] ?? 0);
        if ($modNum > 0) {
            $dq = db_query(
                'SELECT `col_name`,`col_title`,`col_type` FROM `mod_modules_dynamic` WHERE `mod_id`=' . $modNum
            );
            while ($dq && ($dr = $assocOnly(db_fetch_array($dq)))) {
                $cn = (string) ($dr['col_name'] ?? '');
                if ($cn === '') {
                    continue;
                }
                $colTypes[$cn] = (int) ($dr['col_type'] ?? 1);
                if (!empty($dr['col_title'])) {
                    $colTitles[$cn] = (string) $dr['col_title'];
                }
            }
        }

        $pickTitle = function (array $row) {
            foreach (array('video_title', 'title', 'name', 'headline', 'label') as $k) {
                if (!empty($row[$k])) {
                    return (string) $row[$k];
                }
            }
            foreach ($row as $k => $v) {
                if (in_array($k, array('id', 'lang_id', 'order', 'status', 'access', 'page_id', 'body'), true)) {
                    continue;
                }
                if (is_string($v) && trim($v) !== '' && strlen($v) < 200 && strpos($v, '<') === false) {
                    return $v;
                }
            }
            return 'Item #' . (int) ($row['id'] ?? 0);
        };
        $pickImage = function (array $row) use ($colTypes) {
            foreach (array('photo', 'image', 'pic', 'picture', 'img', 'avatar', 'thumbnail', 'thumb') as $k) {
                if (!empty($row[$k])) {
                    return (string) $row[$k];
                }
            }
            foreach ($colTypes as $cn => $ct) {
                if ((int) $ct === 3 && !empty($row[$cn])) {
                    return (string) $row[$cn];
                }
            }
            return '';
        };
        $pickBody = function (array $row) {
            foreach (array('description', 'bio', 'text', 'content', 'body', 'desc') as $k) {
                if (!empty($row[$k])) {
                    return (string) $row[$k];
                }
            }
            return '';
        };
        $pickRole = function (array $row) {
            foreach (array('role', 'position', 'job', 'title_role', 'publication_date', 'date') as $k) {
                if (!empty($row[$k])) {
                    return (string) $row[$k];
                }
            }
            return '';
        };
        $pickVideo = function (array $row) use ($colTypes) {
            foreach (array('video_file', 'video', 'movie', 'media', 'file', 'clip') as $k) {
                if (!empty($row[$k])) {
                    return (string) $row[$k];
                }
            }
            foreach ($colTypes as $cn => $ct) {
                if ((int) $ct === 7 && !empty($row[$cn])) {
                    return (string) $row[$cn];
                }
            }
            return '';
        };
        $mediaUrl = function ($path) {
            $path = trim((string) $path);
            if ($path === '') {
                return '';
            }
            if (preg_match('~^https?://~i', $path) || strpos($path, '//') === 0) {
                return $path;
            }
            if (preg_match('~^(files/|images/|resources/)~i', $path)) {
                // Legacy CMS saves sometimes store video UUID under files/upload/ —
                // rewrite to the real videos folder when the basename looks like a video.
                if (preg_match('~^files/upload/([^/]+\\.(mp4|webm|ogg|mov|m4v))$~i', $path, $m)) {
                    return 'files/videos/' . $m[1];
                }
                return $path;
            }
            if (strpos($path, '/') === 0) {
                return ltrim($path, '/');
            }
            $base = ltrim($path, '/');
            // Browser-direct video uploads land in files/videos/ (bare UUID filenames).
            if (preg_match('~\\.(mp4|webm|ogg|mov|m4v)$~i', $base)) {
                return 'files/videos/' . $base;
            }
            return 'files/upload/' . $base;
        };
        $youtubeId = function ($url) {
            $url = trim((string) $url);
            if (preg_match('~(?:youtube\\.com/watch\\?v=|youtu\\.be/|youtube\\.com/embed/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
                return $m[1];
            }
            return '';
        };
        $vimeoId = function ($url) {
            if (preg_match('~vimeo\\.com/(?:video/)?(\\d+)~', trim((string) $url), $m)) {
                return $m[1];
            }
            return '';
        };
        $isFileVideo = function ($url) {
            $p = parse_url((string) $url, PHP_URL_PATH);
            $p = is_string($p) ? $p : (string) $url;
            return (bool) preg_match('~\\.(mp4|webm|ogg|mov|m4v)(\\?|#|$)~i', $p);
        };
        $renderVideo = function ($raw, $title = '') use ($esc, $mediaUrl, $youtubeId, $vimeoId, $isFileVideo) {
            $raw = trim((string) $raw);
            if ($raw === '') {
                return '';
            }
            $yt = $youtubeId($raw);
            if ($yt !== '') {
                return '<div class="wpx-dyn-video"><iframe src="https://www.youtube.com/embed/'
                    . $esc($yt) . '" title="' . $esc($title) . '" frameborder="0" allowfullscreen'
                    . ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"'
                    . ' loading="lazy"></iframe></div>';
            }
            $vim = $vimeoId($raw);
            if ($vim !== '') {
                return '<div class="wpx-dyn-video"><iframe src="https://player.vimeo.com/video/'
                    . $esc($vim) . '" title="' . $esc($title) . '" frameborder="0" allowfullscreen'
                    . ' allow="autoplay; encrypted-media" loading="lazy"></iframe></div>';
            }
            $src = $mediaUrl($raw);
            if ($isFileVideo($src) || $isFileVideo($raw) || !$youtubeId($raw)) {
                // Treat unknown non-URL paths as uploaded files
                if (!$isFileVideo($src) && !preg_match('~^https?://~i', $src)) {
                    $src = $mediaUrl($raw);
                }
                $typeAttr = '';
                if (preg_match('~\\.webm(\\?|#|$)~i', $src)) {
                    $typeAttr = ' type="video/webm"';
                } elseif (preg_match('~\\.mov(\\?|#|$)~i', $src)) {
                    $typeAttr = ' type="video/quicktime"';
                } elseif (preg_match('~\\.mp4(\\?|#|$)~i', $src)) {
                    $typeAttr = ' type="video/mp4"';
                }
                return '<div class="wpx-dyn-video"><video controls playsinline preload="metadata">'
                    . '<source src="' . $esc($src) . '"' . $typeAttr . ' />'
                    . $esc($title !== '' ? $title : 'Video')
                    . '</video></div>';
            }
            return '';
        };

        $skipKeys = array(
            'id', $wpxDynFk, 'lang_id', 'order', 'status', 'access', 'page_id', 'body',
            'title', 'name', 'bio', 'text', 'content', 'photo', 'image', 'pic', 'role', 'position',
            'video_title', 'video_file', 'video', 'movie', 'description', 'desc', 'publication_date',
            'thumbnail', 'thumb',
        );

        echo '<style data-wpx-dyn-page="1">'
            . '.wpx-dyn-list,.wpx-dyn-item{max-width:960px;margin:0 auto;padding:8px 4px 28px;box-sizing:border-box;color:#1f2533;}'
            . '.wpx-dyn-list>h1,.wpx-dyn-item>h1{margin:0 0 18px;font-size:clamp(1.5rem,3vw,2rem);font-weight:800;line-height:1.25;color:#1f2533;}'
            . '.wpx-dyn-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px;}'
            . '.wpx-dyn-card{display:flex;flex-direction:column;gap:10px;border-radius:14px;overflow:hidden;'
            . 'background:#ffffff;border:1px solid rgba(20,30,60,.12);box-shadow:0 10px 28px rgba(20,30,60,.08);padding-bottom:14px;color:#1f2533;}'
            . '.wpx-dyn-card img,.wpx-dyn-card .wpx-dyn-video{display:block;width:100%;aspect-ratio:16/9;object-fit:cover;background:#0f172a;}'
            . '.wpx-dyn-card .wpx-dyn-video video,.wpx-dyn-card .wpx-dyn-video iframe{width:100%;height:100%;border:0;display:block;}'
            . '.wpx-dyn-card h2{margin:0;padding:0 14px;font-size:1.05rem;font-weight:750;line-height:1.35;color:#1f2533;}'
            . '.wpx-dyn-card h2 a{color:#1f2533;text-decoration:none;}'
            . '.wpx-dyn-card h2 a:hover{text-decoration:underline;color:#1d4ed8;}'
            . '.wpx-dyn-card__meta{margin:0;padding:0 14px;font-size:.88rem;font-weight:650;color:#4b5563;}'
            . '.wpx-dyn-card p{margin:0;padding:0 14px;font-size:.92rem;line-height:1.55;color:#4b5563;}'
            . '.wpx-dyn-item--detail .wpx-dyn-item__image{display:block;width:min(100%,420px);max-height:360px;object-fit:cover;border-radius:14px;margin:0 0 18px;background:#eef2f7;}'
            . '.wpx-dyn-video{position:relative;width:100%;max-width:960px;margin:0 0 20px;border-radius:14px;overflow:hidden;background:#0f172a;aspect-ratio:16/9;}'
            . '.wpx-dyn-video video,.wpx-dyn-video iframe{position:absolute;inset:0;width:100%;height:100%;border:0;}'
            . '.wpx-dyn-item__body{font-size:1.02rem;line-height:1.7;margin:0 0 20px;color:#1f2533;}'
            . '.wpx-dyn-item__fields{display:grid;gap:10px;margin:0;}'
            . '.wpx-dyn-item__fields>div{display:grid;gap:2px;padding:10px 12px;border-radius:10px;background:rgba(20,30,60,.04);}'
            . '.wpx-dyn-item__fields dt{font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;}'
            . '.wpx-dyn-item__fields dd{margin:0;font-size:.98rem;color:#1f2533;}'
            . '.wpx-dyn-back{display:inline-block;margin:0 0 14px;font-size:.92rem;color:#1d4ed8;}'
            . '.wpx-dyn-empty{color:#6b7280;padding:18px 4px;}'
            . '.wpx-dyn-missing-video{padding:18px;border-radius:12px;background:#fff7ed;color:#9a3412;margin:0 0 18px;}'
            . '@media (max-width:640px){.wpx-dyn-grid{grid-template-columns:1fr;}}'
            . '</style>';

        if ($wpxDynId > 0) {
            $list = $assocOnly(db_fetch_array(db_query(
                'SELECT * FROM `' . $wpxDynListTable . '` WHERE `id`=' . (int) $wpxDynId . ' LIMIT 1'
            )));
            if (!$list) {
                echo '<section class="wpx-dyn-empty"><p>' . $esc($isEn ? 'Not found.' : 'הפריט לא נמצא.') . '</p></section>';
                return;
            }
            $lang = $assocOnly(db_fetch_array(db_query(
                'SELECT * FROM `' . $wpxDynLangTable . '` WHERE `' . $wpxDynFk . '`=' . (int) $wpxDynId
                . ' AND `lang_id`=' . (int) $wpxDynLangId . ' LIMIT 1'
            )));
            if (!$lang) {
                $lang = $assocOnly(db_fetch_array(db_query(
                    'SELECT * FROM `' . $wpxDynLangTable . '` WHERE `' . $wpxDynFk . '`=' . (int) $wpxDynId
                    . ' ORDER BY `id` ASC LIMIT 1'
                )));
            }
            $item = $mergeItem($list, $lang);
            $title = $pickTitle($item);
            $image = $pickImage($item);
            $body = $pickBody($item);
            $role = $pickRole($item);
            $video = $pickVideo($item);
            echo '<article class="wpx-dyn-item wpx-dyn-item--detail" data-wpx-module="' . $esc($wpxDynSlug)
                . '" data-wpx-id="' . (int) $wpxDynId . '">';
            echo '<a class="wpx-dyn-back" href=",' . $esc($wpxDynSlug) . '">&larr; '
                . $esc($isEn ? 'Back to list' : 'חזרה לרשימה') . '</a>';
            echo '<h1>' . $esc($title) . '</h1>';
            if ($role !== '') {
                echo '<p class="wpx-dyn-card__meta">' . $esc($role) . '</p>';
            }
            if ($video !== '') {
                echo $renderVideo($video, $title);
            } else {
                // Helpful when CMS item exists but media column empty
                $hasVideoCol = isset($colTypes['video_file']) || isset($colTypes['video']);
                if ($hasVideoCol) {
                    echo '<div class="wpx-dyn-missing-video">'
                        . $esc($isEn
                            ? 'No video file is saved on this item yet. Open it in the CMS, choose the video again, wait for upload to finish, then save.'
                            : 'עדיין לא נשמר קובץ וידאו לפריט זה. ב־CMS בחרו שוב את הסרטון, המתינו לסיום ההעלאה ולחצו שמור.')
                        . '</div>';
                }
            }
            if ($image !== '' && $video === '') {
                echo '<img class="wpx-dyn-item__image" src="' . $esc($mediaUrl($image)) . '" alt="' . $esc($title) . '" loading="lazy">';
            }
            if ($body !== '') {
                echo '<div class="wpx-dyn-item__body">' . $body . '</div>';
            }
            echo '<dl class="wpx-dyn-item__fields">';
            foreach ($item as $k => $v) {
                if (in_array($k, $skipKeys, true) || $v === '' || $v === null) {
                    continue;
                }
                if (is_array($v)) {
                    continue;
                }
                if (is_string($v) && (strpos($v, '<') !== false || strlen($v) > 240)) {
                    continue;
                }
                // Skip seed/system leftovers and numeric garbage
                if (preg_match('/^(page_id|status|order|access|lang_id)$/i', $k)) {
                    continue;
                }
                $label = isset($colTitles[$k]) ? $colTitles[$k] : $k;
                // Skip unknown builder seed columns with tiny numeric values (legacy schema)
                if (!isset($colTypes[$k]) && preg_match('/^(title|body)$/i', $k)) {
                    continue;
                }
                if (!isset($colTypes[$k]) && is_numeric($v) && (string) (int) $v === (string) $v) {
                    continue;
                }
                echo '<div><dt>' . $esc($label) . '</dt><dd>' . $esc($v) . '</dd></div>';
            }
            echo '</dl></article>';
            return;
        }

        $rows = array();
        $q = db_query('SELECT * FROM `' . $wpxDynListTable . '` ORDER BY `id` DESC');
        while ($q && ($lr = db_fetch_array($q))) {
            $id = (int) ($assocOnly($lr)['id'] ?? 0);
            $lang = $assocOnly(db_fetch_array(db_query(
                'SELECT * FROM `' . $wpxDynLangTable . '` WHERE `' . $wpxDynFk . '`=' . $id
                . ' AND `lang_id`=' . (int) $wpxDynLangId . ' LIMIT 1'
            )));
            if (!$lang) {
                $lang = $assocOnly(db_fetch_array(db_query(
                    'SELECT * FROM `' . $wpxDynLangTable . '` WHERE `' . $wpxDynFk . '`=' . $id
                    . ' ORDER BY `id` ASC LIMIT 1'
                )));
            }
            $rows[] = $mergeItem($lr, $lang);
        }

        echo '<section class="wpx-dyn-list" data-wpx-module="' . $esc($wpxDynSlug) . '">';
        // Page template already shows the in-page title for AGA; avoid double h1 when empty grid label needed only.
        echo '<div class="wpx-dyn-grid">';
        foreach ($rows as $item) {
            $id = (int) ($item['id'] ?? 0);
            $title = $pickTitle($item);
            $image = $pickImage($item);
            $body = $pickBody($item);
            $role = $pickRole($item);
            $video = $pickVideo($item);
            $href = ',' . $wpxDynSlug . ',' . $id;
            echo '<article class="wpx-dyn-card">';
            if ($video !== '') {
                echo '<a href="' . $esc($href) . '" style="display:block;text-decoration:none;">' . $renderVideo($video, $title) . '</a>';
            } elseif ($image !== '') {
                echo '<a href="' . $esc($href) . '"><img src="' . $esc($mediaUrl($image)) . '" alt="' . $esc($title) . '" loading="lazy"></a>';
            }
            echo '<h2><a href="' . $esc($href) . '">' . $esc($title) . '</a></h2>';
            if ($role !== '') {
                echo '<p class="wpx-dyn-card__meta">' . $esc($role) . '</p>';
            }
            if ($body !== '') {
                $plain = trim(strip_tags($body));
                if (function_exists('mb_substr')) {
                    $plain = mb_substr($plain, 0, 160, 'UTF-8');
                } else {
                    $plain = substr($plain, 0, 160);
                }
                echo '<p>' . $esc($plain) . '</p>';
            }
            echo '</article>';
        }
        if (empty($rows)) {
            echo '<p class="wpx-dyn-empty">' . $esc($isEn ? 'No items yet.' : 'אין פריטים עדיין.') . '</p>';
        }
        echo '</div></section>';
    }
}
