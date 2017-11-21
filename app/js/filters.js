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