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