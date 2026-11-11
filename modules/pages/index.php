<?php

declare(strict_types=1);

/**
 * Content pages router.
 *
 * - Prefer joining mod_pages + mod_pages_list for the active LANG.
 * - If LANG misses (EN content under lang_id=2 while LANG=1), fall back to any
 *   language row for that page — never blank the page.
 * - Always honor mod_pages_list.template when present, including a deliberately
 *   selected home-style template on an inner page.
 * - Fall back only when the stored id cannot resolve to an active PHP template.
 */

const WPX_PAGES_TEMPLATE_CONTRACT_VERSION = '20260808b';

if (!function_exists('wpx_pages_content_tpl_id')) {
    function wpx_pages_content_tpl_id(): int
    {
        static $id = null;
        if ($id !== null) {
            return $id;
        }
        $row = db_fetch_array(
            db_query("SELECT `id` FROM `templates` WHERE `file` = 'page.php' ORDER BY `id` ASC LIMIT 1")
        ) ?: [];
        $id = (int) ($row['id'] ?? 0);
        if ($id <= 0) {
            $id = 1;
        }
        return $id;
    }
}

if (!function_exists('wpx_pages_is_homepage_template')) {
    function wpx_pages_is_homepage_template(int $tplId): bool
    {
        if ($tplId <= 0) {
            return true;
        }
        $row = db_fetch_array(
            db_query("SELECT `file`, `title` FROM `templates` WHERE `id` = :id LIMIT 1", ['id' => $tplId])
        ) ?: [];
        $file = strtolower(trim((string) ($row['file'] ?? '')));
        $title = trim((string) ($row['title'] ?? ''));
        if ($file === '' || $file === 'wpx-default.php' || $file === 'index.php'
            || $file === 'home.php' || $file === 'default.php') {
            return true;
        }
        if ($title === 'Homepage design' || $title === 'עיצוב האתר' || $title === 'דף נחיתה') {
            return true;
        }
        // Prefer the template assigned to page id=1 as the home shell.
        $home = db_fetch_array(
            db_query("SELECT `template` FROM `mod_pages_list` WHERE `id` = 1 LIMIT 1")
        ) ?: [];
        $homeTpl = (int) ($home['template'] ?? 0);
        return ($homeTpl > 0 && $tplId === $homeTpl);
    }
}

if (!function_exists('wpx_pages_resolve_active_template')) {
    function wpx_pages_resolve_active_template(int $tplId): int
    {
        if ($tplId <= 0) {
            return 0;
        }
        $row = db_fetch_array(db_query(
            "SELECT * FROM `templates` WHERE `id` = :id LIMIT 1",
            ['id' => $tplId]
        )) ?: [];
        if (!$row || (isset($row['archived']) && (int) $row['archived'] > 0)) {
            return 0;
        }
        $file = trim(str_replace('\\', '/', (string) ($row['file'] ?? '')));
        if ($file === '' || basename($file) !== $file || strpos($file, '..') !== false
            || !preg_match('/\.php$/i', $file)) {
            return 0;
        }
        return $tplId;
    }
}

if ($get_id) {
    $listRow = db_fetch_array(db_query(
        "SELECT `id`, `title`, `template`, `access`, `pic` FROM `mod_pages_list` WHERE `id` = :pid LIMIT 1",
        ['pid' => $get_id]
    )) ?: [];
    $listTpl = (int) ($listRow['template'] ?? 0);
    $listAccess = (string) ($listRow['access'] ?? '');

    $GET_PAGEINFO_y = db_query(
        "SELECT * FROM `mod_seo` WHERE `mod` = 'pages' AND `id` = :id AND `lang_id` = :lid ORDER BY `uniqe_id` DESC LIMIT 1",
        ['id' => $get_id, 'lid' => LANG],
    );
    $sss = db_fetch_array($GET_PAGEINFO_y) ?: [];
    if (!$sss) {
        $sss = db_fetch_array(db_query(
            "SELECT * FROM `mod_seo` WHERE `id` = :id AND `lang_id` = :lid ORDER BY `uniqe_id` DESC LIMIT 1",
            ['id' => $get_id, 'lid' => LANG]
        )) ?: [];
    }
    if (!$sss) {
        $sss = db_fetch_array(db_query(
            "SELECT * FROM `mod_seo` WHERE `id` = :id ORDER BY `lang_id` ASC LIMIT 1",
            ['id' => $get_id]
        )) ?: [];
    }

    $GET_PAGEINFO_q = db_query(
        "SELECT L.*, T.`access`, T.`template`
         FROM `mod_pages` L, `mod_pages_list` T
         WHERE T.`id` = L.`page_id`
           AND L.`page_id` = :pid
           AND L.`lang_id` = :lid",
        ['pid' => $get_id, 'lid' => LANG],
    );
    $GET_PAGEINFO_r = db_fetch_array($GET_PAGEINFO_q);

    // LANG mismatch (content under English while LANG is junk/Hebrew id=1).
    if (!$GET_PAGEINFO_r) {
        $GET_PAGEINFO_r = db_fetch_array(db_query(
            "SELECT L.*, T.`access`, T.`template`
             FROM `mod_pages` L, `mod_pages_list` T
             WHERE T.`id` = L.`page_id`
               AND L.`page_id` = :pid
             ORDER BY L.`lang_id` ASC
             LIMIT 1",
            ['pid' => $get_id]
        ));
    }

    // Orphan content without list row (shouldn't happen when list exists).
    if (!$GET_PAGEINFO_r) {
        $orphan = db_fetch_array(db_query(
            "SELECT * FROM `mod_pages` WHERE `page_id` = :pid AND `lang_id` = :lid LIMIT 1",
            ['pid' => $get_id, 'lid' => LANG],
        ));
        if (!$orphan) {
            $orphan = db_fetch_array(db_query(
                "SELECT * FROM `mod_pages` WHERE `page_id` = :pid ORDER BY `lang_id` ASC LIMIT 1",
                ['pid' => $get_id]
            ));
        }
        if ($orphan) {
            $orphan['access'] = $listAccess;
            $orphan['template'] = $listTpl > 0 ? $listTpl : wpx_pages_content_tpl_id();
            $GET_PAGEINFO_r = $orphan;
        }
    }

    if ($GET_PAGEINFO_r) {
        // List template wins — SyncBridge can leave content join with stale template.
        if ($listTpl > 0) {
            $GET_PAGEINFO_r['template'] = $listTpl;
        }
        if ($listAccess !== '') {
            $GET_PAGEINFO_r['access'] = $listAccess;
        }

        // Expose SEO row for templates.php ($P_TITLE / meta).
        $SEO = $sss;

        $seoTitle = trim((string) ($sss['ptitle'] ?? ''));
        if ($seoTitle === '') {
            $seoTitle = trim((string) ($GET_PAGEINFO_r['ptitle'] ?? ''));
        }
        $TEMP_PAGE_TITLE   = htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8');
        $TEMP_INPAGE_TITLE = htmlspecialchars((string) ($GET_PAGEINFO_r['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        if ($TEMP_INPAGE_TITLE === '' && !empty($listRow['title'])) {
            $TEMP_INPAGE_TITLE = htmlspecialchars((string) $listRow['title'], ENT_QUOTES, 'UTF-8');
        }
        if ($TEMP_PAGE_TITLE === '') {
            $TEMP_PAGE_TITLE = $TEMP_INPAGE_TITLE;
        }
        if (!empty($sss['keywords'])) {
            $GET_keywords = (string) $sss['keywords'];
        }
        if (!empty($sss['description'])) {
            $GET_description = (string) $sss['description'];
        }

        if (!do_no_access((string) ($GET_PAGEINFO_r['access'] ?? ''))) {
            if (!function_exists('PAGE_TEXT')) {
                function PAGE_TEXT(): void
                {
                    global $GET_PAGEINFO_r;
                    echo "<meta HTTP-Equiv='Refresh' Content='0; URL=,members,login' />";
                }
            }
            $TEMP_TEMPLATE = wpx_pages_content_tpl_id();
        } else {
            if (!function_exists('PAGE_TEXT')) {
                function PAGE_TEXT(): void
                {
                    global $GET_PAGEINFO_r;
                    echo $GET_PAGEINFO_r['text'] ?? '';
                }
            }
            // The list-row numeric id is the canonical assignment. Do not
            // reinterpret it by filename/title; an admin may intentionally use
            // any active template on any page.
            $tpl = wpx_pages_resolve_active_template((int) ($GET_PAGEINFO_r['template'] ?? 0));
            if ($tpl <= 0) {
                $tpl = wpx_pages_resolve_active_template(wpx_pages_content_tpl_id());
            }
            $TEMP_TEMPLATE = $tpl > 0 ? $tpl : wpx_pages_content_tpl_id();
        }
    } else {
        $TEMP_PAGE_TITLE   = 'עמוד לא קיים';
        $TEMP_INPAGE_TITLE = 'עמוד לא קיים - 404 - Page not found';
        if (!function_exists('PAGE_TEXT')) {
            function PAGE_TEXT(): void
            {
                echo "<% BLOCK_11 %>";
            }
        }
        // Even without content, respect the exact active list assignment.
        $resolvedListTpl = wpx_pages_resolve_active_template($listTpl);
        if ($resolvedListTpl > 0) {
            $TEMP_TEMPLATE = $resolvedListTpl;
        } else {
            $TEMP_TEMPLATE = wpx_pages_content_tpl_id();
        }
    }
}
