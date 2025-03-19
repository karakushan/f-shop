import iziToast from "izitoast";

class FS {
  constructor(is_admin = false) {
    this.ajaxurl = "/wp-admin/admin-ajax.php";
    this.is_admin = is_admin;
    if (this.is_admin) {
      this.nonce = window.FS_BACKEND.nonce ?? null;
    } else {
      this.nonce = window.FS_DATA.nonce ?? null;
    }

    this.nonceField = "fs_secret";
    this.addWishListToCart = this.addWishListToCart.bind(this);
    this.cart = [];
    this.filters = [];
    this.cartType = 'modal';
    this.wishlist = {
      items: [],
      count: 0
    };
    this.modal = {
      modalID: null,
      isOpen: false,
      openModal(modalID) {
        this.modalID = modalID;
        this.isOpen = true;
        document.body.style.overflow = 'hidden';
      },
      closeModal() {
        this.modalID = null;
        this.isOpen = false;
        document.body.style.overflow = '';
      }
    };
  }
  
  getSetting(settingName) {
    if (window.FS_DATA) {
      return typeof window.FS_DATA[settingName] !== 'undefined' ? window.FS_DATA[settingName] : null;
    }
    return null;
  }
  
  getLang(key) {
    if (window.fShop && typeof window.fShop.getLang === 'function') {
      return window.fShop.getLang(key);
    }
    return this.getMessage(key);
  }
  
  strReplace(string, replacements) {
    if (window.fShop && typeof window.fShop.strReplace === 'function') {
      return window.fShop.strReplace(string, replacements);
    }
    
    for (const key in replacements) {
      string = string.replace(new RegExp(key, 'g'), replacements[key]);
    }
    return string;
  }

  getMessage(key) {
    return window.FS_DATA.langs && window.FS_DATA.langs[key]
      ? window.FS_DATA.langs[key]
      : null;
  }

  // Sends a POST request using the fetch method
  post(action, params = {}, files = []) {
    let fData = params instanceof FormData ? params : new FormData();
    for (var key in params) {
      fData.append(key, params[key]);
    }

    if (files.length > 0) {
      for (var i = 0; i < files.length; i++) {
        fData.append("files[]", files[i]);
      }
    }

    fData.append("action", action);
    fData.append(this.nonceField, this.nonce);

    return fetch(this.ajaxurl, {
      method: "POST",
      credentials: "same-origin",
      body: fData,
    }).then((r) => r.json());
  }

  // === WISHLIST ===

  /**
   * Updates wishlist state by fetching latest data
   */
  updateWishlist() {
    return this.post("fs_get_wishlist").then((response) => {
      if (response.success) {
        this.wishlist = response.data;
      }
      return response;
    });
  }

  /**
   * Adds an item to the wishlist by its identifier.
   *
   * @param {string|number} itemId - The identifier of the item to add to the wishlist.
   * @return {Promise<void>} A Promise that resolves once the item is added and
   *                         the appropriate response handling is completed.
   */
  addToWishlist(itemId) {
    return this.post("fs_addto_wishlist", { product_id: itemId }).then(
      (response) => {
        if (response.success) {
          iziToast.success({
            title: response.data.title ?? this.getMessage("success"),
            message: response.data.msg,
            position: "topCenter",
          });
          const wishlistUpdatedEvent = new CustomEvent(
            "fs-wishlist-item-added",
            { detail: { itemId: itemId } }
          );
          window.dispatchEvent(wishlistUpdatedEvent);
          this.updateWishlist();
        } else {
          iziToast.error({
            title: response.data.title ?? this.getMessage("error"),
            message: response.data.msg,
            position: "topCenter",
          });
        }
      }
    );
  }

  // Deletes the wishlist
  cleanWishlist() {
    this.post("fs_clean_wishlist", {}).then((data) => {
      window.location.reload();
    });
  }

  /**
   * Adds all items from the wishlist to the cart by making a server request.
   * Updates the cart information upon a successful operation and displays a success notification.
   *
   * @return {void} This method does not return a value.
   */
  addWishListToCart() {
    this.post("fs_add_wishlist_to_cart").then((response) => {
      // this.updateCarts();
      iziToast.success({
        title: response.data.title,
        message: response.data.message,
        position: "topCenter",
      });
    }).catch((error) => {
      console.error(error);
    });
  }

  /**
   * Removes an item from the wishlist by its identifier.
   *
   * @param {string|number} itemId - The identifier of the wishlist item to be removed.
   * @return {Promise<void>} A Promise that resolves once the item is removed and
   *                         the appropriate response handling is completed.
   */
  removeWishlistItem(itemId) {
    return this.post("fs_del_wishlist_pos", { item_id: itemId }).then(
      (response) => {
        if (response.success) {
          iziToast.success({
            title: response.data.title ?? this.getMessage("success"),
            message: response.data.msg,
            position: "topCenter",
          });
          const wishlistUpdatedEvent = new CustomEvent(
            "fs-wishlist-item-deleted",
            { detail: { itemId: itemId } }
          );
          window.dispatchEvent(wishlistUpdatedEvent);
        } else {
          iziToast.error({
            title: response.data.title ?? this.getMessage("error"),
            message: response.data.msg,
            position: "topCenter",
          });
        }
      }
    );
  }

  // === CART ===
  getCart() {
    return this.post("fs_get_cart", {});
  }

  deleteCartItem(index) {
    return this.post("fs_delete_cart_item", { index: index }).then((r) => {
      const cartUpdatedEvent = new CustomEvent("fs-cart-updated");
      window.dispatchEvent(cartUpdatedEvent);

      return r;
    });
  }

  deleteCart() {
    return this.post("fs_delete_cart", {}).then((r) => {
      const cartUpdatedEvent = new CustomEvent("fs-cart-updated");
      window.dispatchEvent(cartUpdatedEvent);

      return r;
    });
  }

  changeCartCount(index, count) {
    return this.post("fs_change_cart_count", {
      index: index,
      count: count,
    }).then((r) => {
      const cartUpdatedEvent = new CustomEvent("fs-cart-updated");
      window.dispatchEvent(cartUpdatedEvent);

      return r;
    });
  }

  // === ATTRIBUTES ===
  insertAttribute(postId, attributeName, attributeValue) {
    return this.post("fs_add_custom_attribute", {
      post_id: postId,
      name: attributeName,
      value: attributeValue,
    });
  }

  insertChildAttribute(postId, attributeValue, parentId) {
    return this.post("fs_add_child_attribute", {
      post_id: postId,
      value: attributeValue,
      parent_id: parentId,
    });
  }

  getAttributes(postId) {
    return this.post("fs_get_post_attributes", { post_id: postId });
  }

  detachAttribute(postId, attributeId) {
    return this.post("fs_detach_attribute", {
      post_id: postId,
      attribute_id: attributeId,
    });
  }

  attachAttribute(postId, attributeId) {
    return this.post("fs_attach_attribute", {
      post_id: postId,
      attribute_id: attributeId,
    });
  }

  sendOrder($event, order = { cart: [] }) {
    window.dispatchEvent(new CustomEvent("fs-checkout-start-submit"));
    const formData = new FormData($event.target);

    if (order.cart.length === 0 && typeof Alpine.store("FS") !== "undefined") {
      order.cart = Alpine.store("FS").cart;
    }

    if (order.cart.length > 0)
      order.cart.forEach((item, index) => {
        formData.append("cart[" + index + "][ID]", item.ID);
        formData.append("cart[" + index + "][count]", item.count);
      });

    return this.post("order_send", formData).then((r) => {
      window.dispatchEvent(new CustomEvent("fs-checkout-finish-submit"));
      if (r.success) {
        if (r.data.redirect.length) window.location.href = r.data.redirect;
      }
      return r;
    });
  }

  cloneOrder(orderId) {
    return this.post("fs_clone_order", { order_id: orderId });
  }

  /**
   * Добавляет товар в корзину
   * 
   * @param {number|string} productId - ID товара
   * @param {number} count - количество товара
   * @param {number|null} variation - ID вариации товара
   * @param {object} attr - атрибуты товара
   * @returns {Promise} - промис с результатом запроса
   */
  addToCart(productId, count = 1, variation = null, attr = {}) {
    // Подготавливаем объект с данными товара для событий
    const detail = {
      id: productId,
      count: count,
      variation: variation,
      attr: attr,
      success: true
    };
    
    // Вызываем событие перед добавлением товара в корзину
    document.dispatchEvent(new CustomEvent("fs_before_add_product", { detail }));
    
    // Делаем AJAX запрос для добавления товара в корзину
    return this.post('add_to_cart', {
      post_id: productId,
      count: count,
      variation: variation,
      attr: JSON.stringify(attr)
    }).then(result => {
      if (result.success) {
        // Добавляем детали товара из результата запроса
        this._updateProductDetails(detail, result);
        
        // Вызываем события успешного добавления товара в корзину
        this._triggerCartEvents(detail, result.data.product);
        
        // Отображаем уведомление или корзину в зависимости от настроек
        this._showCartNotification(detail);
        
        return result;
      } else {
        return result;
      }
    });
  }
  
  /**
   * Обновляет детали товара из результата запроса
   * 
   * @param {object} detail - объект с деталями товара
   * @param {object} result - результат запроса
   * @private
   */
  _updateProductDetails(detail, result) {
    if (result.data && result.data.product) {
      detail.name = result.data.product.name;
      detail.price = result.data.product.price;
      detail.currency = result.data.product.currency;
      detail.image = result.data.product.thumbnail;
    }
  }
  
  /**
   * Вызывает события успешного добавления товара в корзину
   * 
   * @param {object} detail - объект с деталями товара
   * @private
   */
  _triggerCartEvents(detail, product) {
    // Событие добавления товара в корзину
    document.dispatchEvent(new CustomEvent("fs_add_to_cart", {detail: {
      product: product,
      data: detail
    }}));
    
    // Событие обновления корзины
    window.dispatchEvent(new CustomEvent('fs-cart-updated'));
  }
  
  /**
   * Отображает уведомление или корзину в зависимости от настроек
   * 
   * @param {object} detail - объект с деталями товара
   * @private
   */
  _showCartNotification(detail) {
    // Получаем тип отображения корзины из настроек или используем значение по умолчанию
    const cartType = this.getSetting('fs_cart_type') || this.cartType;
    
    if (cartType === 'modal') {
      // Отображаем всплывающее окно с информацией о добавленном товаре
      this._showModalNotification(detail);
    }
  }
  
  /**
   * Отображает всплывающее окно с информацией о добавленном товаре
   * 
   * @param {object} detail - объект с деталями товара
   * @private
   */
  _showModalNotification(detail) {
    iziToast.show({
      image: detail.image,
      imageWidth: window.innerWidth > 768 ? 150 : 90,
      theme: 'light',
      timeout: false,
      maxWidth: 540,
      closeOnEscape: true,
      title: this.getLang('added'),
      message: this.strReplace(this.getLang('addToCartButtons'), {
        '%product%': detail.name,
        '%price%': detail.price,
        '%currency%': detail.currency
      }),
      position: 'topCenter',
    });
  }
  
  /**
   * Отображает корзину как боковую панель
   * 
   * @private
   */
  _showSideCart() {
    const cartWrap = document.querySelector("[data-fs-action=\"showCart\"]");
    if (cartWrap) {
      cartWrap.style.display = 'block';
      
      // Обработка кнопки закрытия боковой корзины
      const closeBtn = cartWrap.querySelector('.close-cart');
      if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
          e.preventDefault();
          cartWrap.style.display = 'none';
          const cartWidget = cartWrap.querySelector('[data-fs-element="cart-widget"]');
          if (cartWidget) {
            cartWidget.innerHTML = '';
          }
        });
      }
    }
  }

  getCategoryAttributes(attributeId, categoryId = null) {
    return this.post("fs_get_category_attributes", {
      category_id: categoryId,
      attribute_id: attributeId,
    });
  }

  getUriAttributes(url) {
    const urlObj = new URL(url);
    const params = new URLSearchParams(urlObj.search);
    const paramsArray = [];
    params.forEach((value, key) => {
      paramsArray.push(value);
    });
    return paramsArray.map((v) => parseInt(v));
  }

  // === AUTH ===
  login($event) {
    const formData = new FormData($event.target);
    return this.post("fs_login", formData);
  }

  register($event) {
    const formData = new FormData($event.target);

    return this.post("fs_profile_create", formData);
  }

  resetPassword($event) {
    const formData = new FormData($event.target);

    return this.post("fs_lostpassword", formData);
  }

  liveSearch(query) {
    return this.post("fs_livesearch", { search: query });
  }

  // === Products ===
  // Подсчет цены товара с учетом атрибутов указанных в вариациях
  calculatePrice(productId, attributes = {}) {
    return this.post("fs_calculate_price", {
      product_id: productId,
      attributes: Object.values(attributes),
    });
  }

  getMaxMinPrice(term_id) {
    return this.post("fs_get_max_min_price", { term_id: term_id });
  }

  getCategoryBrands(term_id) {
    return this.post("fs_get_category_brands", { term_id: term_id });
  }

  getProductComments(post_id, per_page) {
    return this.post("fs_get_product_comments", {
      post_id: post_id,
      per_page: per_page,
    });
  }

  sendProductComment(post_id, comment, files = []) {
    return this.post(
      "fs_send_product_comment",
      {
        post_id: post_id,
        name: comment.name,
        email: comment.email,
        body: comment.body,
        rating: comment.rating,
      },
      files
    );
  }

  commentLikeDislike(comment_id, type = "like") {
    return this.post("fs_comment_like_dislike", {
      comment_id: comment_id,
      type: type,
    });
  }

  // Sets the product rating
  setRating(post_id, rating) {
    let voted_products = localStorage.getItem("voted_products");
    if (voted_products) voted_products = JSON.parse(voted_products);

    // If you've already voted, we'll show an error.
    if (voted_products && voted_products[post_id]) {
      iziToast.error({
        message: this.getMessage("ratingError"),
        position: "topCenter",
      });
      return;
    }

    if (!voted_products) voted_products = {};
    voted_products[post_id] = rating;

    this.post("fs_set_rating", { product: post_id, value: rating }).then(
      (r) => {
        if (r.success) {
          localStorage.setItem(
            "voted_products",
            JSON.stringify(voted_products)
          );
          iziToast.success({
            title: r.data.title,
            message: r.data.msg,
            position: "topCenter",
          });
        }
      }
    );
  }

  /**
   * Cleans the viewed products list for the current user
   * 
   * @return {Promise} A promise that resolves when the viewed products are cleared
   */
  cleanViewedProducts() {
    return this.post("fs_clean_viewed_products").then((response) => {
      if (response.success) {
        iziToast.success({
          title: response.data.title ?? this.getMessage("success"),
          message: response.data.msg,
          position: "topCenter",
        });
        window.location.reload();
      } else {
        iziToast.error({
          title: response.data.title ?? this.getMessage("error"), 
          message: response.data.msg,
          position: "topCenter",
        });
      }
    });
  }
}

export default FS;
