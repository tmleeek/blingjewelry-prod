var searchJS = Class.create();
searchJS.prototype = {
    initialize : function(){
        
    },
    submitSearch : function(form){
        var query = $('search').value;
        this.sendAjax(form.action+'?name='+query, form.method, 'search-results');
    },
    sendAjax : function(action,method,output_id){
        if (!method) { 
            method = 'post'; 
        }
        if (!output_id) {
            output_id = 'searchRequestResult';
        }
        var req = new Ajax.Request(action, {
            'method' : method,
            'onSuccess' : function(transport) {
                var searchjs = new searchJS();
                searchjs.hideLoader();
                $(output_id).update(transport.responseText);
                var merJSObj = new merchandiserJS();
                merJSObj.hideDuplicates();
                merJSObj.observeCategoryAdd();
                affectResultedProducts();
            },
            'onFailure' : function(transport) {
                var searchjs = new searchJS();
                searchjs.hideLoader();
                $(output_id).update(transport.responseText);
            }
        });
        this.showLoader();
        $(output_id).childElements().each( function(item) {
            item.remove();
        });
    },
    loadFeature : function(action){
        var req = new Ajax.Request(action, {
            method : 'post',
            parameters: {
                show: 'intro'
            },
            onSuccess : function(transport) {
                var searchjs = new searchJS();
                searchjs.hideLoader();
                $('featureInfo').update(transport.responseText);
            }
        });
        this.showLoader();
    },
    showLoader : function(sMaskId) {
        if (!sMaskId) {
            sMaskId = 'loading-mask';
        }
        $(sMaskId).style.display = 'block';
    },
    hideLoader : function() {
        $('loading-mask').style.display = 'none';
    }
}