jQuery(document).ready(function ($) {
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
        iziToast.show({
            image: event.detail.image,
            imageWidth: 150,
            theme: 'light',
            timeout: false,
            maxWidth: 540,
            closeOnEscape: true,
            title: fShop.getLang('success'),
            message: fShop.strReplace(fShop.getLang('addToCartButtons'), {
                '%product%': button.data('name'),
                '%price%': button.data('price'),
                '%currency%': button.data('currency')
            }),
            position: 'topCenter',

        });
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
                '%product%': button.data('name'),
                '%wishlist_url%': fShop.getSettings('wishlistUrl')
            }),
            position: 'topCenter',

        });
        event.preventDefault();
    }, false);

// Срабатывает если покупатель пытается добавить отсуствующий товар в корзину
    document.addEventListener("fsBuyNoAvailable", function (event) {
        iziToast.show({
            theme: 'light',
            /*icon: 'fas fa-info-circle',*/
            timeout: false,
            maxWidth: 340,
            overlay: true,
            title: 'Сообщить о наличии',
            message: 'Товара &laquo;' + event.detail.name + '&raquo; нет на складе.<br> Оставьте Ваш E-mail и мы сообщим когда товар будет в наличии. <br>',
            position: 'topCenter',
            id: 'report-availability',
            buttons: [
                ['<input type="email" name="userEmail" placeholder="Ваш E-mail">', function (instance, toast) {
                    console.log(instance) // using the name of input to get the value
                }, 'input'],
                ['<button>Отправить</button>', function (instance, toast) {
                    let userEmail = $(toast).find('[name="userEmail"]').val();
                    $.ajax({
                        type: 'POST',
                        url: fShop.ajaxurl,
                        data: fShop.ajaxData('fs_report_availability', {
                            "email": userEmail,
                            "product_id": event.detail['product-id'],
                            "product_name": event.detail.name,
                            "product_url": event.detail.url,
                            "variation": event.detail.variation,
                            "count": event.detail.count
                        }),
                        success: function (result) {
                            if (result.success) {
                                $(toast).find('.iziToast-message').addClass('success').html(result.data.msg);
                                $(toast).find('.iziToast-buttons').hide();
                            } else {
                                $(toast).find('.iziToast-message').addClass('error').html(result.data.msg);
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

                }]
            ]

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
                        'ID': form.find('input[name="product-id"]').val(),
                        'count': 1,
                        'attr': [],
                        'variation': null
                    };
                    $.ajax({
                        type: 'POST',
                        url: fShop.ajaxurl,
                        data: fShop.ajaxData('order_send', {
                            'fs_first_name': form.find('input[name="buyer-name"]').val(),
                            'fs_phone': form.find('input[name="buyer-phone"]').val(),
                            'fs_email': form.find('input[name="buyer-email"]').val(),
                            'cart': cart
                        }),
                        success: function (result) {
                            if (result.success) {
                                $(toast).find('.iziToast-message').text(result.data.msg)
                            }
                        }
                    })
                    ;
                    return false;

                });
            },
            title: 'Покупка товара &laquo;' + event.detail.name + '&raquo;',
            message:
                '<form method="post">' +
                '<div class="row">' +
                '<div class="col-md-5">' +
                '<img src="' + event.detail.thumbnail + '" alt="' + event.detail.name + '">' +
                '<div class="fs-price">' + event.detail.price + ' <span>' + event.detail.currency + '</span></div>' +
                '</div>' +
                '<div class="col-md-7">' +
                '<input type="hidden" name="product-id" value="' + event.detail.id + '">' +
                '<div class="form-group"><input type="text" name="buyer-name" placeholder="Ваше имя" class="form-control" required></div>' +
                '<div class="form-group"><input type="tel" name="buyer-phone" placeholder="Номер телефона" class="form-control" required></div>' +
                '<div class="form-group"><input type="email" name="buyer-email" placeholder="E-mail" class="form-control" required></div>' +
                '<div class="form-group"><input type="submit" class="btn btn-primary" value="Купить" class="form-control" required></div>' +
                '</div>' +
                '</div>' +
                '</form>',
            position: 'topCenter',
            id: 'buyOneClickPopup',


        });
    });
});
