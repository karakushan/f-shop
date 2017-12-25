// валидация и отправка формы заказа
var validator = jQuery('[name="fs-order-send"]');
jQuery('[data-fs-action="order-send"]').click(function (e) {
        e.preventDefault();
        validator.submit();
    }
)
validator.validate({
    ignore: [],
    submitHandler: function (form) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: validator.serialize(),
            beforeSend: function () {
                jQuery('[data-fs-action="order-send"]').html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader">');
            }
        })
            .done(function (response) {
                jQuery('button[data-fs-action=order-send]').find('.fs-preloader').fadeOut('slow');
                if (!IsJsonString(response)) return false;
                var jsonData = JSON.parse(response);
                /* если статус заказ успешный */
                if (jsonData.success) {
                    // создаём событие
                    var send_order = new CustomEvent("fs_send_order", {
                        detail: {
                            order_id: jsonData.order_id,
                            sum: jsonData.sum
                        }
                    });
                    document.dispatchEvent(send_order);

                    jQuery('[data-fs-action="order-send"]').html('Отправлено');
                    if (jsonData.redirect != false) document.location.href = jsonData.redirect;
                } else {
                    if (jsonData.error_code != 'undefined') {
                        console.log(jsonData.error_code);
                    }
                    jQuery('[name="fs-order-send"] .fs-form-info').addClass('error').html(jsonData.text).fadeIn();
                    jQuery('[data-fs-action="order-send"]').html('Отправить');
                }
            });


    }
});