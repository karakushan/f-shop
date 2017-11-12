/**
 * функция транслитерации
 */
function fs_transliteration(text){
// Символ, на который будут заменяться все спецсимволы
    var space = '-';
// переводим в нижний регистр
    text = text.toLowerCase();

// Массив для транслитерации
    var transl = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e', 'ж': 'zh',
        'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
        'о': 'o', 'п': 'p', 'р': 'r','с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h',
        'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh','ъ': space, 'ы': 'y', 'ь': space, 'э': 'e', 'ю': 'yu', 'я': 'ya',
        ' ': space, '_': space, '`': space, '~': space, '!': space, '@': space,
        '#': space, '$': space, '%': space, '^': space, '&': space, '*': space,
        '(': space, ')': space,'-': space, '\=': space, '+': space, '[': space,
        ']': space, '\\': space, '|': space, '/': space,'.': space, ',': space,
        '{': space, '}': space, '\'': space, '"': space, ';': space, ':': space,
        '?': space, '<': space, '>': space, '№':space
    };

    var result = '';
    var curent_sim = '';

    for(var i=0; i < text.length; i++) {
        // Если символ найден в массиве то меняем его
        if(transl[text[i]] != undefined) {
            if(curent_sim != transl[text[i]] || curent_sim != space){
                result += transl[text[i]];
                curent_sim = transl[text[i]];
            }
        }
        // Если нет, то оставляем так как есть
        else {
            result += text[i];
            curent_sim = text[i];
        }
    }

    result = TrimStr(result);
    return result;

}

function TrimStr(s) {
    s = s.replace(/^-/, '');
    return s.replace(/-$/, '');
}


// открытие модального окна
jQuery(document).on('click', "[data-fs-action='modal']", function (e) {
    e.preventDefault();
    var modalId = jQuery(this).attr('href');
    jQuery(modalId).fadeIn();
})
// закрытие модального окна
jQuery(document).on('click', "[data-fs-action='modal-close']", function (e) {
    e.preventDefault();
    var modalParentlId = jQuery(this).parents('.fs-modal');
    jQuery(modalParentlId).fadeOut();
})
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
//добавление товара в корзину (сессию)
jQuery('[data-action=add-to-cart]').on('click', function (event) {
    event.preventDefault();

    // проверяем выбрал ли пользователь обязательные атибуты товара, например размер
    var fsAttrReq = true;
    jQuery('[name="fs-attr"]').each(function () {
        if (jQuery(this).val() == '') {
            fsAttrReq = false;
            // создаём событие
            var no_selected_attr = new CustomEvent("fs_no_selected_attr");
            document.dispatchEvent(no_selected_attr);
        }
    });

    if (!fsAttrReq) return fsAttrReq;

    var curent = jQuery(this);
    var product_id = curent.data('product-id');
    var attr = curent.data('attr');

    // объект передаваемый в события
    var detail = {
        button: curent,
        id: product_id,
        name: curent.data('product-name'),
        attr: attr,
        image:curent.data('image'),
        success: true,
        text: {
            success: curent.data('success'),
            error: curent.data('error')
        }
    }


    var productObject = {
        "action": 'add_to_cart',
        "attr": attr,
        'post_id': product_id
    };
    jQuery.ajax({
        url: FastShopData.ajaxurl,
        data: productObject,
        beforeSend: function () {
            // создаём событие
            var before_add_product = new CustomEvent("fs_before_add_product", {
                detail: detail
            });
            document.dispatchEvent(before_add_product);
            return before_add_product.success;
        }
    })
        .done(function (result) {
            // создаём событие
            var add_to_cart = new CustomEvent("fs_add_to_cart", {
                detail: detail
            });
            document.dispatchEvent(add_to_cart);
        });

});
//добавление товара в список желаний
jQuery('[data-fs-action="wishlist"]').on('click', function (event) {
    event.preventDefault();
    var product_id = jQuery(this).data('product-id');
    var product_name = jQuery(this).data('name');
    var curentBlock = jQuery(this);
    jQuery.ajax({
        url: FastShopData.ajaxurl,
        data: {action: 'fs_addto_wishlist', product_id: product_id},
        beforeSend: function () {
            // генерируем событие добавления в список желаний
            var before_to_wishlist = new CustomEvent("fs_before_to_wishlist", {
                detail: {
                    id: product_id,
                    image:curentBlock.data('image'),
                    name: product_name,
                    button: curentBlock
                }
            });
            document.dispatchEvent(before_to_wishlist);
        }
    })
        .done(function (result) {
            var ajax_data = jQuery.parseJSON(result);
            // генерируем событие добавления в список желаний
            var add_to_wishlist = new CustomEvent("fs_add_to_wishlist", {
                detail: {id: product_id, name: product_name, button: curentBlock, image:curentBlock.data('image'), ajax_data: ajax_data}
            });
            document.dispatchEvent(add_to_wishlist);

        });
});