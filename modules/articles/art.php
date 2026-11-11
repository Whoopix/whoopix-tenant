<?php

if ($get_id) {

    $GET_PAGEINFO_q = db_query("
        SELECT *
        FROM `mod_blog_posts`
        WHERE `id`='".intval($get_id)."'
        LIMIT 1
    ") or die(db_error());

    if(db_num_rows($GET_PAGEINFO_q) > 0){

        $GET_PAGEINFO_r = db_fetch_array($GET_PAGEINFO_q);

        $TEMP_PAGE_TITLE   = htmlspecialchars($GET_PAGEINFO_r['title'] ?? 'מאמר');
        $TEMP_INPAGE_TITLE = htmlspecialchars($GET_PAGEINFO_r['title'] ?? 'מאמר');

        function PAGE_TEXT(){

            global $GET_PAGEINFO_r;

            $title      = htmlspecialchars($GET_PAGEINFO_r['title'] ?? 'מאמר');
            $content    = $GET_PAGEINFO_r['content'] ?? '';
            $excerpt    = htmlspecialchars($GET_PAGEINFO_r['excerpt'] ?? '');
            $image      = htmlspecialchars($GET_PAGEINFO_r['featured_image'] ?? '');
            $created_at = $GET_PAGEINFO_r['created_at'] ?? '';

?>

<section
    class="blog-detail-hero"
    style="background-image:url('<?php echo $image; ?>')"
>

    <div class="container">

        <div class="blog-detail-hero-content">

            <span class="blog-pill">
                Site Blog
            </span>

            <h1>
                <?php echo $title; ?>
            </h1>

            <div class="blog-detail-meta">

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

                <ul>

                    <li>

                        <i class="fa-regular fa-calendar"></i>

                        <?php
                        if($created_at != ""){
                            echo date("d/m/Y", strtotime($created_at));
                        }
                        ?>

                    </li>

                    <li>
                        <i class="fa-regular fa-clock"></i>
                        מאמר מקצועי
                    </li>

                    <li>
                        <i class="fa-solid fa-users"></i>
                        ביזפלייס
                    </li>

                </ul>

            </div>

            <div class="blog-detail-share">

                <span>שיתוף</span>

                <a href="#">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>

                <a href="#">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>

                <a href="#">
                    <i class="fa-brands fa-linkedin-in"></i>
                </a>

            </div>

        </div>

    </div>

</section>

<section class="blog-detail-body">

    <div class="container">

        <div class="row g-4">

            <div class="col-lg-8">

                <article class="blog-article">

                    <?php if($image != "") { ?>

                    <div class="postimage mb-3">

                        <img
                            src="<?php echo $image; ?>"
                            alt="<?php echo $title; ?>"
                        />

                    </div>

                    <?php } ?>

                    <?php if($excerpt != "") { ?>

                    <p class="article-excerpt">

                        <?php echo $excerpt; ?>

                    </p>

                    <?php } ?>

                    <div class="article-content">

                        <?php echo $content; ?>

                    </div>

                </article>

            </div>

            <div class="col-lg-4">

                <aside class="blog-detail-sidebar">

                    <div class="blog-widget">

                        <h5>מאמרים נוספים</h5>

                        <ul class="popular-list">

<?php

$RELATED = db_query("
    SELECT *
    FROM `mod_blog_posts`
    WHERE `status`='published'
    AND `id` <> '".intval($GET_PAGEINFO_r['id'])."'
    ORDER BY RAND()
    LIMIT 5
") or die(db_error());

while($R = db_fetch_array($RELATED)) {

    $rid    = intval($R['id']);
    $rtitle = htmlspecialchars($R['title'] ?? 'מאמר');

?>

                            <li>

                                <span>Site</span>

                                <p>

                                    <a href=",articles,art_<?php echo $rid; ?>">

                                        <?php echo $rtitle; ?>

                                    </a>

                                </p>

                            </li>

<?php } ?>

                        </ul>

                    </div>

                    <div class="blog-widget">

                        <h5>הרשמה לעדכונים</h5>

                        <p>
                            קבלו מאמרים ועדכונים חדשים ישירות למייל.
                        </p>

                        <form class="newsletter-form">

                            <input
                                type="email"
                                class="form-control"
                                placeholder="הכניסו כתובת אימייל"
                            />

                            <button
                                class="btn btn-primary w-100"
                                type="submit"
                            >
                                הרשמה
                            </button>

                        </form>

                    </div>

                </aside>

            </div>

        </div>

    </div>

</section>

<?php
        }

        $TEMP_TEMPLATE = 5;
    }
}

?>
