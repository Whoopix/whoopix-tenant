<!DOCTYPE html>
<html lang="ar" class="rtl">
  <head>
<link href="/css/jquery-ui.css" rel="stylesheet">
<link href="/css/bootstrap.css" rel="stylesheet">
<link href="/css/font-awesome.css" rel="stylesheet">
<link href="/css/icofont.css" rel="stylesheet">
<link href="/css/superslides.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
     <title><?php echo htmlspecialchars((string)($GET_SITE_CONFIG_r["name"] ?? "")); ?> | <?php echo htmlspecialchars((string)($TEMP_PAGE_TITLE ?? "")); ?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=0.8" name="viewport"/>
        <meta name="keywords" content="<?php echo htmlspecialchars((string)($GET_SITE_CONFIG_r["keywords"] ?? "")); ?>" />
		<meta name="description" content="<?php echo htmlspecialchars((string)($GET_SITE_CONFIG_r["description"] ?? "")); ?>" />
    <!-- Fav Icon -->
    <link rel="shortcut icon" href="favicon.ico" />

    <!-- Owl carousel -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet" />

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/v4-shims.min.css" rel="stylesheet" integrity="sha512-veZLkufL0qjcloU3GqyNh2cOqjduXLgni09I72g783Fyudzxcm2A7lxj6Qxn4YrnhJdD8rB9vkR+rOtfF4TZ1g==" crossorigin="anonymous" />

    <!-- Custom Style -->
    <link href="resources/design/css/main.css" rel="stylesheet" />


    <!-- RTL Style -->
    <link href="resources/design/css/rtl-style.css" rel="stylesheet" />
    <link href="resources/design/css/site-overrides.css?v=6" rel="stylesheet" />
    <link href="resources/design/css/site-content-blocks.css?v=1" rel="stylesheet" />
    <?php
    $bpBlocksCss = dirname(__DIR__) . '/resources/design/css/site-content-blocks.css';
    echo '<style>';
    if (is_file($bpBlocksCss)) {
        echo file_get_contents($bpBlocksCss);
    } else {
        ?>
    .site-content-section{direction:rtl;text-align:right;padding:clamp(48px,6vw,88px) 0 clamp(56px,7vw,96px);background:#f1f5f9}
    .site-content-block{background:#1e2530;color:rgba(255,255,255,.5);border-radius:20px;padding:clamp(32px,5vw,56px) clamp(24px,4vw,48px);max-width:min(920px,100%);margin-inline:auto;box-shadow:0 24px 64px rgba(15,23,42,.22)}
    .site-content-block--wide{max-width:min(1140px,100%)}
    .site-content-block__title{margin:0 0 16px;font-size:clamp(1.65rem,3vw,2.1rem);font-weight:700;line-height:1.35;color:#fff}
    .site-content-block__lead{margin:0 0 28px;font-size:1.05rem;line-height:1.85;color:rgba(255,255,255,.72);max-width:56ch}
    .site-content-block__body{font-size:.95rem;line-height:1.85;color:rgba(255,255,255,.72)}
    .site-content-block__cta,.site-content-block__body a.site-content-block__cta{display:inline-block;margin-top:8px;color:#fff;font-weight:600;font-size:14px;text-decoration:none}
    .site-content-block__cta:hover{color:#7db4ff}
    .site-content-block__switch{margin:24px 0 0;text-align:center;color:rgba(255,255,255,.72)}
    .site-content-block__switch a{color:#fff;font-weight:600;text-decoration:none}
    .site-content-block__error{margin:0 0 20px;padding:12px 16px;border-radius:12px;background:rgba(239,68,68,.15);color:#fecaca;font-weight:600}
    .site-content-block .form-label{color:rgba(255,255,255,.92);font-weight:600}
    .site-content-block .form-control,.site-content-block .form-select,.site-content-block textarea.form-control{background:#fff;border:1px solid rgba(148,163,184,.35);border-radius:12px;color:#0f172a;padding:12px 16px}
    .site-content-block .form-check-label{color:rgba(255,255,255,.72)}
    .site-content-block .btn-primary{background:#60a5fa;border-color:#60a5fa;color:#0f172a;font-weight:700;border-radius:12px;box-shadow:none}
    .site-content-block .auth-link{color:#60a5fa}
    .site-content-block__body h1,.site-content-block__body h2,.site-content-block__body h3{color:#fff;font-weight:700}
    .site-content-block__body p,.site-content-block__body li{color:rgba(255,255,255,.72)}
        <?php
    }
    echo '</style>';
    ?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <![endif]-->
  </head>
  <body>
<?php  include "includes/header.php"; ?>

    <section class="site-content-section">
      <div class="container">
        <div class="site-content-block site-content-block--wide">
          <?php
          $bpPageTitle = trim((string)($TEMP_INPAGE_TITLE ?? ''));
          $bpIsMembers = (($get_mod ?? '') === 'members');
          if ($bpPageTitle !== '' && !$bpIsMembers) {
          ?>
          <h1 class="site-content-block__title"><?php echo htmlspecialchars($bpPageTitle); ?></h1>
          <?php } ?>
          <?php PAGE_TEXT(); ?>
        </div>
      </div>
    </section>

		<?php  include "includes/footer.php"; ?>

    <!-- Bootstrap's JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!-- Owl carousel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous"></script>

    <!-- Custom js -->
    <script src="resources/designjs/script.js"></script>

    <!-- Mobile Menu Toggle Script -->
    <script>
      (function () {
        const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");
        const mobileMenu = document.querySelector(".mobile-menu");
        const mobileMenuClose = document.querySelector(".mobile-menu-close");
        const mobileMenuOverlay = document.querySelector(
          ".mobile-menu-overlay"
        );
        const body = document.body;

        function openMenu() {
          if (mobileMenu) {
            mobileMenu.classList.add("show");
          }
          if (mobileMenuOverlay) {
            mobileMenuOverlay.classList.add("show");
          }
          if (body) {
            body.style.overflow = "hidden";
          }
        }

        function closeMenu() {
          if (mobileMenu) {
            mobileMenu.classList.remove("show");
          }
          if (mobileMenuOverlay) {
            mobileMenuOverlay.classList.remove("show");
          }
          if (body) {
            body.style.overflow = "";
          }
          // Close all dropdowns when closing menu
          const openDropdowns = mobileMenu.querySelectorAll(
            ".dropdown-menu.show"
          );
          openDropdowns.forEach((dropdown) => {
            dropdown.classList.remove("show");
          });
        }

        if (mobileMenuToggle) {
          mobileMenuToggle.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            openMenu();
          });
        }

        if (mobileMenuClose) {
          mobileMenuClose.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            closeMenu();
          });
        }

        if (mobileMenuOverlay) {
          mobileMenuOverlay.addEventListener("click", function () {
            closeMenu();
          });
        }

        // Close menu when clicking on a non-dropdown link
        if (mobileMenu) {
          const mobileMenuLinks = mobileMenu.querySelectorAll(
            ".nav-link:not(.dropdown-toggle), .dropdown-item"
          );
          mobileMenuLinks.forEach((link) => {
            link.addEventListener("click", function (e) {
              // Only close if it's not a dropdown toggle
              if (!this.classList.contains("dropdown-toggle")) {
                setTimeout(closeMenu, 300);
              }
            });
          });
        }

        // Close menu on window resize if it's larger than mobile
        window.addEventListener("resize", function () {
          if (window.innerWidth > 991) {
            closeMenu();
          }
        });

        // Close menu on ESC key
        document.addEventListener("keydown", function (e) {
          if (
            e.key === "Escape" &&
            mobileMenu &&
            mobileMenu.classList.contains("show")
          ) {
            closeMenu();
          }
        });
      })();
    </script>
  </body>
</html>
