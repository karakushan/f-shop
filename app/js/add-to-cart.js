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
        message: 'Товар &laquo;'+event.detail.name+'&raquo; добавлен в корзину!',
        position: 'topCenter',

    });
    button.find('.fs-atc-preloader').fadeOut();
    setTimeout(function () {
        button.find('.fs-atc-info').fadeOut();
    }, 4000);

    event.preventDefault();
}, false);