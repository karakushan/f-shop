(function ($) {
    window.fShop = {
        ajaxurl: FastShopData.ajaxurl,
        langs: FastShopData.lang,
        getLang: function (string) {
            return FastShopData.lang[string];
        },
        strReplace: function (string, obj) {
            for (var x in obj) {
                string = string.replace(new RegExp(x, 'g'), obj[x]);
            }
            return string;
        },
        getSettings: function (settingName) {
            return FastShopData[settingName];
        },
        sendOrder: function () {

        },
        updateCarts: function () {
            jQuery("[data-fs-element=\"cart-widget\"]").each(function () {
                let templatePath = "cart-widget/widget";
                if (jQuery(this).data("template") != "") {
                    templatePath = jQuery(this).data("template");
                }
                fs_get_cart(templatePath, this);
            });
        },
        productQuantityPluss: function (el) {
            let parent = el.parents('[data-fs-element="fs-quantity"]');
            let jQueryinput = parent.find('input');
            let maxAttr = jQueryinput.attr("max");
            let max = parseInt(maxAttr);
            let curVal = Number(jQueryinput.val());
            let newVal = curVal + 1;
            if (newVal > max) {
                iziToast.show({
                    theme: 'light',
                    message: this.getLang('limit_product'),
                    position: 'topCenter',
                });
            } else {
                jQueryinput.val(newVal);
                jQueryinput.change();
            }
        },
        setProductGallery: function (productId, variationId = null) {
            $.ajax({
                type: 'POST',
                url: fShop.axaxurl,
                beforeSend: function () {
                },
                data: {
                    "action": "fs_get_product_gallery_ids",
                    "product_id": productId,
                    "variation_id": variationId
                }
                ,
                success: function (res) {
                    if (res.success) {
                        if (res.data.gallery) {
                            $(lightSlider[0]).html(res.data.gallery);
                            lightSlider.refresh();
                        }
                    }
                }
            })
            ;
        },
        changeCartItemCount: function (el) {
            var cartItem = el.data('item-id');
            var productCount = Number(el.val());

            //если покупатель вбил неправильное к-во товаров
            if (productCount < 1) {
                el.val(1);
            } else {
                $.ajax({
                    url: fShop.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'fs_change_cart_count',
                        item_id: cartItem,
                        count: productCount
                    },
                    success: function (res) {
                        console.log(res);
                        if (res.success) {
                            fShop.updateCarts();

                            // создаём событие
                            let cart_change_count = new CustomEvent("fs_cart_change_count", {
                                detail: {itemId: cartItem, count: productCount}
                            });
                            document.dispatchEvent(cart_change_count);

                            if (el.data('refresh')) location.reload();
                        }

                    }
                });
            }


        },
        selectVariation: function () {
            var variation = $(this).val();
            var productId = $(this).data("product-id");
            var maxCount = $(this).data("max");
            // Изменяем данные в кнопке "добавить в корзину"
            $("[data-action=\"add-to-cart\"]").each(function (index, value) {
                if ($(this).data("product-id") == productId) {
                    $(this).attr("data-variation", variation);
                }
            });
            // Изменяем данные в квантификаторе товара
            $("[data-fs-action=\"change_count\"]").each(function (index, value) {
                if ($(this).data("fs-product-id") == productId) {
                    $(this).attr("max", maxCount);
                    if (maxCount != "" && $(this).val() > maxCount) {
                        $(this).val(maxCount);
                    }
                }
            });

            // Подгружаем изображения в галерею аяксом
            fShop.setProductGallery(productId, variation);
            // создаём событие
            var fs_select_variant = new CustomEvent("fs_select_variant", {
                detail: {
                    "variationId": variation,
                    "productId": productId
                }
            });
            document.dispatchEvent(fs_select_variant);


        }
    };

    // Выбор вариации товара
    $(document).on('change', "[data-fs-element=\"select-variation\"]", fShop.selectVariation);
    // увеличение к-ва товара на единицу
    $(document).on('click', "[data-fs-count=\"pluss\"]", function (event) {
        fShop.productQuantityPluss($(this));
    });
    // Изменение количества товаров в корзине
    $(document).on('change input', '[data-fs-type="cart-quantity"]', function () {
        fShop.changeCartItemCount($(this))
    });


//добавление товара в корзину (сессию)
    jQuery(document).on('click', '[data-action=add-to-cart]', function (event) {
        event.preventDefault();
        let el = jQuery(this);
        let product_id = el.data('product-id');
        let variation = el.data('variation');
        let count = el.attr('data-count');

        // если кнопка выключена, выводим сообщение почему товар не доступен
        if (el.attr("data-disabled") == "true") {
            iziToast.show({
                theme: 'light',
                message: el.data("disabled-message"),
                position: 'topCenter',
            });
            return;
        }

        // подтягиваем атрибуты товаров
        var attr = {};
        jQuery('[data-fs-element="attr"]').each(function () {
            if (jQuery(this).data("product-id") == product_id && jQuery(this).prop("checked")) {
                attr[jQuery(this).attr("name")] = jQuery(this).val();
            }
        });


        // объект передаваемый в события
        var detail = {
            button: el,
            id: product_id,
            name: el.data('product-name'),
            price: el.data('price'),
            variation: variation,
            currency: el.data('currency'),
            sku: el.data('sku'),
            category: el.data('category'),
            count: count,
            attr: attr,
            image: el.data('image'),
            success: true,
            text: {
                success: el.data('success'),
                error: el.data('error')
            }
        };


        var productObject = {
            "action": 'add_to_cart',
            "attr": attr,
            "count": count,
            "variation": variation,
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
                fShop.updateCarts();
                // создаём событие
                let add_to_cart = new CustomEvent("fs_add_to_cart", {
                    detail: detail
                });
                document.dispatchEvent(add_to_cart);
            });

    });


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
        var parseAtts = [];
        jQuery("[data-fs-element=\"attr\"]").each(function (index, value) {
            if (jQuery(this).data('product-id') == productId) {
                var val = jQuery(this).val();
                if (val) parseAtts.push(val);
            }
        });
        jQuery.ajax({
            type: 'POST',
            url: FastShopData.ajaxurl,
            data: {action: "fs_get_variated", product_id: productId, atts: parseAtts},
            cache: false,
            success: function (result) {
                if (result.success) {
                    cartbutton.attr("data-disabled", false);
                    cartbutton.removeAttr("data-disabled-message");

                    if (result.data.price) {
                        jQuery("[data-fs-element=\"price\"]").each(function (index, value) {
                            if (jQuery(this).data("product-id") == productId) {
                                jQuery(this).html(result.data.price);
                            }
                        });
                    }

                    if (result.data.basePrice) {
                        jQuery("[data-fs-element=\"base-price\"]").each(function (index, value) {
                            if (jQuery(this).data("product-id") == productId) {
                                jQuery(this).html(result.data.basePrice);
                            }
                        });
                    }
                    // создаём событие "fs_after_change_att"
                    var fs_after_change_att = new CustomEvent("fs_after_change_att", {
                        detail: {
                            el: el,
                            productId: productId,
                            data: result.data
                        }
                    });
                    document.dispatchEvent(fs_after_change_att);
                } else {
                    cartbutton.attr("data-disabled", true);
                    cartbutton.attr("data-disabled-message", result.data.msg);
                }
            }
        });
    });


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
                    fShop.updateCarts();
                    iziToast.show({
                        theme: 'light',
                        message: result.data.message,
                        position: 'topCenter',

                    });
                    if (el.data('refresh')) {
                        location.reload();
                    }
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

// слайдер товара
    var lightGallery = jQuery("#product_slider").lightGallery();
    if (typeof fs_lightslider_options != "undefined") {
        window.lightSlider = jQuery('#product_slider').lightSlider(fs_lightslider_options);
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
                url: fShop.ajaxurl,
                type: 'POST',
                data: order_send.serialize(),
                beforeSend: function () {
                    orderSendBtn.html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader">');
                }
            })
                .done(function (response) {
                    console.log(response);
                    orderSendBtn.find('button[data-fs-action=order-send]').find('.fs-preloader').fadeOut('slow');
                    /* если статус заказ успешный */
                    if (response.success) {
                        // создаём событие
                        let send_order = new CustomEvent("fs_send_order", {
                            detail: {
                                order_id: response.data.order_id,
                                sum: response.data.sum
                            }
                        });
                        document.dispatchEvent(send_order);

                        if (response.data.redirect != false) {
                            document.location.href = response.data.redirect;
                        } else {
                            iziToast.show({
                                theme: 'light',
                                title: fShop.getLang('success'),
                                message: response.data.msg,
                                position: 'topCenter'
                            });
                        }
                    } else {
                        iziToast.show({
                            theme: 'light',
                            title: fShop.getLang('error'),
                            message: response.data.msg,
                            position: 'topCenter'
                        });
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
    jQuery(document).on('click', '[data-fs-action="wishlist"]', function (event) {
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


})(jQuery)

