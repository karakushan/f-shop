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