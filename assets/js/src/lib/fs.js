import iziToast from "izitoast";

class FS {
    constructor() {
        this.ajaxurl = window.fShop.ajaxurl;
        this.nonce = window.fShop.nonce;
        this.nonceField = 'fs_secret';
        this.cleanWishList = this.cleanWishList.bind(this)
        this.addWishListToCart = this.addWishListToCart.bind(this)
    }

    post(action, params) {
        let data = new FormData();
        data.append('action', action);
        data.append(this.nonceField, this.nonce);
        for (let key in params) {
            data.append(key, params[key]);
        }
        return fetch(this.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data,
        })
    }

    cleanWishList() {
        this.post('fs_clean_wishlist', {})
            .then((response) => response.json())
            .then((data) => {
                    window.location.reload()
                }
            )
    }

    addWishListToCart() {
        this.post('fs_add_wishlist_to_cart', {})
            .then((response) => response.json())
            .then((response) => {
                    this.updateCarts();
                    iziToast.success({
                        title: response.data.title,
                        message: response.data.message,
                        position: 'topCenter'
                    });
                }
            )
    }

    updateCarts() {
        let self = this;
        jQuery("[data-fs-element=\"cart-widget\"]").each(function () {
            let templatePath = "cart-widget/widget";
            if (jQuery(this).data("template") != "") {
                templatePath = jQuery(this).data("template");
            }
            self.fs_get_cart(templatePath, this);
        });
    }

    fs_get_cart(cartTemplate, cartWrap) {
        let parameters = {
            action: 'fs_get_cart', template: cartTemplate
        };
        jQuery.ajax({
            type: 'POST', url: fShop.ajaxurl, data: parameters, dataType: 'html', success: function (data) {
                if (data) jQuery(cartWrap).html(data);
            }, error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            }
        });
    }
}

export default FS;