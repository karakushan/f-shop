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
        theme: 'light',
        title: fShop.getLang('success'),
        message: fShop.strReplace(fShop.getLang('addToCart'), {
            '%product%': button.data('name'),
            '%cart_url%': fShop.getSettings('cartUrl')
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
