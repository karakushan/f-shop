(function ($) {
    window.fShop = {
        ajaxurl: FastShopData.ajaxurl,
        nonce: FastShopData.fs_nonce,
        langs: FastShopData.lang,
        getLang: function (string) {
            return FastShopData.lang[string];
        },
        // Выполняет поиск значения value в массиве array
        find:
            function find(array, value) {
                if (array.indexOf) { // если метод существует
                    return array.indexOf(value);
                }
                for (var i = 0; i < array.length; i++) {
                    if (array[i] === value) return i;
                }
                return -1;
            },
        ajaxData: function (action, data) {
            data.action = action;
            data.fs_secret = this.nonce
            return data;
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
            if (jQueryinput.data('limit') == 1 && newVal > max) {
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
            let ajaxUrl = this.ajaxurl;

            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                beforeSend: function () {
                },
                data: fShop.ajaxData('fs_get_product_gallery_ids', {
                    "product_id": productId,
                    "variation_id": variationId
                }),
                success: function (res) {
                    if (res.success) {
                        if (res.data.gallery) {
                            $("#fs-product-slider-wrapper").html('<ul id="product_slider">' + res.data.gallery + '</ul>');
                            $("#fs-product-slider-wrapper").find("#product_slider").lightSlider(fs_lightslider_options);
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
    // Покупка в 1 клик
    $(document).on('click', '[data-fs-element="buy-one-click"]', function (event) {
        event.preventDefault();
        let detail = $(this).data();
        // создаём событие
        let fsBuyOneClick = new CustomEvent("fsBuyOneClick", {
            detail: detail
        });
        document.dispatchEvent(fsBuyOneClick);
    });

    // Предпросмотр фото при загрузке в личном кабинете
    jQuery("#fs_user_avatar").change(function () {
        let bgImg = $(this).parent()
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function () {
                var dataURL = reader.result;
                bgImg.css("background-image", "url(" + dataURL + ")");
            };
            reader.readAsDataURL(this.files[0]);

        }
    });

    // Сохраняет данные пользователя методом AJAX
    $(document).on('submit', '[name=fs-save-user-data]', function (event) {
        event.preventDefault();
        let formData = new FormData();
        $(this).find('input,select').each(function (index, value) {
            if ($(this).attr('type') == 'file') {
                formData.append($(this).attr('name'), $(this).prop('files')[0])
            } else if ($(this).attr('type') == 'checkbox') {
                if ($(this).prop('checked')) {
                    formData.append($(this).attr('name'), 1);
                } else {
                    formData.append($(this).attr('name'), 0);
                }
            } else {
                formData.append($(this).attr('name'), $(this).val())
            }
        });
        $.ajax({
            type: 'POST',
            url: fShop.ajaxurl,
            data: formData,
            contentType: false,
            processData: false,
            success: function (data) {
                console.log(data);
                if (data.success) {
                    iziToast.show({
                        theme: 'light',
                        color: 'green',
                        message: data.data.msg,
                        position: 'topCenter',
                    });
                } else {
                    iziToast.show({
                        theme: 'light',
                        color: 'red',
                        message: data.data.msg,
                        position: 'topCenter',
                    });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
            }
        });
        return false;
    });


//добавление товара в корзину (сессию)
    jQuery(document).on('click', '[data-action=add-to-cart]', function (event) {
        event.preventDefault();
        let el = jQuery(this);
        let productData = el.data();
        let product_id = el.data('product-id');
        let variation = el.attr('data-variation');
        let count = el.attr('data-count');

        if (productData.available == false && fShop.getSettings('preorderWindow') == 1) {
            // создаём событие
            let fsBuyNoAvailable = new CustomEvent("fsBuyNoAvailable", {
                detail: productData
            });
            document.dispatchEvent(fsBuyNoAvailable);
            return;
        }

        if (el.attr("data-disabled") == 'true') {
            iziToast.warning({
                position: "topCenter",
                title: fShop.getLang('error'),
                message: el.attr("data-disabled-message"),
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
            name: el.data('name'),
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
            "attr": attr,
            "count": count,
            "variation": variation,
            'post_id': product_id
        }

        jQuery.ajax({
            url: fShop.ajaxurl,
            data: fShop.ajaxData('add_to_cart', productObject),
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
    jQuery('[data-fs-element="attr"]').on('change', function (event) {
        event.preventDefault();
        var el = jQuery(this);
        var productId = el.data('product-id');
        var cartbutton = jQuery('[data-action="add-to-cart"][data-product-id="' + productId + '"]');
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
        if (cartbutton.data('variated') == 1)
            jQuery.ajax({
                type: 'POST',
                url: fShop.ajaxurl,
                data: {action: "fs_get_variated", product_id: productId, atts: parseAtts, current: attrVal},
                success: function (result) {
                    if (result.success) {
                        let price = Number(result.data.options.price);
                        let pricePromo = Number(result.data.options.action_price);

                        if (pricePromo && pricePromo < price) {
                            price = pricePromo;
                        }

                        cartbutton.attr("data-disabled", false);
                        cartbutton.attr("data-variation", result.data.options.variation);
                        cartbutton.attr("data-price", price);
                        cartbutton.removeAttr("data-disabled-message");

                        if (result.data.active) {
                            // устанавливаем опции в селектах в актив
                            for (var key in result.data.active) {
                                $("[data-fs-element=\"attr\"][name='" + key + "'] option").each(function (index, value) {
                                    if ($(this).attr("value") == result.data.active[key]) {
                                        $(this).prop("selected", true);
                                    }
                                });
                            }
                            $("[data-action=\"add-to-cart\"][data-product-id=\"" + productId + "\"]").attr("data-attr", JSON.stringify(result.data.active));

                        }

                        if (typeof result.data.variation == 'number') {
                            jQuery("[data-action=\"add-to-cart\"]").each(function (index, value) {
                                if (jQuery(this).data("product-id") == productId) {
                                    jQuery(this).attr("data-variation", result.data.variation);
                                }
                            });
                        }

                        if (result.data.price) {
                            jQuery("[data-fs-element=\"price\"]").each(function (index, value) {
                                if (jQuery(this).data("product-id") == productId) {
                                    jQuery(this).html(result.data.price);
                                }
                            });
                        }

                        if (result.data.basePrice) {
                            jQuery('[data-fs-element="base-price"][data-product-id="' + productId + '"]').html(result.data.basePrice);
                        } else {
                            jQuery('[data-fs-element="base-price"][data-product-id="' + productId + '"]').html('');
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

                        iziToast.warning({
                            title: fShop.getLang('error'),
                            message: result.data.msg,
                        });
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

    // Shows address fields when choosing delivery to
    jQuery(document).on('change', '[name="fs_delivery_methods"]', function (event) {
        let deliveryId = jQuery(this).val();
        if (deliveryId.length)
            jQuery.ajax({
                type: 'POST',
                url: fShop.ajaxurl,
                beforeSend: function () {
                    // создаём событие
                    var fs_change_delivery = new CustomEvent("fs_change_delivery", {
                        detail: {deliveryId: deliveryId}
                    });
                    document.dispatchEvent(fs_change_delivery);
                },
                data: fShop.ajaxData('fs_show_shipping', {delivery: deliveryId}),
                success: function (result) {
                    if (result.success) {
                        if (result.data.price) {
                            jQuery("[data-fs-element=\"delivery-cost\"]").html(result.data.price);
                            jQuery("[data-fs-element=\"total-amount\"]").html(result.data.total);
                            jQuery("[data-fs-element=\"taxes-list\"]").replaceWith(result.data.taxes);
                        }
                        if (result.data.show) {
                            jQuery("#fs-shipping-fields")
                                .html(result.data.html)
                                .addClass('active')
                                .fadeIn();
                        } else {
                            jQuery("#fs-shipping-fields")
                                .html('')
                                .removeClass('active')
                                .fadeOut();
                        }
                    } else {
                        jQuery("#fs-shipping-fields")
                            .html('')
                            .removeClass('active')
                            .fadeOut();
                        if (result.data.msg) {
                            console.log(result.data.msg);
                        }
                    }

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


        if (!el.data('confirm') || confirm(el.data("confirm"))) {
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
    var userProfileCreate = jQuery('form[name="fs-register"]');
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
                url: fShop.ajaxurl,
                type: 'POST',
                data: userProfileCreate.serialize(),
                beforeSend: function () {
                    userProfileCreate.find('.form-info').html('').fadeOut();
                    userProfileCreate.find('.fs-preloader').fadeIn();
                },
                success: function (result) {
                    userProfileCreate.find('.fs-preloader').fadeOut();

                    if (result.success) {
                        userProfileCreate.find('.fs-form-info').removeClass('bg-danger').addClass('bg-success').fadeIn().html(result.data.msg);
                        // если операция прошла успешно - очищаем поля
                        userProfileCreate.find('input').each(function () {
                            if (jQuery(this).attr('type') != 'hidden') {
                                jQuery(this).val('');
                            }
                        });
                    } else {
                        userProfileCreate.find('.fs-form-info').removeClass('bg-success').addClass('bg-danger').fadeIn().html(result.data.msg);
                    }

                }
            })

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


    /*  Обработка формы сброса пароля */
    $(document).on('submit', '[name=fs-lostpassword]', function (event) {
        event.preventDefault();
        let form = $(this);
        let formData = form.serialize();
        $.ajax({
            type: 'POST',
            url: fShop.ajaxurl,
            data: formData,
            success: function (result) {
                if (result.success) {
                    form.find('.fs-form-info').removeClass('bg-danger').addClass('bg-success').fadeIn().html(result.data.msg);
                    // если операция прошла успешно - очищаем поля
                    form.find('input').each(function () {
                        if (jQuery(this).attr('type') != 'hidden') {
                            jQuery(this).val('');
                        }
                    });
                } else {
                    form.find('.fs-form-info').removeClass('bg-success').addClass('bg-danger').fadeIn().html(result.data.msg);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
            },
            complete: function () {
                //afer ajax call is completed
            }
        });


        return false;
    });


    /*
    * Общий обрабочик форм
    * TODO:в дальнейшем убрать все обработчики, использовать только этот
     */
    $("form[data-ajax=on]").on('submit', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: fShop.ajaxurl,
            data: formData,
            success: function (response) {
                console.log(response);
                if (response.success) {
                    iziToast.show({
                        theme: 'light',
                        title: fShop.getLang('success'),
                        message: response.data.msg,
                        position: 'topCenter'
                    });
                } else {
                    iziToast.show({
                        theme: 'light',
                        title: fShop.getLang('error'),
                        message: response.data.msg,
                        position: 'topCenter'
                    });
                }

                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            }
        });

        return false;
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
                },
                success: function (response) {
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
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log('error...', xhr);
                },
                complete: function () {
                    //afer ajax call is completed
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
            type: 'POST',
            url: fShop.ajaxurl,
            data: fShop.ajaxData('fs_addto_wishlist', {product_id: product_id}),
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
                if (result.success) {
                    jQuery('[data-fs-element="whishlist-widget"]').html(result.data.body);
                    // генерируем событие добавления в список желаний
                    var add_to_wishlist = new CustomEvent("fs_add_to_wishlist", {
                        detail: {
                            id: product_id,
                            name: product_name,
                            button: curentBlock,
                            image: curentBlock.data('image'),
                            ajax_data: result.data
                        }
                    });
                    document.dispatchEvent(add_to_wishlist);
                }
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

    // Живой поиск товаров
    $("[name=s]").on('input', function (e) {
        let searchVal = $(this).val();
        let form = $(this).parents('form');

        $.ajax({
            type: 'POST',
            url: fShop.ajaxurl,
            data: fShop.ajaxData('fs_livesearch', {
                search: searchVal
            }),
            success: function (data) {
                if (data.success) {
                    if (form.find('.fs-livesearch-data').length) {
                        form.find('.fs-livesearch-data').replaceWith(data.data.html);
                    } else {
                        form.append(data.data.html);
                    }
                }
                console.log(data);
                // do something with ajax data

            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            },
            complete: function () {
                //afer ajax call is completed
            }
        });
    })

    $("[name=s]").focusout(function (e) {
        $(".fs-livesearch-data").fadeOut(function () {
        });
    });

    // === ДОБАВЛЯЕМ СПЕЦИАЛЬНЫЕ КЛАССЫ ДЛЯ ВРАППЕРА РАДИО КНОПОК ПРИ ОФОРМЛЕНИИ ПОКУПКИ

    // Это сработает сразу после загрузки страницы
    $(".fs-field-wrap .radio:first-child").addClass('active').find('input').prop('checked', true);

    // Это сработает сразу после переключения input radio
    $(".fs-field-wrap").on('change', '[type="radio"]', function () {
        $(this)
            .parents('.fs-field-wrap')
            .find('.radio')
            .removeClass('active')

        $(this)
            .parents('.radio')
            .addClass('active')
    })


})(jQuery)

