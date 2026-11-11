<?php
/**
 * Minimal public blog — listing + article from mod_blog_posts.
 */
global $get_id, $TEMP_TEMPLATE, $TEMP_PAGE_TITLE, $TEMP_INPAGE_TITLE, $SEO;

echo '<!-- WPX_BLOG_RUNTIME wpx-v2 -->';

$wpxBlogIsHe = !(defined('LANGCODE') && strtolower((string) LANGCODE) === 'en');
$wpxBlogId = isset($get_id) ? (int) $get_id : 0;
if ($wpxBlogId <= 0 && preg_match('~(?:^|,)blog,(\d+)(?:$|[/?#])~', (string) ($_SERVER['REQUEST_URI'] ?? ''), $m)) {
    $wpxBlogId = (int) $m[1];
}

$wpxBlogPost = null;
if ($wpxBlogId > 0) {
    $row = db_fetch_array(db_query(
        "SELECT * FROM mod_blog_posts WHERE id=" . $wpxBlogId . " AND status='published' LIMIT 1"
    ));
    if (is_array($row) && !empty($row['id'])) {
        $wpxBlogPost = array();
        foreach ($row as $k => $v) {
            if (!is_int($k) && !ctype_digit((string) $k)) {
                $wpxBlogPost[$k] = $v;
            }
        }
    }
}

$tpl = db_fetch_array(db_query("SELECT id AS template FROM templates WHERE file='page.php' LIMIT 1"));
if (empty($tpl['template'])) {
    $tpl = db_fetch_array(db_query("SELECT id AS template FROM templates WHERE id=1 LIMIT 1"));
}
if (!empty($tpl['template'])) {
    $TEMP_TEMPLATE = (int) $tpl['template'];
}

if ($wpxBlogPost) {
    $title = trim((string) ($wpxBlogPost['title'] ?? ''));
    $TEMP_PAGE_TITLE = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $TEMP_INPAGE_TITLE = $TEMP_PAGE_TITLE;
    $SEO = array(
        'ptitle' => $title,
        'description' => (string) ($wpxBlogPost['meta_description'] ?? $wpxBlogPost['excerpt'] ?? ''),
        'mod' => 'blog',
        'id' => (string) $wpxBlogPost['id'],
    );
} else {
    $TEMP_PAGE_TITLE = $wpxBlogIsHe ? 'בלוג' : 'Blog';
    $TEMP_INPAGE_TITLE = $TEMP_PAGE_TITLE;
}

if (!function_exists('PAGE_TEXT')) {
    function PAGE_TEXT() {
        global $wpxBlogPost, $wpxBlogIsHe;
        $esc = function ($s) {
            return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        };
        echo '<style data-wpx-blog="1">'
            . '.wpx-blog-list,.wpx-blog-post{max-width:960px;margin:0 auto;padding:8px 4px 32px;color:#1f2533;}'
            . '.wpx-blog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:22px;}'
            . '.wpx-blog-card{border-radius:14px;overflow:hidden;background:#fff;border:1px solid rgba(20,30,60,.12);box-shadow:0 10px 28px rgba(20,30,60,.08);}'
            . '.wpx-blog-card__media{display:block;aspect-ratio:16/10;background:#eef2f7;}'
            . '.wpx-blog-card__media img{width:100%;height:100%;object-fit:cover;display:block;}'
            . '.wpx-blog-card__body{padding:16px 18px 20px;}'
            . '.wpx-blog-card h2{margin:0 0 8px;font-size:1.15rem;}'
            . '.wpx-blog-card h2 a{color:#0a1d37;text-decoration:none;}'
            . '.wpx-blog-card p{margin:0;color:#4b5563;line-height:1.55;}'
            . '.wpx-blog-post h1{margin:0 0 16px;font-size:clamp(1.6rem,3vw,2.1rem);color:#0a1d37;}'
            . '.wpx-blog-post__image{width:100%;max-height:440px;object-fit:cover;border-radius:14px;margin:0 0 22px;}'
            . '.wpx-blog-content{font-size:1.05rem;line-height:1.75;}'
            . '.wpx-blog-back{display:inline-block;margin:0 0 16px;color:#1d4ed8;}'
            . '.wpx-blog-empty{color:#6b7280;padding:18px 4px;}'
            . '</style>';

        if (is_array($wpxBlogPost) && !empty($wpxBlogPost['id'])) {
            $title = $esc($wpxBlogPost['title'] ?? '');
            $img = trim((string) ($wpxBlogPost['featured_image'] ?? ''));
            if ($img !== '' && $img[0] === '/') {
                $img = ltrim($img, '/');
            }
            echo '<article class="wpx-blog-post">';
            echo '<a class="wpx-blog-back" href=",blog">&larr; ' . $esc($wpxBlogIsHe ? 'חזרה לבלוג' : 'Back to blog') . '</a>';
            echo '<h1>' . $title . '</h1>';
            if ($img !== '') {
                echo '<img class="wpx-blog-post__image" src="' . $esc($img) . '" alt="' . $title . '">';
            }
            echo '<div class="wpx-blog-content">' . (string) ($wpxBlogPost['content'] ?? '') . '</div>';
            echo '</article>';
            return;
        }

        $rows = array();
        $q = db_query("SELECT * FROM mod_blog_posts WHERE status='published' ORDER BY created_at DESC, id DESC");
        while ($q && ($row = db_fetch_array($q))) {
            $item = array();
            foreach ($row as $k => $v) {
                if (!is_int($k) && !ctype_digit((string) $k)) {
                    $item[$k] = $v;
                }
            }
            if (!empty($item['id'])) {
                $rows[] = $item;
            }
        }

        echo '<section class="wpx-blog-list"><div class="wpx-blog-grid">';
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $title = $esc($row['title'] ?? '');
            $excerpt = trim(strip_tags((string) ($row['excerpt'] ?? '')));
            if ($excerpt === '') {
                $excerpt = trim(strip_tags((string) ($row['content'] ?? '')));
            }
            if (function_exists('mb_substr')) {
                $excerpt = mb_substr($excerpt, 0, 160, 'UTF-8');
            } else {
                $excerpt = substr($excerpt, 0, 160);
            }
            $img = trim((string) ($row['featured_image'] ?? ''));
            if ($img !== '' && isset($img[0]) && $img[0] === '/') {
                $img = ltrim($img, '/');
            }
            $href = ',blog,' . $id;
            echo '<article class="wpx-blog-card">';
            if ($img !== '') {
                echo '<a class="wpx-blog-card__media" href="' . $esc($href) . '"><img src="' . $esc($img) . '" alt="' . $title . '" loading="lazy"></a>';
            }
            echo '<div class="wpx-blog-card__body"><h2><a href="' . $esc($href) . '">' . $title . '</a></h2>';
            if ($excerpt !== '') {
                echo '<p>' . $esc($excerpt) . '</p>';
            }
            echo '</div></article>';
        }
        if (!$rows) {
            echo '<p class="wpx-blog-empty">' . $esc($wpxBlogIsHe ? 'אין פוסטים עדיין.' : 'No posts yet.') . '</p>';
        }
        echo '</div></section>';
    }
}
