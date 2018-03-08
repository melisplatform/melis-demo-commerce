/**
 * Init function for the plugin to be rendered
 * This function is used when reloading a plugin in the back office after
 * changing parameters or drag/dropping it.
 * This function is automatically called and must be nammed PluginName_init
 * It will always receive the id of the plugin as a parameter, in case multiple
 * same plugin are on the page.
 */

function MelisCommerceCategoryProductListPlugin_init(idPlugin){
	
	idPlugin = typeof idPlugin != "undefined" ? "#"+idPlugin+" .category-productlist-list-slider" : ".category-productlist-list-slider";
	
	if($(idPlugin).length){
		
		$(idPlugin).not(".slick-initialized").slick({
			slidesToShow: 3,
			slidesToScroll: 1,
			autoplay: false,
			autoplaySpeed: 5000,
			dots: false,
			arrows: true,
			prevArrow: '<div class="arrow-left"><i class="zmdi zmdi-chevron-left"></i></div>',
			nextArrow: '<div class="arrow-right"><i class="zmdi zmdi-chevron-right"></i></div>',
			responsive: [
				{breakpoint: 1169,  settings: { slidesToShow: 4 }},
				{breakpoint: 969,   settings: { slidesToShow: 3 }},
				{breakpoint: 767,   settings: { slidesToShow: 2 }},
				{breakpoint: 479,   settings: { slidesToShow: 1 }},
			]
		});
	}
}

$(function(){
	MelisCommerceCategoryProductListPlugin_init();
});