/**
 * функция транслитерации
 */
function fs_transliteration(text){
// Символ, на который будут заменяться все спецсимволы
    var space = '-';
// переводим в нижний регистр
    text = text.toLowerCase();

// Массив для транслитерации
    var transl = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e', 'ж': 'zh',
        'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
        'о': 'o', 'п': 'p', 'р': 'r','с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h',
        'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh','ъ': space, 'ы': 'y', 'ь': space, 'э': 'e', 'ю': 'yu', 'я': 'ya',
        ' ': space, '_': space, '`': space, '~': space, '!': space, '@': space,
        '#': space, '$': space, '%': space, '^': space, '&': space, '*': space,
        '(': space, ')': space,'-': space, '\=': space, '+': space, '[': space,
        ']': space, '\\': space, '|': space, '/': space,'.': space, ',': space,
        '{': space, '}': space, '\'': space, '"': space, ';': space, ':': space,
        '?': space, '<': space, '>': space, '№':space
    };

    var result = '';
    var curent_sim = '';

    for(var i=0; i < text.length; i++) {
        // Если символ найден в массиве то меняем его
        if(transl[text[i]] != undefined) {
            if(curent_sim != transl[text[i]] || curent_sim != space){
                result += transl[text[i]];
                curent_sim = transl[text[i]];
            }
        }
        // Если нет, то оставляем так как есть
        else {
            result += text[i];
            curent_sim = text[i];
        }
    }

    result = TrimStr(result);
    return result;

}

function TrimStr(s) {
    s = s.replace(/^-/, '');
    return s.replace(/-$/, '');
}

// проверяет является ли переменная числом
function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

// проверяет является ли строка JSON объектом
function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        console.log(str);
        return false;
    }
    return true;
}

// очищает от пустых элементов массива
Array.prototype.clean = function(deleteValue) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == deleteValue) {
            this.splice(i, 1);
            i--;
        }
    }
    return this;
};


// открытие модального окна
jQuery(document).on('click', "[data-fs-action='modal']", function (e) {
    e.preventDefault();
    var modalId = jQuery(this).attr('href');
    jQuery(modalId).fadeIn();
})
// закрытие модального окна
jQuery(document).on('click', "[data-fs-action='modal-close']", function (e) {
    e.preventDefault();
    var modalParentlId = jQuery(this).parents('.fs-modal');
    jQuery(modalParentlId).fadeOut();
})
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
                        jQuery("[data-fs-element=\"old_price\"]").parent().css('visibility','hidden');
                        jQuery("[data-fs-element=\"discount\"]").parent().css('visibility','hidden');
                    }else{
                        jQuery("[data-fs-element=\"base_price\"]").text(json.base_price);
                        jQuery("[data-fs-element=\"old_price\"]").parent().css('visibility','visible');
                        jQuery("[data-fs-element=\"discount\"]").parent().css('visibility','visible');
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
//добавление товара в корзину (сессию)
jQuery('[data-action=add-to-cart]').on('click', function (event) {
    event.preventDefault();

    // проверяем выбрал ли пользователь обязательные атибуты товара, например размер
    var fsAttrReq = true;
    jQuery('[name="fs-attr"]').each(function () {
        if (jQuery(this).val() == '') {
            fsAttrReq = false;
            // создаём событие
            var no_selected_attr = new CustomEvent("fs_no_selected_attr");
            document.dispatchEvent(no_selected_attr);
        }
    });

    if (!fsAttrReq) return fsAttrReq;

    var curent = jQuery(this);
    var product_id = curent.data('product-id');
    var attr = curent.data('attr');

    // объект передаваемый в события
    var detail = {
        button: curent,
        id: product_id,
        name: curent.data('product-name'),
        attr: attr,
        image:curent.data('image'),
        success: true,
        text: {
            success: curent.data('success'),
            error: curent.data('error')
        }
    }


    var productObject = {
        "action": 'add_to_cart',
        "attr": attr,
        'post_id': product_id
    };
    jQuery.ajax({
        url: FastShopData.ajaxurl,
        data: productObject,
        beforeSend: function () {
            // создаём событие
            var before_add_product = new CustomEvent("fs_before_add_product", {
                detail: detail
            });
            document.dispatchEvent(before_add_product);
            return before_add_product.success;
        }
    })
        .done(function (result) {
            // создаём событие
            var add_to_cart = new CustomEvent("fs_add_to_cart", {
                detail: detail
            });
            document.dispatchEvent(add_to_cart);
        });

});
//добавление товара в список желаний
jQuery('[data-fs-action="wishlist"]').on('click', function (event) {
    event.preventDefault();
    var product_id = jQuery(this).data('product-id');
    var product_name = jQuery(this).data('name');
    var curentBlock = jQuery(this);
    jQuery.ajax({
        url: FastShopData.ajaxurl,
        data: {action: 'fs_addto_wishlist', product_id: product_id},
        beforeSend: function () {
            // генерируем событие добавления в список желаний
            var before_to_wishlist = new CustomEvent("fs_before_to_wishlist", {
                detail: {
                    id: product_id,
                    image:curentBlock.data('image'),
                    name: product_name,
                    button: curentBlock
                }
            });
            document.dispatchEvent(before_to_wishlist);
        }
    })
        .done(function (result) {
            var ajax_data = jQuery.parseJSON(result);
            // генерируем событие добавления в список желаний
            var add_to_wishlist = new CustomEvent("fs_add_to_wishlist", {
                detail: {id: product_id, name: product_name, button: curentBlock, image:curentBlock.data('image'), ajax_data: ajax_data}
            });
            document.dispatchEvent(add_to_wishlist);

        });
});
// валидация и отправка формы заказа
var validator = jQuery('[name="fs-order-send"]');
jQuery('[data-fs-action="order-send"]').click(function (e) {
        e.preventDefault();
        validator.submit();
    }
)
validator.validate({
    ignore: [],
    submitHandler: function (form) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: validator.serialize(),
            beforeSend: function () {
                jQuery('[data-fs-action="order-send"]').html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader">');
            }
        })
            .done(function (response) {
                jQuery('button[data-fs-action=order-send]').find('.fs-preloader').fadeOut('slow');
                if (!IsJsonString(response)) return false;
                var jsonData = JSON.parse(response);
                /* если статус заказ успешный */
                if (jsonData.success) {
                    // создаём событие
                    var send_order = new CustomEvent("fs_send_order", {
                        detail: {
                            order_id: jsonData.order_id,
                            sum: jsonData.sum
                        }
                    });
                    document.dispatchEvent(send_order);

                    jQuery('[data-fs-action="order-send"]').html('Отправлено');
                    if (jsonData.redirect != false) document.location.href = jsonData.redirect;
                } else {
                    if (jsonData.error_code != 'undefined') {
                        console.log(jsonData.error_code);
                    }
                    jQuery('[name="fs-order-send"] .fs-form-info').addClass('error').html(jsonData.text).fadeIn();
                    jQuery('[data-fs-action="order-send"]').html('Отправить');
                }
            });


    }
});