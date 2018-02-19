// Квантификатор товара
jQuery(document).ready(function (jQuery) {
    // уменьшение к-ва товара на единицу
    jQuery(document).on('click', '[data-fs-count="minus"]', function () {
        var jQueryinput = jQuery(jQuery(this).data('target'));
        var count = parseInt(jQueryinput.val()) - 1;
        count = count < 1 ? 1 : count;
        jQueryinput.val(count);
        jQueryinput.change();
        return false;
    });

    // увеличение к-ва товара на единицу
    jQuery(document).on('click', '[data-fs-count="pluss"]', function () {
        var jQueryinput = jQuery(jQuery(this).data('target'));
        jQueryinput.val(parseInt(jQueryinput.val()) + 1);
        jQueryinput.change();
        return false;
    });

    //Изменение к-ва добавляемых продуктов
    jQuery('[data-fs-action="change_count"]').on('change input', function (event) {
        event.preventDefault();
        /* Act on the event */
        var productId = jQuery(this).data('fs-product-id');
        var count = jQuery(this).val();
        if (count < 1) {
            jQuery(this).val(1);
            count = 1;
        }
        var cartButton = jQuery('#fs-atc-' + productId);
        var cartButtonAttr = cartButton.data('attr');
        cartButtonAttr.count = count;
        cartButton.attr('data-attr', JSON.stringify(cartButtonAttr));
        // создаём событие
        var change_count = new CustomEvent("fs_change_count", {
            detail: {count: count}
        });
        document.dispatchEvent(change_count);
    });
});


//Изменение количества продуктов в корзине
jQuery(document).ready(function (jQuery) {
    jQuery(document).on('change input', '[data-fs-type="cart-quantity"]', function (event) {
        event.preventDefault();
        var productId = jQuery(this).data('product-id');
        var productCount = jQuery(this).val();

        //если покупатель вбил неправильное к-во товаров
        if (!isNumeric(productCount) || productCount <= 0) {
            jQuery(this).val(1);
            productCount = 1;
            jQuery(this).parent().css({'position': 'relative'});
            jQuery(this).prev('.count-error').text(fs_message.count_error).fadeIn(400);
        } else {
            jQuery(this).prev('.count-error').text('').fadeOut(800);
        }

        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_cart',
                product: productId,
                count: productCount
            }
        })
            .done(function (result) {
                try {
                    var json = JSON.parse(result);
                    if (json.status) {
                        jQuery("[data-fs-element=\"total-amount\"]").text(json.total);
                        // создаём событие
                        var cart_change_count = new CustomEvent("fs_cart_change_count", {
                            detail: {count: productCount, total: json.total}
                        });
                        document.dispatchEvent(cart_change_count);
                    }
                } catch (e) {
                    console.log('Ошибка ' + e.name + ":" + e.message + "\n" + e.stack);
                }
            });


    });
});