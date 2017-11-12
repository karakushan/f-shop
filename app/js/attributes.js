// изменяем атрибуты товара по изменению input radio
jQuery('[data-action="change-attr"]').on('change', function () {
    var curent = jQuery(this);
    var target = jQuery(this).data('target');
    var productId = jQuery(this).data('product-id');
    var attrObj = jQuery('#fs-atc-' + productId).data('attr');
    var name = jQuery(this).attr('name');
    jQuery(target).val(jQuery(this).val());
    attrObj.terms = [];
    jQuery('[name="' + name + '"]').each(function (index) {
        jQuery(this).prop('checked', false);
    });
    jQuery(this).prop('checked', true);

    jQuery('[data-action="change-attr"]').each(function (index) {
        if (jQuery(this).prop('checked') && jQuery(this).val()) {
            attrObj.terms[index] = jQuery(this).val();
        }
    });
    Array.prototype.clean = function(deleteValue) {
        for (var i = 0; i < this.length; i++) {
            if (this[i] == deleteValue) {
                this.splice(i, 1);
                i--;
            }
        }
        return this;
    };
    attrObj.terms.clean(undefined);
    jQuery('#fs-atc-' + productId).attr('data-attr', JSON.stringify(attrObj));
});