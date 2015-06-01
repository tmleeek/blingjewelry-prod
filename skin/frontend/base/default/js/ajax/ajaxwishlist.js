function ajaxCompare(url,id){
	url = url.replace("catalog/product_compare/add","ajax/wishlist/compare");
	url += 'isAjax/1/';
	jQuery('#ajax_compare_loader'+id).show();
	jQuery.ajax( {
		url : url,
		dataType : 'json',
		success : function(data) {
			jQuery('#ajax_compare_loader'+id).hide();
			if(data.status == 'ERROR'){
				alert(data.message);
			}else{
				alert(data.message);
				if(jQuery('.block-compare').length){
                    jQuery('.block-compare').replaceWith(data.sidebar);
                }else{
                    if(jQuery('.col-right').length){
                    	jQuery('.col-right').prepend(data.sidebar);
                    }
                }
			}
		}
	});
}
function ajaxWishlist(url,id){
	url = url.replace("wishlist/index","ajax/wishlist");
	url = url.replace("https","http");
	url += 'isAjax/1/';
	jQuery('#ajax_wishlist_loader'+id).show();
	jQuery.ajax( {
		url : url,
		dataType : 'json',
		success : function(data) {
			jQuery('#ajax_wishlist_loader'+id).hide();
			if(data.status == 'ERROR'){
				alert(data.message);
			}else{
				alert(data.message);
				if(jQuery('.block-wishlist').length){
                    jQuery('.block-wishlist').replaceWith(data.sidebar);
                }else{
                    if(jQuery('.col-right').length){
                    	jQuery('.col-right').prepend(data.sidebar);
                    }
                }
                if(jQuery('.header .links').length){
                    jQuery('.header .links').replaceWith(data.toplink);
                }
			}
		}
	});
}