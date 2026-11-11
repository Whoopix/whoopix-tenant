<!DOCTYPE html>
<html lang="he" class="rtl">
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
    <link href="resources/design/css/site-overrides.css?v=2" rel="stylesheet" />

    <!--[if lt IE 9]>
    <![endif]-->
  </head>
  <body>
<?php  include "includes/header.php"; ?>
 
     <!-- Jobs Hero -->
    <section class="jobs-hero">
      <div class="container">
        <div class="jobs-hero-wrapper">
          <span class="jobs-hero-badge">אינדקס עסקים מתקדם</span>
          <h1 class="jobs-hero-title">
           <?php echo htmlspecialchars((string)($TEMP_INPAGE_TITLE ?? '')); ?>
          </h1>
          <p class="jobs-hero-copy">
            מצאו בעלי מקצוע ושירותים איכותיים מכל התחומים – לפי אזור, תחום וסוג שירות.
          </p>
          <form class="jobs-hero-form" method="GET" action="">
            <div class="row g-3">
              <div class="col-lg-6 col-md-6">
                <label class="jobs-hero-field">
                  <i class="fa fa-search" aria-hidden="true"></i>
                  <input
                    type="text"
                    class="form-control"
                    name="search"
                    value="<?php echo htmlspecialchars((string)($_GET['search'] ?? '')); ?>"
                    placeholder="חפש בעל מקצוע, שירות או עסק"
                  />
                </label>
              </div>

              <div class="col-lg-5 col-md-6">
                <label class="jobs-hero-field select-field">
                  <i class="fa fa-map-marker" aria-hidden="true"></i>

                  <?php
                  $selected_area = $_GET['area'] ?? '';
                  $areas = [
                    'center' => 'מרכז',
                    'north' => 'צפון',
                    'south' => 'דרום',
                    'jerusalem' => 'ירושלים',
                    'hasharon' => 'השרון',
                    'gush_dan' => 'גוש דן',
                    'haifa' => 'חיפה והקריות',
                    'eilat' => 'אילת',
                    'beer_sheva' => 'באר שבע'
                  ];
                  ?>

                  <select class="form-select" name="area">
                    <option value="">בחר אזור / עיר</option>

                    <?php foreach($areas as $area_key => $area_name){ ?>
                      <option value="<?php echo $area_key; ?>" <?php echo ($selected_area == $area_key ? 'selected' : ''); ?>>
                        <?php echo $area_name; ?>
                      </option>
                    <?php } ?>

                  </select>
                </label>
              </div>
              <div class="col-lg-1 col-md-6">
                <button
                  class="btn btn-primary jobs-hero-submit w-100"
                  type="submit"
                >
                  <i class="fa fa-search" aria-hidden="true"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>

      <section class="jobs-board">
      <div class="container">
	  <?php  PAGE_TEXT(); ?>
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

        if (mobileMenu) {
          const mobileMenuLinks = mobileMenu.querySelectorAll(
            ".nav-link:not(.dropdown-toggle), .dropdown-item"
          );
          mobileMenuLinks.forEach((link) => {
            link.addEventListener("click", function (e) {
              if (!this.classList.contains("dropdown-toggle")) {
                setTimeout(closeMenu, 300);
              }
            });
          });
        }

        window.addEventListener("resize", function () {
          if (window.innerWidth > 991) {
            closeMenu();
          }
        });

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