//Переадресовываем все фильтры на значение, которое они возвращают
jQuery('[data-fs-action="filter"]').on('change', function (e) {
    e.preventDefault();
    if (jQuery(this).attr('type') == 'checkbox') {
        if (jQuery(this).prop('checked')) {
            console.log('checked');
            window.location.href = jQuery(this).val();
        } else {
            window.location.href = jQuery(this).data('fs-redirect');
            console.log('not-checked');
        }
    } else {
        window.location.href = jQuery(this).val();
    }
});