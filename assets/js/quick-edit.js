/*
 * Post Bulk Edit Script
 * Hooks into the inline post editor functionality to extend it to our custom metadata
 */

jQuery(document).ready(function ($) {

    // Prepopulating our quick-edit post info
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
            // Get field values via AJAX for accurate data
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fs_quick_edit_values',
                    post_id: post_id,
                    fields: ['fs_price', 'fs_articul', 'fs_remaining_amount']
                },
                success: function(response) {
                    if (response.success && response.data.fields) {
                        var fields = response.data.fields;
                        if (fields.fs_price !== undefined) {
                            $("#edit-" + post_id + " .fs_price").val(fields.fs_price);
                        }
                        if (fields.fs_articul !== undefined) {
                            $("#edit-" + post_id + " .fs_vendor_code").val(fields.fs_articul);
                        }
                        if (fields.fs_remaining_amount !== undefined) {
                            $("#edit-" + post_id + " .fs_stock").val(fields.fs_remaining_amount);
                        }
                    }
                }
            });
        }

    };

});