$(function(){
    MelisCommerceRelatedProductsPlugin_init();
});

function MelisCommerceRelatedProductsPlugin_init(pluginId){
    pluginId = (pluginId == undefined) ? ".related-product-slider" : "#"+pluginId;

    $(pluginId).slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: false,
        autoplaySpeed: 5000,
        dots: false,
        arrows: true,
        prevArrow: '<div class="arrow-left"><i class="zmdi zmdi-chevron-left"></i></div>',
        nextArrow: '<div class="arrow-right"><i class="zmdi zmdi-chevron-right"></i></div>',
        responsive: [
            {  breakpoint: 1169,  settings: { slidesToShow: 4,  }  },
            {  breakpoint: 969,   settings: { slidesToShow: 3,  }  },
            {  breakpoint: 767,   settings: { slidesToShow: 2, }   },
            {  breakpoint: 479,   settings: { slidesToShow: 1, }   },
        ]
    });
}