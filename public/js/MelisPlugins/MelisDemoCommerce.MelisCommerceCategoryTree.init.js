$(function(){
    MelisCommerceCategoryTree_init();

    $("body").on("click", "#cat-treeview ul li a", function() {
        $(this).toggleClass('active');
    });
});

function MelisCommerceCategoryTree_init(pluginId){
    pluginId = (pluginId == undefined) ? ".product-cat-treeview" : "#" + pluginId + " .product-cat-treeview ul";

    $(pluginId).treeview({
        animated: "normal",
        collapsed: true,
        unique: true
    });
}
