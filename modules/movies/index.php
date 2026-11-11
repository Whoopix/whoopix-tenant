<?php
/**
 * Movies / videos — list + single view from mod_movies.
 */
declare(strict_types=1);

if ($get_id) {
    $movieId = (int) $get_id;
    $row = db_fetch_array(db_query(
        'SELECT * FROM `mod_movies` WHERE `id` = :id AND `published` = 1 LIMIT 1',
        ['id' => $movieId]
    ));

    if (!$row) {
        $TEMP_PAGE_TITLE   = 'סרטון לא נמצא';
        $TEMP_INPAGE_TITLE = '404';
        if (!function_exists('PAGE_TEXT')) {
            function PAGE_TEXT(): void
            {
                echo '<p>הסרטון המבוקש לא נמצא.</p>';
            }
        }
        $TEMP_TEMPLATE = 2;
    } else {
        $title = htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $desc  = (string) ($row['description'] ?? '');
        $video = trim((string) ($row['video_file'] ?? ''));
        $thumb = trim((string) ($row['thumbnail'] ?? ''));

        $TEMP_PAGE_TITLE   = $title;
        $TEMP_INPAGE_TITLE = $title;

        if (!function_exists('PAGE_TEXT')) {
            function PAGE_TEXT(): void
            {
                global $title, $desc, $video, $thumb;
                $videoUrl = $video;
                if ($videoUrl !== '' && strpos($videoUrl, '://') === false && $videoUrl[0] !== '/') {
                    $videoUrl = '/files/videos/' . ltrim($videoUrl, '/');
                }
                $thumbUrl = $thumb;
                if ($thumbUrl !== '' && strpos($thumbUrl, '://') === false && $thumbUrl[0] !== '/') {
                    if (strpos($thumbUrl, 'files/') === 0) {
                        $thumbUrl = '/' . $thumbUrl;
                    } else {
                        $thumbUrl = '/files/imported/' . ltrim($thumbUrl, '/');
                    }
                }
                ?>
                <section class="wpx-movies-single section">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10">
                                <?php if ($videoUrl !== ''): ?>
                                    <div class="wpx-movie-player ratio ratio-16x9 mb-4">
                                        <?php if (preg_match('~(?:youtube\.com|youtu\.be|vimeo\.com)~i', $videoUrl)): ?>
                                            <iframe src="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                title="<?= $title ?>" allowfullscreen loading="lazy"
                                                style="width:100%;height:100%;border:0;border-radius:12px;"></iframe>
                                        <?php else: ?>
                                            <video controls preload="metadata" poster="<?= htmlspecialchars($thumbUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                style="width:100%;height:100%;border-radius:12px;background:#000;">
                                                <source src="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>">
                                            </video>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($desc !== ''): ?>
                                    <div class="wpx-movie-desc"><?= $desc ?></div>
                                <?php endif; ?>
                                <p class="mt-4"><a href=",movies" class="btn btn-outline-primary">&larr; חזרה לכל הסרטונים</a></p>
                            </div>
                        </div>
                    </div>
                </section>
                <?php
            }
        }
        $TEMP_TEMPLATE = 2;
    }
} else {
    $TEMP_PAGE_TITLE   = 'סרטונים';
    $TEMP_INPAGE_TITLE = 'סרטונים';

    if (!function_exists('PAGE_TEXT')) {
        function PAGE_TEXT(): void
        {
            $q = db_query(
                'SELECT * FROM `mod_movies` WHERE `published` = 1 ORDER BY `sort_order` ASC, `id` DESC'
            );
            ?>
            <section class="wpx-movies-list section">
                <div class="container">
                    <div class="row g-4">
                        <?php
                        $count = 0;
                        while ($r = db_fetch_array($q)) {
                            $count++;
                            $id    = (int) ($r['id'] ?? 0);
                            $title = htmlspecialchars((string) ($r['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $desc  = htmlspecialchars(strip_tags((string) ($r['description'] ?? '')), ENT_QUOTES, 'UTF-8');
                            $video = trim((string) ($r['video_file'] ?? ''));
                            $thumb = trim((string) ($r['thumbnail'] ?? ''));
                            $videoUrl = $video;
                            if ($videoUrl !== '' && strpos($videoUrl, '://') === false && $videoUrl[0] !== '/') {
                                $videoUrl = '/files/videos/' . ltrim($videoUrl, '/');
                            }
                            $thumbUrl = $thumb;
                            if ($thumbUrl === '' && $videoUrl !== '' && !preg_match('~(?:youtube|vimeo)~i', $videoUrl)) {
                                $thumbUrl = $videoUrl;
                            }
                            if ($thumbUrl !== '' && strpos($thumbUrl, '://') === false && $thumbUrl[0] !== '/') {
                                if (strpos($thumbUrl, 'files/') === 0) {
                                    $thumbUrl = '/' . $thumbUrl;
                                } else {
                                    $thumbUrl = '/files/imported/' . ltrim($thumbUrl, '/');
                                }
                            }
                            if ($thumbUrl === '') {
                                $thumbUrl = 'https://via.placeholder.com/550x380?text=' . rawurlencode($title);
                            }
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <article class="wpx-movie-card h-100">
                                    <a href=",movies,<?= $id ?>" class="wpx-movie-card-link d-block text-decoration-none">
                                        <div class="wpx-movie-thumb position-relative overflow-hidden rounded-3">
                                            <img src="<?= htmlspecialchars($thumbUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= $title ?>" loading="lazy"
                                                style="width:100%;aspect-ratio:550/380;object-fit:cover;display:block;">
                                            <span class="wpx-movie-play position-absolute top-50 start-50 translate-middle">
                                                <i class="fa fa-play-circle fa-3x text-white" style="opacity:.92;"></i>
                                            </span>
                                        </div>
                                        <div class="wpx-movie-meta pt-3">
                                            <h3 class="h5 mb-1"><?= $title ?></h3>
                                            <?php if ($desc !== ''): ?>
                                                <p class="text-muted small mb-0"><?= $desc ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </article>
                            </div>
                            <?php
                        }
                        if ($count === 0) {
                            echo '<div class="col-12"><p class="text-muted">אין סרטונים להצגה כרגע.</p></div>';
                        }
                        ?>
                    </div>
                </div>
            </section>
            <?php
        }
    }
    $TEMP_TEMPLATE = 2;
}
