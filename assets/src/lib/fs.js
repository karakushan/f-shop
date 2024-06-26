import iziToast from "izitoast";

class FS {
    constructor(is_admin = false) {
        this.ajaxurl = '/wp-admin/admin-ajax.php';
        this.is_admin = is_admin;
        if (this.is_admin) {
            this.nonce = window.FS_BACKEND.nonce ?? null;
        } else {
            this.nonce = window.FS_DATA.nonce ?? null;
        }

        this.nonceField = 'fs_secret';
        this.addWishListToCart = this.addWishListToCart.bind(this)
        this.cart = [];
        this.filters = [];
    }

    // Sends a POST request using the fetch method
    post(action, params = {}) {
        let fData = params instanceof FormData ? params : new FormData();
        for (var key in params) {
            fData.append(key, params[key]);
        }

        fData.append('action', action);
        fData.append(this.nonceField, this.nonce);
        return fetch(this.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: fData,
        }).then((r) => r.json());
    }

    // === WISHLIST ===

    // Deletes the wishlist
    cleanWishlist() {
        this.post('fs_clean_wishlist', {})
            .then((data) => {
                    window.location.reload()
                }
            )
    }

    // Adds the entire wishlist to the cart
    addWishListToCart() {
        this.post('fs_add_wishlist_to_cart', {})
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

    // === ATTRIBUTES ===
    insertAttribute(postId, attributeName, attributeValue) {
        return this.post('fs_add_custom_attribute', {post_id: postId, name: attributeName, value: attributeValue})
    }

    insertChildAttribute(postId, attributeValue, parentId) {
        return this.post('fs_add_child_attribute', {post_id: postId, value: attributeValue, parent_id: parentId})
    }

    getAttributes(postId) {
        return this.post('fs_get_post_attributes', {post_id: postId})
    }

    detachAttribute(postId, attributeId) {
        return this.post('fs_detach_attribute', {post_id: postId, attribute_id: attributeId})
    }

    attachAttribute(postId, attributeId) {
        return this.post('fs_attach_attribute', {post_id: postId, attribute_id: attributeId})
    }

    sendOrder($event, order = {cart: []}) {
        window.dispatchEvent(new CustomEvent('fs-checkout-start-submit'));
        const formData = new FormData($event.target);

        if (order.cart.length === 0 && typeof Alpine.store('FS') !== 'undefined') {
            order.cart = Alpine.store('FS').cart;
        }

        if (order.cart.length > 0)
            order.cart.forEach((item, index) => {
                formData.append('cart[' + index + '][ID]', item.ID)
                formData.append('cart[' + index + '][count]', item.count)
            })

        return this.post('order_send', formData)
            .then((r) => {
                window.dispatchEvent(new CustomEvent('fs-checkout-finish-submit'));
                if (r.success) {
                    if (r.data.redirect.length) window.location.href = r.data.redirect;
                }
                return r;
            })
    }

    addToCart(productId, count = 1) {
        return this.cart.push({ID: productId, count: count})
    }

    getCategoryAttributes(attributeId, categoryId = null) {
        return this.post('fs_get_category_attributes', {category_id: categoryId, attribute_id: attributeId})
    }

    getUriAttributes(url) {
        const urlObj = new URL(url);
        const params = new URLSearchParams(urlObj.search);
        const paramsArray = [];
        params.forEach((value, key) => {
            paramsArray.push(value);
        });
        return paramsArray.map(v => parseInt(v));
    }

    login(data) {
        return this.post('fs_login', data)
    }

    liveSearch(query) {
        return this.post('fs_livesearch', {search: query})
    }

}

export default FS;