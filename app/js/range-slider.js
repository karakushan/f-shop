//слайдер диапазона цены

var u = new Url;
var p_start = u.query.price_start == undefined ? 0 : u.query.price_start;
var p_end = u.query.price_end == undefined ? FastShopData.fs_slider_max : u.query.price_end;


jQuery('[data-fs-element="range-slider"]').slider({
    range: true,
    min: 0,
    max: FastShopData.fs_slider_max,
    values: [p_start, p_end],
    slide: function (event, ui) {
        jQuery('[data-fs-element="range-end"] ').html('<span>' + ui.values[1] + '</span> ' + FastShopData.fs_currency);
        jQuery('[data-fs-element="range-start"] ').html(ui.values[0] + ' ' + FastShopData.fs_currency);
        jQuery("#slider-range > .ui-slider-handle:nth-child(2)").html('<span><span class="val">' + ui.values[0] + '</span>&nbsp;' + FastShopData.fs_currency + '</span>');
        jQuery("#slider-range > .ui-slider-handle:nth-child(3)").html('<span><span class="val">' + ui.values[1] + '</span>&nbsp;' + FastShopData.fs_currency + '</span>');
        jQuery('[data-fs-element="range-start-input"]').val(ui.values[0]);
        jQuery('[data-fs-element="range-end-input"]').val(ui.values[1]);
    },
    change: function (event, ui) {

        u.query.fs_filter = FastShopData.fs_nonce;
        u.query.price_start = ui.values[0];
        u.query.price_end = ui.values[1];
        // console.log(u.toString());
        window.location.href = u.toString();


    }
});
jQuery('[data-fs-element="range-end"] ').html(p_end + ' ' + FastShopData.fs_currency);
jQuery('[data-fs-element="range-start"] ').html(p_start + ' ' + FastShopData.fs_currency);

jQuery('[data-fs-element="range-start-input"]').val(p_start);
jQuery('[data-fs-element="range-end-input"]').val(p_end);

jQuery("#slider-range > .ui-slider-handle:nth-child(2)").html('<span><span class="val">' + p_start + '</span>&nbsp;' + FastShopData.fs_currency + '</span>');
jQuery("#slider-range > .ui-slider-handle:nth-child(3)").html('<span><span class="val">' + p_end + '</span>&nbsp;' + FastShopData.fs_currency + '</span>');
jQuery("#minPrice .val").html(p_start + ' ' + FastShopData.fs_currency);
jQuery("#maxPrice .val").html(p_end + ' ' + FastShopData.fs_currency);
