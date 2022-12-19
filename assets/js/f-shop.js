jQuery(document).ready(function ($) {
    let fShop = Object.assign({
        getLang: function (string) {
            return this.langs[string];
        }, // Выполняет поиск значения value в массиве array
        find: function find(array, value) {
            if (array.indexOf) { // если метод существует
                return array.indexOf(value);
            }
            for (var i = 0; i < array.length; i++) {
                if (array[i] === value) return i;
            }
            return -1;
        }, ajaxData: function (action, data) {
            data.action = action;
            data.fs_secret = this.nonce
            return data;
        }, strReplace: function (string, obj) {
            for (var x in obj) {
                string = string.replace(new RegExp(x, 'g'), obj[x]);
            }
            return string;
        }, getSettings: function (settingName) {
            return this[settingName];
        }, updateCarts: function () {
            jQuery("[data-fs-element=\"cart-widget\"]").each(function () {
                let templatePath = "cart-widget/widget";
                if (jQuery(this).data("template") != "") {
                    templatePath = jQuery(this).data("template");
                }
                fs_get_cart(templatePath, this);
            });
        }, productQuantityPluss: function (el) {
            let parent = el.parents('[data-fs-element="fs-quantity"]');
            let input = parent.find('input');
            let step = Number(input.attr('step'));
            let max = input.attr("max");
            let value = Number(input.val()) + step;

            if (max != '' && value > Number(max)) {
                iziToast.show({
                    theme: 'light', message: this.getLang('limit_product'), position: 'topCenter',
                });
            } else {
                input.val(value);
                input.change();
            }
        }, setProductGallery: function (productId, variationId = null) {
            $.ajax({
                type: 'POST', url: this.ajaxurl, beforeSend: function () {
                }, data: this.ajaxData('fs_get_product_gallery_ids', {
                    "product_id": productId, "variation_id": variationId
                }), success: function (res) {
                    if (res.success) {
                        if (res.data.gallery) {
                            $("#fs-product-slider-wrapper").html('<ul id="product_slider">' + res.data.gallery + '</ul>');
                            $("#fs-product-slider-wrapper").find("#product_slider").lightSlider(fs_lightslider_options);
                        }
                    }
                }
            });
        }, changeCartItemCount: function (el) {
            let cartItem = el.data('item-id');
            let productCount = Number(el.val());
            let step = Number(el.attr('step'));
            let min = Number(el.attr('min'));


            //если покупатель вбил неправильное к-во товаров
            if (productCount < min) {
                el.val(min);
            } else {
                let plugin = this;
                $.ajax({
                    url: this.ajaxurl, type: 'POST', data: {
                        action: 'fs_change_cart_count', item_id: cartItem, count: productCount
                    }, success: function (response) {
                        if (response.success) {
                            plugin.updateCarts();
                            $('[data-fs-element="cart-cost"]').text(response.data.cost)
                            $('[data-fs-element="total-amount"]').text(response.data.total)

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


        }, selectVariation: function () {
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
            this.setProductGallery(productId, variation);
            // создаём событие
            var fs_select_variant = new CustomEvent("fs_select_variant", {
                detail: {
                    "variationId": variation, "productId": productId
                }
            });
            document.dispatchEvent(fs_select_variant);


        }
    }, window.fShop);

    $(document).on('click', '[data-fs-element="comment-like"]', function (event) {
        let commentId = $(this).data('comment-id');
        let countEl = $(this).find('[data-fs-element="comment-like-count"]')
        $.ajax({
            type: 'POST', url: fShop.ajaxurl, data: {
                action: 'fs_like_comment', comment_id: commentId
            }, success: function (data) {
                if (data.success && data.data.count) {
                    countEl.text(data.data.count)
                    iziToast.show({
                        theme: 'light', color: 'green', message: data.data.msg, position: 'bottomRight',
                    });
                } else {
                    iziToast.show({
                        theme: 'light', color: 'red', message: data.data.msg, position: 'bottomRight',
                    });
                }
            }, error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            }, complete: function () {
                //afer ajax call is completed
            }
        });
    });

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
            beforeSend: function () {
                $('.fs-form .meter').fadeIn(100);
            },
            success: function (data) {
                if (data.success) {
                    iziToast.show({
                        theme: 'light', color: 'green', message: data.data.msg, position: 'topCenter',
                    });
                } else {
                    iziToast.show({
                        theme: 'light', color: 'red', message: data.data.msg, position: 'topCenter',
                    });
                }

                $('.fs-form .meter').fadeOut(100);
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
        let count = Number(el.attr('data-count'));

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
                position: "topCenter", title: fShop.getLang('error'), message: el.attr("data-disabled-message"),
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
                success: el.data('success'), error: el.data('error')
            }
        };


        var productObject = {
            "attr": attr, "count": count, "variation": variation, 'post_id': product_id
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
                url: fShop.ajaxurl,
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
                                el: curent, productId: productId, data: json
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
        if (cartbutton.data('variated') == 1) jQuery.ajax({
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
                            el: el, productId: productId, data: result.data
                        }
                    });
                    document.dispatchEvent(fs_after_change_att);
                } else {
                    cartbutton.attr("data-disabled", true);
                    cartbutton.attr("data-disabled-message", result.data.msg);

                    iziToast.warning({
                        title: fShop.getLang('error'), message: result.data.msg,
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

    // Скрываем некоторые поля в форме отправки заказа при загрузке страницы
    function getCheckoutData() {
        let el = jQuery('[name="fs_delivery_methods"]');
        let val = el.val();

        if (el.attr('type') == 'radio' || el.attr('type') == 'checkbox') {
            val = jQuery('[name="fs_delivery_methods"]:checked').val();
        }

        jQuery.ajax({
            type: 'POST', url: fShop.ajaxurl, beforeSend: function () {

            }, data: fShop.ajaxData('fs_show_shipping', {
                fs_delivery_methods: val
            }), success: function (result) {
                if (!result.success) return;

                if (result.data.price) {
                    jQuery("[data-fs-element=\"delivery-cost\"]").html(result.data.price);
                    jQuery("[data-fs-element=\"total-amount\"]").html(result.data.total);
                    jQuery("[data-fs-element=\"taxes-list\"]").replaceWith(result.data.taxes);
                    jQuery("[data-fs-element=\"packing-cost\"]").html(result.data.packing_cost);
                }

                // Оключаем поля которые нужно скрыть
                $('.fs-checkout-form input,.fs-checkout-form .fs-field-wrap').fadeIn(0);
                if (typeof result.data.disableFields !== 'undefined' && result.data.disableFields.length > 0) {
                    result.data.disableFields.forEach(function (field) {
                        $('[name="' + field + '"]')
                            .parents('.fs-field-wrap')
                            .fadeOut(0);

                        $('[name="' + field + '"]')
                            .prop('required', false)
                            .fadeOut(0);
                    })
                }

                // Устанавливаем обязательные поля
                $('.fs-checkout-form [data-ajax-req="true"]').removeAttr('required');
                if (result.data.requiredFields.length) {
                    for (let i in result.data.requiredFields) {
                        let field = $('[name="' + result.data.requiredFields[i] + '"]');
                        // Добавляем звёздочку в placeholder  к обязательным полям
                        // if (!field.data('placeholder')) {
                        //     let placeholder = field.attr('placeholder');
                        //     field.attr('data-placeholder', placeholder);
                        //     if (typeof placeholder!=='undefined' && placeholder.indexOf('*') === -1) {
                        //         field.attr('placeholder', field.attr('data-placeholder') + '*')
                        //     }
                        // }
                        field.attr('required', 'required').attr('data-ajax-req', true);
                    }
                }
            }


        });
    }

    if (jQuery('[name="fs_delivery_methods"]').length) getCheckoutData();

    // Shows address fields when choosing delivery to
    jQuery(document).on('change', '[name="fs_delivery_methods"]', function (event) {
        // создаём событие
        let deliveryId = jQuery(this).val();
        let fs_change_delivery = new CustomEvent("fs_change_delivery", {
            detail: {deliveryId: deliveryId}
        });
        document.dispatchEvent(fs_change_delivery);
        getCheckoutData();
    });

//Удаление продукта из корзины
    jQuery(document).on('click', '[data-fs-type="product-delete"]', function (event) {
        event.preventDefault();
        var el = jQuery(this);
        var item = el.data('cart-item');
        var sendData = {
            action: 'fs_delete_product', item: item
        };


        if (!el.data('confirm') || confirm(el.data("confirm"))) {
            jQuery.ajax({
                url: fShop.ajaxurl, type: 'POST', data: sendData
            }).success(function (result) {
                if (result.success) {
                    fShop.updateCarts();
                    iziToast.show({
                        theme: 'light', message: result.data.message, position: 'topCenter',

                    });
                    if (el.data('refresh')) {
                        location.reload();
                    }
                } else {
                    iziToast.show({
                        theme: 'light', message: result.data.message, position: 'topCenter',
                    });
                }
            });
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
                url: fShop.ajaxurl, type: 'POST', data: sendData
            }).success(function (result) {
                if (result.success) {
                    iziToast.show({
                        theme: 'light', message: result.data.message, position: 'topCenter',

                    });
                    setTimeout(function () {
                        location.reload();
                    }, 5000);
                } else {
                    iziToast.show({
                        theme: 'light', message: result.data.message, position: 'topCenter',
                    });
                }
            });
        }
    });

// добавление товара к сравнению
    jQuery(document).on('click', '[data-action="add-to-comparison"]', function (event) {
        event.preventDefault();
        var el = jQuery(this);
        jQuery.ajax({
            url: fShop.ajaxurl, type: 'POST', beforeSend: function () {
                el.find('.fs-atc-preloader').fadeIn();
            }, data: {
                action: 'fs_add_to_comparison', product_id: el.data('product-id'), product_name: el.data('product-name')
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
                window.location.href = jQuery(this).val();
            } else {
                window.location.href = jQuery(this).data('fs-redirect');
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
    if (jQuery("#product_slider").length) {
        jQuery("#product_slider").lightGallery();
        if (typeof fs_lightslider_options != "undefined") {
            window.lightSlider = jQuery('#product_slider').lightSlider(fs_lightslider_options);
        }
    }

// Квантификатор товара
    jQuery(document).ready(function (jQuery) {
        // уменьшение к-ва товара на единицу
        jQuery(document).on('click', '[data-fs-count="minus"]', function (e) {
            e.preventDefault();
            let parent = jQuery(this).parents('[data-fs-element="fs-quantity"]');
            let input = parent.find('input');
            let count = Number(input.val())
            let min = input.attr('min') != '' ? Number(input.attr('min')) : 1;
            let step = input.attr('step') ? Number(input.attr('step')) : 1;
            if (count - step >= min) {
                input.val(count - step);
                input.change();
            }
            return false;
        });


        //Изменение к-ва добавляемых продуктов
        jQuery('[data-fs-action="change_count"]').on('change input', function (event) {
            event.preventDefault();
            /* Act on the event */
            let el = $(this);
            var productId = el.data('fs-product-id');
            var count = Number(el.val());
            let step = Number(el.attr('step'));
            if (count < step) {
                el.val(step);
                count = step;
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
    let catalogMinPrice = Number(fShop.catalogMinPrice);
    let catalogMaxPrice = Number(fShop.catalogMaxPrice);
    var u = new Url;
    var p_start = u.query.price_start == undefined ? catalogMinPrice : u.query.price_start;
    var p_end = u.query.price_end == undefined ? catalogMaxPrice : u.query.price_end;

    jQuery('[data-fs-element="range-slider"]').each(function (index, value) {
        var rangeSLider = jQuery(this);
        var sliderWrapper = rangeSLider.parents("[data-fs-element=\"jquery-ui-slider\"]");
        var sliderEnd = $('[data-fs-element="range-end"]');
        var sliderStart = $('[data-fs-element="range-start"]');
        rangeSLider.slider({
            range: true,
            min: catalogMinPrice,
            max: catalogMaxPrice,
            values: [p_start, p_end],
            slide: function (event, ui) {
                if (sliderStart.data("currency")) {
                    sliderStart.html(ui.values[0] + ' <span>' + fShop.fs_currency + '</span>');
                } else {
                    sliderStart.html(ui.values[0]);
                }
                if (sliderEnd.data("currency")) {
                    sliderEnd.html(ui.values[1] + ' <span>' + fShop.fs_currency + '</span>');
                } else {
                    sliderEnd.html(ui.values[1]);
                }
                $('[data-fs-element="range-start-input"]').val(ui.values[0]);
                $('[data-fs-element="range-end-input"]').val(ui.values[1]);
            },
            change: function (event, ui) {
                u.query.fs_filter = fShop.nonce;
                u.query.price_start = ui.values[0];
                u.query.price_end = ui.values[1];
                window.location.href = u.toString();
            }
        });

        if (sliderStart.data("currency")) {
            sliderStart.html(p_start + ' <span>' + fShop.fs_currency + '</span>');
        } else {
            sliderStart.html(p_start);
        }
        if (sliderEnd.data("currency")) {
            sliderEnd.html(p_end + ' <span>' + fShop.fs_currency + '</span>');
        } else {
            sliderEnd.html(p_end);
        }

        sliderWrapper.find('[data-fs-element="range-start-input"]').val(p_start);
        sliderWrapper.find('[data-fs-element="range-end-input"]').val(p_end);
    });


    function createFilterUrl(baseUrl) {
        let start = jQuery('[data-fs-element="range-start-input"]').val();
        let end = jQuery('[data-fs-element="range-end-input"]').val();
        return baseUrl + '&price_start=' + start + '&price_end=' + end;
    }

    jQuery(document).on('input keyup', '[data-fs-element="range-start-input"],[data-fs-element="range-end-input"]', function (event) {
        let baseUrl = jQuery(this).data('url');
        document.location.href = createFilterUrl(baseUrl);
    });

// валидация формы редактирования личных данных
    var userInfoEdit = jQuery('form[name="fs-profile-edit"]');
    userInfoEdit.validate({
        rules: {
            "fs-password": {
                minlength: 6
            }, "fs-repassword": {
                equalTo: "#fs-password"
            }
        }, messages: {
            "fs-repassword": {
                equalTo: "пароль и повтор пароля не совпадают"
            }, "fs-password": {
                minlength: "минимальная длина 6 символов"
            },
        }, submitHandler: function (form) {
            jQuery.ajax({
                url: fShop.ajaxurl, type: 'POST', data: userInfoEdit.serialize(), beforeSend: function () {
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
            }, "fs-repassword": {
                equalTo: "#fs-password"
            }
        }, messages: {
            "fs-repassword": {
                equalTo: "пароль и повтор пароля не совпадают"
            }, "fs-password": {
                minlength: "минимальная длина 6 символов"
            },
        }, submitHandler: function (form) {
            jQuery.ajax({
                url: fShop.ajaxurl, type: 'POST', data: userProfileCreate.serialize(), beforeSend: function () {
                    userProfileCreate.find('.form-info').html('').fadeOut();
                    userProfileCreate.find('.fs-preloader').fadeIn();
                }, success: function (result) {
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
                url: fShop.ajaxurl, type: 'POST', data: loginForm.serialize(), beforeSend: function () {
                    loginForm.find('.form-info').fadeOut().removeClass('bg-danger').html('');
                    loginForm.find('.fs-preloader').fadeIn();
                }
            })
                .done(function (result) {
                    var data = JSON.parse(result);
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
            type: 'POST', url: fShop.ajaxurl, data: formData, success: function (result) {
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
            type: 'POST', url: fShop.ajaxurl, data: formData, success: function (response) {
                if (response.success) {
                    iziToast.show({
                        theme: 'light',
                        title: fShop.getLang('success'),
                        message: response.data.msg,
                        position: 'topCenter'
                    });
                } else {
                    iziToast.show({
                        theme: 'light', title: fShop.getLang('error'), message: response.data.msg, position: 'topCenter'
                    });
                }

                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                }

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
    });
    order_send.validate({
        ignore: [], submitHandler: function (form) {
            jQuery.ajax({
                url: fShop.ajaxurl,
                type: 'POST',
                data: order_send.serialize(),
                dataType: 'json',
                async: false,
                beforeSend: function () {
                    orderSendBtn.html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader">');
                },
                success: function (response) {
                    orderSendBtn.find('button[data-fs-action=order-send]').find('.fs-preloader').fadeOut('slow');
                    /* если статус заказ успешный */
                    if (response.success) {
                        // создаём событие
                        let send_order = new CustomEvent("fs_send_order", {
                            detail: {
                                order_id: response.data.order_id, sum: response.data.sum
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
                    type: 'POST', url: fShop.ajaxurl, data: {
                        action: "fs_set_rating", value: ratingVal, product: productId
                    }, cache: false, success: function (data) {
                        localStorage.setItem("fs_user_voted_" + productId, 1);
                        iziToast.show({
                            theme: 'light',
                            title: 'Поздравляем!',
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
                        id: product_id, image: curentBlock.data('image'), name: product_name, button: curentBlock
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
                url: fShop.ajaxurl, data: {
                    action: 'fs_del_wishlist_pos', position: product_id
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
            type: 'POST', url: fShop.ajaxurl, data: fShop.ajaxData('fs_livesearch', {
                search: searchVal
            }), success: function (data) {
                if (data.success) {
                    if (form.find('.fs-livesearch-data').length) {
                        form.find('.fs-livesearch-data').replaceWith(data.data.html);
                    } else {
                        form.append(data.data.html);
                    }
                }
            }
        });
    })

    $("[name=s]").focusout(function (e) {
        $(".fs-livesearch-data").fadeOut(function () {
        });
    });

    // Активирует радио кнопки на странице оформления покупки
    $(".fs-field-wrap").each(function () {
        $(this).find('.radio').first().addClass('active').find('input').prop('checked', true)
    })

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
            'а': 'a',
            'б': 'b',
            'в': 'v',
            'г': 'g',
            'д': 'd',
            'е': 'e',
            'ё': 'e',
            'ж': 'zh',
            'з': 'z',
            'и': 'i',
            'й': 'j',
            'к': 'k',
            'л': 'l',
            'м': 'm',
            'н': 'n',
            'о': 'o',
            'п': 'p',
            'р': 'r',
            'с': 's',
            'т': 't',
            'у': 'u',
            'ф': 'f',
            'х': 'h',
            'ц': 'c',
            'ч': 'ch',
            'ш': 'sh',
            'щ': 'sh',
            'ъ': space,
            'ы': 'y',
            'ь': space,
            'э': 'e',
            'ю': 'yu',
            'я': 'ya',
            ' ': space,
            '_': space,
            '`': space,
            '~': space,
            '!': space,
            '@': space,
            '#': space,
            '$': space,
            '%': space,
            '^': space,
            '&': space,
            '*': space,
            '(': space,
            ')': space,
            '-': space,
            '\=': space,
            '+': space,
            '[': space,
            ']': space,
            '\\': space,
            '|': space,
            '/': space,
            '.': space,
            ',': space,
            '{': space,
            '}': space,
            '\'': space,
            '"': space,
            ';': space,
            ':': space,
            '?': space,
            '<': space,
            '>': space,
            '№': space
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
        var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
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
        var newParam = key + '=' + val, params = '&' + newParam;

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

// строковая фнкция, которая позволяет производить поиск и замену сразу по нескольким значениям переданным в виде объекта
    String.prototype.allReplace = function (obj) {
        var retStr = this;
        for (var x in obj) {
            retStr = retStr.replace(new RegExp(x, 'g'), obj[x]);
        }
        return retStr;
    };

// получает корзину через шаблон "cartTemplate"  и выводит её внутри "cartWrap"
    function fs_get_cart(cartTemplate, cartWrap) {
        let parameters = {
            action: 'fs_get_cart', template: cartTemplate
        };
        jQuery.ajax({
            type: 'POST', url: fShop.ajaxurl, data: parameters, dataType: 'html', success: function (data) {
                if (data) jQuery(cartWrap).html(data);
            }, error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            }
        });
    }

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

        var button = event.detail.button;

        // Show the basket as a modal window
        if (fShop.getSettings('fs_cart_type') == 'modal') {
            iziToast.show({
                image: event.detail.image,
                imageWidth: $(window).width() > 768 ? 150 : 90,
                theme: 'light',
                timeout: false,
                maxWidth: 540,
                closeOnEscape: true,
                title: fShop.getLang('added'),
                message: fShop.strReplace(fShop.getLang('addToCartButtons'), {
                    '%product%': button.data('name'),
                    '%price%': button.data('price'),
                    '%currency%': button.data('currency')
                }),
                position: 'topCenter',

            });
        } else
            // Show the cart as a sidebar
        if (fShop.getSettings('fs_cart_type') == 'side') {

            let cartWrap = $("[data-fs-action=\"showCart\"]");

            cartWrap.fadeIn(200, function () {
            });

            $("[data-fs-action=\"showCart\"]").on('click', '.close-cart', function (event) {
                event.preventDefault();
                $("[data-fs-action=\"showCart\"]").fadeOut(800, function () {
                    cartWrap.find('[data-fs-element="cart-widget"]').html('');
                });
            });

        }

        button.find('.fs-atc-preloader').fadeOut();
        setTimeout(function () {
            button.find('.fs-atc-info').fadeOut();
        }, 4000);

        event.preventDefault();
    }, false);

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
        let button = event.detail.button;
        button.find('.fs-wh-preloader').fadeOut();
        let afterText = fShop.getLang('addToWishlist');
        iziToast.show({
            image: event.detail.image,
            theme: 'light',
            title: fShop.getLang('success'),
            message: fShop.strReplace(fShop.getLang('addToWishlist'), {
                '%product%': button.data('name'), '%wishlist_url%': fShop.getSettings('wishlistUrl')
            }),
            position: 'topCenter',

        });
        event.preventDefault();
    }, false);

// Срабатывает если покупатель пытается добавить отсуствующий товар в корзину
    document.addEventListener("fsBuyNoAvailable", function (event) {
        iziToast.show({
            theme: 'light', /*icon: 'fas fa-info-circle',*/
            timeout: false,
            maxWidth: 340,
            overlay: true,
            title: 'Сообщить о наличии',
            message: 'Товара &laquo;' + event.detail.name + '&raquo; нет на складе.<br> Оставьте Ваш E-mail и мы сообщим когда товар будет в наличии. <br>',
            position: 'topCenter',
            id: 'report-availability',
            buttons: [['<input type="email" name="userEmail" placeholder="Ваш E-mail">', function (instance, toast) {
                console.log(instance) // using the name of input to get the value
            }, 'input'], ['<button>Отправить</button>', function (instance, toast) {
                let userEmail = $(toast).find('[name="userEmail"]').val();
                $.ajax({
                    type: 'POST', url: fShop.ajaxurl, data: fShop.ajaxData('fs_report_availability', {
                        "email": userEmail,
                        "product_id": event.detail['product-id'],
                        "product_name": event.detail.name,
                        "product_url": event.detail.url,
                        "variation": event.detail.variation,
                        "count": event.detail.count
                    }), success: function (result) {
                        if (result.success) {
                            $(toast).find('.iziToast-message').addClass('success').html(result.data.msg);
                            $(toast).find('.iziToast-buttons').hide();
                        } else {
                            $(toast).find('.iziToast-message').addClass('error').html(result.data.msg);
                        }
                    }, error: function (xhr, ajaxOptions, thrownError) {
                        console.log('error...', xhr);
                        //error logging
                    }, complete: function () {
                        //afer ajax call is completed
                    }
                });

            }]]

        });
    });

    $(document).on('click', '#buyOneClickPopup input', function (event) {
        $(this).focus();
    });
// Обработка кнопки "Покупка в клик"
    document.addEventListener("fsBuyOneClick", function (event) {
        iziToast.show({
            theme: 'light',
            timeout: false,
            maxWidth: 540,
            overlay: true,
            onOpening: function (instance, toast) {
                $(toast).on('submit', 'form', function (event) {
                    event.preventDefault();
                    let form = $(this);
                    let productId = form.find('input[name="product-id"]').val();
                    let cart = new Object;
                    cart[productId] = {
                        'ID': form.find('input[name="product-id"]').val(), 'count': 1, 'attr': [], 'variation': null
                    };
                    $.ajax({
                        type: 'POST', url: fShop.ajaxurl, data: fShop.ajaxData('order_send', {
                            'fs_first_name': form.find('input[name="buyer-name"]').val(),
                            'fs_phone': form.find('input[name="buyer-phone"]').val(),
                            'fs_email': form.find('input[name="buyer-email"]').val(),
                            'cart': cart
                        }), success: function (result) {
                            if (result.success) {
                                $(toast).find('.iziToast-message').text(result.data.msg)
                            }
                        }
                    });
                    return false;

                });
            },
            title: 'Покупка товара &laquo;' + event.detail.name + '&raquo;',
            message: '<form method="post">' + '<div class="row">' + '<div class="col-md-5">' + '<img src="' + event.detail.thumbnail + '" alt="' + event.detail.name + '">' + '<div class="fs-price">' + event.detail.price + ' <span>' + event.detail.currency + '</span></div>' + '</div>' + '<div class="col-md-7">' + '<input type="hidden" name="product-id" value="' + event.detail.id + '">' + '<div class="form-group"><input type="text" name="buyer-name" placeholder="Ваше имя" class="form-control" required></div>' + '<div class="form-group"><input type="tel" name="buyer-phone" placeholder="Номер телефона" class="form-control" required></div>' + '<div class="form-group"><input type="email" name="buyer-email" placeholder="E-mail" class="form-control" required></div>' + '<div class="form-group"><input type="submit" class="btn btn-primary" value="Купить" class="form-control" required></div>' + '</div>' + '</div>' + '</form>',
            position: 'topCenter',
            id: 'buyOneClickPopup',


        });
    });

    // Табы личного кабинента
    $('.fs-dashboard__nav').on('click', ' a', function (event) {
        event.preventDefault();
        let parent = $(this).parents('.fs-dashboard__tabs:first')
        let target = $(this).attr('href')
        parent.find('.fs-dashboard__nav:first li').removeClass('active');
        $(this).parents('li').addClass('active');
        parent.find('.fs-dashboard__tab').removeClass('active')
        parent.find(target).addClass('active')
    });

    // Collapse
    $(document).on('click', '[data-fs-toggle="collapse"]', function (event) {
        event.preventDefault();
        $($(this).data('fs-target')).slideToggle();
    });

})

