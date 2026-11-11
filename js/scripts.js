$(document).ready(function() {
    "use strict";

    // Superfish dropdowns (CMS submenus under .sf-menu).
    // No animation — height/opacity inline styles were leaving submenus invisible.
    var $sf = $("ul.sf-menu");
    if ($sf.length && typeof $.fn.superfish === "function") {
        $sf.superfish({
            delay: 120,
            animation: {},
            speed: 0,
            autoArrows: false,
            dropShadows: false,
            disableHI: true
        });
    }

    // Accordion.
    var oa = $( ".accordion" );
    if (oa.length > 0) {
        oa.accordion({
            heightStyle: "content"
        });
    }

    /*----------------------------------------------------*/
    /* MOBILE DETECT FUNCTION
     /*----------------------------------------------------*/
    var isMobile = {
        Android: function() {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function() {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function() {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function() {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function() {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function() {
            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
        }
    };


  /*----------------------------------------------------*/
    // carouFredSel.
  /*----------------------------------------------------*/
    var ob = $('#clients').find('.carousel.main ul');
    if (ob.length > 0) {
        ob.carouFredSel({
            auto: {
                timeoutDuration: 8000
            },
            responsive: true,
            prev: '.clients_prev',
            next: '.clients_next',
            width: '100%',
            scroll: {
                // fx : "crossfade",
                items: 1,
                duration: 1000,
                easing: "easeOutExpo"
            },
            items: {
                width: '600',
                height: 'variable', //  optionally resize item-height
                visible: {
                    min: 1,
                    max: 2
                }
            },
            mousewheel: false,
            swipe: {
                onMouse: true,
                onTouch: true
            }
        });
    }

    var oc = $('#location').find('.carousel.main ul');
    if (oc.length > 0) {
        oc.carouFredSel({
            auto: {
                timeoutDuration: 8000
            },
            responsive: true,
            pagination: '.location_pagination',
            width: '100%',
            scroll: {
                // fx : "crossfade",
                items: 1,
                duration: 1000,
                easing: "easeOutExpo"
            },
            items: {
                width: '600',
                height: 'variable', //  optionally resize item-height
                visible: {
                    min: 1,
                    max: 1
                }
            },
            mousewheel: false,
            swipe: {
                onMouse: true,
                onTouch: true
            }
        });
    }

    var o = $('#meet').find('.carousel.main ul');
    if (o.length > 0) {
        o.carouFredSel({
            auto: {
                timeoutDuration: 8000
            },
            responsive: true,
            prev: '.meet_prev',
            next: '.meet_next',
            width: '100%',
            scroll: {
                // fx : "crossfade",
                items: 1,
                duration: 1000,
                easing: "easeOutExpo"
            },
            items: {
                width: '450',
                height: 'variable', //  optionally resize item-height
                visible: {
                    min: 1,
                    max: 4
                }
            },
            mousewheel: false,
            swipe: {
                onMouse: true,
                onTouch: true
            }
        });
    }


    $(window).on("resize",updateSizes_vat).on("load",updateSizes_vat);
    function updateSizes_vat(){
        $('#clients').find('.carousel.main ul').trigger("updateSizes");
        $('#location').find('.carousel.main ul').trigger("updateSizes");
        $('#meet').find('.carousel.main ul').trigger("updateSizes");
    }
    updateSizes_vat();


  /*----------------------------------------------------*/
    // Sticky.
  /*----------------------------------------------------*/
    $("#top2").sticky({
        topSpacing:0,
        getWidthFrom: 'body',
        responsiveWidth: true
    });





  /*----------------------------------------------------*/
    // PARALLAX CALLING
  /*----------------------------------------------------*/
    $(window).on('load', function () {
        parallaxInit();
    });
    function parallaxInit() {
        var testMobile;
        testMobile = isMobile.any();

        if (testMobile == null)
        {
            $('.parallax .bg1').addClass("bg-fixed").parallax("50%", 0.5);


        }
    }
    parallaxInit();

  /*----------------------------------------------------*/
    // Superslides
  /*----------------------------------------------------*/
  
  
    $('#slides').superslides({
        play: 7000,
        animation: 'fade', // slide
        pagination: false,
        inherit_height_from: '#home'
    });






  /*----------------------------------------------------*/
    // MENU SMOOTH SCROLLING
  /*----------------------------------------------------*/
    $(".navbar_ .nav a, .menu_bot a, .scroll-to").on('click',function(event){
        var href = $(this).attr("href") || "";
        if (href.indexOf("#") === -1 || href === "#" || href.indexOf("/#") === 0) {
            if (href.charAt(0) !== "#" || href.length <= 1) {
                return;
            }
        }
        var hash = href.indexOf("#") >= 0 ? href.substring(href.indexOf("#")) : href;
        if (hash.charAt(0) !== "#" || $(hash).length === 0) {
            return;
        }
        var headerH = $('#top2').outerHeight();
        if (hash === "#home") {
            $("html, body").animate({
                scrollTop: 0 + 'px'
            }, {
                duration: 1200,
                easing: "easeInOutExpo"
            });
        } else {
            $("html, body").animate({
                scrollTop: $(hash).offset().top - headerH + 'px'
            }, {
                duration: 1200,
                easing: "easeInOutExpo"
            });
        }
        event.preventDefault();
    });

  /*----------------------------------------------------*/
    // Appear
  /*----------------------------------------------------*/
    $('.animated').appear(function() {
        // console.log("111111111111");
        var elem = $(this);
        var animation = elem.data('animation');
        if ( !elem.hasClass('visible') ) {
            var animationDelay = elem.data('animation-delay');
            if ( animationDelay ) {
                setTimeout(function(){
                    elem.addClass( animation + " visible" );
                }, animationDelay);
            } else {
                elem.addClass( animation + " visible" );
            }
        }
    });

    // Animate number
    $('.animated-number').appear(function() {
        var elem = $(this);
        var b = elem.text();
        var d = elem.data('duration');
        var animationDelay = elem.data('animation-delay');
        if ( !animationDelay ) { animationDelay = 0; }
        elem.text("0");

        setTimeout(function(){
            elem.animate({ num: b }, {
                duration: d,
                step: function (num){
                    this.innerHTML = (num).toFixed(0)
                }
            });
        }, animationDelay);
    });






});

/////////////////////// load
$(window).load(function() {



});