function ampromo_init()
{
    var itemsNode = $('ampromo-items');

    if (!itemsNode)
        return;

    $$('#ampromo-items form').each(function(e){
        var validation = new Validation(e, {
            onFormValidate: function(res, form){
                if (res)
                {
                    $$('#ampromo-items button').each(function(element){
                        element.setAttribute('disabled', 'disabled');
                    });
                }
            }});
        e.down('.button.add').observe('click', function(){
            if (validation.validate())
                this.up('form').submit();
        });
    });

    $$('.bundle-option-select option,' +
    '.product-custom-option option').each(function(element){
            element.text = element.text.replace(/\s+\+.+$/, '');
    });

    if ($$('.bundle-option-select').length > 0) {
        if (!('bundle' in window)) {
            Object.extend(Product.Bundle.prototype, {
                initialize: function(){},
                changeSelection: function(){},
                reloadPrice: function(){}
                });
            window.bundle = new Product.Bundle({defaultValues: false});
        }
    }


    var overlay = $('ampromo-overlay');

    if (overlay)
    {
        $('ampromo-items-add').down('a').observe('click', ampromo_popup);

        overlay.down('.close').observe('click', function(){
            $('ampromo-overlay').fade();
        });

        overlay.observe('click', function(event){
            if (event.target.id == 'ampromo-overlay')
                $('ampromo-overlay').fade();
        });

        if (itemsNode.hasClassName('carousel'))
        {
            new Carousel('ampromo-carousel-wrapper', $$('#ampromo-carousel-content .slide'), $$('.ampromo-carousel-control'), {
                visibleSlides: 2,
                controlClassName: 'ampromo-carousel-control'
            });
        }
    }

    $('ampromo-items-add').setAttribute('data-initialized', '1');
}

/**
 * Used to fix issues related to ajax reloads
 */

function ampromo_check_initialization(e)
{
    e.stop();
    if ($('ampromo-items-add').readAttribute('data-initialized') === null)
    {
        ampromo_init();
        ampromo_popup();
    }
}

function ampromo_popup()
{
    var overlay = $('ampromo-overlay');

    if (overlay.visible())
        return;

    var items = $('ampromo-items');
    overlay.show();
    centerVertically(items);
    overlay.hide();

    overlay.appear();

    if (items.getStyle('position') == 'static' && $('amscheckout-main'))
        window.scroll(items.offsetLeft, items.offsetTop);
}

function centerVertically(element)
{
    var vpHeight = $(document).viewport.getHeight();
    var height = element.clientHeight;

    var avTop = (vpHeight / 2) - (height / 2);

    if (avTop <= 10)
        avTop = 10;

    element.style.top = avTop+ 'px';
}
