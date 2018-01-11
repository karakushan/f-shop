// открытие модального окна
jQuery(document).on('click', "[data-fs-action='modal']", function (e) {
    e.preventDefault();
    var modalId = jQuery(this).attr('href');
    jQuery(modalId).fadeIn();
});
// закрытие модального окна
jQuery(document).on('click', "[data-fs-action='modal-close']", function (e) {
    e.preventDefault();
    var modalParentlId = jQuery(this).parents('.fs-modal');
    jQuery(modalParentlId).fadeOut();
});