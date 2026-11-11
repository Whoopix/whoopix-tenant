"use strict";
/* ==== Jquery Functions ==== */
(function($) {
  $(document).ready(function () {
    // Hero Revolution Slider
    var $heroRev = $('#hero_rev_slider');
    if ($heroRev.length && typeof $heroRev.revolution === 'function') {
      $heroRev.show().revolution({
        sliderType: 'standard',
        sliderLayout: 'auto',
        delay: 4000,
        navigation: {
          arrows: { enable: false },
          bullets: { enable: true, hide_onmobile: false }
        },
        responsiveLevels: [1200, 992, 768, 576],
        visibilityLevels: [1200, 992, 768, 576],
        gridwidth: [600, 600, 600, 600],
        gridheight: [600, 600, 600, 600],
        disableProgressBar: 'on',
        lazyType: 'none'
      });
    }

    var $catCarousel = $('.category-carousel');
    if ($catCarousel.length) {
      $catCarousel.owlCarousel({
        loop: true,
        margin: 24,
        nav: false,
        dots: false,
        autoplay: false,
        responsive: {
          0: { items: 1.2, stagePadding: 20 },
          576: { items: 2 },
          768: { items: 3 },
          992: { items: 4 },
          1200: { items: 5 }
        }
      });
      $('.category-prev').on('click', function () {
        $catCarousel.trigger('prev.owl.carousel');
      });
      $('.category-next').on('click', function () {
        $catCarousel.trigger('next.owl.carousel');
      });
    }

    var $testimonialCarousel = $('.testimonials-carousel');
    if ($testimonialCarousel.length) {
      $testimonialCarousel.owlCarousel({
        loop: true,
        margin: 24,
        nav: false,
        dots: true,
        autoplay: false,
        responsive: {
          0: { items: 1 },
          768: { items: 2 },
          1200: { items: 3 }
        }
      });
      $('.testimonials-prev').on('click', function () {
        $testimonialCarousel.trigger('prev.owl.carousel');
      });
      $('.testimonials-next').on('click', function () {
        $testimonialCarousel.trigger('next.owl.carousel');
      });
    }

    var $companyCarouselV2 = $('.company-carousel-v2');
    if ($companyCarouselV2.length) {
      $companyCarouselV2.owlCarousel({
        loop: true,
        margin: 20,
        nav: false,
        dots: false,
        autoplay: false,
        responsive: {
          0: { items: 1.3, stagePadding: 20 },
          576: { items: 2.3, stagePadding: 24 },
          768: { items: 3.3, stagePadding: 24 },
          992: { items: 4 },
          1200: { items: 4 }
        }
      });
      $('.company-prev-v2').on('click', function () {
        $companyCarouselV2.trigger('prev.owl.carousel');
      });
      $('.company-next-v2').on('click', function () {
        $companyCarouselV2.trigger('next.owl.carousel');
      });
    }
  });
})(jQuery);