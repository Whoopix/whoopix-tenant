<?php
/**
 * Whoopix editor-safe dynamic template module tags.
 *
 * Stored syntax:
 *   <wpx-module data-name="menu" data-scope="header">-menu-</wpx-module>
 *   <wpx-module data-name="news" data-limit="3">-news-</wpx-module>
 *
 * This file is shared verbatim with tenants. It only invokes registered
 * renderers; tag names and options are strictly whitelisted.
 */

if (!defined('WPX_MODULE_TAGS_VERSION')) {
    define('WPX_MODULE_TAGS_VERSION', '20260809-dyn-v1');
}

if (!function_exists('wpx_module_tags_registry')) {
    function wpx_module_tags_registry()
    {
        static $registry;
        if ($registry === null) {
            $registry = array(
                'menu' => array(
                    'renderer' => 'wpx_module_tags_render_menu',
                    'options' => array(
                        'scope' => array('type' => 'enum', 'values' => array('header', 'footer'), 'default' => 'header'),
                        'class' => array('type' => 'class', 'default' => ''),
                    ),
                ),
                'news' => array(
                    'renderer' => 'wpx_module_tags_render_news',
                    'options' => array(
                        'limit' => array('type' => 'int', 'min' => 1, 'max' => 12, 'default' => 3),
                        'class' => array('type' => 'class', 'default' => ''),
                    ),
                ),
                // Reserved names document the public contract. They remain inert
                // until a renderer is explicitly registered.
                'services' => array('options' => array()),
                'team' => array('options' => array()),
                'testimonials' => array('options' => array()),
                'contact' => array('options' => array()),
                'products' => array('options' => array()),
                'blog' => array('options' => array()),
                'forms' => array('options' => array()),
            );
        }
        return $registry;
    }
}

if (!function_exists('wpx_module_tags_register')) {
    function wpx_module_tags_register($name, $renderer, array $options = array())
    {
        $name = strtolower(trim((string) $name));
        if (!preg_match('/^[a-z][a-z0-9_-]{0,31}$/', $name) || !is_callable($renderer)) {
            return false;
        }
        $registry =& $GLOBALS['wpx_module_tags_extensions'];
        if (!is_array($registry)) {
            $registry = array();
        }
        $registry[$name] = array('renderer' => $renderer, 'options' => $options);
        return true;
    }
}

if (!function_exists('wpx_module_tags_full_registry')) {
    function wpx_module_tags_full_registry()
    {
        $registry = wpx_module_tags_registry();
        foreach ((array) ($GLOBALS['wpx_module_tags_extensions'] ?? array()) as $name => $spec) {
            $registry[$name] = $spec;
        }
        return $registry;
    }
}

if (!function_exists('wpx_module_tags_options')) {
    function wpx_module_tags_options(array $attributes, array $schema)
    {
        $out = array();
        foreach ($schema as $key => $rule) {
            $raw = array_key_exists($key, $attributes) ? trim((string) $attributes[$key]) : ($rule['default'] ?? '');
            $type = (string) ($rule['type'] ?? 'text');
            if ($type === 'enum') {
                $out[$key] = in_array($raw, (array) ($rule['values'] ?? array()), true)
                    ? $raw : (string) ($rule['default'] ?? '');
            } elseif ($type === 'int') {
                $value = (int) $raw;
                $out[$key] = max((int) ($rule['min'] ?? 0), min((int) ($rule['max'] ?? 100), $value));
            } elseif ($type === 'class') {
                $tokens = preg_split('/\s+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
                $safe = array();
                foreach ((array) $tokens as $token) {
                    if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_-]{0,63}$/', $token)) {
                        $safe[] = $token;
                    }
                }
                $out[$key] = implode(' ', array_slice(array_unique($safe), 0, 8));
            } else {
                $out[$key] = preg_replace('/[^a-zA-Z0-9_.:-]/', '', $raw);
            }
        }
        return $out;
    }
}

if (!function_exists('wpx_module_tags_parse_attributes')) {
    function wpx_module_tags_parse_attributes($source)
    {
        $attrs = array();
        if (preg_match_all('/\bdata-([a-z0-9_-]+)\s*=\s*(["\'])(.*?)\2/is', (string) $source, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attrs[strtolower($match[1])] = html_entity_decode($match[3], ENT_QUOTES, 'UTF-8');
            }
        }
        return $attrs;
    }
}

if (!function_exists('wpx_module_tags_safe_table')) {
    /** Allow known core tables plus builder-created mod_{slug} / mod_{slug}_list. */
    function wpx_module_tags_safe_table($table)
    {
        $table = (string) $table;
        if (in_array($table, array(
            'mod_menu', 'mod_news', 'mod_news_list', 'mod_seo',
            'mod_modules', 'mod_modules_dynamic',
        ), true)) {
            return true;
        }
        return (bool) preg_match('/^mod_[a-z][a-z0-9_]{0,47}(_list)?$/', $table);
    }
}

if (!function_exists('wpx_module_tags_rows')) {
    function wpx_module_tags_rows($table, array $context = array())
    {
        $api = $context['api'] ?? null;
        if ($api && method_exists($api, 'fetchAll')) {
            return (array) $api->fetchAll($table, array('per_page' => 500));
        }
        $rows = array();
        if (!wpx_module_tags_safe_table($table)) {
            return $rows;
        }
        if (function_exists('db_query') && function_exists('db_fetch_array')) {
            $query = @db_query('SELECT * FROM `' . $table . '`');
            while ($query && ($row = db_fetch_array($query))) {
                $rows[] = $row;
            }
        } elseif (function_exists('mysql_query') && function_exists('mysql_fetch_assoc')) {
            $query = @mysql_query('SELECT * FROM `' . $table . '`');
            while ($query && ($row = mysql_fetch_assoc($query))) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}

if (!function_exists('wpx_module_tags_language_id')) {
    function wpx_module_tags_language_id(array $context = array())
    {
        if (!empty($context['lang_id'])) {
            return (int) $context['lang_id'];
        }
        return defined('LANG') ? (int) LANG : 1;
    }
}

if (!function_exists('wpx_module_tags_href')) {
    function wpx_module_tags_href($href)
    {
        $href = trim((string) $href);
        if ($href === '/' || strcasecmp($href, 'index.php') === 0) {
            $href = './';
        }
        if (preg_match('/^\/?page_(\d+)\.php$/i', $href, $match)) {
            $href = ((int) $match[1] <= 1) ? './' : ',pages,' . (int) $match[1];
        }
        if (function_exists('tenant_public_href') && !preg_match('~^(?:https?:|mailto:|tel:|#)~i', $href)) {
            $href = tenant_public_href($href);
        }
        return $href;
    }
}

if (!function_exists('wpx_module_tags_render_menu')) {
    function wpx_module_tags_menu_style()
    {
        return '<style data-wpx-module-style="menu">'
            . 'header[data-wpx-header-variant] nav[class*="__panel"]{flex-wrap:nowrap!important;}'
            . 'header[data-wpx-header-variant] .wpx-nav__dynamic[data-wpx-module="menu"]{display:flex!important;align-items:center!important;flex:1 1 auto!important;width:auto!important;min-width:0!important;flex-wrap:nowrap!important;}'
            . 'header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-scope="header"]{display:flex!important;align-items:center!important;justify-content:flex-end;flex:1 1 auto;width:100%;min-width:0;gap:clamp(1rem,2.5vw,2.25rem);flex-wrap:nowrap!important;}'
            . 'header[data-wpx-header-variant="centered"] .wpx-module--menu[data-wpx-module-scope="header"]{justify-content:center;}'
            . 'header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-scope="header"]>.wpx-menu__link,header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-scope="header"]>.wpx-nav__drop{flex:0 0 auto;min-width:0;}'
            . 'header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-density="compact"]{gap:.35rem;}'
            . 'header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-density="compact"]>.wpx-menu__link,header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-density="compact"]>.wpx-nav__drop{flex:1 1 0;min-width:2.75rem;max-width:9rem;}'
            . 'header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-density="compact"]>.wpx-menu__link,header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-density="compact"]>.wpx-nav__drop>summary{overflow:hidden;text-overflow:ellipsis;}'
            . 'header[data-wpx-header-variant] .wpx-nav__drop>summary{list-style:none;}'
            . 'header[data-wpx-header-variant] .wpx-nav__drop>summary::marker{content:"";font-size:0;}'
            . '.tpl-module-preview[data-wpx-module-preview="menu"]{display:contents!important;}'
            . '.wpx-future-footer .wpx-module--menu[data-wpx-module-scope="footer"]{display:grid;width:100%;gap:.25rem;}'
            . '@media (min-width:900px) and (max-width:1180px){'
            . 'header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-scope="header"]{gap:.7rem;}'
            . 'header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-scope="header"]>.wpx-menu__link,header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-scope="header"]>.wpx-nav__drop>summary{font-size:.8rem;}'
            . 'header[data-wpx-header-variant] nav[class*="__panel"]{gap:.65rem!important;}'
            . 'header[data-wpx-header-variant] nav[class*="__panel"]>.wpx-u-btn{padding-inline:.7rem!important;white-space:nowrap;}'
            . '}'
            . '@media (max-width:899px){'
            . 'header[data-wpx-header-variant] .wpx-nav__dynamic[data-wpx-module="menu"],header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-scope="header"]{display:grid!important;grid-template-columns:minmax(0,1fr)!important;justify-items:stretch!important;justify-content:stretch!important;width:100%!important;max-width:100%!important;gap:.2rem!important;}'
            . 'header[data-wpx-header-variant] .wpx-module--menu[data-wpx-module-scope="header"]>*{box-sizing:border-box;width:100%!important;max-width:100%!important;min-width:0!important;}'
            . '}'
            . '</style>';
    }

    function wpx_module_tags_render_menu(array $options, array $context = array())
    {
        $langId = wpx_module_tags_language_id($context);
        $footer = ($options['scope'] ?? 'header') === 'footer';
        $byParent = array();
        foreach (wpx_module_tags_rows('mod_menu', $context) as $row) {
            $rowLang = (int) ($row['lang_id'] ?? 0);
            if ($rowLang !== 0 && $rowLang !== $langId) {
                continue;
            }
            if (isset($row['show_m']) && (string) $row['show_m'] !== '1') {
                continue;
            }
            $parent = (int) ($row['parent'] ?? 0);
            $isFooter = array_key_exists('footer_menu', $row)
                ? (string) $row['footer_menu'] === '1' : $parent === -1;
            if ($footer) {
                if (!$isFooter && $parent !== -1) { continue; }
                if ($parent === -1) { $parent = 0; }
            } elseif ($isFooter || $parent === -1) {
                continue;
            }
            $row['_parent'] = $parent;
            $byParent[$parent][] = $row;
        }
        foreach ($byParent as &$rows) {
            usort($rows, function ($a, $b) {
                $ao = (int) ($a['order'] ?? 0);
                $bo = (int) ($b['order'] ?? 0);
                return $ao === $bo ? ((int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0)) : ($ao <=> $bo);
            });
        }
        unset($rows);
        $itemCount = 0;
        $anchor = function ($row) {
            $title = trim((string) ($row['title'] ?? ''));
            $href = wpx_module_tags_href($row['link'] ?? '');
            if ($title === '' || $href === '') { return ''; }
            $external = preg_match('~^https?://~i', $href);
            $placeholder = $href === '#';
            $requestPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
            $hrefPath = (string) parse_url($href, PHP_URL_PATH);
            $current = !$external && !$placeholder && $hrefPath !== ''
                && trim($requestPath, '/') === trim($hrefPath, '/');
            // CTA/action styling belongs to the template's explicit action
            // control. Guessing from a "Contact" label creates duplicate pills.
            return '<a class="wpx-menu__link'
                . ($current ? ' wpx-menu__link--current' : '')
                . ($placeholder ? ' wpx-menu__link--placeholder' : '') . '" href="'
                . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"'
                . ($current ? ' aria-current="page"' : '')
                . ($placeholder ? ' aria-disabled="true"' : '')
                . ($external ? ' target="_blank" rel="noopener noreferrer"' : '')
                . '>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</a>';
        };
        $out = '';
        $topRows = (array) ($byParent[0] ?? array());
        // Collapse duplicate titles/links that sometimes appear after onboarding seeds.
        $seenMenu = array();
        $topRows = array_values(array_filter($topRows, function ($row) use (&$seenMenu) {
            $key = strtolower(trim((string) ($row['title'] ?? '')) . '|' . trim((string) ($row['link'] ?? '')));
            if ($key === '|' || isset($seenMenu[$key])) {
                return false;
            }
            $seenMenu[$key] = true;
            return true;
        }));
        $overflowRows = count($topRows) > 6 ? array_slice($topRows, 5) : array();
        if ($overflowRows) {
            $topRows = array_slice($topRows, 0, 5);
        }
        foreach ($topRows as $row) {
            $children = (array) ($byParent[(int) ($row['id'] ?? 0)] ?? array());
            $itemCount++;
            if ($children) {
                $out .= '<details class="wpx-nav__drop"><summary>'
                    . htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES, 'UTF-8')
                    . '</summary><div class="wpx-nav__drop-menu">' . $anchor($row);
                foreach ($children as $child) { $out .= $anchor($child); }
                $out .= '</div></details>';
            } else {
                $out .= $anchor($row);
            }
        }
        if ($overflowRows) {
            $allTitles = implode(' ', array_map(function ($row) { return (string) ($row['title'] ?? ''); }, $topRows));
            $moreLabel = preg_match('/[א-ת]/u', $allTitles) ? 'עוד' : 'More';
            $out .= '<details class="wpx-nav__drop wpx-nav__overflow"><summary>'
                . htmlspecialchars($moreLabel, ENT_QUOTES, 'UTF-8')
                . '</summary><div class="wpx-nav__drop-menu">';
            foreach ($overflowRows as $row) {
                $out .= $anchor($row);
                foreach ((array) ($byParent[(int) ($row['id'] ?? 0)] ?? array()) as $child) {
                    $out .= $anchor($child);
                }
            }
            $out .= '</div></details>';
            $itemCount++;
        }
        // New / empty tenants: never leave the literal "-menu-" alias visible.
        // Anchor to on-page sections so composed templates still feel navigable.
        if ($itemCount === 0 || $out === '') {
            $langCode = strtolower((string) ($context['lang_code'] ?? ''));
            $he = ($langCode === 'he') || ($langId === 2) || !empty($context['rtl']);
            if ($footer) {
                $fallback = $he
                    ? array(array('בית', '/'), array('שירותים', '#services'), array('צור קשר', '#contact'))
                    : array(array('Home', '/'), array('Services', '#services'), array('Contact', '#contact'));
            } else {
                $fallback = $he
                    ? array(
                        array('בית', '/'),
                        array('שירותים', '#services'),
                        array('אודות', '#about'),
                        array('צור קשר', '#contact'),
                    )
                    : array(
                        array('Home', '/'),
                        array('Services', '#services'),
                        array('About', '#about'),
                        array('Contact', '#contact'),
                    );
            }
            $out = '';
            $itemCount = 0;
            foreach ($fallback as $pair) {
                $itemCount++;
                $out .= '<a class="wpx-menu__link wpx-menu__link--fallback" href="'
                    . htmlspecialchars($pair[1], ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($pair[0], ENT_QUOTES, 'UTF-8') . '</a>';
            }
        }
        $class = trim('wpx-module wpx-module--menu ' . ($options['class'] ?? ''));
        static $styleEmitted = false;
        $style = $styleEmitted ? '' : wpx_module_tags_menu_style();
        $styleEmitted = true;
        return $style . '<div class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8')
            . '" data-wpx-module-rendered="menu" data-wpx-module-scope="'
            . ($footer ? 'footer' : 'header') . '" data-wpx-module-count="' . $itemCount
            . '" data-wpx-module-density="' . ($itemCount >= 6 ? 'compact' : 'normal') . '">' . $out . '</div>';
    }
}

if (!function_exists('wpx_module_tags_render_news')) {
    function wpx_module_tags_render_news(array $options, array $context = array())
    {
        $langId = wpx_module_tags_language_id($context);
        $list = array();
        foreach (wpx_module_tags_rows('mod_news_list', $context) as $row) {
            $list[(int) ($row['id'] ?? 0)] = $row;
        }
        $items = array();
        foreach (wpx_module_tags_rows('mod_news', $context) as $row) {
            if ((int) ($row['lang_id'] ?? 0) !== $langId) { continue; }
            $parent = $list[(int) ($row['page_id'] ?? 0)] ?? array();
            if (isset($parent['access']) && (string) $parent['access'] !== '1') { continue; }
            if (isset($parent['visible']) && (string) $parent['visible'] !== '1') { continue; }
            if (isset($parent['published']) && (string) $parent['published'] !== '1') { continue; }
            $items[] = array_merge($parent, $row);
        }
        usort($items, function ($a, $b) {
            $ad = (string) ($a['date'] ?? $a['created_at'] ?? '');
            $bd = (string) ($b['date'] ?? $b['created_at'] ?? '');
            return $ad === $bd ? ((int) ($b['page_id'] ?? 0) <=> (int) ($a['page_id'] ?? 0)) : strcmp($bd, $ad);
        });
        $items = array_slice($items, 0, (int) ($options['limit'] ?? 3));
        $out = '';
        foreach ($items as $item) {
            $id = (int) ($item['page_id'] ?? $item['id'] ?? 0);
            $title = trim((string) ($item['title'] ?? ''));
            if ($id <= 0 || $title === '') { continue; }
            $url = ',news,' . $id;
            $seoRows = wpx_module_tags_rows('mod_seo', $context);
            foreach ($seoRows as $seo) {
                if ((string) ($seo['mod'] ?? '') === 'news' && (int) ($seo['id'] ?? 0) === $id
                    && (int) ($seo['lang_id'] ?? 0) === $langId && trim((string) ($seo['plink'] ?? '')) !== '') {
                    $url = '/' . ltrim((string) $seo['plink'], '/');
                    break;
                }
            }
            $url = wpx_module_tags_href($url);
            $image = trim((string) ($item['pic'] ?? ''));
            $date = trim((string) ($item['date'] ?? $item['created_at'] ?? ''));
            $out .= '<article class="wpx-news__item">';
            if ($image !== '') {
                $out .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="wpx-news__image">'
                    . '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="" loading="lazy"></a>';
            }
            $out .= '<div class="wpx-news__body">';
            if ($date !== '') {
                $out .= '<time datetime="' . htmlspecialchars(substr($date, 0, 10), ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '</time>';
            }
            $out .= '<h3><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</a></h3></div></article>';
        }
        $class = trim('wpx-module wpx-module--news wpx-news ' . ($options['class'] ?? ''));
        return '<div class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8')
            . '" data-wpx-module-rendered="news">' . $out . '</div>';
    }
}

if (!function_exists('wpx_module_tags_render_dynamic')) {
    /**
     * Generic listing for builder-created content types using mod_modules_dynamic
     * field definitions when available (title/image/body heuristics as fallback).
     */
    function wpx_module_tags_render_dynamic($slug, array $options = array(), array $context = array())
    {
        $slug = strtolower(trim((string) $slug));
        if (!preg_match('/^[a-z][a-z0-9_]{0,47}$/', $slug)) {
            return '';
        }
        $mods = wpx_module_tags_rows('mod_modules', $context);
        $modRow = null;
        foreach ($mods as $row) {
            if ((string) ($row['module'] ?? '') === $slug && (string) ($row['status'] ?? '1') !== '0') {
                $modRow = $row;
                break;
            }
        }
        if (!$modRow) {
            return '';
        }
        $langId = wpx_module_tags_language_id($context);
        $listTable = 'mod_' . $slug . '_list';
        $langTable = 'mod_' . $slug;
        $fk = $slug . '_id';
        $limit = max(1, min(48, (int) ($options['limit'] ?? 12)));
        $defs = array();
        $modId = (int) ($modRow['id'] ?? 0);
        if ($modId > 0) {
            foreach (wpx_module_tags_rows('mod_modules_dynamic', $context) as $def) {
                if ((int) ($def['mod_id'] ?? 0) !== $modId) { continue; }
                // Prefer front language; admin lang ids differ — keep first seen.
                $col = (string) ($def['col_name'] ?? '');
                if ($col === '' || isset($defs[$col])) { continue; }
                $defs[$col] = (int) ($def['col_type'] ?? 1);
            }
        }
        $listById = array();
        foreach (wpx_module_tags_rows($listTable, $context) as $row) {
            $listById[(int) ($row['id'] ?? 0)] = $row;
        }
        $langByParent = array();
        foreach (wpx_module_tags_rows($langTable, $context) as $row) {
            $parent = (int) ($row[$fk] ?? $row['page_id'] ?? 0);
            $rowLang = (int) ($row['lang_id'] ?? 0);
            if ($parent <= 0) { continue; }
            if ($rowLang === $langId || !isset($langByParent[$parent])) {
                $langByParent[$parent] = $row;
            }
        }
        $items = array();
        foreach ($listById as $id => $listRow) {
            if ($id <= 0) { continue; }
            $items[] = array_merge($listRow, (array) ($langByParent[$id] ?? array()), array('id' => $id));
        }
        usort($items, function ($a, $b) {
            return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
        });
        $items = array_slice($items, 0, $limit);
        $pick = function (array $row, array $keys) {
            foreach ($keys as $k) {
                if (!empty($row[$k])) { return (string) $row[$k]; }
            }
            return '';
        };
        $out = '';
        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            $title = $pick($item, array('title', 'name', 'headline', 'label'));
            if ($title === '') {
                foreach ($item as $k => $v) {
                    if (is_string($v) && $v !== '' && !in_array($k, array('id', 'lang_id', $fk), true)
                        && strlen($v) < 180 && strpos($v, '<') === false
                        && (empty($defs[$k]) || (int) $defs[$k] === 1)) {
                        $title = $v;
                        break;
                    }
                }
            }
            if ($title === '') { $title = 'Item #' . $id; }
            $image = $pick($item, array('photo', 'image', 'pic', 'picture', 'img', 'avatar'));
            if ($image === '' && $defs) {
                foreach ($defs as $col => $type) {
                    if ((int) $type === 3 && !empty($item[$col])) { $image = (string) $item[$col]; break; }
                }
            }
            $body = $pick($item, array('bio', 'text', 'content', 'body', 'description', 'desc'));
            $href = wpx_module_tags_href(',' . $slug . ',' . $id);
            $out .= '<article class="wpx-dyn-card" data-wpx-dyn-id="' . $id . '">';
            if ($image !== '') {
                $out .= '<a class="wpx-dyn-card__image" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
                    . '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="'
                    . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" loading="lazy"></a>';
            }
            $out .= '<h3><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</a></h3>';
            if ($body !== '') {
                $plain = trim(strip_tags($body));
                if (function_exists('mb_substr')) { $plain = mb_substr($plain, 0, 140, 'UTF-8'); }
                else { $plain = substr($plain, 0, 140); }
                $out .= '<p>' . htmlspecialchars($plain, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            $out .= '</article>';
        }
        $class = trim('wpx-module wpx-module--dynamic wpx-dyn-list ' . ($options['class'] ?? ''));
        $heading = trim((string) ($modRow['name_he'] ?? $modRow['name_en'] ?? $slug));
        if ($langId === 1 && trim((string) ($modRow['name_en'] ?? '')) !== '') {
            // Prefer English label when front lang id 1 is typically "en" in admin map;
            // live LANGCODE is more reliable when present.
            if (defined('LANGCODE') && strtolower((string) LANGCODE) === 'en') {
                $heading = trim((string) $modRow['name_en']);
            }
        }
        if (defined('LANGCODE') && strtolower((string) LANGCODE) === 'en'
            && trim((string) ($modRow['name_en'] ?? '')) !== '') {
            $heading = trim((string) $modRow['name_en']);
        }
        static $dynStyleEmitted = false;
        $style = '';
        if (!$dynStyleEmitted) {
            $dynStyleEmitted = true;
            $style = '<style data-wpx-module-style="dynamic">'
                . '.wpx-module--dynamic,.wpx-dyn-list{max-width:1120px;margin:0 auto;padding:28px 16px 40px;box-sizing:border-box;color:#1f2533;}'
                . '.wpx-dyn-list__title{margin:0 0 18px;font-size:clamp(1.35rem,2.4vw,1.85rem);font-weight:800;line-height:1.25;color:#1f2533;}'
                . '.wpx-dyn-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px;}'
                . '.wpx-dyn-card{display:flex;flex-direction:column;gap:10px;padding:0 0 14px;border-radius:14px;overflow:hidden;'
                . 'background:#ffffff;border:1px solid rgba(20,30,60,.12);box-shadow:0 10px 28px rgba(20,30,60,.08);color:#1f2533;}'
                . '.wpx-dyn-card__image,.wpx-dyn-card a img{display:block;width:100%;aspect-ratio:4/3;object-fit:cover;background:#eef2f7;}'
                . '.wpx-dyn-card h3,.wpx-dyn-card h2{margin:0;padding:0 14px;font-size:1.05rem;font-weight:750;line-height:1.35;color:#1f2533;}'
                . '.wpx-dyn-card h3 a,.wpx-dyn-card h2 a{color:#1f2533;text-decoration:none;}'
                . '.wpx-dyn-card h3 a:hover,.wpx-dyn-card h2 a:hover{text-decoration:underline;color:#1d4ed8;}'
                . '.wpx-dyn-card p{margin:0;padding:0 14px;font-size:.92rem;line-height:1.55;color:#4b5563;}'
                . '.wpx-dyn-empty{color:#6b7280;padding:12px 4px;}'
                . '@media (max-width:640px){.wpx-dyn-grid{grid-template-columns:1fr;gap:14px;}.wpx-module--dynamic,.wpx-dyn-list{padding:20px 12px 32px;}}'
                . '</style>';
        }
        if ($out === '') {
            $out = '<p class="wpx-dyn-empty">' . htmlspecialchars(
                (defined('LANGCODE') && strtolower((string) LANGCODE) === 'en') ? 'No items yet.' : 'אין פריטים עדיין.',
                ENT_QUOTES, 'UTF-8'
            ) . '</p>';
        }
        return $style . '<section class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8')
            . '" data-wpx-module-rendered="dynamic" data-wpx-module="'
            . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '" data-wpx-module-count="'
            . count($items) . '"><h2 class="wpx-dyn-list__title">'
            . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h2><div class="wpx-dyn-grid">'
            . $out . '</div></section>';
    }
}

if (!function_exists('wpx_module_tags_render_one')) {
    function wpx_module_tags_render_one($name, array $attributes = array(), array $context = array())
    {
        $name = strtolower(trim((string) $name));
        $registry = wpx_module_tags_full_registry();
        $spec = $registry[$name] ?? null;
        if ($spec && !empty($spec['renderer']) && is_callable($spec['renderer'])) {
            $options = wpx_module_tags_options($attributes, (array) ($spec['options'] ?? array()));
            return call_user_func($spec['renderer'], $options, $context);
        }
        // Reserved inert names (team, services, …) and any builder slug fall through
        // to the generic dynamic renderer when the tenant has a live mod_modules row.
        if (preg_match('/^[a-z][a-z0-9_]{0,47}$/', $name)) {
            $options = wpx_module_tags_options($attributes, array(
                'limit' => array('type' => 'int', 'min' => 1, 'max' => 48, 'default' => 12),
                'class' => array('type' => 'class', 'default' => ''),
            ));
            $rendered = wpx_module_tags_render_dynamic($name, $options, $context);
            if ($rendered !== '') {
                return $rendered;
            }
        }
        if (!empty($context['admin_preview'])) {
            return '<div class="wpx-module-diagnostic" data-wpx-module-error="unknown">'
                . 'Unknown module: ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</div>';
        }
        return '<!-- wpx-module unavailable: ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ' -->';
    }
}

if (!function_exists('wpx_module_tags_expand')) {
    function wpx_module_tags_expand($html, array $context = array())
    {
        $html = (string) $html;
        $preview = !empty($context['admin_preview']);
        $index = 0;
        $html = preg_replace_callback(
            '~<wpx-module\b([^>]*)>([\s\S]*?)</wpx-module\s*>~i',
            function ($match) use ($context, $preview, &$index) {
                $attributes = wpx_module_tags_parse_attributes($match[1]);
                $name = strtolower((string) ($attributes['name'] ?? ''));
                $rendered = wpx_module_tags_render_one($name, $attributes, $context);
                if (!$preview) { return $rendered; }
                $marker = $index++;
                return '<!--wpx-module-preview-' . $marker . ' name='
                    . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '-->' . $rendered
                    . '<!--/wpx-module-preview-' . $marker . '-->';
            },
            $html
        );
        // Simple aliases are accepted only as standalone text, never interpreted
        // as PHP or as an arbitrary function/expression.
        $html = preg_replace_callback(
            '~<(script|style|textarea|pre|code)\b[\s\S]*?</\1\s*>|(?<![a-z0-9_-])-(menu|news)-(?![a-z0-9_-])~i',
            function ($match) use ($context, $preview, &$index) {
                if (!empty($match[1])) { return $match[0]; }
                $name = strtolower($match[2]);
                $rendered = wpx_module_tags_render_one($name, array(), $context);
                if (!$preview) { return $rendered; }
                $marker = $index++;
                return '<!--wpx-module-preview-' . $marker . ' name=' . $name . '-->'
                    . $rendered . '<!--/wpx-module-preview-' . $marker . '-->';
            },
            $html
        );
        return $html;
    }
}
