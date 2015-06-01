
var vmOpenEdit = Class.create();
vmOpenEdit.prototype = {
    openPopup : function(){
        var category = $('merchandiser.open.category_id');
        var store = $('merchandiser.open.store_id');
        var window_height = screen.height*0.8;
        if (0 != category.value) {
            var form = $('merchandiser.open.form');
            var url = form.action+'?'+category.name+'='+category.value+'&'+store.name+'='+store.value;
            var windowParams = 'menubar=no,location=no,width=1064px,height='+window_height+'px,left=100px,scrollbars=yes';
            window.lastLoadedNode = category.value;
            window.open(url, 'merchandiser_category', windowParams);
        }
    },
    initialize : function(){
        window.lastLoadedNode = null;
        window.refreshed = true;
        Ajax.Responders.register({
            onComplete: function() {
                var category_id = tree.currentNodeId; // fetch category id from category tree
                var store_id = tree.storeId;
                var catID = $('category_id').value; // this contains loaded category id
                if (window.refreshed) {
                    window.refreshed = false;
                }
                $('merchandiser.open.category_id').value = category_id;
                $('merchandiser.open.store_id').value = store_id;
                if (2 >= catID) {
                    $('merchandiser.open.button').addClassName('disabled');
                    $('merchandiser.open.button').disable();
                    $('group_198merchandise_option').disable();
                    $('group_198merchandiser_heroproducts').addClassName('disabled');
                    $('group_198merchandiser_heroproducts').disable();
                    $('automatic_sort').disable();
                    $('emptyAddBtn_cat_').addClassName('disabled');
                    $('emptyAddBtn_cat_').disable();
                } else {
                    $('merchandiser.open.button').removeClassName('disabled');
                    $('merchandiser.open.button').enable();
                    $('group_198merchandise_option').enable();
                    $('group_198merchandiser_heroproducts').removeClassName('disabled');
                    $('group_198merchandiser_heroproducts').enable();
                    $('automatic_sort').enable();
                    $('emptyAddBtn_cat_').removeClassName('disabled');
                    $('emptyAddBtn_cat_').enable();
                }
            }
        });
    
        Event.observe($('merchandiser.open.button'), 'click', function() {
            var vmopenedit = new vmOpenEdit();
            vmopenedit.openPopup();
        });
    }
}