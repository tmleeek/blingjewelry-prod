var reload_minicart = true;

(function($) {
    $.ajaxSetup({
        cache: false
    });
    
    $.fn.handleMinicartSuccess = function(data) {
        $('.qtip-content').html(data.minicart);
        $('.header-basket').removeClass('dont-reload');
        if (data.messages != '') {
            $('.messages-block').first().html(data.messages);
        }
        $('.header-basket a').html(data.headerbasket);
        
        if (data.redirect != '') {
            window.location = data.redirect;
        } else {
            var height = 74;
            height *= parseInt($('.minicart-max').html());
            
            $('.block-cart .mini-products-list').css('max-height', height + 'px');
        }
    }
    
    $.fn.showMinicart = function() {
        $('.header-basket a').qtip('show');
    }
    
    $.fn.initMinicart = function() {
        if ($('.qtip').length) {
            $('.header-basket a').qtip('destroy');
        }
        
        $('.header-basket a').qtip({
            content: {
                prerender: true,
                text: '<p class="loading">Loading...</p>',
            },
            position: {
                corner: {
                     target: 'bottomRight',
                    tooltip: 'topRight',
                }
            },
            hide: {
                fixed: false,
                when: {
                    event: 'unfocus',
                }
            },
            style: {
                width: 350
            },
            api: {
                onRender: function() {
                    if ($('.qtip-content').find('.loading').length) {
                        if (!$('.header-basket').is('.dont-reload')) {
                            $.ajax({
                                url: $('.minicart-url').html(),
                                dataType: 'json',
                                success: function(data, status, xhr) {
                                    $.fn.handleMinicartSuccess(data);
                                }
                            });
                        }
                    }
                }
            }
        });
    }
    
    $(document).ready(function() {
        $.fn.initMinicart();
        
        $('.add-to-cart p input').attr('onclick', '');
        $('.add-to-cart p input').click(function(event) {
            event.stopPropagation();
            
            if (productAddToCartForm.validator.validate()) {
                var form_data = '';
                $('.header-basket').addClass('dont-reload');
                $.fn.initMinicart();
                $.fn.showMinicart();
                window.scrollTo(100,0);
                
                if ($('#product_addtocart_form').length) {
                    form_data = $('#product_addtocart_form').find("input[type='hidden'], :input:not(:hidden)").serialize();
                }
                
                $.ajax({
                    url: $('.minicart-add-url').html(),
                    dataType: 'json',
                    type: 'POST',
                    data: form_data,
                    success: function(data, status, xhr) {
                        $.fn.handleMinicartSuccess(data);
                    }
                });
            }
            
            return false;
        });
        
        $('.cmsaddtocart').attr('onclick', '');
        $('.cmsaddtocart').click(function() {
            var product_id = '';
            product_id = $(this).parent().parent().parent().find('.regular-price').attr('id');
            product_id = product_id.match(/[0-9]+/);
            
            if (product_id.length) {
                $('.header-basket').addClass('dont-reload');
                $.fn.initMinicart();
                $.fn.showMinicart();
                window.scrollTo(100,0);
                
                product_id = product_id[0];
                
                $.ajax({
                    url: $('.minicart-add-url').html(),
                    dataType: 'json',
                    type: 'POST',
                    data: {'product': product_id},
                    success: function(data, status, xhr) {
                        $.fn.handleMinicartSuccess(data);
                    }
                });
            }
            
            return false;
        });
    });
})( jQuery );
