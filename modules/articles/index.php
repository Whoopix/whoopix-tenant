<?php

if ($get_id == '') {

    $TEMP_PAGE_TITLE = "בלוג ומאמרים";
    $TEMP_INPAGE_TITLE = "בלוג ומאמרים";

    function PAGE_TEXT() {

?>

<section class="blog-hero">
    <div class="container">
        <div class="row align-items-center g-4">

            <div class="col-lg-7">

                <span class="blog-pill">Site Blog</span>

                <h1>בלוג ומאמרים לעסקים</h1>

                <p>
                    מאמרים מקצועיים, מדריכים, טיפים ועדכונים
                    לבעלי עסקים בישראל.
                </p>

            </div>

            <div class="col-lg-5">

                <div class="blog-hero-card">

                    <p>תוכן מקצועי לעסקים</p>

                    <h3>
                        מדריכים שיעזרו לעסק שלכם לצמוח בדיגיטל
                    </h3>

                </div>

            </div>

        </div>
    </div>
</section>

<section class="content-shell">
    <div class="container">

        <div class="blog-grid blog-grid-wide">

<?php

$GET_UPDATES = db_query("
    SELECT *
    FROM `mod_blog_posts`
    WHERE `status`='published'
    ORDER BY `id` DESC
") or die(db_error());

while($GET_UPDATESR = db_fetch_array($GET_UPDATES)) {

    $article_id      = intval($GET_UPDATESR['id'] ?? 0);
    $article_title   = htmlspecialchars($GET_UPDATESR['title'] ?? 'מאמר');
    $article_excerpt = strip_tags($GET_UPDATESR['excerpt'] ?? '');
    $article_image   = htmlspecialchars($GET_UPDATESR['featured_image'] ?? '');

?>

            <article class="blog-card wide">

                <div class="blog-card-media">

                    <img
                        src="<?php echo $article_image; ?>"
                        alt="<?php echo $article_title; ?>"
                    />

                    <span class="blog-card-tag">
                        מאמר מקצועי
                    </span>

                </div>

                <div class="blog-card-body">

                    <div class="blog-card-meta">

                        <span>
                            <?php
                            if(!empty($GET_UPDATESR['created_at'])){
                                echo date("d/m/Y", strtotime($GET_UPDATESR['created_at']));
                            }
                            ?>
                        </span>

                        <span>•</span>

                        <span>Site</span>

                    </div>

                    <h3>

                        <a href=",articles,art_<?php echo $article_id; ?>">
                            <?php echo $article_title; ?>
                        </a>

                    </h3>

                    <p>

                        <?php echo mb_substr($article_excerpt,0,220); ?>...

                    </p>

                    <div class="blog-card-footer">

                        <div class="author">

                            <img
                                src="images/coment-avatar-1.jpg"
                                alt="Site"
                            />

                            <div>

                                <strong>Site</strong>

                                <span>
                                    מערכת תוכן לעסקים
                                </span>

                            </div>

                        </div>

                        <a
                            href=",articles,art_<?php echo $article_id; ?>"
                            class="text-link"
                        >

                            לקריאת המאמר
                            <i class="fa-solid fa-arrow-left"></i>

                        </a>

                    </div>

                </div>

            </article>

<?php } ?>

        </div>

    </div>
</section>

<?php
    }

    $TEMP_TEMPLATE = 5;

} else {

    $TEMP_PAGE_TITLE = "מאמר";
    $TEMP_INPAGE_TITLE = "מאמר";

    function PAGE_TEXT() {

        global $get_id;

        $GET_UPDATES = db_query("
            SELECT *
            FROM `mod_blog_posts`
            WHERE `id`='".intval($get_id)."'
            LIMIT 1
        ") or die(db_error());

        $GET_UPDATESR = db_fetch_array($GET_UPDATES);

        $title   = htmlspecialchars($GET_UPDATESR['title'] ?? 'מאמר');
        $content = $GET_UPDATESR['content'] ?? '';
        $image   = htmlspecialchars($GET_UPDATESR['featured_image'] ?? '');

?>

<section class="article-page">

    <div class="container">

        <div class="article-header">

            <h1><?php echo $title; ?></h1>

        </div>

        <?php if($image != "") { ?>

        <div class="article-cover" style="margin-bottom:30px;">

            <img
                src="<?php echo $image; ?>"
                alt="<?php echo $title; ?>"
                style="width:100%;border-radius:12px;"
            />

        </div>

        <?php } ?>

        <div class="article-content">

            <?php echo $content; ?>

        </div>

    </div>

</section>

<?php
    }

    $TEMP_TEMPLATE = 2;
}

?>
