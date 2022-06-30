/*
 * Post Bulk Edit Script
 * Hooks into the inline post editor functionality to extend it to our custom metadata
 */

jQuery(document).ready(function ($) {

    /*//Prepopulating our quick-edit post info
    var $inline_editor = inlineEditPost.edit;
    inlineEditPost.edit = function (id) {

        //call old copy
        $inline_editor.apply(this, arguments);

        //our custom functionality below
        var post_id = 0;
        if (typeof(id) == 'object') {
            post_id = parseInt(this.getId(id));
        }

        if (post_id != 0) {
            //цена
            var price = $("#post-" + post_id + ' .fs-price-blank').text();
            $("#edit-" + post_id + " .fs_price").val(price);

            var vendor_code = $("#post-" + post_id + ' .fs_vendor_code').text();
            $("#edit-" + post_id + " .fs_vendor_code").val(vendor_code);

            var in_stock = $("#post-" + post_id + ' .fs_stock_real').text();
            $("#edit-" + post_id + " .fs_stock").val(in_stock);
        }

    }*/

});