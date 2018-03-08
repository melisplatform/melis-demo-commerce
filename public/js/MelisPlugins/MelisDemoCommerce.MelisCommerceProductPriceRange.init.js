$(function(){
    MelisCommerceProductPriceRange_init();
});

function MelisCommerceProductPriceRange_init(pluginId){
    pluginId = (pluginId == undefined) ? "#slider-range" : "#"+pluginId+" #slider-range" ;
    /*----------------------------
     price-slider active
    ------------------------------ */
    $(pluginId).slider({
        range: true,
        min: $( pluginId ).data('defaultmin'),
        max: $( pluginId ).data('defaultmax'),
        values: [ $( pluginId ).data('min'), $( pluginId ).data('max') ],
        slide: function( event, ui ) {
            $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
        }
    });
    $( "#amount" ).val( "$" + $( pluginId ).slider( "values", 0 ) + " - $" + $( pluginId ).slider( "values", 1 ) );
}