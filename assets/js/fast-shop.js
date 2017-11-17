/* Можно использовать глобальный объект FastShopData
 ajaxurl - ссылка на ajax обрабочик,
 fs_slider_max - максимальная цена установленная на сайте
 fs_currency - символ установленной валюты на текущий момент
 fs_lang - текущая локаль
 */


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

jQuery(function (jQuery) {
    // обработка кнопки быстрого заказа
    jQuery('[data-fs-action="quick_order_button"]').on('click', function (event) {
        event.preventDefault();
        var pName = jQuery(this).data('product-name');
        var pId = jQuery(this).data('product-id');
        jQuery('[name="fs_cart[product_name]"]').val(pName);
        jQuery('[name="fs_cart[product_id]"]').val(pId);
    });

    //живой поиск по сайту
    jQuery('form[name="live-search"]').on('click', '.close-search', function (event) {
        event.preventDefault();
        jQuery(this).parents('.search-results').fadeOut(0);
    });
    jQuery('form[name="live-search"] input[name="s"]').on('keyup focus click input', function (event) {
        event.preventDefault();
        var search_input = jQuery(this);
        var search = jQuery(this).val();
        var parents_form = search_input.parents('form');
        var results_div = parents_form.find('.search-results');
        if (search.length > 1) {
            jQuery.ajax({
                url: FastShopData.ajaxurl,
                type: 'POST',
                data: {action: 'fs_livesearch', s: search},
                beforeSend: function () {
                    search_input.next().addClass('search-animate');
                }
            })
                .done(function (data) {
                    results_div.fadeIn(800).html(data);

                })
                .always(function () {
                    search_input.next().removeClass('search-animate');
                });
        } else {
            results_div.fadeOut(800).html('');
        }


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
});


// Квантификатор товара
jQuery(document).ready(function (jQuery) {
    jQuery('[data-fs-count="minus"]').on('click', function () {
        var jQueryinput = jQuery(jQuery(this).data('target'));
        var count = parseInt(jQueryinput.val()) - 1;
        count = count < 1 ? 1 : count;
        jQueryinput.val(count);
        jQueryinput.change();
        return false;
    });
    jQuery('[data-fs-count="pluss"]').click(function () {
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
    jQuery('[data-fs-type="cart-quantity"]').on('change input', function (event) {
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
            .done(function () {
                location.reload();
            });


    });
});

//Удаление продукта из корзины
jQuery(document).ready(function (jQuery) {
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
});

//очищаем корзину
jQuery(document).ready(function (jQuery) {
    jQuery('[data-fs-type="delete-cart"]').on('click', function (event) {
        event.preventDefault();
        if (confirm(fs_message.delete_all_text)) {
            document.location.href = jQuery(this).data('url');
        }
    });
});

jQuery(document).ready(function (jQuery) {
    //Образует js объект с данными о продукте и помещает в кнопку добавления в корзину в атрибут 'data-json'
    jQuery('[data-fs-element="attr"]').on('change input', function (event) {
        event.preventDefault();
        var productId = jQuery(this).data('product-id');
        var cartbutton = jQuery('#fs-atc-' + productId);
        var productObject = cartbutton.data('attr');
        var attrName = jQuery(this).attr('name');
        var attrVal = jQuery(this).val();
        //если покупатель вбил неправильное к-во товаров
        if (jQuery(this).attr('name') == 'count') {
            if (!isNumeric(attrVal) || attrVal <= 0) {
                jQuery(this).val(1);
                attrVal = 1;
                jQuery(this).parent().css({'position': 'relative'});
                jQuery(this).prev('.count-error').text(fs_message.count_error).fadeIn(400);
            } else {
                jQuery(this).prev('.count-error').text('').fadeOut(800);
            }
            productObject.count = attrVal;
        }
        productObject.attr[attrName] = attrVal;
        var jsontostr = JSON.stringify(productObject);
        cartbutton.attr('data-attr', jsontostr);
    });
});

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

//слайдер диапазона цены
(function (jQuery) {
    var u = new Url;
    var p_start = u.query.price_start == undefined ? 0 : u.query.price_start;
    var p_end = u.query.price_end == undefined ? FastShopData.fs_slider_max : u.query.price_end;


    jQuery('[data-fs-element="range-slider"]').slider({
        range: true,
        min: 0,
        max: FastShopData.fs_slider_max,
        values: [p_start, p_end],
        slide: function (event, ui) {
            jQuery('[data-fs-element="range-end"] ').html('<span>' + ui.values[1] + '</span> ' + FastShopData.fs_currency);
            jQuery('[data-fs-element="range-start"] ').html(ui.values[0] + ' ' + FastShopData.fs_currency);
            jQuery("#slider-range > .ui-slider-handle:nth-child(2)").html('<span><span class="val">' + ui.values[0] + '</span>&nbsp;' + FastShopData.fs_currency + '</span>');
            jQuery("#slider-range > .ui-slider-handle:nth-child(3)").html('<span><span class="val">' + ui.values[1] + '</span>&nbsp;' + FastShopData.fs_currency + '</span>');
            jQuery('[data-fs-element="range-start-input"]').val(ui.values[0]);
            jQuery('[data-fs-element="range-end-input"]').val(ui.values[1]);
        },
        change: function (event, ui) {

            u.query.fs_filter = FastShopData.fs_nonce;
            u.query.price_start = ui.values[0];
            u.query.price_end = ui.values[1];
            // console.log(u.toString());
            window.location.href = u.toString();


        }
    });
    jQuery('[data-fs-element="range-end"] ').html(p_end + ' ' + FastShopData.fs_currency);
    jQuery('[data-fs-element="range-start"] ').html(p_start + ' ' + FastShopData.fs_currency);

    jQuery('[data-fs-element="range-start-input"]').val(p_start);
    jQuery('[data-fs-element="range-end-input"]').val(p_end);

    jQuery("#slider-range > .ui-slider-handle:nth-child(2)").html('<span><span class="val">' + p_start + '</span>&nbsp;' + FastShopData.fs_currency + '</span>');
    jQuery("#slider-range > .ui-slider-handle:nth-child(3)").html('<span><span class="val">' + p_end + '</span>&nbsp;' + FastShopData.fs_currency + '</span>');
    jQuery("#minPrice .val").html(p_start + ' ' + FastShopData.fs_currency);
    jQuery("#maxPrice .val").html(p_end + ' ' + FastShopData.fs_currency);

//Переадресовываем все фильтры на значение, которое они возвращают
    jQuery('[data-fs-action="filter"]').on('change', function (e) {
        e.preventDefault();
        if (jQuery(this).attr('type') == 'checkbox') {
            if (jQuery(this).prop('checked')) {
                window.location.href = jQuery(this).val();
            } else {
                window.location.href = jQuery(this).data('fs-redirect');
            }
        } else {
            window.location.href = jQuery(this).val();
        }
    });

// слайдер товара
    if (typeof fs_lightslider_options != "undefined") {
        jQuery('#product_slider').lightSlider(fs_lightslider_options);
    }

    // Событие срабатывает перед добавлением товара в список желаний
    document.addEventListener("fs_before_to_wishlist", function (event) {
        // действие которое инициирует событие, здесь может быть любой ваш код
        var button = event.detail.button;
        button.find('.fs-wh-preloader').fadeIn().html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader">');
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
            title: 'Успех!',
            message: 'Товар &laquo;'+event.detail.name+'&raquo; добавлен в список желаний!',
            position: 'topCenter',

        });
        event.preventDefault();
    }, false);

    // Событие срабатывает перед добавлением товара в корзину
    document.addEventListener("fs_before_add_product", function (event) {
        // действие которое инициирует событие, здесь может быть любой ваш код
        var button = event.detail.button;
        button.find('.fs-atc-preloader').fadeIn().html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader">');
        event.preventDefault();
    }, false);
    // Событие срабатывает когда товар добавлен в корзину
    document.addEventListener("fs_add_to_cart", function (event) {
        // действие которое инициирует событие
        fs_get_cart('cart-widget/widget', '[data-fs-element="cart-widget"]')
        var button = event.detail.button;
        iziToast.show({
            image: event.detail.image,
            theme: 'light',
            title: 'Успех!',
            message: 'Товар &laquo;'+event.detail.name+'&raquo; добавлен в корзину!',
            position: 'topCenter',

        });
        button.find('.fs-atc-preloader').fadeOut();
        // button.find('.fs-atc-info').fadeIn().html(event.detail.text.success);
        setTimeout(function () {
            button.find('.fs-atc-info').fadeOut();
        }, 4000)

        event.preventDefault();
    }, false);


})(jQuery);

// получает корзину через шаблон "cartTemplate"  и выводит её внутри "cartWrap"
function fs_get_cart(cartTemplate, cartWrap) {
    var parameters = {
        action: 'fs_get_cart',
        template: cartTemplate
    }
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
        .done(function (data) {
            if (!IsJsonString(data)) return;
            var json = jQuery.parseJSON(data);
            el.find('.fs-atc-preloader').fadeOut();
            el.find('.fs-atc-info').fadeIn().html(el.data('success'));
            setTimeout(function () {
                el.find('.fs-atc-info').fadeOut(1000)
            }, 3000);
        });
});


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

