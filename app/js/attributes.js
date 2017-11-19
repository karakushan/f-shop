// изменяем атрибуты товара по изменению input radio
jQuery('[data-action="change-attr"]').on('change', function () {

    var curent = jQuery(this);// получаем элемент который был изменён
    var productId = curent.data('product-id');// получаем ID товара из атрибутов
    var atcButton = jQuery('#fs-atc-' + productId); // получаем кнопку добавить в корзину
    var attrObj = atcButton.data('attr');// получаем  атрибуты кнопки "добавить в корзину" в виде объекта
    var variated = atcButton.data('variated');// узнаём вариативный товар или нет

    // выключаем чекбоксы всей группе атрибутов
    attrObj.attr = [];
    jQuery('[name="' + curent.attr('name') + '"]').each(function (index) {
        jQuery(this).prop('checked', false);
    });
    // делаем активным чекбокс на который нажали
    curent.prop('checked', true);

    jQuery('[data-action="change-attr"]').each(function (index) {
        if (jQuery(this).prop('checked') && jQuery(this).val()) {
            attrObj.attr[index] = jQuery(this).val();
        }
    });

    // производим очистку массыва атрибутов от пустых значений
    attrObj.attr.clean(undefined);

    // делаем аякс запрос для получения вариативной цены и подмены на сайте
    if (variated) {
        jQuery.ajax({
            type: 'POST',
            url: FastShopData.ajaxurl,
            data: {action: "fs_get_variated", product_id: productId, atts: attrObj.attr},
            success: function (data) {
                if (IsJsonString(data)) {
                    var json = jQuery.parseJSON(data);
                    if (json.result) {
                        jQuery("[data-fs-element=\"base_price\"]").text(json.base_price);
                        attrObj.count = json.count;
                        jQuery("[data-fs-element=\"old_price\"]").parent().css('visibility', 'hidden');
                        jQuery("[data-fs-element=\"discount\"]").parent().css('visibility', 'hidden');
                    } else {
                        jQuery("[data-fs-element=\"base_price\"]").text(json.base_price);
                        jQuery("[data-fs-element=\"old_price\"]").parent().css('visibility', 'visible');
                        jQuery("[data-fs-element=\"discount\"]").parent().css('visibility', 'visible');
                    }
                } else {
                    console.log(data);
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            },
            complete: function () {
                //afer ajax call is completed
            }
        });

    }

    jQuery('#fs-atc-' + productId).attr('data-attr', JSON.stringify(attrObj));
});