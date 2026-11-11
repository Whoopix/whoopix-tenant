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

    <!-- WPIX almoni fonts (gj/gg) -->
    <link href="resources/design/css/site-overrides.css?v=3" rel="stylesheet" />

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="resources/design/js/html5shiv.min.js"></script>
      <script src="resources/design/js/respond.min.js"></script>
    <![endif]-->
  
<style>

.modern-biz-meta{
    display:flex;
    flex-direction:column;
    gap:8px;
    margin-top:14px;
}

.biz-meta-item{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:14px;
    color:#666;
    line-height:1.6;
}

.biz-meta-item i{
    width:16px;
    color:#0d6efd;
}

</style>


</head>
  <body>
<?php  include "includes/header.php"; ?>
    <!-- Hero start -->
    <section class="hero-section">
      <div class="container">
        <div class="row align-items-center g-4">
          <div class="col-lg-6">
            <span class="hero-badge">אינדקס עסקים מקיף</span>
            <h1 class="hero-title">
              ברוכים הבאים לביז-פלייס
            </h1>
            <p class="hero-copy">
              האינדקס העסקי המוביל בישראל — מצאו בעלי מקצוע ועסקים מומלצים באזורכם, או הצטרפו כעסק וקבלו פניות חדשות מלקוחות מדי יום.
            </p>
            <form class="hero-search" action=",search" method="get">
              <div class="hero-search-fields">
                <label class="hero-field">
                  <i class="fa fa-search" aria-hidden="true"></i>
                  <input
                    type="text"
                    class="form-control"
                    name="word"
                    placeholder="חפש בעל מקצוע"
                  />
                </label>

             <label class="hero-field select-field">
	<i class="fa fa-map-marker" aria-hidden="true"></i>
	<select class="form-select" name="category">
		<option value="" selected>בחר אזור</option>
		<option value="center">מרכז</option>
		<option value="north">צפון</option>
		<option value="south">דרום</option>
		<option value="jerusalem">ירושלים</option>
		<option value="sharon">השרון</option>
		<option value="haifa">חיפה והקריות</option>
		<option value="tel_aviv">תל אביב</option>
		<option value="beer_sheva">באר שבע והסביבה</option>
		<option value="eilat">אילת</option>
	</select>
</label>
                <button
                  type="submit"
                  class="btn hero-submit"
                  aria-label="חפש"
                >
                  <i class="fa fa-search" aria-hidden="true"></i>
                </button>
              </div>
            </form>
            <div class="hero-actions d-flex flex-wrap align-items-center gap-3">
              <div class="hero-stat">
                <span class="stat-value">50k+</span>
                <span class="stat-label">עסקים באתר</span>
              </div>
              <div class="hero-links d-flex gap-3">
                <a href=",members,register" class="hero-link"
                  ><i class="fa fa-briefcase" aria-hidden="true"></i> הרשם כבעל מקצוע</a
                >
                <a href=",members,register" class="hero-link"
                  ><i class="fa fa-user-o" aria-hidden="true"></i> הרשם כספק</a
                >
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="hero-visual">
              <img
                src="resources/design/images/site-hero.jpg?v=1"
                alt="ביז-פלייס — אינדקס העסקים והשירותים המקומי"
                class="img-fluid hero-image"
              />
              <div class="hero-floating-card">
                <span class="card-label">נרשמים ונהנים מפניות איכותיות</span>
                <button type="button" class="btn btn-sm btn-primary">
                  ההרשמה חינם
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Hero End -->

    <div class="infodatawrap">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <a
              href=",members,register"
             
              class="userloginbox"
            >
              <h3>הרשם כבעל מקצוע באינדקס</h3>
              <p>התחל לקבל פניות רלוונטיות לעסק</p>
              <img
                src="resources/design/images/icons/search-job-icon.png"
                alt=""
              />
            </a>
          </div>
          <div class="col-md-6">
            <a
              href=",members,register"
             
              class="userloginbox postjobbox"
            >
              <h3>הרשם כמפיץ באתר</h3>
              <p>התחל להרוויח מהפנייה של אנשים</p>
              <img src="resources/design/images/icons/postjob.png" alt="" />
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Companies start 
    <section class="section company-section">
      <div class="container">
        <div class="titleTop text-center">
          <div class="subtitle">هنا يمكنك أن ترى</div>
          <h3>أفضل الشركات توظف</h3>
        </div>
        <div class="row g-4 company-grid">
          <div class="col-12 col-sm-6 col-lg-3">
            <a href="company-detail.html" class="company-card">
              <div class="company-logo">
                <img
                  src="resources/design/images/employers/emplogo1.jpg"
                  alt="Multimedia Design"
                />
              </div>
              <h5>Multimedia Design</h5>
              <div class="company-meta">
                <i class="fa fa-map-marker" aria-hidden="true"></i> الولايات المتحدة الأمريكية
              </div>
              <div class="company-openings">
                <i class="fa fa-briefcase" aria-hidden="true"></i> 5 وظائف متاحة
              </div>
            </a>
          </div>
          <div class="col-12 col-sm-6 col-lg-3">
            <a href="company-detail.html" class="company-card">
              <div class="company-logo">
                <img src="resources/design/images/employers/emplogo2.jpg" alt="Power Wave" />
              </div>
              <h5>Power Wave</h5>
              <div class="company-meta">
                <i class="fa fa-map-marker" aria-hidden="true"></i> United
                States of America
              </div>
              <div class="company-openings">
                <i class="fa fa-briefcase" aria-hidden="true"></i> 2 وظائف متاحة
              </div>
            </a>
          </div>
          <div class="col-12 col-sm-6 col-lg-3">
            <a href="company-detail.html" class="company-card">
              <div class="company-logo">
                <img src="resources/design/images/employers/emplogo3.jpg" alt="Travel Advisor" />
              </div>
              <h5>Travel Advisor</h5>
              <div class="company-meta">
                <i class="fa fa-map-marker" aria-hidden="true"></i> United
                States of America
              </div>
              <div class="company-openings">
                <i class="fa fa-briefcase" aria-hidden="true"></i> 0 وظائف متاحة
              </div>
            </a>
          </div>
          <div class="col-12 col-sm-6 col-lg-3">
            <a href="company-detail.html" class="company-card">
              <div class="company-logo">
                <img
                  src="resources/design/images/employers/emplogo4.jpg"
                  alt="New Design Studio"
                />
              </div>
              <h5>New Design Studio</h5>
              <div class="company-meta">
                <i class="fa fa-map-marker" aria-hidden="true"></i> United
                States of America
              </div>
              <div class="company-openings">
                <i class="fa fa-briefcase" aria-hidden="true"></i> وظيفة واحدة متاحة
              </div>
            </a>
          </div>
          <div class="col-12 col-sm-6 col-lg-3">
            <a href="company-detail.html" class="company-card">
              <div class="company-logo">
                <img src="resources/design/images/employers/emplogo5.jpg" alt="Net Design" />
              </div>
              <h5>Net Design</h5>
              <div class="company-meta">
                <i class="fa fa-map-marker" aria-hidden="true"></i> United
                States of America
              </div>
              <div class="company-openings">
                <i class="fa fa-briefcase" aria-hidden="true"></i> وظيفة واحدة متاحة
              </div>
            </a>
          </div>
          <div class="col-12 col-sm-6 col-lg-3">
            <a href="company-detail.html" class="company-card">
              <div class="company-logo">
                <img src="resources/design/images/employers/emplogo6.jpg" alt="Power Color" />
              </div>
              <h5>Power Color</h5>
              <div class="company-meta">
                <i class="fa fa-map-marker" aria-hidden="true"></i> United
                States of America
              </div>
              <div class="company-openings">
                <i class="fa fa-briefcase" aria-hidden="true"></i> 2 وظائف متاحة
              </div>
            </a>
          </div>
          <div class="col-12 col-sm-6 col-lg-3">
            <a href="company-detail.html" class="company-card">
              <div class="company-logo">
                <img src="resources/design/images/employers/emplogo7.jpg" alt="Connect People" />
              </div>
              <h5>Connect People</h5>
              <div class="company-meta">
                <i class="fa fa-map-marker" aria-hidden="true"></i> United
                States of America
              </div>
              <div class="company-openings">
                <i class="fa fa-briefcase" aria-hidden="true"></i> 2 وظائف متاحة
              </div>
            </a>
          </div>
          <div class="col-12 col-sm-6 col-lg-3">
            <a href="company-detail.html" class="company-card">
              <div class="company-logo">
                <img src="resources/design/images/employers/emplogo8.jpg" alt="Surf Wave" />
              </div>
              <h5>Surf Wave</h5>
              <div class="company-meta">
                <i class="fa fa-map-marker" aria-hidden="true"></i> United
                States of America
              </div>
              <div class="company-openings">
                <i class="fa fa-briefcase" aria-hidden="true"></i> وظيفة واحدة متاحة
              </div>
            </a>
          </div>
        </div>
        <div class="company-viewall text-center">
          <a href="company-detail.html" class="btn btn-primary"
            >عرض جميع الشركات المميزة</a
          >
        </div>
      </div>
    </section>
    <!-- Top Companies end -->

    <!-- Categories start -->
    <section class="section category-section">
      <div class="container">
        <div class="titleTop text-center">
          <div class="subtitle">אינדקס</div>
          <h3>קטגוריות מובילות</h3>
        </div>
        <div class="category-carousel-wrap">
          <div class="category-nav category-prev">
            <i class="fa fa-angle-left" aria-hidden="true"></i>
          </div>
          <div class="category-nav category-next">
            <i class="fa fa-angle-right" aria-hidden="true"></i>
          </div>
          <div class="owl-carousel category-carousel">
		  
		  <?php
					$i = 1;
					$GET_CAT_q=db_query("SELECT * FROM `mod_sop_cats` ORDER BY `order` ASC");
					while ($GET_CAT_r=db_fetch_array($GET_CAT_q)){

					$q=db_query("SELECT * FROM `mod_sop` WHERE
						`cat_id` =".$GET_CAT_r['id']."
					 AND `parent` = '0' ORDER BY `order` LIMIT 3")or die(db_error());?>
            <div class="category-card">
              <div class="category-icon">
                <img
                  src="resources/design/images/categories/business-management.png"
                  alt="Business Management"
                />
              </div>
              <h5><?=$GET_CAT_r['title'];?></h5>
              <a href=",categories,<?=$GET_CAT_r['id'];?>" class="category-jobs"
                ><i class="fa fa-briefcase" aria-hidden="true"></i> צפה בקטגוריה</a
              >
            </div>
			
			<?php  } ?>
			
			
          </div>
        </div>
        <div class="category-viewall text-center">
          <a href="#." class="btn btn-primary">לכל הקטגוריות</a>
        </div>
      </div>
    </section>
    <!-- Categories end -->

    <!-- Industries start
    <section class="section industries-section">
      <div class="container">
        <div class="titleTop text-center">
          <div class="subtitle">استكشف القطاعات</div>
          <h3>الصناعات الشائعة</h3>
        </div>
        <div class="industries-grid">
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-industry" aria-hidden="true"></i
            ></span>
            التصنيع (5)
          </a>
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-female" aria-hidden="true"></i
            ></span>
            الأزياء (2)
          </a>
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-plug" aria-hidden="true"></i
            ></span>
            الإلكترونيات (2)
          </a>
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-bullhorn" aria-hidden="true"></i
            ></span>
            الإعلان/العلاقات العامة (2)
          </a>
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-desktop" aria-hidden="true"></i
            ></span>
            تقنية المعلومات (2)
          </a>
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-truck" aria-hidden="true"></i
            ></span>
            البريد/اللوجستيات (1)
          </a>
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-car" aria-hidden="true"></i
            ></span>
            السيارات (1)
          </a>
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-graduation-cap" aria-hidden="true"></i
            ></span>
            التعليم/التدريب (1)
          </a>
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-university" aria-hidden="true"></i
            ></span>
            الخدمات المصرفية/المالية (1)
          </a>
          <a href="#." class="industry-chip">
            <span class="chip-icon"
              ><i class="fa fa-heartbeat" aria-hidden="true"></i
            ></span>
            الصحة واللياقة البدنية (1)
          </a>
        </div>
      </div>
    </section>
    <!-- Industries end -->

    <!-- How it Works start -->
  <section class="section howit-section">
	<div class="container">
		<div class="titleTop text-center">
			<div class="subtitle">צעדים פשוטים</div>
			<h3>תהליך הוספה</h3>
		</div>
		<div class="row g-4 justify-content-center howit-grid">
			<div class="col-12 col-md-4">
				<div class="howit-card">
					<div class="howit-icon">
						<i class="fa-solid fa-user-plus"></i>
					</div>
					<h4>יצירת חשבון</h4>
					<p>פתח חשבון בקלות והתחל להשתמש באתר.</p>
				</div>
			</div>
			<div class="col-12 col-md-4">
				<div class="howit-card">
					<div class="howit-icon">
						<i class="fa-solid fa-file"></i>
					</div>
					<h4>השלמת פרופיל</h4>
					<p>הוסף את כל הפרטים החשובים כדי שלקוחות יוכלו להכיר אותך.</p>
				</div>
			</div>
			<div class="col-12 col-md-4">
				<div class="howit-card">
					<div class="howit-icon">
						<i class="fa-solid fa-paper-plane"></i>
					</div>
					<h4>קבלת פניות או פרסום שירות</h4>
					<p>
						קבל פניות מלקוחות או מצא ספקים בקלות ובמהירות.
					</p>
				</div>
			</div>
		</div>
	</div>
</section>
    <!-- How it Works Ends -->

    <!-- Featured Jobs start
    <div class="section">
      <div class="container">
        <!-- title start 
        <div class="titleTop">
          <div class="subtitle">هنا يمكنك أن ترى</div>
          <h3>الوظائف <span>المميزة</span></h3>
        </div>
        <!-- title end -->

        <!--Featured Job start
        <div class="row g-4 featured-jobs">
          <div class="col-12 col-md-6 col-lg-3">
            <div class="job-card">
              <div class="job-card-status">
                <span class="job-card-status-icon"
                  ><i class="fa fa-briefcase" aria-hidden="true"></i
                ></span>
                دوام كامل/دائم
              </div>
              <h4 class="job-card-title">
                <a href="#.">مصمم Full Stack</a>
              </h4>
              <div class="job-card-location">
                <i class="fa fa-map-marker" aria-hidden="true"></i> Barrington
              </div>
              <div class="job-card-footer">
                <div class="job-card-meta">
                  <span class="job-card-date">Mar 07, 2025</span>
                  <span class="job-card-company">Connect People</span>
                </div>
                <div class="job-card-logo">
                  <img
                    src="resources/design/images/employers/emplogo7.jpg"
                    alt="Connect People"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="job-card">
              <div class="job-card-status">
                <span class="job-card-status-icon"
                  ><i class="fa fa-briefcase" aria-hidden="true"></i
                ></span>
                دوام جزئي
              </div>
              <h4 class="job-card-title">
                <a href="#.">أخصائي تسويق</a>
              </h4>
              <div class="job-card-location">
                <i class="fa fa-map-marker" aria-hidden="true"></i> New York
              </div>
              <div class="job-card-footer">
                <div class="job-card-meta">
                  <span class="job-card-date">Mar 10, 2025</span>
                  <span class="job-card-company">Power Wave</span>
                </div>
                <div class="job-card-logo">
                  <img src="resources/design/images/employers/emplogo2.jpg" alt="Power Wave" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="job-card">
              <div class="job-card-status">
                <span class="job-card-status-icon"
                  ><i class="fa fa-briefcase" aria-hidden="true"></i
                ></span>
                عمل حر
              </div>
              <h4 class="job-card-title"><a href="#.">مهندس واجهة المستخدم</a></h4>
              <div class="job-card-location">
                <i class="fa fa-map-marker" aria-hidden="true"></i> Los Angeles
              </div>
              <div class="job-card-footer">
                <div class="job-card-meta">
                  <span class="job-card-date">Mar 12, 2025</span>
                  <span class="job-card-company">Design Studio</span>
                </div>
                <div class="job-card-logo">
                  <img
                    src="resources/design/images/employers/emplogo4.jpg"
                    alt="Design Studio"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="job-card">
              <div class="job-card-status">
                <span class="job-card-status-icon"
                  ><i class="fa fa-briefcase" aria-hidden="true"></i
                ></span>
                عقد
              </div>
              <h4 class="job-card-title"><a href="#.">محلل بيانات</a></h4>
              <div class="job-card-location">
                <i class="fa fa-map-marker" aria-hidden="true"></i> Chicago
              </div>
              <div class="job-card-footer">
                <div class="job-card-meta">
                  <span class="job-card-date">Mar 15, 2025</span>
                  <span class="job-card-company">Sphere Tech</span>
                </div>
                <div class="job-card-logo">
                  <img src="resources/design/images/employers/emplogo9.jpg" alt="Sphere Tech" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="job-card">
              <div class="job-card-status">
                <span class="job-card-status-icon"
                  ><i class="fa fa-briefcase" aria-hidden="true"></i
                ></span>
                تدريب
              </div>
              <h4 class="job-card-title">
                <a href="#.">مهندس ضمان جودة مبتدئ</a>
              </h4>
              <div class="job-card-location">
                <i class="fa fa-map-marker" aria-hidden="true"></i> Austin
              </div>
              <div class="job-card-footer">
                <div class="job-card-meta">
                  <span class="job-card-date">Mar 18, 2025</span>
                  <span class="job-card-company">Media Wave</span>
                </div>
                <div class="job-card-logo">
                  <img src="resources/design/images/employers/emplogo10.jpg" alt="Media Wave" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="job-card">
              <div class="job-card-status">
                <span class="job-card-status-icon"
                  ><i class="fa fa-briefcase" aria-hidden="true"></i
                ></span>
                عن بُعد
              </div>
              <h4 class="job-card-title"><a href="#.">مدير منتج</a></h4>
              <div class="job-card-location">
                <i class="fa fa-map-marker" aria-hidden="true"></i> Remote
              </div>
              <div class="job-card-footer">
                <div class="job-card-meta">
                  <span class="job-card-date">Mar 20, 2025</span>
                  <span class="job-card-company">Power Color</span>
                </div>
                <div class="job-card-logo">
                  <img src="resources/design/images/employers/emplogo6.jpg" alt="Power Color" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="job-card">
              <div class="job-card-status">
                <span class="job-card-status-icon"
                  ><i class="fa fa-briefcase" aria-hidden="true"></i
                ></span>
                مختلط
              </div>
              <h4 class="job-card-title"><a href="#.">مهندس DevOps</a></h4>
              <div class="job-card-location">
                <i class="fa fa-map-marker" aria-hidden="true"></i> Seattle
              </div>
              <div class="job-card-footer">
                <div class="job-card-meta">
                  <span class="job-card-date">Mar 22, 2025</span>
                  <span class="job-card-company">Surf Wave</span>
                </div>
                <div class="job-card-logo">
                  <img src="resources/design/images/employers/emplogo8.jpg" alt="Surf Wave" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="job-card">
              <div class="job-card-status">
                <span class="job-card-status-icon"
                  ><i class="fa fa-briefcase" aria-hidden="true"></i
                ></span>
                دوام كامل
              </div>
              <h4 class="job-card-title">
                <a href="#.">مطور تطبيقات الجوال</a>
              </h4>
              <div class="job-card-location">
                <i class="fa fa-map-marker" aria-hidden="true"></i> Miami
              </div>
              <div class="job-card-footer">
                <div class="job-card-meta">
                  <span class="job-card-date">Mar 24, 2025</span>
                  <span class="job-card-company">Power Wave</span>
                </div>
                <div class="job-card-logo">
                  <img src="resources/design/images/employers/emplogo2.jpg" alt="Power Wave" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--Featured Job end-->

        <!--button start-->
        <div class="category-viewall text-center">
          <a href="job-listing.html" class="btn btn-primary"
            >הרשמה לאתר</a
          >
        </div>
        <!--button end-->
      </div>
    </div>
    <!-- Featured Jobs ends -->

   <!-- Video start -->
<section class="section video-section-v2">
	<div class="container">
		<div class="row align-items-center g-5">
			<div class="col-lg-6">
				<div class="video-content-v2">
					<span class="video-badge-v2">כאן תוכלו לראות</span>
					<h2 class="video-title-v2">
						הסבר על האינדקס <span class="video-highlight-v2">בסרטון</span>
					</h2>
					<p class="video-text-v2">
						גלה פלטפורמה מתקדמת שמחברת בין לקוחות לבעלי מקצוע. אנו מספקים כלים חכמים למציאת השירות המתאים ביותר או לפרסום העסק שלך בקלות.
					</p>
					<ul class="video-features-v2">
						<li>
							<i class="fa fa-check-circle" aria-hidden="true"></i> הכירו את הפלטפורמה שלנו
						</li>
						<li>
							<i class="fa fa-check-circle" aria-hidden="true"></i> גלו סיפורי הצלחה
						</li>
						<li>
							<i class="fa fa-check-circle" aria-hidden="true"></i> ראו איך זה עובד
						</li>
					</ul>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="video-wrapper-v2">
					<div class="video-thumbnail-v2">
						<img
							src="resources/design/images/video-thumbnail.jpg"
							alt="תצוגה מקדימה של הסרטון"
							class="video-image-v2"
						/>
						<div class="video-overlay-v2"></div>
						<button
							class="video-play-btn-v2"
							type="button"
							data-bs-toggle="modal"
							data-bs-target="#videoModalV2"
							aria-label="הפעלת סרטון"
						>
							<div class="play-btn-circle-v2">
								<i class="fa fa-play" aria-hidden="true"></i>
							</div>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- Video end -->
    <!-- Video Modal V2 -->
    <div
      class="modal fade"
      id="videoModalV2"
      tabindex="-1"
      aria-labelledby="videoModalV2Label"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content video-modal-content-v2">
          <button
            type="button"
            class="btn-close video-modal-close-v2"
            data-bs-dismiss="modal"
            aria-label="إغلاق"
          ></button>
          <div class="video-modal-body-v2">
            <div class="ratio ratio-16x9">
              <iframe
                src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                title=""
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Latest Jobs start -->
    <div class="section greybg">
      <div class="container">
        <!-- title start -->
        <div class="titleTop">
          <div class="subtitle">עסקים חדשים באתר</div>
          <h3>עסקים <span>מובילים</span></h3>
        </div>
        <!-- title end -->

        <div class="row g-4 latest-jobs">
		
		
		
		
		
		
		
		<?php 
		
		
		
		$get_latest_biz = db_query("SELECT * FROM `mod_biz` ORDER BY `id` DESC LIMIT 12");
			while ($glb = db_fetch_array($get_latest_biz)){
		
		
		?>
		
		
		
          <div class="col-12 col-md-6 col-lg-4">
            <div class="latest-job-card">
              <div class="latest-job-header">
                <?php
                $__cat_name = 'עסק';

                // המערכת שומרת ב-mod_biz.cat_id את ה-ID של mod_sop
                if (!empty($glb['cat_id'])) {

                  $__subcat_q = db_query("
                    SELECT title 
                    FROM mod_sop 
                    WHERE id = '".intval($glb['cat_id'])."'
                    LIMIT 1
                  ");

                  $__subcat_r = db_fetch_array($__subcat_q);

                  if (!empty($__subcat_r['title'])) {
                    $__cat_name = $__subcat_r['title'];
                  }
                }
                ?>

                <span class="badge badge-status fulltime"><?php echo htmlspecialchars($__cat_name); ?></span>
                <a href=",products,<?=$glb['id']; ?>" class="bookmark"
                  ><i class="fa fa-heart-o" aria-hidden="true"></i
                ></a>
              </div>
              <h4><a href=",products,<?=$glb['id']; ?>"><?=$glb['name'];?></a></h4>
              <?php
              $biz_cat_name = '';

              if (!empty($glb['cat_id'])) {
                $cat_query = db_query("SELECT title FROM mod_sop WHERE id = '".intval($glb['cat_id'])."' LIMIT 1");
                $cat_data = db_fetch_array($cat_query);

                if (!empty($cat_data['title'])) {
                  $biz_cat_name = $cat_data['title'];
                }
              }
              ?>

              <div class="latest-job-meta modern-biz-meta">

                <?php if($biz_cat_name != '') { ?>
                <span class="biz-meta-item">
                  <i class="fa fa-briefcase" aria-hidden="true"></i>
                  <?php echo htmlspecialchars($biz_cat_name); ?>
                </span>
                <?php } ?>

                <?php if(!empty($glb['address'])) { ?>
                <span class="biz-meta-item">
                  <i class="fa fa-map-marker" aria-hidden="true"></i>
                  <?php echo htmlspecialchars($glb['address']); ?>
                </span>
                <?php } ?>

                <?php if(!empty($glb['c_name'])) { ?>
                <span class="biz-meta-item">
                  <i class="fa fa-user" aria-hidden="true"></i>
                  <?php echo htmlspecialchars($glb['c_name']); ?>
                </span>
                <?php } ?>

              </div>
              <div class="latest-job-footer">
                <div class="latest-job-company">
				
				
               
				  
				  			<?php
									$_logo = (string) ($glb['logofile'] ?? '');
									$_biz_logo = 'resources/design/images/coment-avatar-3.jpg';

									if (
										$_logo !== '' &&
										file_exists($_SERVER['DOCUMENT_ROOT'].'/files/upload/'.$_logo)
									) {
										$_biz_logo = '/files/upload/'.$_logo;
									}
									?>

									<img src="<?php echo $_biz_logo; ?>" style="width:100%;object-fit:cover;">
                
                </div>
                <a href=",products,<?=$glb['id']; ?>" class="btn btn-outline-primary btn-sm"
                  >לעמוד העסק</a
                >
              </div>
            </div>
          </div>
		  
		  
		  
		  
			<?php  } ?>
		  
		  
	
	</div>
        <div class="category-viewall text-center">
          <a href="#." class="btn btn-primary">לכל העסקים</a>
        </div>
      </div>
    </div>
    <!-- Latest Jobs ends -->

 
    <!-- Testimonials start -->
    <section class="section testimonials-section" id="reviews">
      <div class="container">
        <div class="titleTop text-center">
          <div class="subtitle">מה אומרים על העסקים</div>
          <h3>ביקורות</h3>
        </div>
        <div class="testimonials-wrap">
          <div class="testimonials-nav testimonials-prev">
            <i class="fa fa-angle-left" aria-hidden="true"></i>
          </div>
          <div class="testimonials-nav testimonials-next">
            <i class="fa fa-angle-right" aria-hidden="true"></i>
          </div>
          <div class="owl-carousel testimonials-carousel">
		  
		  
		  
		  
		  
		  
		  
		  
		  		<?php 
		$get_latest_comments = db_query("SELECT * FROM mod_feedback_list ORDER BY id DESC LIMIT 25");
		while ($glc = db_fetch_array($get_latest_comments)){



			$get_latest_biz = db_query("SELECT * FROM mod_biz WHERE id = '".$glc['product_id']."'");
			$glb = db_fetch_array($get_latest_biz);


					$get_link = db_query("SELECT * FROM `mod_seo` WHERE `mod` = 'products' AND `id` = '".$glb['id']."'");
					$glink = db_fetch_array($get_link);
					
					
					?>
		  
		  
		  
		  
		  
		  
		  
            <?php
              $_t_logo   = (string) ($glb['logofile'] ?? '');

              $_t_avatar = 'resources/design/images/coment-avatar-3.jpg';

              if (
                $_t_logo !== '' &&
                file_exists($_SERVER['DOCUMENT_ROOT'].'/files/upload/' . $_t_logo)
              ) {
                $_t_avatar = '/files/upload/' . $_t_logo;
              }

              $_t_name   = (string) ($glb['name']   ?? '');
              $_t_role   = (string) ($glb['c_name'] ?? '');
              $_t_text   = (string) ($glc['text']   ?? '');
            ?>
            <div class="testimonial-card">
              <div class="testimonial-quote">
                <i class="fa fa-quote-left" aria-hidden="true"></i>
              </div>
              <p>
                "<?php echo htmlspecialchars(mb_substr($_t_text, 0, 100)); ?>"
              </p>
              <div class="testimonial-author">
                <img src="<?php echo htmlspecialchars($_t_avatar); ?>" alt="<?php echo htmlspecialchars($_t_name); ?>" style="width:56px;height:56px;object-fit:cover;border-radius:50%;" />
                <div>
                  <span class="name"><?php echo htmlspecialchars($_t_name); ?></span>
                  <span class="role"><?php echo htmlspecialchars($_t_role); ?></span>
                </div>
              </div>
            </div>
        
			
			<?php  } ?>
			
			
			
			
			
			
			
			
		
		</div>
        </div>
      </div>
    </section>
    <!-- Testimonials End -->



    <!-- Subscribe -->
    <section class="section subscribe-section">
      <div class="container">
        <div class="subscribe-wrapper">
          <div class="subscribe-copy">
            <span class="subscribe-badge">ניוזלטר</span>
            <h3>הרשמה לרשימת תפוצה</h3>
            <p>
              הירשם עכשיו לרשימת התפוצה שלנו לקבלת עדכונים
            </p>
          </div>
          <form class="subscribe-form">
            <div class="input-group">
              <span class="input-group-text"
                ><i class="fa fa-envelope" aria-hidden="true"></i
              ></span>
              <input
                type="email"
                class="form-control"
                placeholder="כתובת האימייל שלך"
                required
              />
              <button class="btn btn-primary" type="submit">הרשם</button>
            </div>
            <small class="subscribe-note"
              >קבל מבצעים חדשים מעסיקם חמים</small
            >
          </form>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="titleTop text-center">
          <div class="subtitle">כתבות</div>
          <h3>בלוג</h3>
        </div>

        <div class="blog-grid">
		
		
		
				<?php

				$get_articles_list = db_query("SELECT * FROM mod_articles_list ORDER BY id DESC LIMIT 4");
				while ($gal = db_fetch_array($get_articles_list)){
				$get_content = db_query("SELECT * FROM mod_articles WHERE art_id = '".$gal['id']."'");
				$gc = db_fetch_array($get_content);
				?>
          <article class="blog-card">
            <div class="blog-card-media">
              <?php
              $__article_image = 'resources/design/images/coment-avatar-3.jpg';

              if (
                !empty($gal['pic']) &&
                file_exists($_SERVER['DOCUMENT_ROOT'].'/files/articles/'.$gal['pic'])
              ) {
                $__article_image = '/files/articles/'.$gal['pic'];
              }
              ?>

              <img src="<?php echo $__article_image; ?>" alt="<?=$gal['title'];?>" />
              <span class="blog-card-tag"><?=$gal['title'];?></span>
            </div>
            <div class="blog-card-body">
             
              <h3>
                <a href=",articles,art_<?=$gal['id'];?>"
                  ><?=$gal['title'];?></a
                >
              </h3>
              <p>
                <?=$gc['desc'];?>
              </p>
   
            </div>
          </article>
		  
		  
				<?php   } ?>
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
    <script src="resources/design/js/script.js"></script>

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
