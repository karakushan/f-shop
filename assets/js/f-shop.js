var fs_message;
var event;
// переводы сообщений
var FastShopLang = {
    uk: {
        confirm_text: "Вы точно хочете видалити позицію «%s» із списку бажань?",
        wishText: "Товар успішно доданий в список бажань!",
        delete_text: "Вы точно хочете видалити товар «%s» із кошика?",
        delete_all_text: "Ви точно хочете видалити всі товари із кошика?",
        count_error: "к-сть товарів не може бути меньше 1"


    },
    ru_RU: {
        confirm_text: "Вы точно хотите удалить позицию «%s» из списка желаний?",
        wishText: "Товар успешно добавлен в список желаний!",
        delete_text: "Вы точно хотите удалить продукт «%s» из корзины?",
        delete_all_text: "Вы точно хотите удалить все товары из корзины?",
        count_error: "к-во товаров не может быть меньше единицы"

    }

};
//переключатель сообщений в зависимости от локали
switch (FastShopData.fs_lang) {
    case "ru_RU":
        fs_message = FastShopLang.ru_RU;
        break;
    case "uk":
        fs_message = FastShopLang.uk;
        break;
    default:
        fs_message = FastShopLang.ru_RU;
}

/**
 * функция транслитерации
 */
function fs_transliteration(text) {
// Символ, на который будут заменяться все спецсимволы
    var space = '-';
// переводим в нижний регистр
    text = text.toLowerCase();

// Массив для транслитерации
    var transl = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e', 'ж': 'zh',
        'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
        'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h',
        'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': space, 'ы': 'y', 'ь': space, 'э': 'e', 'ю': 'yu', 'я': 'ya',
        ' ': space, '_': space, '`': space, '~': space, '!': space, '@': space,
        '#': space, '$': space, '%': space, '^': space, '&': space, '*': space,
        '(': space, ')': space, '-': space, '\=': space, '+': space, '[': space,
        ']': space, '\\': space, '|': space, '/': space, '.': space, ',': space,
        '{': space, '}': space, '\'': space, '"': space, ';': space, ':': space,
        '?': space, '<': space, '>': space, '№': space
    };

    var result = '';
    var curent_sim = '';

    for (var i = 0; i < text.length; i++) {
        // Если символ найден в массиве то меняем его
        if (transl[text[i]] != undefined) {
            if (curent_sim != transl[text[i]] || curent_sim != space) {
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
Array.prototype.clean = function (deleteValue) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == deleteValue) {
            this.splice(i, 1);
            i--;
        }
    }
    return this;
};

// установка куки
function setCookie(name, value, options) {
    options = options || {};

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires * 1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for (var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
            updatedCookie += "=" + propValue;
        }
    }

    document.cookie = updatedCookie;
}

// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

// удаление куки
function deleteCookie(name) {
    setCookie(name, "", {
        expires: -1
    })
}

/**
 * Add a URL parameter (or changing it if it already exists)
 * @param {search} string  this is typically document.location.search
 * @param {key}    string  the key to set
 * @param {val}    string  value
 */
var addUrlParam = function (search, key, val) {
    var newParam = key + '=' + val,
        params = '&' + newParam;

    // If the "search" string exists, then build params from it
    if (search) {
        // Try to replace an existance instance
        params = search.replace(new RegExp('([?&])' + key + '[^&]*'), 'jQuery1' + newParam);

        // If nothing was replaced, then add the new param to the end
        if (params === search) {
            params += '&' + newParam;
        }
    }

    return params;
};


//добавление товара в корзину (сессию)
jQuery(document).on('click', '[data-action=add-to-cart]', function (event) {
    event.preventDefault();


    var curent = jQuery(this);
    var product_id = curent.data('product-id');
    var count = curent.attr('data-count');

    // подтягиваем атрибуты товаров
    var attr = {};
    jQuery('[data-fs-element="attr"]').each(function () {
        if (jQuery(this).data("product-id") == product_id && jQuery(this).prop("checked")) {
            attr[jQuery(this).attr("name")] = jQuery(this).val();
        }
    });


    // объект передаваемый в события
    var detail = {
        button: curent,
        id: product_id,
        name: curent.data('product-name'),
        price: curent.data('price'),
        currency: curent.data('currency'),
        sku: curent.data('sku'),
        category: curent.data('category'),
        count: count,
        attr: attr,
        image: curent.data('image'),
        success: true,
        text: {
            success: curent.data('success'),
            error: curent.data('error')
        }
    };


    var productObject = {
        "action": 'add_to_cart',
        "attr": attr,
        "count": count,
        'post_id': product_id
    }

    jQuery.ajax({
        url: FastShopData.ajaxurl,
        data: productObject,
        type: "POST",
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

// Событие срабатывает перед добавлением товара в корзину
document.addEventListener("fs_before_add_product", function (event) {
    // действие которое инициирует событие, здесь может быть любой ваш код
    var button = event.detail.button;
    button.find('.fs-atc-preloader').fadeIn().html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader" width="16">');
    event.preventDefault();
}, false);

// Событие срабатывает когда товар добавлен в корзину
document.addEventListener("fs_add_to_cart", function (event) {

    // действие которое инициирует событие
    fs_get_cart('cart-widget/widget', '[data-fs-element="cart-widget"]');
    var button = event.detail.button;
    iziToast.show({
        image: event.detail.image,
        theme: 'light',
        message: button.data("success-message"),
        position: 'topCenter',

    });
    button.find('.fs-atc-preloader').fadeOut();
    setTimeout(function () {
        button.find('.fs-atc-info').fadeOut();
    }, 4000);

    event.preventDefault();
}, false);

// изменяем атрибуты товара по изменению input radio
jQuery('[data-action="change-attr"]').on('change', function () {

    var curent = jQuery(this);// получаем элемент который был изменён
    var productId = curent.data('product-id');// получаем ID товара из атрибутов
    var atcButton = jQuery('#fs-atc-' + productId); // получаем кнопку добавить в корзину
    var attrObj = atcButton.data('attr');// получаем  атрибуты кнопки "добавить в корзину" в виде объекта
    var variated = atcButton.data('variated');// узнаём вариативный товар или нет
    var parent = curent.parents("[data-fs-type=\"product-item\"]");// обёртка для одной позиции товара


    // выключаем чекбоксы всей группе атрибутов
    attrObj = [];
    jQuery('[name="' + curent.attr('name') + '"]').each(function (index) {
        jQuery(this).prop('checked', false);
    });
    // делаем активным чекбокс на который нажали
    curent.prop('checked', true);

    // добавляем значения выбраных элементов в data-attr нашей кнопки "в корзину"
    parent.find('[data-action="change-attr"]').each(function (index) {
        // если это радио кнопки выбора атрибутов
        if (jQuery(this).data("product-id") == productId && jQuery(this).val()) {
            if (jQuery(this).attr("type") == "radio" && jQuery(this).prop("checked")) {
                attrObj[index] = jQuery(this).val();
            }
            // если это не радио кнопки выбора атрибутов
            if (jQuery(this).attr("type") != "radio") {
                attrObj[index] = jQuery(this).val();
            }
        }
    });

    // производим очистку массыва атрибутов от пустых значений
    attrObj.clean(undefined);

    // делаем аякс запрос для получения вариативной цены и подмены на сайте
    if (variated) {
        jQuery.ajax({
            type: 'POST',
            url: FastShopData.ajaxurl,
            data: {action: "fs_get_variated", product_id: productId, atts: attrObj},
            beforeSend: function () {
                parent.find("[data-fs-element=\"price\"]").addClass('blink');
            },
            success: function (data) {
                if (IsJsonString(data)) {
                    var json = jQuery.parseJSON(data);
                    // создаём событие "fs_after_change_att"
                    var fs_after_change_att = new CustomEvent("fs_after_change_att", {
                        detail: {
                            el: curent,
                            productId: productId,
                            data: json
                        }
                    });
                    document.dispatchEvent(fs_after_change_att);
                } else {
                    console.log(data);
                }

            }
        });

    }

    jQuery('#fs-atc-' + productId).attr('data-attr', attrObj);
});

//Записываем выбранные характеристики товара в data-attr
jQuery('[data-fs-element="attr"]').on('change input', function (event) {
    event.preventDefault();
    var el = jQuery(this);
    var productId = el.data('product-id');
    var cartbutton = jQuery('.fs-atc-' + productId);
    var productObject = cartbutton.first().data('attr');
    var attrName = el.attr('name');
    var attrVal = el.val();
    productObject[attrName] = Number(attrVal);
    cartbutton.attr('data-attr', JSON.stringify(productObject));
});

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
    if (confirm(jQuery(this).data("confirm"))) {
        document.location.href = jQuery(this).data('url');
    }
});

// Показывает поля адреса при выборе доставки по адресу
jQuery(document).on('change', '[name="fs_delivery_methods"]', function (event) {
    var deliveryId = jQuery(this).val();
    if (!deliveryId.length) deliveryId = 0;

    var parameters = {
        action: 'fs_show_shipping',
        delivery: deliveryId
    };
    jQuery.ajax({
        type: 'POST',
        url: FastShopData.ajaxurl,
        data: parameters,
        success: function (result) {
            try {
                var json = JSON.parse(result);
                if (json.price) {
                    jQuery("[data-fs-element=\"delivery-cost\"]").html(json.price);
                    jQuery("[data-fs-element=\"total-amount\"]").html(json.total);
                    jQuery("[data-fs-element=\"taxes-list\"]").replaceWith(json.taxes);
                }
                if (json.show) {
                    jQuery("#fs-shipping-fields")
                        .html(json.html)
                        .addClass('active')
                        .fadeIn();
                } else {
                    jQuery("#fs-shipping-fields")
                        .html('')
                        .removeClass('active')
                        .fadeOut();
                }
            } catch (e) {
                jQuery("#fs-shipping-fields")
                    .html('')
                    .removeClass('active')
                    .fadeOut();
                console.log('Ошибка ' + e.name + ":" + e.message + "\n" + e.stack);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log('error...', xhr);
            //error logging
        }
    });


});

//Удаление продукта из корзины
jQuery(document).on('click', '[data-fs-type="product-delete"]', function (event) {
    event.preventDefault();
    var el = jQuery(this);
    var item = el.data('cart-item');
    var sendData = {
        action: 'fs_delete_product',
        item: item
    };
    if (confirm(el.data("confirm"))) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: sendData
        }).success(function (result) {
            if (result.success) {
                iziToast.show({
                    theme: 'light',
                    message: result.data.message,
                    position: 'topCenter',

                });
                setTimeout(function () {
                    location.reload();
                }, 5000);
            } else {
                iziToast.show({
                    theme: 'light',
                    message: result.data.message,
                    position: 'topCenter',
                });
            }
        })
        ;
    }
});

//Удаление всех товаров из корзины
jQuery(document).on('click', '[data-fs-element="delete-cart"]', function (event) {
    event.preventDefault();
    var el = jQuery(this);
    var sendData = {
        action: 'fs_delete_cart',
    };
    if (confirm(el.data("confirm"))) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: sendData
        }).success(function (result) {
            if (result.success) {
                iziToast.show({
                    theme: 'light',
                    message: result.data.message,
                    position: 'topCenter',

                });
                setTimeout(function () {
                    location.reload();
                }, 5000);
            } else {
                iziToast.show({
                    theme: 'light',
                    message: result.data.message,
                    position: 'topCenter',
                });
            }
        })
        ;
    }
});

// добавление товара к сравнению
jQuery(document).on('click', '[data-action="add-to-comparison"]', function (event) {
    event.preventDefault();
    var el = jQuery(this);
    jQuery.ajax({
        url: FastShopData.ajaxurl,
        type: 'POST',
        beforeSend: function () {
            el.find('.fs-atc-preloader').fadeIn();
        },
        data: {
            action: 'fs_add_to_comparison',
            product_id: el.data('product-id'),
            product_name: el.data('product-name')
        },
    })
        .success(function (data) {

            iziToast.show({
                theme: 'light',
                title: 'Успех!',
                message: 'Товар добавлен в список  сравнения!',
                position: 'topCenter',

            });
        })
        .done(function (data) {
            el.find('.fs-atc-preloader').fadeOut();
        });
});

// обработка кнопки быстрого заказа
jQuery('[data-fs-action="quick_order_button"]').on('click', function (event) {
    event.preventDefault();
    var pName = jQuery(this).data('product-name');
    var pId = jQuery(this).data('product-id');
    jQuery('[name="fs_cart[product_name]"]').val(pName);
    jQuery('[name="fs_cart[product_id]"]').val(pId);
});

//Переадресовываем все фильтры на значение, которое они возвращают
jQuery('[data-fs-action="filter"]').on('change', function (e) {
    e.preventDefault();
    if (jQuery(this).attr('type') == 'checkbox') {
        if (jQuery(this).prop('checked')) {
            console.log('checked');
            window.location.href = jQuery(this).val();
        } else {
            window.location.href = jQuery(this).data('fs-redirect');
            console.log('not-checked');
        }
    } else {
        window.location.href = jQuery(this).val();
    }
});

// Скрываем результаты при потере фокуса input
jQuery(document).on('click', '.fs-ls-close', function (event) {
    event.preventDefault();
    var searchField = jQuery(this);
    var form = searchField.parents('form');
    form.find('.livesearch-wrapper').fadeOut().html('');
});

// открытие модального окна
jQuery(document).on('click', "[data-fs-action='modal']", function (e) {
    e.preventDefault();
    var modalId = jQuery(this).attr('href');
    jQuery(modalId).fadeIn();
});
// закрытие модального окна
jQuery(document).on('click', "[data-fs-action='modal-close']", function (e) {
    e.preventDefault();
    var modalParentlId = jQuery(this).parents('.fs-modal');
    jQuery(modalParentlId).fadeOut();
});
jQuery("#product_slider").lightGallery();
// слайдер товара
if (typeof fs_lightslider_options != "undefined") {
    jQuery('#product_slider').lightSlider(fs_lightslider_options);
}

// Квантификатор товара
jQuery(document).ready(function (jQuery) {
    // уменьшение к-ва товара на единицу
    jQuery(document).on('click', '[data-fs-count="minus"]', function () {
        var parent = jQuery(this).parents('[data-fs-element="fs-quantity"]');
        var jQueryinput = parent.find('input');
        var count = parseInt(jQueryinput.val()) - 1;
        count = count < 1 ? 1 : count;
        jQueryinput.val(count);
        jQueryinput.change();

        return false;
    });

    // увеличение к-ва товара на единицу
    jQuery(document).on('click', '[data-fs-count="pluss"]', function () {
        var parent = jQuery(this).parents('[data-fs-element="fs-quantity"]');
        var jQueryinput = parent.find('input');
        var maxAttr = jQueryinput.attr("max");
        var max = parseInt(maxAttr);

        if (typeof maxAttr == "undefined") {
            jQueryinput.val(parseInt(jQueryinput.val()) + 1);
            jQueryinput.change();
        } else {
            if (max < parseInt(jQueryinput.val())) {
                jQueryinput.val(parseInt(jQueryinput.val()) + 1);
                jQueryinput.change();
            } else {
                iziToast.show({
                    theme: 'light',
                    message: FastShopData.lang.limit_product,
                    position: 'topCenter',

                });
            }

        }
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
        cartButton.attr('data-count', count);
        // создаём событие
        var change_count = new CustomEvent("fs_change_count", {
            detail: {count: count, productId: productId}
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


//слайдер диапазона цены
var u = new Url;
var p_start = u.query.price_start == undefined ? 0 : u.query.price_start;
var p_end = u.query.price_end == undefined ? FastShopData.fs_slider_max : u.query.price_end;


jQuery('[data-fs-element="range-slider"]').each(function (index, value) {
    var rangeSLider = jQuery(this);
    var sliderWrapper = rangeSLider.parents("[data-fs-element=\"jquery-ui-slider\"]");
    var sliderEnd = sliderWrapper.find('[data-fs-element="range-end"]');
    var sliderStart = sliderWrapper.find('[data-fs-element="range-start"]');
    rangeSLider.slider({
        range: true,
        min: 0,
        max: FastShopData.fs_slider_max,
        values: [p_start, p_end],
        slide: function (event, ui) {
            if (sliderStart.data("currency")) {
                sliderStart.html(ui.values[0] + ' <span>' + FastShopData.fs_currency + '</span>');
            } else {
                sliderStart.html(ui.values[0]);
            }
            if (sliderEnd.data("currency")) {
                sliderEnd.html(ui.values[1] + ' <span>' + FastShopData.fs_currency + '</span>');
            } else {
                sliderEnd.html(ui.values[1]);
            }
            sliderWrapper.find('[data-fs-element="range-start-input"]').val(ui.values[0]);
            sliderWrapper.find('[data-fs-element="range-end-input"]').val(ui.values[1]);
        },
        change: function (event, ui) {

            u.query.fs_filter = FastShopData.fs_nonce;
            u.query.price_start = ui.values[0];
            u.query.price_end = ui.values[1];
            // console.log(u.toString());
            window.location.href = u.toString();


        }
    });

    if (sliderStart.data("currency")) {
        sliderStart.html(p_start + ' <span>' + FastShopData.fs_currency + '</span>');
    } else {
        sliderStart.html(p_start);
    }
    if (sliderEnd.data("currency")) {
        sliderEnd.html(p_end + ' <span>' + FastShopData.fs_currency + '</span>');
    } else {
        sliderEnd.html(p_end);
    }

    sliderWrapper.find('[data-fs-element="range-start-input"]').val(p_start);
    sliderWrapper.find('[data-fs-element="range-end-input"]').val(p_end);
});

jQuery(document).on('input keyup', '[data-fs-element="range-start-input"]', function (event) {
    document.location.href = jQuery(this).data('url') + '&price_start=' + jQuery(this).val();
});
jQuery(document).on('input keyup', '[data-fs-element="range-end-input"]', function (event) {
    document.location.href = jQuery(this).data('url') + '&price_end=' + jQuery(this).val();
});

// валидация формы редактирования личных данных
var userInfoEdit = jQuery('form[name="fs-profile-edit"]');
userInfoEdit.validate({
    rules: {
        "fs-password": {
            minlength: 6
        },
        "fs-repassword": {
            equalTo: "#fs-password"
        }
    },
    messages: {
        "fs-repassword": {
            equalTo: "пароль и повтор пароля не совпадают"
        },
        "fs-password": {
            minlength: "минимальная длина 6 символов"
        },
    },
    submitHandler: function (form) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: userInfoEdit.serialize(),
            beforeSend: function () {
                userInfoEdit.find('.fs-form-info').fadeOut().removeClass('fs-error fs-success').html();
                userInfoEdit.find('[data-fs-element="submit"]').html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif">');
            }
        })
            .done(function (result) {
                userInfoEdit.find('[data-fs-element="submit"]').html('Сохранить');
                var data = JSON.parse(result);
                if (data.status == 0) {
                    userInfoEdit.find('.fs-form-info').addClass('fs-error').fadeIn().html(data.message);
                } else {
                    userInfoEdit.find('.fs-form-info').addClass('fs-success').fadeIn().html(data.message);
                }
                setTimeout(function () {
                    userInfoEdit.find('.fs-form-info').fadeOut('slow').removeClass('fs-error fs-success').html();
                }, 5000)
            });
    }
});

// регистрация пользователя
var userProfileCreate = jQuery('form[name="fs-profile-create"]');
userProfileCreate.validate({
    rules: {
        "fs-password": {
            minlength: 6
        },
        "fs-repassword": {
            equalTo: "#fs-password"
        }
    },
    messages: {
        "fs-repassword": {
            equalTo: "пароль и повтор пароля не совпадают"
        },
        "fs-password": {
            minlength: "минимальная длина 6 символов"
        },
    },
    submitHandler: function (form) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: userProfileCreate.serialize(),
            beforeSend: function () {
                userProfileCreate.find('.form-info').html('').fadeOut();
                userProfileCreate.find('.fs-preloader').fadeIn();
            }
        })
            .done(function (result) {
                userProfileCreate.find('.fs-preloader').fadeOut();
                var data = JSON.parse(result);
                if (data.status == 1) {
                    userProfileCreate.find('.form-info').removeClass('bg-danger').addClass('bg-success').fadeIn().html(data.message);
                    // если операция прошла успешно - очищаем поля
                    userProfileCreate.find('input').each(function () {
                        if (jQuery(this).attr('type') != 'hidden') {
                            jQuery(this).val('');
                        }
                    });
                } else {
                    userProfileCreate.find('.form-info').removeClass('bg-success').addClass('bg-danger').fadeIn().html(data.message);
                }


            });
    }
});

// авторизация пользователя
var loginForm = jQuery('form[name="fs-login"]');
loginForm.validate({
    submitHandler: function (form) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: loginForm.serialize(),
            beforeSend: function () {
                loginForm.find('.form-info').fadeOut().removeClass('bg-danger').html('');
                loginForm.find('.fs-preloader').fadeIn();
            }
        })
            .done(function (result) {
                var data = JSON.parse(result);
                console.log(data);
                loginForm.find('.fs-preloader').fadeOut();
                if (data.status == 0) {
                    loginForm.find('.fs-form-info').addClass('bg-danger').fadeIn().html(data.error);
                } else {
                    if (data.redirect == false) {
                        location.reload();
                    } else {
                        location.href = data.redirect;
                    }
                }
            });
    }
});


// валидация и отправка формы заказа
var order_send = jQuery('[name="fs-order-send"]');
var orderSendBtn = order_send.find('[data-fs-action="order-send"]');

orderSendBtn.click(function (e) {
        e.preventDefault();
        order_send.submit();
    }
);
order_send.validate({
    ignore: [],
    submitHandler: function (form) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: order_send.serialize(),
            beforeSend: function () {
                orderSendBtn.html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader">');
            }
        })
            .done(function (response) {
                orderSendBtn.find('button[data-fs-action=order-send]').find('.fs-preloader').fadeOut('slow');
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

                    orderSendBtn.html(orderSendBtn.data("after-send"));
                    if (jsonData.redirect != false) document.location.href = jsonData.redirect;
                } else {
                    if (jsonData.error_code != 'undefined') {
                        console.log(jsonData.error_code);
                    }
                    order_send.find('.fs-form-info').addClass('error').html(jsonData.text).fadeIn();
                    orderSendBtn.html(orderSendBtn.data("content"));
                }
            });


    }
});

(function ($) {
    // Рейтинг товара
    $('[data-fs-element="rating"]').on('click', '[data-fs-element="rating-item"]', function (e) {
        e.preventDefault();

        var ratingVal = $(this).data('rating');
        var wrapper = $(this).parents("[data-fs-element=\"rating\"]");
        var productId = wrapper.find('input').data('product-id');
        wrapper.find('input').val(ratingVal);


        if (!localStorage.getItem('fs_user_voted_' + productId)) {

            wrapper.find('[data-fs-element="rating-item"]').each(function (index, value) {
                if ($(this).data('rating') <= ratingVal) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });

            jQuery.ajax({
                type: 'POST',
                url: FastShopData.ajaxurl,
                data: {
                    action: "fs_set_rating",
                    value: ratingVal,
                    product: productId
                },
                cache: false,
                success: function (data) {
                    localStorage.setItem("fs_user_voted_" + productId, 1)
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log('error...', xhr);
                    //error logging
                },
                complete: function () {
                    iziToast.show({
                        theme: 'light',
                        title: 'Позравляем!',
                        message: 'Ваш голос засчитан!',
                        position: 'topCenter',

                    });
                }
            })
        } else {
            iziToast.show({
                theme: 'light',
                title: 'Информация!',
                message: 'Ваш голос не засчитан потому что Вы уже проголосовали за этот товар!',
                position: 'topCenter',

            });
        }

    });


}(jQuery));


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
            curentBlock.find(".fs-atc-preloader").fadeIn();
            // генерируем событие добавления в список желаний
            var before_to_wishlist = new CustomEvent("fs_before_to_wishlist", {
                detail: {
                    id: product_id,
                    image: curentBlock.data('image'),
                    name: product_name,
                    button: curentBlock
                }
            });
            document.dispatchEvent(before_to_wishlist);
        }
    })
        .done(function (result) {
            var ajax_data = jQuery.parseJSON(result);
            jQuery('[data-fs-element="whishlist-widget"]').html(ajax_data.body);
            // генерируем событие добавления в список желаний
            var add_to_wishlist = new CustomEvent("fs_add_to_wishlist", {
                detail: {
                    id: product_id,
                    name: product_name,
                    button: curentBlock,
                    image: curentBlock.data('image'),
                    ajax_data: ajax_data
                }
            });
            document.dispatchEvent(add_to_wishlist);

            curentBlock.find(".fs-atc-preloader").fadeOut();

        });
});

//удаление товара из списка желаний
jQuery('[data-fs-action="wishlist-delete-position"]').on('click', function (event) {
    var product_id = jQuery(this).data('product-id');
    var product_name = jQuery(this).data('product-name');
    var parents = jQuery(this).parents('li');

    if (confirm(fs_message.confirm_text.replace('%s', product_name))) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            data: {
                action: 'fs_del_wishlist_pos',
                position: product_id
            },
        })
            .done(function (success) {
                var data = jQuery.parseJSON(success);
                jQuery('#fs-wishlist').html(data.body);
            });


    }
});


// Событие срабатывает перед добавлением товара в список желаний
document.addEventListener("fs_before_to_wishlist", function (event) {
    // действие которое инициирует событие, здесь может быть любой ваш код
    var button = event.detail.button;
    button.find('.fs-wh-preloader').fadeIn().html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader">');
    event.preventDefault();
}, false);

// Событие срабатывает перед добавлением товара в список желаний
document.addEventListener("fs_cart_change_count", function (event) {
    // здесь может быть любой ваш код
    document.location.reload();
    event.preventDefault();
}, false);

// Событие срабатывает после добавления товара в список желаний
document.addEventListener("fs_add_to_wishlist", function (event) {
    // действие которое инициирует событие, здесь может быть любой ваш код
    var button = event.detail.button;
    button.find('.fs-wh-preloader').fadeOut();
    iziToast.show({
        image: event.detail.image,
        theme: 'light',
        message: button.data("success-message"),
        position: 'topCenter',

    });
    event.preventDefault();
}, false);