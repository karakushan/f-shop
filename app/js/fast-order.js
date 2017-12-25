// обработка кнопки быстрого заказа
jQuery('[data-fs-action="quick_order_button"]').on('click', function (event) {
    event.preventDefault();
    var pName = jQuery(this).data('product-name');
    var pId = jQuery(this).data('product-id');
    jQuery('[name="fs_cart[product_name]"]').val(pName);
    jQuery('[name="fs_cart[product_id]"]').val(pId);
});