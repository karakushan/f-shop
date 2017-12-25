//живой поиск по сайту
jQuery('form[name="live-search"]').on('click', '.close-search', function (event) {
    event.preventDefault();
    jQuery(this).parents('.search-results').fadeOut(0);
});
jQuery('form[name="live-search"] input[name="s"]').on('keyup focus click input', function (event) {
    event.preventDefault();
    var search_input = jQuery(this);
    var search = jQuery(this).val();
    var parents_form = search_input.parents('form');
    var results_div = parents_form.find('.search-results');
    if (search.length > 1) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: {action: 'fs_livesearch', s: search},
            beforeSend: function () {
                search_input.next().addClass('search-animate');
            }
        })
            .done(function (data) {
                results_div.fadeIn(800).html(data);

            })
            .always(function () {
                search_input.next().removeClass('search-animate');
            });
    } else {
        results_div.fadeOut(800).html('');
    }


});