<?php
/**
 * Public catalog — project detail (and optional listing).
 * Tenant schema: mod_products_list.pic1 + mod_products.prod_id
 */
declare(strict_types=1);

require_once __DIR__ . '/../../includes/wpx_catalog.php';

$PUT_LEFT = false;
$get_id = isset($get_id) ? (int) $get_id : 0;

// Prefer inner AGA shell registered as page.php; fall back to page.php / contact shell.
$tplRow = db_fetch_array(db_query("SELECT id FROM templates WHERE file = 'page.php' LIMIT 1"));
if (!empty($tplRow['id'])) {
    $TEMP_TEMPLATE = (int) $tplRow['id'];
} else {
    $tplRow = db_fetch_array(db_query("SELECT id FROM templates WHERE file = 'i4w04.php' LIMIT 1"));
    $TEMP_TEMPLATE = !empty($tplRow['id']) ? (int) $tplRow['id'] : 1;
}

$product = $get_id > 0 ? wpx_catalog_fetch_product($get_id) : null;

if ($product) {
    $title = trim((string) ($product['lang_title'] ?? '')) ?: trim((string) ($product['title'] ?? ''));
    $TEMP_PAGE_TITLE = $title;
    $TEMP_INPAGE_TITLE = $title;
    $SEO = [
        'ptitle' => trim((string) ($product['ptitle'] ?? '')) ?: $title,
        'keywords' => (string) ($product['keywords'] ?? ''),
        'description' => (string) ($product['description'] ?? ''),
        'mod' => 'products',
        'id' => (string) $get_id,
    ];
} else {
    $TEMP_PAGE_TITLE = 'פרויקטים';
    $TEMP_INPAGE_TITLE = 'פרויקטים';
}

if (!function_exists('PAGE_TEXT')) {
    function PAGE_TEXT()
    {
        global $product, $get_id;

        if (!$product) {
            echo '<div class="about-txt" style="direction:rtl;"><p>הפרויקט לא נמצא.</p>';
            echo '<p><a href="/projects">חזרה לפרויקטים</a></p></div>';
            return;
        }

        $title = trim((string) ($product['lang_title'] ?? '')) ?: trim((string) ($product['title'] ?? ''));
        $text = trim((string) ($product['text'] ?? ''));
        if ($text === '') {
            $text = trim((string) ($product['long_desc'] ?? $product['desc'] ?? ''));
        }
        $pic = wpx_catalog_pic_src((string) ($product['pic1'] ?? ''));
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safePic = htmlspecialchars($pic, ENT_QUOTES, 'UTF-8');
        $catId = (int) ($product['cat_id'] ?? 0);
        $back = '/projects';
        if ($catId === 2) {
            $back = '/commercial';
        } elseif ($catId === 3) {
            $back = '/tama-38';
        } elseif ($catId === 4) {
            $back = '/residential';
        }
        ?>
        <div class="post post-short" style="direction:rtl;">
            <div class="post-header">
                <div class="post-image">
                    <div class="img-inner"><img src="<?= $safePic ?>" alt="<?= $safeTitle ?>" class="img-responsive"></div>
                </div>
            </div>
            <div class="post-story">
                <h2><?= $safeTitle ?></h2>
                <div class="post-story-body">
                    <?= $text ?>
                </div>
                <div class="post-story-link clearfix" style="margin-top:20px;">
                    <a href="<?= htmlspecialchars($back, ENT_QUOTES, 'UTF-8') ?>" class="btn-default btn3"><span>חזרה לקטגוריה</span></a>
                    <a href="/projects" class="btn-default btn3" style="margin-right:10px;"><span>כל הפרויקטים</span></a>
                </div>
            </div>
        </div>
        <?php
    }
}
