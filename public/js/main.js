/*

Template:  clothing- Responsive Multi-purpose HTML5 Template
Template URI: http://bootexperts.com
Description: This is html5 template
Author: BootExperts
Author URI: http://bootexperts.com
Version: 1.0

*/
/*================================================
[  Table of contents  ]
================================================
	01. jQuery MeanMenu
	02. wow js active
	03. scrollUp jquery active

 
======================================
[ End table content ]
======================================*/


(function($) {
    "use strict";

    /*-------------------------------------------
    	01. jQuery MeanMenu
    --------------------------------------------- */
    jQuery('nav#dropdown').meanmenu();


    /*-------------------------------------------
    	02. wow js active
    --------------------------------------------- */
    new WOW().init();


	/*--------------------------
	 scrollUp
	---------------------------- */	
	$.scrollUp({
        scrollText: "<i class='zmdi zmdi-arrow-merge'></i>",
        easingType: 'linear',
        scrollSpeed: 900,
        animation: 'fade'
    }); 
	
	/*-------------------------------------------
	 Owl carousel 
	--------------------------------------------- */
	var $homepageOwl            = $(".homepage-slider-owl"),
        $aboutUsOwl             = $(".aboutus-slider-owl"),
        $totalTestimonialOwl    = $('.total-testimonial-owl'),
        $latestNewsOwl          = $('.latest-news-owl'),
        $previewLatestNewsOwl   = $('.news-prev-iframe .latest-news-owl'),
        $totalBrandOwl          = $('.total-brand-owl');

        if ( $homepageOwl.length ) {
            $homepageOwl.owlCarousel({
                lazyLoad:true,
                items: 1,
                smartSpeed:1500,
                loop:true,
                autoplay:true,
                autoplayTimeout:8000
            });
        }
        
        if ( $aboutUsOwl.length ) {
            $aboutUsOwl.owlCarousel({
                margin: 30,
                dots: false,
                responsiveClass:true,
                responsive:{
                    0:{
                        items: 1,
                        nav:false
                    },
                    480:{
                        items: 2,
                        nav:false
                    },
                    768:{
                        items: 3,
                        nav:false,
                        dots: true,
                    },
                    1200:{
                        items: 4,
                        nav:false,
                        dots: true,
                    },
                    1400:{
                        items: 4,
                        nav: true,
                        navText: ["<i class='fa fa-angle-left'>","<i class='fa fa-angle-right'>"],
                    }
                }
            });
        }

        if ( $totalTestimonialOwl.length ) {
            $totalTestimonialOwl.owlCarousel({
                items: 1,
            });
        }
        
        if ( $latestNewsOwl.length ) {
            var owlOptions = {
                items: 3,
                margin: 15,
                dots: true,
                nav: true,
                navText: ["<i class='fa fa-angle-left'>","<i class='fa fa-angle-right'>"],
            };

            $latestNewsOwl.owlCarousel(owlOptions);

            /* if ( $(".tab-preview.active").length ) {
                console.log(".tab-preview.active found, load front end owl carousel with responsive class");
                $latestNewsOwl.owlCarousel(owlOptions[0]);
            }
            else {
                console.log(".tab-preview.active not found, load back end owl carousel without responsive class");
                $latestNewsOwl.owlCarousel(owlOptions[1]);
            } */
        }
        
        if ( $totalBrandOwl.length ) {
            $totalBrandOwl.owlCarousel({
                items: 6,
                margin: 15,
                responsiveClass:true,
                responsive:{
                    0:{
                        items:2,
                        dots: true,
                        nav:false
                    },
                    768:{
                        items:4,
                        nav:false
                    },
                    1400:{
                        dots: false,
                        nav: true,
                        navText: ["<i class='fa fa-angle-left'>","<i class='fa fa-angle-right'>"],

                    }
                }
            });
        }

    /*----------------------------
    fancybox active
    ------------------------------ */
    $(function() {
        $('.fancybox').fancybox();
    });
    
    /*************************
     tooltip
    *************************/
    $('[data-toggle="tooltip"]').tooltip({
        animated: 'fade',
        placement: 'top',
        container: 'body'
    });

    /*----------------------------
    Countdown active
    ------------------------------ */
    $('[data-countdown]').each(function () {
        var $this = $(this);
        $this.html("Countdown initializing..");
    });
    
    setTimeout(function(){ 
        $('[data-countdown]').each(function () {
            var $this = $(this);
            var currentYear = new Date().getFullYear() + 1;
            $this.countdown(currentYear+'/10/10', function (event) {
                $this.html(event.strftime('<span class="cdown days"><span class="time-count">%-D</span> <p>Days</p></span><span class="cdown hour"><span class="time-count">%-H</span> <p>Hour</p></span><span class="cdown minutes"><span class="time-count">%M</span> <p>Min</p></span> <span class="cdown second"><span class="time-count">%S</span> <p>Sec</p></span>'));
            });
        });
    }, 30000);
    
    /*----------------------------
    active match height
    ------------------------------ */ 
    $(function() {
        $('.item').matchHeight();
    });

    /*----------------------------
    color filter active
    ------------------------------ */ 
    $('.color-filter li a').on('click', function() {
        $(this).toggleClass('active');
    });

    /*----------------------------
    size filter active
    ------------------------------ */ 
    $('.size-filter li a').on('click', function() {
        $(this).toggleClass('active');
    });
    
    
    $('.acc-toggle').on('click', function(){
        if ($('.acc-toggle input').is(':checked')) {
            $('.create-acc-body').slideDown();
        }else{
            $('.create-acc-body').slideUp();
        }
    });
        
    $('.ship-toggle').on('click', function(){
        if ($('.ship-toggle input').is(':checked')) {
            $('.ship-acc-body').slideDown();
        }else{
            $('.ship-acc-body').slideUp();
        }
    });
})(jQuery);

$(window).on("scroll", function() {
	if ($(this).scrollTop() > 1){ 
		$('#sticky-header').addClass("sticky");
	  }
	  else{
		$('#sticky-header').removeClass("sticky");
	  }
});


