//живой поиск по сайту
jQuery('input[name="s"]').on('keyup input click', function (event) {
    event.preventDefault();
    var searchField = jQuery(this);
    var search = jQuery(this).val();
    var form = searchField.parents('form');
    form.css("position", "relative");
    var results = form.find('.livesearch-wrapper');
    if (search.length > 1) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: {action: 'fs_livesearch', s: search},
            beforeSend: function () {
                if (searchField.prev().hasClass('fs-ls-preloader')) {
                    searchField.prev().fadeIn();
                } else {
                    searchField.before('<img src="/wp-content/plugins/f-shop/assets/img/blocks-preloader.svg" class="fs-ls-preloader" style="display: none;">');
                }

            }
        })
            .done(function (data) {
                if (data.length) {
                    if (searchField.next().hasClass('livesearch-wrapper')) {
                        searchField.next().html(data);
                    } else {
                        searchField.after("<div class='livesearch-wrapper'>" + data + "</div>");
                    }
                    form.find('.fs-ls-preloader').fadeOut();
                    form.find('.livesearch-wrapper').fadeIn();
                }

            })
            .always(function () {
                searchField.next().removeClass('search-animate');
            });
    } else {
        results.fadeOut().html('');
    }


});
// Скрываем результаты при потере фокуса input
jQuery(document).on('click', '.fs-ls-close', function (event) {
    event.preventDefault();
    var searchField = jQuery(this);
    var form = searchField.parents('form');
    form.find('.livesearch-wrapper').fadeOut().html('');
});
