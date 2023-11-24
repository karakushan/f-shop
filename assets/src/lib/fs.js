import iziToast from "izitoast";

class FS {
    constructor(is_admin=false) {
        this.ajaxurl = '/wp-admin/admin-ajax.php';
        this.is_admin = is_admin;
        if (this.is_admin) {
            this.nonce = window.FS_BACKEND.nonce ?? null;
        } else {
            this.nonce = window.fShop.nonce ?? null;
        }

        this.nonceField = 'fs_secret';

        this.addWishListToCart = this.addWishListToCart.bind(this)
    }

    // Sends a POST request using the fetch method
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

    // === WISHLIST ===

    // Deletes the wishlist
    cleanWishlist() {
        this.post('fs_clean_wishlist', {})
            .then((response) => response.json())
            .then((data) => {
                    window.location.reload()
                }
            )
    }

    // Adds the entire wishlist to the cart
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
}

export default FS;