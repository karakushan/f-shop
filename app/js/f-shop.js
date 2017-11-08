jQuery("[data-fs-action='modal']").on('click', function (e) {
    e.preventDefault();
    var modalId=$(this).attr('href');
    jQuery(modalId).fadeIn();
    console.log(modalId);
})