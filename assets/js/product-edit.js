jQuery(document).ready(function ($) {

    $.validator.addMethod('lessThanEqual', function (value, element, param) {
        if (this.optional(element)) return true;
        let i = parseFloat(value);
        let j = parseFloat(param);
        return i < j;

    }, "Значение должно быть меньше {0}");

    $("#post").validate({
        // debug: true,
        rules: {
            fs_price: {
                required: true,
                number: true,
            },
            fs_action_price: {
                lessThanEqual: $('[name = "fs_price"]').val()
            }
        }
    });

});

