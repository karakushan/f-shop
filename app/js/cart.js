// получает корзину через шаблон "cartTemplate"  и выводит её внутри "cartWrap"
function fs_get_cart(cartTemplate, cartWrap) {
    var parameters = {
        action: 'fs_get_cart',
        template: cartTemplate
    };
    jQuery.ajax({
        type: 'POST',
        url: FastShopData.ajaxurl,
        data: parameters,
        dataType: 'html',
        success: function (data) {
            if (data) jQuery(cartWrap).html(data);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log('error...', xhr);
            //error logging
        }
    });
}

//очищаем корзину
jQuery('[data-fs-type="delete-cart"]').on('click', function (event) {
    event.preventDefault();
    if (confirm(fs_message.delete_all_text)) {
        document.location.href = jQuery(this).data('url');
    }
});

//Удаление продукта из корзины
jQuery(document).on('click', '[data-fs-type="product-delete"]', function (event) {
    event.preventDefault();
    var productId = jQuery(this).data('fs-id');
    var productName = jQuery(this).data('fs-name');
    if (confirm(fs_message.delete_text.replace('%s', productName))) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            dataType: 'html',
            data: {
                action: 'delete_product',
                product: productId
            }
        })
            .done(function () {
                location.reload();
            })
            .fail(function () {
                console.log("ошибка удаления товара из корзины");
            })
            .always(function () {

            });
    }
});
