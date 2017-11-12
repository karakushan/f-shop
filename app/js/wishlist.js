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
                    image:curentBlock.data('image'),
                    name: product_name,
                    button: curentBlock
                }
            });
            document.dispatchEvent(before_to_wishlist);
        }
    })
        .done(function (result) {
            var ajax_data = jQuery.parseJSON(result);
            // генерируем событие добавления в список желаний
            var add_to_wishlist = new CustomEvent("fs_add_to_wishlist", {
                detail: {id: product_id, name: product_name, button: curentBlock, image:curentBlock.data('image'), ajax_data: ajax_data}
            });
            document.dispatchEvent(add_to_wishlist);

        });
});