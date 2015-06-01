// Pixel offset from left to display charms on bracelet
charm_offset = 30;

// Pixel width of a charm slot
charm_slot_width = 46;

// Maximum number of charms selectable at once
charm_max_slots = 14;

// y-axis offset for displaying charm on bracelet
charm_default_offset = 25;

// Position of cursor when dragging charm to bracelet
charm_cursor_x = 75;
charm_cursor_y = 0;



bracelet_selection = {
    'bracelet': {},
    'pendants': []
};

bracelet_slots = {
};

(function($) {
    /*

    You can now create a spinner using any of the variants below:

    $("#el").spin(); // Produces default Spinner using the text color of #el.
    $("#el").spin("small"); // Produces a 'small' Spinner using the text color of #el.
    $("#el").spin("large", "white"); // Produces a 'large' Spinner in white (or any valid CSS color).
    $("#el").spin({ ... }); // Produces a Spinner using your custom settings.

    $("#el").spin(false); // Kills the spinner.

    */

    $.fn.spin = function(opts, color) {
        var presets = {
            "tiny": { lines: 8, length: 2, width: 2, radius: 3 },
            "small": { lines: 8, length: 4, width: 3, radius: 5 },
            "large": { lines: 10, length: 8, width: 4, radius: 8 }
        };
        if (Spinner) {
            return this.each(function() {
                var $this = $(this),
                    data = $this.data();

                if (data.spinner) {
                    data.spinner.stop();
                    delete data.spinner;
                }
                if (opts !== false) {
                    if (typeof opts === "string") {
                        if (opts in presets) {
                            opts = presets[opts];
                        } else {
                            opts = {};
                        }
                        if (color) {
                            opts.color = color;
                        }
                    }
                    if(this.nodeName.toLowerCase() == 'input') {
                        var pos = $this.position();
                        data.spinner = new Spinner($.extend({color: $this.css('color')}, opts)).spin();
                        $(data.spinner.el).css({
                            position: 'absolute',
                            top: pos.top+4+'px',
                            left: pos.left+$this.width()-4+'px'
                        }).insertAfter($this);
                    } else {
                        data.spinner = new Spinner($.extend({color: $this.css('color')}, opts)).spin(this);
                    }
                }
            });
        } else {
            throw "Spinner class not available.";
        }
    };
})(jQuery);

(function( $ ) {
    $.fn.update_bracelet = function() {
        $.fn.check_default_bracelet();
        $.fn.update_active_bracelet();
        $.fn.update_totals();
    }
    
    $.fn.check_default_bracelet = function() {
        if (!bracelet_selection['bracelet'].hasOwnProperty('id')) {
            bracelet_selection['bracelet'] = {
                'id': $('#default_bracelet').attr('data-product-id'),
                'name': $('#default_bracelet').find('.description .name').html(),
                'price': $('#default_bracelet').attr('data-price'),
                'image': $('#default_bracelet').attr('data-image'),
                'thumb': $('#default_bracelet').attr('data-thumb')
            };
        }
    }
    
    $.fn.update_slots = function() {
        bracelet_slots = {}
        for (var p in bracelet_selection['pendants']) {
            if (bracelet_selection['pendants'].hasOwnProperty(p)) {
                var pendant = bracelet_selection['pendants'][p];
                
                if (typeof(pendant['price']) != 'undefined') {
                    if (typeof(pendant['price']) != 'undefined') {
                        slot = pendant['slot'];
                        
                        bracelet_slots[slot] = pendant;
                    }
                }
            }
        }
    }
    
    $.fn.closest_slot = function(x) {
        var closest = -1;
        x -= charm_offset;
        var closest_distance = 9999;
        $.fn.update_slots();
        
        for (var i = 0; i < charm_max_slots; i++) {
            if (!bracelet_slots.hasOwnProperty(i)) {
                var distance = Math.abs(x-(i * charm_slot_width));
                if (distance < closest_distance) {
                    closest_distance = distance;
                    closest = i;
                }
            }
        }
        
        return closest;
    }
    
    $.fn.update_active_bracelet = function() {
        $('.active_bracelet').html("<div class=\"bracelet_view\" style=\"background-image: url("+bracelet_selection['bracelet']['image']+");\"></div>");
        
        $('.selected_bracelet .title').html(bracelet_selection['bracelet']['name']);
        
        for (var p in bracelet_selection['pendants']) {
            if (bracelet_selection['pendants'].hasOwnProperty(p)) {
                var pendant = bracelet_selection['pendants'][p];
                
                if (typeof(pendant) != 'undefined') {
                    if (typeof(pendant['price']) != 'undefined') {
                        $('.active_bracelet').append("<div class=\"pendant\" data-product-id=\""+pendant['id']+"\" data-specific=\""+pendant['specific']+"\" style=\"left: " + ((pendant['slot']*charm_slot_width)+charm_offset) + "px; z-index:"+ pendant['slot']+"; top: " + pendant['offsety'] + "px; background-image: url(" + pendant['image'] + ")\" />");
                    }
                }
            }
        }
        
        $('.active_bracelet .pendant').dblclick(function () {
            $.fn.remove_charm($(this).attr('data-product-id'), $(this).attr('data-specific'));
        });
        
        
        $('.active_bracelet .pendant').draggable({
        });
    }
    
    $.fn.update_totals = function() {
        $('.selected .bracelet').html('<li style="background-image: url('+bracelet_selection['bracelet']['thumb']+')"><span class="price">$'+bracelet_selection['bracelet']['price']+'</span><span class="buttons"><span class="change">Change</span></span></li>');
        $('.selected .pendants').html('');
        
        var total = 0;
        
        total += parseFloat(bracelet_selection['bracelet']['price']);
        
        for (var p in bracelet_selection['pendants']) {
            if (bracelet_selection['pendants'].hasOwnProperty(p)) {
                var pendant = bracelet_selection['pendants'][p];
                
                if (typeof(pendant) != 'undefined') {
                    if (typeof(pendant['price']) != 'undefined') {
                        $('.selected .pendants').append('<li data-product-id="'+pendant['id']+'" data-specific="'+pendant['specific']+'" style="background-image: url('+pendant['thumb']+')"><span class="price">$'+pendant['price']+'</span><span class="buttons"><span class="remove">Remove</span></span></li>');
                    
                        total += parseFloat(pendant['price']);
                    }
                }
            }
        }
        
        $('.subtotal .price').html('$' + (Math.round(total*100)/100));
        
        $('.selected .bracelet .change').on('click', function() {
            $.fn.toggle_bracelets();
            return false;
        });
        
        $('.selected .pendants .remove').on('click', function () {
            $.fn.remove_charm($(this).parent().parent().attr('data-product-id'), $(this).parent().attr('data-specific'));
        });
    }
    
    $.fn.remove_charm = function(id, specific) {
        index = -1;
        
        if (typeof(specific) == 'undefined') {
            specific = 0;
        }
        
        for (var i = 0; i < bracelet_selection['pendants'].length; i++) {
            p = bracelet_selection['pendants'][i];
            
            if (typeof(p) != 'undefined') {
                if (id == p['id']) {
                    if (specific == 0 || specific == p['specific']) {
                        index = i;
                        break;
                    }
                }
            }
        }
        
        if (index != -1) {
            delete bracelet_selection['pendants'][index];
        }
        
        $.fn.update_bracelet();
    }
    
    $.fn.update_controls = function() {
        $('.page-selector').change(function() {
            $('.charm-list').spin();
            
            $.ajax({
                url: $(this).val(),
                dataType: 'json',
                success: function (data, status, xhr) {
                    $('.charm-list').html(data.charmlist);
                    $('.charms .title').html(data.title);
                    $.fn.update_controls();
                    $.fn.update_charms();
                },
                complete: function (status, xhr) {
                    x = 1;
                }
            });
        });
        
        var current_page = parseInt($('.current_page').html());
        var max_page = parseInt($('.max_page').html());
        
        if (current_page < max_page) {
            $('.pages .next').show();
            
            $('.pages .next').click(function() {
                var current_page = $('.current_page').html();
                $('.page-selector').val($('.page-selector').find('option:eq('+current_page+')').attr('value'))
                $('.page-selector').change();
            });
        }
        
        if (current_page > 1) {
            $('.pages .previous').show();
            
            $('.pages .previous').click(function() {
                var current_page = $('.current_page').html();
                $('.page-selector').val($('.page-selector').find('option:eq('+(current_page-2)+')').attr('value'))
                $('.page-selector').change();
            });
        }
    }
    
    $.fn.too_many_charms = function() {
        $('.active_bracelet .too_many').remove();
        $('.active_bracelet').append('<div class="too_many"><span class="head"><span class="title">Notice</span><span class="close">X</span></span><span class="message">Only ' + charm_max_slots + ' charms may be selected at once.</span></div>');
        
        $('.too_many .close').click(function() {
            $('.active_bracelet .too_many').remove();
        });
    }
    
    $.fn.update_charms = function() {
        $('.charms .charm').draggable({
            'helper': function (event) {
                $(this).find('.description').hide();
                $(this).removeClass('active');
                var that = $(this).clone();
                
                that.css('background-image', 'url('+that.attr('data-image')+')');
                that.css('width', '150');
                that.css('height', '150');
                
                return that[0];
            },
            'cursorAt': { left: charm_cursor_x, top: charm_cursor_y }
        });
        
        $('.charms .charm .view').click(function() {
            var content = $(this).parent().parent().parent().find('.charm_view').html();
            
            $.fancybox({
                'padding'        : 0,
                'autoScale'        : false,
                'autoDimensions'        : false,
                'transitionIn'    : 'none',
                'transitionOut'    : 'none',
                'width'        : 930,
                'height'        : 495,
                'content': content
            });
            
            var that = this;
            $('.charm_right').click(function() {
                $(that).parent().find('.add').click();
                $.fancybox.close();
            });
        });
        
        $('.charms .charm').dblclick(function () {
            $(this).find('.add').click();
        });
        
        $('.charms .charm .add').click(function() {
            closest = $.fn.closest_slot(Math.floor((Math.random()*500)+50));
            $.fancybox.close();
            
            if (closest == -1) {
                $.fn.too_many_charms();
            } else {
                var pendant = {
                    'id': $(this).parent().parent().parent().attr('data-product-id'),
                    'name': $(this).parent().parent().parent().find('.description .name').html(),
                    'price': $(this).parent().parent().parent().attr('data-price'),
                    'image': $(this).parent().parent().parent().attr('data-image'),
                    'thumb': $(this).parent().parent().parent().attr('data-thumb'),
                    'slot': closest,
                    'offsety': charm_default_offset,
                    'specific': bracelet_selection['pendants'].length
                };
                
                bracelet_selection['pendants'].push(pendant);
                
                $.fn.update_bracelet();
            }
        });
        
        $('.charms .charm').hover(function() {
            $(this).find('.description').show();
            $(this).addClass('active');
        }, function() {
            $(this).find('.description').hide();
            $(this).removeClass('active');
        });
    }
    
    $.fn.toggle_bracelets = function() {
        if (!$('.bracelets').is(':visible')) {
            $('.bracelets').show('blind');
        } else {
            $('.bracelets').hide('blind');
        }
    }
    
    $.fn.submit_request = function(action, method, values) {
        var form = $('<form/>', {
            action: action,
            method: method
        });
        $.each(values, function() {
            form.append($('<input/>', {
                type: 'hidden',
                name: this.name,
                value: this.value
            }));    
        });
        form.appendTo('body').submit();
    }
    
    $(document).ready(function() {
        if ($('#bracelet_chooser').length) {
            $.fn.update_charms();
            
            $('.selected_bracelet .action').click(function() {
                $.fn.toggle_bracelets();
                return false;
            });
            
            $('.selected_bracelet .clear').click(function() {
                bracelet_selection = {
                    'bracelet': {},
                    'pendants': []
                };
                
                $.fn.update_bracelet();
                return false;
            });
            
            $('.main_section .bracelets .bracelet').click(function() {
                $('.bracelets').hide('blind');
                
                bracelet_selection['bracelet'] = {
                    'id': $(this).attr('data-product-id'),
                    'name': $(this).find('.description .name').html(),
                    'price': $(this).attr('data-price'),
                    'image': $(this).attr('data-image'),
                    'thumb': $(this).attr('data-thumb')
                };
                
                $.fn.update_bracelet();
            });
            
            $('.active_bracelet').droppable({
                drop: function(event, ui) {
                    var closest = $.fn.closest_slot(ui.position.left)
                    
                    if (ui.draggable.is('.active_bracelet .pendant')) {
                        i = ui.draggable.css('z-index');
                        
                        object = bracelet_slots[i];
                        
                        ii = bracelet_selection['pendants'].indexOf(object);
                        
                        bracelet_selection['pendants'][ii]['slot'] = closest;
                        $.fn.update_bracelet();
                    } else {
                        if (closest == -1) {
                            $.fn.too_many_charms();
                        } else {
                            var pendant = {
                                'id': ui.draggable.attr('data-product-id'),
                                'name': ui.draggable.find('.description .name').html(),
                                'price': ui.draggable.attr('data-price'),
                                'image': ui.draggable.attr('data-image'),
                                'thumb': ui.draggable.attr('data-thumb'),
                                'slot': closest,
                                'offsety': charm_default_offset,
                                'specific': bracelet_selection['pendants'].length
                            };
                            
                            bracelet_selection['pendants'].push(pendant);
                            
                            $.fn.update_bracelet();
                        }
                    }
                }
            });
            
            $('.addtocart').click(function () {
                if (typeof(bracelet_selection['bracelet']['id']) != 'undefined') {
                    product_ids = []
                    product_ids.push(bracelet_selection['bracelet']['id']);
                    
                    for (var p in bracelet_selection['pendants']) {
                        if (bracelet_selection['pendants'].hasOwnProperty(p)) {
                            var pendant = bracelet_selection['pendants'][p];
                            
                            if (typeof(pendant) != 'undefined') {
                                if (typeof(pendant['price']) != 'undefined') {
                                    product_ids.push(pendant['id']);
                                }
                            }
                        }
                    }
                    
                    product_ids = product_ids.toString();
                    
                    $.fn.submit_request($('#bracelet_addtocart_url').html(), 'POST', [
                        { name: 'product_ids', value: product_ids }
                    ]);
                }
            });
            
            $('.filters input:checkbox').change(function() {
                var values = [];
                $('.charm-list').spin();
                
                $('.filters').find(':checked').each(function() {
                    values.push($(this).attr('value'));
                });
                
                values = values.toString();
                
                $.ajax({
                    url: $('#bracelet_update_url').html(),
                    data: {'filters': values},
                    dataType: 'json',
                    type: 'GET',
                    success: function (data, status, xhr) {
                        $('.charm-list').html(data.charmlist);
                        $('.charms .title').html(data.title);
                        $.fn.update_controls();
                        $.fn.update_charms();
                    },
                    complete: function (status, xhr) {
                        x = 1;
                    }
                });
            });
            
            $.fn.update_bracelet();
            $.fn.update_controls();
        }
    });
})( jQuery );
