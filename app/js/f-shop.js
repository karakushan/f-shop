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
        price: curent.data('price'),
        currency: curent.data('currency'),
        sku: curent.data('sku'),
        category: curent.data('category'),
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
    fs_get_cart('cart-widget/widget', '[data-fs-element="cart-widget"]');
    var button = event.detail.button;
    iziToast.show({
        image: event.detail.image,
        theme: 'light',
        title: 'Успех!',
        message: 'Товар &laquo;' + event.detail.name + '&raquo; добавлен в корзину!',
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
            beforeSend: function () {
                jQuery("[data-fs-element=\"price\"]").addClass('blink');
            },
            success: function (data) {
                if (IsJsonString(data)) {
                    var json = jQuery.parseJSON(data);
                    var priceFull=json.base_price + ' <span>' +json.currency+ '</span>';
                    if (json.result) {
                        jQuery("[data-fs-element=\"price\"]").removeClass('blink').html(priceFull);
                        attrObj.count = json.count;
                        jQuery('[data-fs-action="change_count"]').val(json.count);
                        jQuery("[data-fs-element=\"base-price\"]").parent().css('visibility', 'hidden');
                        jQuery("[data-fs-element=\"discount\"]").parent().css('visibility', 'hidden');
                    } else {
                        jQuery("[data-fs-element=\"price\"]").html(priceFull);
                        jQuery("[data-fs-element=\"base-price\"]").parent().css('visibility', 'visible');
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
//живой поиск по сайту
jQuery('input[name="s"]').on('keyup input click', function (event) {
    event.preventDefault();
    var searchField = jQuery(this);
    var search = jQuery(this).val();
    var form = searchField.parents('form');
    form.css("position", "relative");
    var results = form.find('.livesearch-wrapper');
    if (search.length > 1) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: {action: 'fs_livesearch', s: search},
            beforeSend: function () {
                if (searchField.prev().hasClass('fs-ls-preloader')) {
                    searchField.prev().fadeIn();
                } else {
                    searchField.before('<img src="/wp-content/plugins/f-shop/assets/img/blocks-preloader.svg" class="fs-ls-preloader" style="display: none;">');
                }

            }
        })
            .done(function (data) {
                if (data.length) {
                    if (searchField.next().hasClass('livesearch-wrapper')) {
                        searchField.next().html(data);
                    } else {
                        searchField.after("<div class='livesearch-wrapper'>" + data + "</div>");
                    }
                    form.find('.fs-ls-preloader').fadeOut();
                    form.find('.livesearch-wrapper').fadeIn();
                }

            })
            .always(function () {
                searchField.next().removeClass('search-animate');
            });
    } else {
        results.fadeOut().html('');
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
// слайдер товара
if (typeof fs_lightslider_options != "undefined") {
    jQuery('#product_slider').lightSlider(fs_lightslider_options);
}

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
//слайдер диапазона цены

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
var validator = jQuery('[name="fs-order-send"]');
jQuery('[data-fs-action="order-send"]').click(function (e) {
        e.preventDefault();
        validator.submit();
    }
);
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