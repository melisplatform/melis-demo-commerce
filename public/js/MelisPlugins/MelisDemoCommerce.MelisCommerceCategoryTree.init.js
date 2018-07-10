var isCategoryTreeAlreadyInitialize = false;
$(function(){
    MelisCommerceCategoryTree_init();

    $("body").on("click", "#cat-treeview ul li a", function() {
        $(this).toggleClass('active');
    });
});

function MelisCommerceCategoryTree_init(pluginId){
    if(!isCategoryTreeAlreadyInitialize) {
        pluginId = (pluginId == undefined) ? "#cat-treeview" : "#" + pluginId + " #cat-treeview ul";

        $(pluginId).treeview({
            animated: "normal",
            collapsed: true,
            unique: true,
        });
        isCategoryTreeAlreadyInitialize = true;
    }

    setTimeout(function(){
        isCategoryTreeAlreadyInitialize = false;
    }, 1000);
}
