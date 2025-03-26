import "./scss/f-shop.scss";

// GLightbox
import FsLightbox from "fslightbox";

import Swiper from "swiper";
import { Controller, Navigation, Pagination } from "swiper/modules";
import FS from "./lib/fs";

// Initialize Alpine Store for FS
if (typeof Alpine !== "undefined") {
  Alpine.store("FS", new FS());
  Alpine.store("FS").loading = false;
  Alpine.store("FS").showNoAvailableModal = false;
  Alpine.store("FS").noAvailableProductData = null;

  // Add no-available modal template to the page
  document.addEventListener("DOMContentLoaded", function () {
    // Create modal template if it doesn't exist
    if (!document.getElementById("fs-no-available-modal")) {
      const modalTemplate = document.createElement("div");
      modalTemplate.id = "fs-no-available-modal";
      modalTemplate.innerHTML = `
                <div 
                    x-data 
                    x-show="$store.FS.showNoAvailableModal" 
                    x-transition.opacity 
                    class="fs-modal-overlay"
                    style="display: none;"
                >
                    <div class="fs-modal-container">
                        <div class="fs-modal-header">
                            <h3>Сообщить о наличии</h3>
                            <button @click="$store.FS.showNoAvailableModal = false" class="fs-modal-close">&times;</button>
                        </div>
                        <div class="fs-modal-body">
                            <p>Товара &laquo;<span x-text="$store.FS.noAvailableProductData ? $store.FS.noAvailableProductData.name : ''"></span>&raquo; нет на складе.</p>
                            <p>Оставьте Ваш E-mail и мы сообщим когда товар будет в наличии.</p>
                            <form @submit.prevent="
                                $store.FS.post('fs_report_availability', {
                                    email: $event.target.email.value,
                                    product_id: $store.FS.noAvailableProductData['product-id'],
                                    product_name: $store.FS.noAvailableProductData.name,
                                    product_url: $store.FS.noAvailableProductData.url,
                                    variation: $store.FS.noAvailableProductData.variation,
                                    count: $store.FS.noAvailableProductData.count
                                }).then(result => {
                                    if (result.success) {
                                        $refs.formContainer.style.display = 'none';
                                        $refs.successMessage.textContent = result.data.msg;
                                        $refs.successMessage.style.display = 'block';
                                        setTimeout(() => {
                                            $store.FS.showNoAvailableModal = false;
                                        }, 3000);
                                    } else {
                                        $refs.errorMessage.textContent = result.data.msg;
                                        $refs.errorMessage.style.display = 'block';
                                    }
                                })
                            ">
                                <div x-ref="formContainer">
                                    <div class="fs-form-group">
                                        <input type="email" name="email" placeholder="Ваш E-mail" required class="fs-form-control">
                                    </div>
                                    <div class="fs-form-group">
                                        <button type="submit" class="fs-btn fs-btn-primary">Отправить</button>
                                    </div>
                                </div>
                                <div x-ref="successMessage" class="fs-alert fs-alert-success" style="display: none;"></div>
                                <div x-ref="errorMessage" class="fs-alert fs-alert-danger" style="display: none;"></div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
      document.body.appendChild(modalTemplate);

      // Add basic styles if they don't exist
      if (!document.getElementById("fs-modal-styles")) {
        const styles = document.createElement("style");
        styles.id = "fs-modal-styles";
        styles.textContent = `
                    .fs-modal-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: rgba(0, 0, 0, 0.5);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                    }
                    .fs-modal-container {
                        background-color: white;
                        border-radius: 5px;
                        max-width: 500px;
                        width: 90%;
                        padding: 20px;
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                    }
                    .fs-modal-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 15px;
                    }
                    .fs-modal-close {
                        background: none;
                        border: none;
                        font-size: 24px;
                        cursor: pointer;
                    }
                    .fs-form-group {
                        margin-bottom: 15px;
                    }
                    .fs-form-control {
                        width: 100%;
                        padding: 8px 12px;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                    }
                    .fs-btn {
                        padding: 8px 16px;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                    }
                    .fs-btn-primary {
                        background-color: #4CAF50;
                        color: white;
                    }
                    .fs-alert {
                        padding: 12px;
                        border-radius: 4px;
                        margin-bottom: 15px;
                    }
                    .fs-alert-success {
                        background-color: #d4edda;
                        color: #155724;
                    }
                    .fs-alert-danger {
                        background-color: #f8d7da;
                        color: #721c24;
                    }
                `;
        document.head.appendChild(styles);
      }
    }
  });
}

window.Swiper = Swiper;

document.addEventListener("DOMContentLoaded", function () {
  const thumbsSwiper = new Swiper("#productGalleryThumbs", {
    modules: [Controller],
    direction: "vertical",
    slidesPerView: 5,
    spaceBetween: 10,
    grabCursor: true,
    loop: false,
    centeredSlidesBounds: true,
    thumbs: {
      swiper: mainGallerySwiper,
    },
  });

  const mainGallerySwiper = new Swiper("#productGallery", {
    slidesPerView: 1,
    loop: false,
    modules: [Navigation, Pagination, Controller],

    navigation: {
      nextEl: ".fs-swiper-next",
      prevEl: ".fs-swiper-prev",
    },
    pagination: {
      el: ".swiper-pagination",
    },
    controller: {
      control: thumbsSwiper,
    },
    thumbs: {
      swiper: thumbsSwiper,
    },
  });

  // Ensure the current slide is set as active
  mainGallerySwiper.on("slideChange", function () {
    const activeIndex = mainGallerySwiper.activeIndex;
    thumbsSwiper.slideTo(activeIndex);
    // Activate the current thumbnail slide to match the main gallery
    thumbsSwiper.slides.forEach((slide) =>
      slide.classList.remove("swiper-slide-active")
    );
    const activeThumbnail = thumbsSwiper.slides[mainGallerySwiper.activeIndex];
    if (activeThumbnail) {
      activeThumbnail.classList.add("swiper-slide-active");
    }
  });

  thumbsSwiper.on("click", function (swiper, event) {
    const clickedIndex = swiper.clickedIndex;
    if (clickedIndex !== undefined) {
      mainGallerySwiper.slideTo(clickedIndex);
    }

    thumbsSwiper.slideTo(mainGallerySwiper.activeIndex);
    // Activate the current thumbnail slide to match the main gallery
    thumbsSwiper.slides.forEach((slide) =>
      slide.classList.remove("swiper-slide-active")
    );
    const activeThumbnail = thumbsSwiper.slides[mainGallerySwiper.activeIndex];
    if (activeThumbnail) {
      activeThumbnail.classList.add("swiper-slide-active");
    }
  });

  window.FsLightbox = FsLightbox;
});

jQuery(document).ready(function ($) {
  let fShop = Object.assign(
    {
      getLang: function (string) {
        return this.langs[string];
      }, // Выполняет поиск значения value в массиве array
      find: function find(array, value) {
        if (array.indexOf) {
          // если метод существует
          return array.indexOf(value);
        }
        for (var i = 0; i < array.length; i++) {
          if (array[i] === value) return i;
        }
        return -1;
      },
      ajaxData: function (action, data) {
        data.action = action;
        data.fs_secret = this.nonce;
        return data;
      },
      strReplace: function (string, obj) {
        for (var x in obj) {
          string = string.replace(new RegExp(x, "g"), obj[x]);
        }
        return string;
      },
      getSettings: function (settingName) {
        return this[settingName];
      },
      updateCarts: function () {
        jQuery('[data-fs-element="cart-widget"]').each(function () {
          let templatePath = "cart-widget/widget";
          if (jQuery(this).data("template") != "") {
            templatePath = jQuery(this).data("template");
          }
          fs_get_cart(templatePath, this);
        });
      },
      productQuantityPluss: function (el) {
        let parent = el.parents('[data-fs-element="fs-quantity"]');
        let input = parent.find("input");
        let step = Number(input.attr("step"));
        let max = input.attr("max");
        let value = Number(input.val()) + step;

        if (max != "" && value > Number(max)) {
          iziToast.show({
            theme: "light",
            message: this.getLang("limit_product"),
            position: "topCenter",
          });
        } else {
          input.val(value);
          input.change();
        }
      },
      setProductGallery: function (productId, variationId = null) {
        $.ajax({
          type: "POST",
          url: this.ajaxurl,
          beforeSend: function () {},
          data: this.ajaxData("fs_get_product_gallery_ids", {
            product_id: productId,
            variation_id: variationId,
          }),
          success: function (res) {
            if (res.success) {
              if (res.data.gallery) {
                $("#fs-product-slider-wrapper").html(
                  '<ul id="product_slider">' + res.data.gallery + "</ul>"
                );
                $("#fs-product-slider-wrapper")
                  .find("#product_slider")
                  .lightSlider(fs_lightslider_options);
              }
            }
          },
        });
      },
      changeCartItemCount: function (el) {
        let cartItem = el.data("item-id");
        let productCount = Number(el.val());
        let step = Number(el.attr("step"));
        let min = Number(el.attr("min"));

        //если покупатель вбил неправильное к-во товаров
        if (productCount < min) {
          el.val(min);
        } else {
          let plugin = this;
          $.ajax({
            url: this.ajaxurl,
            type: "POST",
            data: {
              action: "fs_change_cart_count",
              item_id: cartItem,
              count: productCount,
            },
            success: function (response) {
              if (response.success) {
                plugin.updateCarts();
                $('[data-fs-element="cart-cost"]').text(response.data.cost);
                $('[data-fs-element="total-amount"]').text(response.data.total);

                // создаём событие
                let cart_change_count = new CustomEvent(
                  "fs_cart_change_count",
                  {
                    detail: { itemId: cartItem, count: productCount },
                  }
                );
                document.dispatchEvent(cart_change_count);

                if (el.data("refresh")) location.reload();
              }
            },
          });
        }
      },
      selectVariation: function () {
        var variation = $(this).val();
        var productId = $(this).data("product-id");
        var maxCount = $(this).data("max");
        // Изменяем данные в кнопке "добавить в корзину"
        $('[data-action="add-to-cart"]').each(function (index, value) {
          if ($(this).data("product-id") == productId) {
            $(this).attr("data-variation", variation);
          }
        });
        // Изменяем данные в квантификаторе товара
        $('[data-fs-action="change_count"]').each(function (index, value) {
          if ($(this).data("fs-product-id") == productId) {
            $(this).attr("max", maxCount);
            if (maxCount != "" && $(this).val() > maxCount) {
              $(this).val(maxCount);
            }
          }
        });

        // Подгружаем изображения в галерею аяксом
        this.setProductGallery(productId, variation);
        // создаём событие
        var fs_select_variant = new CustomEvent("fs_select_variant", {
          detail: {
            variationId: variation,
            productId: productId,
          },
        });
        document.dispatchEvent(fs_select_variant);
      },
    },
    window.fShop
  );

  $(document).on("click", '[data-fs-element="comment-like"]', function (event) {
    let commentId = $(this).data("comment-id");
    let countEl = $(this).find('[data-fs-element="comment-like-count"]');
    $.ajax({
      type: "POST",
      url: fShop.ajaxurl,
      data: {
        action: "fs_like_comment",
        comment_id: commentId,
      },
      success: function (data) {
        if (data.success && data.data.count) {
          countEl.text(data.data.count);
          iziToast.show({
            theme: "light",
            color: "green",
            message: data.data.msg,
            position: "bottomRight",
          });
        } else {
          iziToast.show({
            theme: "light",
            color: "red",
            message: data.data.msg,
            position: "bottomRight",
          });
        }
      },
      error: function (xhr, ajaxOptions, thrownError) {
        console.log("error...", xhr);
        //error logging
      },
      complete: function () {
        //afer ajax call is completed
      },
    });
  });

  // Выбор вариации товара
  $(document).on(
    "change",
    '[data-fs-element="select-variation"]',
    fShop.selectVariation
  );
  // увеличение к-ва товара на единицу
  $(document).on("click", '[data-fs-count="pluss"]', function (event) {
    fShop.productQuantityPluss($(this));
  });
  // Изменение количества товаров в корзине
  $(document).on("change input", '[data-fs-type="cart-quantity"]', function () {
    fShop.changeCartItemCount($(this));
  });
  // Покупка в 1 клик
  $(document).on(
    "click",
    '[data-fs-element="buy-one-click"]',
    function (event) {
      event.preventDefault();
      let detail = $(this).data();
      // создаём событие
      let fsBuyOneClick = new CustomEvent("fsBuyOneClick", {
        detail: detail,
      });
      document.dispatchEvent(fsBuyOneClick);
    }
  );

  // Сохраняет данные пользователя методом AJAX
  $(document).on("submit", "[name=fs-save-user-data]", function (event) {
    event.preventDefault();
    let formData = new FormData();
    $(this)
      .find("input,select")
      .each(function (index, value) {
        if ($(this).attr("type") == "file") {
          formData.append($(this).attr("name"), $(this).prop("files")[0]);
        } else if ($(this).attr("type") == "checkbox") {
          if ($(this).prop("checked")) {
            formData.append($(this).attr("name"), 1);
          } else {
            formData.append($(this).attr("name"), 0);
          }
        } else {
          formData.append($(this).attr("name"), $(this).val());
        }
      });
    $.ajax({
      type: "POST",
      url: fShop.ajaxurl,
      data: formData,
      contentType: false,
      processData: false,
      beforeSend: function () {
        $(".fs-form .meter").fadeIn(100);
      },
      success: function (data) {
        if (data.success) {
          iziToast.show({
            theme: "light",
            color: "green",
            message: data.data.msg,
            position: "topCenter",
          });
        } else {
          iziToast.show({
            theme: "light",
            color: "red",
            message: data.data.msg,
            position: "topCenter",
          });
        }

        $(".fs-form .meter").fadeOut(100);
      },
    });
    return false;
  });

  //добавление товара в корзину (сессию) - удаляем этот обработчик, так как теперь используем Alpine.js функционал
  // Старый код jQuery обработчика убираем, так как он будет заменен на Alpine.js функционал
  // через атрибут x-on:click в HTML

  // изменяем атрибуты товара по изменению input radio
  jQuery('[data-action="change-attr"]').on("change", function () {
    var curent = jQuery(this); // получаем элемент который был изменён
    var productId = curent.data("product-id"); // получаем ID товара из атрибутов
    var atcButton = jQuery("#fs-atc-" + productId); // получаем кнопку добавить в корзину
    var attrObj = atcButton.data("attr"); // получаем  атрибуты кнопки "добавить в корзину" в виде объекта
    var variated = atcButton.data("variated"); // узнаём вариативный товар или нет
    var parent = curent.parents('[data-fs-type="product-item"]'); // обёртка для одной позиции товара

    // выключаем чекбоксы всей группе атрибутов
    attrObj = [];
    jQuery('[name="' + curent.attr("name") + '"]').each(function (index) {
      jQuery(this).prop("checked", false);
    });
    // делаем активным чекбокс на который нажали
    curent.prop("checked", true);

    // добавляем значения выбраных элементов в data-attr нашей кнопки "в корзину"
    parent.find('[data-action="change-attr"]').each(function (index) {
      // если это радио кнопки выбора атрибутов
      if (jQuery(this).data("product-id") == productId && jQuery(this).val()) {
        if (
          jQuery(this).attr("type") == "radio" &&
          jQuery(this).prop("checked")
        ) {
          attrObj[index] = jQuery(this).val();
        }
        // если это не радио кнопки выбора атрибутов
        if (jQuery(this).attr("type") != "radio") {
          attrObj[index] = jQuery(this).val();
        }
      }
    });

    // производим очистку массыва атрибутов от пустых значений
    attrObj.clean(undefined);

    // делаем аякс запрос для получения вариативной цены и подмены на сайте
    if (variated) {
      jQuery.ajax({
        type: "POST",
        url: fShop.ajaxurl,
        data: {
          action: "fs_get_variated",
          product_id: productId,
          atts: attrObj,
        },
        beforeSend: function () {
          parent.find('[data-fs-element="price"]').addClass("blink");
        },
        success: function (data) {
          if (IsJsonString(data)) {
            var json = jQuery.parseJSON(data);
            // создаём событие "fs_after_change_att"
            var fs_after_change_att = new CustomEvent("fs_after_change_att", {
              detail: {
                el: curent,
                productId: productId,
                data: json,
              },
            });
            document.dispatchEvent(fs_after_change_att);
          } else {
            console.log(data);
          }
        },
      });
    }

    jQuery("#fs-atc-" + productId).attr("data-attr", attrObj);
  });

  //Записываем выбранные характеристики товара в data-attr
  jQuery('[data-fs-element="attr"]').on("change", function (event) {
    event.preventDefault();
    var el = jQuery(this);
    var productId = el.data("product-id");
    var cartbutton = jQuery(
      '[data-action="add-to-cart"][data-product-id="' + productId + '"]'
    );
    var productObject = cartbutton.first().data("attr");
    var attrName = el.attr("name");
    var attrVal = el.val();
    productObject[attrName] = Number(attrVal);
    cartbutton.attr("data-attr", JSON.stringify(productObject));
    var parseAtts = [];
    jQuery('[data-fs-element="attr"]').each(function (index, value) {
      if (jQuery(this).data("product-id") == productId) {
        var val = jQuery(this).val();
        if (val) parseAtts.push(val);
      }
    });
    if (cartbutton.data("variated") == 1)
      jQuery.ajax({
        type: "POST",
        url: fShop.ajaxurl,
        data: {
          action: "fs_get_variated",
          product_id: productId,
          atts: parseAtts,
          current: attrVal,
        },
        success: function (result) {
          if (result.success) {
            let price = Number(result.data.options.price);
            let pricePromo = Number(result.data.options.action_price);

            if (pricePromo && pricePromo < price) {
              price = pricePromo;
            }

            cartbutton.attr("data-disabled", false);
            cartbutton.attr("data-variation", result.data.options.variation);
            cartbutton.attr("data-price", price);
            cartbutton.removeAttr("data-disabled-message");

            if (result.data.active) {
              // устанавливаем опции в селектах в актив
              for (var key in result.data.active) {
                $('[data-fs-element="attr"][name=\'' + key + "'] option").each(
                  function (index, value) {
                    if ($(this).attr("value") == result.data.active[key]) {
                      $(this).prop("selected", true);
                    }
                  }
                );
              }
              $(
                '[data-action="add-to-cart"][data-product-id="' +
                  productId +
                  '"]'
              ).attr("data-attr", JSON.stringify(result.data.active));
            }

            if (typeof result.data.variation == "number") {
              jQuery('[data-action="add-to-cart"]').each(function (
                index,
                value
              ) {
                if (jQuery(this).data("product-id") == productId) {
                  jQuery(this).attr("data-variation", result.data.variation);
                }
              });
            }

            if (result.data.price) {
              jQuery('[data-fs-element="price"]').each(function (index, value) {
                if (jQuery(this).data("product-id") == productId) {
                  jQuery(this).html(result.data.price);
                }
              });
            }

            if (result.data.basePrice) {
              jQuery(
                '[data-fs-element="base-price"][data-product-id="' +
                  productId +
                  '"]'
              ).html(result.data.basePrice);
            } else {
              jQuery(
                '[data-fs-element="base-price"][data-product-id="' +
                  productId +
                  '"]'
              ).html("");
            }

            // создаём событие "fs_after_change_att"
            var fs_after_change_att = new CustomEvent("fs_after_change_att", {
              detail: {
                el: el,
                productId: productId,
                data: result.data,
              },
            });
            document.dispatchEvent(fs_after_change_att);
          } else {
            cartbutton.attr("data-disabled", true);
            cartbutton.attr("data-disabled-message", result.data.msg);

            iziToast.warning({
              title: fShop.getLang("error"),
              message: result.data.msg,
            });
          }
        },
      });
  });

  //Удаление продукта из корзины
  jQuery(document).on(
    "click",
    '[data-fs-type="product-delete"]',
    function (event) {
      event.preventDefault();
      var el = jQuery(this);
      var item = el.data("cart-item");
      var sendData = {
        action: "fs_delete_product",
        item: item,
      };

      if (!el.data("confirm") || confirm(el.data("confirm"))) {
        jQuery
          .ajax({
            url: fShop.ajaxurl,
            type: "POST",
            data: sendData,
          })
          .success(function (result) {
            if (result.success) {
              fShop.updateCarts();
              iziToast.show({
                theme: "light",
                message: result.data.message,
                position: "topCenter",
              });
              if (el.data("refresh")) {
                location.reload();
              }
            } else {
              iziToast.show({
                theme: "light",
                message: result.data.message,
                position: "topCenter",
              });
            }
          });
      }
    }
  );

  //Удаление всех товаров из корзины
  jQuery(document).on(
    "click",
    '[data-fs-element="delete-cart"]',
    function (event) {
      event.preventDefault();
      var el = jQuery(this);
      var sendData = {
        action: "fs_delete_cart",
      };
      if (confirm(el.data("confirm"))) {
        jQuery
          .ajax({
            url: fShop.ajaxurl,
            type: "POST",
            data: sendData,
          })
          .success(function (result) {
            if (result.success) {
              iziToast.show({
                theme: "light",
                message: result.data.message,
                position: "topCenter",
              });
              setTimeout(function () {
                location.reload();
              }, 5000);
            } else {
              iziToast.show({
                theme: "light",
                message: result.data.message,
                position: "topCenter",
              });
            }
          });
      }
    }
  );

  // добавление товара к сравнению
  jQuery(document).on(
    "click",
    '[data-action="add-to-comparison"]',
    function (event) {
      event.preventDefault();
      var el = jQuery(this);
      jQuery
        .ajax({
          url: fShop.ajaxurl,
          type: "POST",
          beforeSend: function () {
            el.find(".fs-atc-preloader").fadeIn();
          },
          data: {
            action: "fs_add_to_comparison",
            product_id: el.data("product-id"),
            product_name: el.data("product-name"),
          },
        })
        .success(function (data) {
          iziToast.show({
            theme: "light",
            title: "Успех!",
            message: "Товар добавлен в список  сравнения!",
            position: "topCenter",
          });
        })
        .done(function (data) {
          el.find(".fs-atc-preloader").fadeOut();
        });
    }
  );

  // обработка кнопки быстрого заказа
  jQuery('[data-fs-action="quick_order_button"]').on("click", function (event) {
    event.preventDefault();
    var pName = jQuery(this).data("product-name");
    var pId = jQuery(this).data("product-id");
    jQuery('[name="fs_cart[product_name]"]').val(pName);
    jQuery('[name="fs_cart[product_id]"]').val(pId);
  });

  //Переадресовываем все фильтры на значение, которое они возвращают
  jQuery('[data-fs-action="filter"]').on("change", function (e) {
    e.preventDefault();
    if (jQuery(this).attr("type") == "checkbox") {
      if (jQuery(this).prop("checked")) {
        window.location.href = jQuery(this).val();
      } else {
        window.location.href = jQuery(this).data("fs-redirect");
      }
    } else {
      window.location.href = jQuery(this).val();
    }
  });

  // Скрываем результаты при потере фокуса input
  jQuery(document).on("click", ".fs-ls-close", function (event) {
    event.preventDefault();
    var searchField = jQuery(this);
    var form = searchField.parents("form");
    form.find(".livesearch-wrapper").fadeOut().html("");
  });

  // открытие модального окна
  jQuery(document).on("click", "[data-fs-action='modal']", function (e) {
    e.preventDefault();
    var modalId = jQuery(this).attr("href");
    jQuery(modalId).fadeIn();
  });
  // закрытие модального окна
  jQuery(document).on("click", "[data-fs-action='modal-close']", function (e) {
    e.preventDefault();
    var modalParentlId = jQuery(this).parents(".fs-modal");
    jQuery(modalParentlId).fadeOut();
  });

  // Квантификатор товара
  jQuery(document).ready(function (jQuery) {
    // уменьшение к-ва товара на единицу
    jQuery(document).on("click", '[data-fs-count="minus"]', function (e) {
      e.preventDefault();
      let parent = jQuery(this).parents('[data-fs-element="fs-quantity"]');
      let input = parent.find("input");
      let count = Number(input.val());
      let min = input.attr("min") != "" ? Number(input.attr("min")) : 1;
      let step = input.attr("step") ? Number(input.attr("step")) : 1;
      if (count - step >= min) {
        input.val(count - step);
        input.change();
      }
      return false;
    });

    //Изменение к-ва добавляемых продуктов
    jQuery('[data-fs-action="change_count"]').on(
      "change input",
      function (event) {
        event.preventDefault();
        /* Act on the event */
        let el = $(this);
        var productId = el.data("fs-product-id");
        var count = Number(el.val());
        let step = Number(el.attr("step"));
        if (count < step) {
          el.val(step);
          count = step;
        }
        var cartButton = jQuery("#fs-atc-" + productId);
        cartButton.attr("data-count", count);
        // создаём событие
        var change_count = new CustomEvent("fs_change_count", {
          detail: { count: count, productId: productId },
        });
        document.dispatchEvent(change_count);
      }
    );
  });

  function createFilterUrl(baseUrl) {
    let start = jQuery('[data-fs-element="range-start-input"]').val();
    let end = jQuery('[data-fs-element="range-end-input"]').val();
    return baseUrl + "&price_start=" + start + "&price_end=" + end;
  }

  jQuery(document).on(
    "input keyup",
    '[data-fs-element="range-start-input"],[data-fs-element="range-end-input"]',
    function (event) {
      let baseUrl = jQuery(this).data("url");
      document.location.href = createFilterUrl(baseUrl);
    }
  );

  // валидация формы редактирования личных данных
  var userInfoEdit = jQuery('form[name="fs-profile-edit"]');
  if (userInfoEdit.length) {
    userInfoEdit.validate({
      rules: {
        "fs-password": {
          minlength: 6,
        },
        "fs-repassword": {
          equalTo: "#fs-password",
        },
      },
      messages: {
        "fs-repassword": {
          equalTo: "пароль и повтор пароля не совпадают",
        },
        "fs-password": {
          minlength: "минимальная длина 6 символов",
        },
      },
      submitHandler: function (form) {
        jQuery
          .ajax({
            url: fShop.ajaxurl,
            type: "POST",
            data: userInfoEdit.serialize(),
            beforeSend: function () {
              userInfoEdit
                .find(".fs-form-info")
                .fadeOut()
                .removeClass("fs-error fs-success")
                .html();
              userInfoEdit
                .find('[data-fs-element="submit"]')
                .html(
                  '<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif">'
                );
            },
          })
          .done(function (result) {
            userInfoEdit.find('[data-fs-element="submit"]').html("Сохранить");
            var data = JSON.parse(result);
            if (data.status == 0) {
              userInfoEdit
                .find(".fs-form-info")
                .addClass("fs-error")
                .fadeIn()
                .html(data.message);
            } else {
              userInfoEdit
                .find(".fs-form-info")
                .addClass("fs-success")
                .fadeIn()
                .html(data.message);
            }
            setTimeout(function () {
              userInfoEdit
                .find(".fs-form-info")
                .fadeOut("slow")
                .removeClass("fs-error fs-success")
                .html();
            }, 5000);
          });
      },
    });
  }

  // регистрация пользователя
  var userProfileCreate = jQuery('form[name="fs-register"]');
  if (typeof userProfileCreate.validate === "function") {
    userProfileCreate.validate({
      rules: {
        "fs-password": {
          minlength: 6,
        },
        "fs-repassword": {
          equalTo: "#fs-password",
        },
      },
      messages: {
        "fs-repassword": {
          equalTo: "пароль и повтор пароля не совпадают",
        },
        "fs-password": {
          minlength: "минимальная длина 6 символов",
        },
      },
      submitHandler: function (form) {
        jQuery.ajax({
          url: fShop.ajaxurl,
          type: "POST",
          data: userProfileCreate.serialize(),
          beforeSend: function () {
            userProfileCreate.find(".form-info").html("").fadeOut();
            userProfileCreate.find(".fs-preloader").fadeIn();
          },
          success: function (result) {
            userProfileCreate.find(".fs-preloader").fadeOut();

            if (result.success) {
              userProfileCreate
                .find(".fs-form-info")
                .removeClass("bg-danger")
                .addClass("bg-success")
                .fadeIn()
                .html(result.data.msg);
              // если операция прошла успешно - очищаем поля
              userProfileCreate.find("input").each(function () {
                if (jQuery(this).attr("type") != "hidden") {
                  jQuery(this).val("");
                }
              });
            } else {
              userProfileCreate
                .find(".fs-form-info")
                .removeClass("bg-success")
                .addClass("bg-danger")
                .fadeIn()
                .html(result.data.msg);
            }
          },
        });
      },
    });
  }

  // авторизация пользователя
  var loginForm = jQuery('form[name="fs-login"]');
  if (typeof loginForm.validate === "function") {
    loginForm.validate({
      submitHandler: function (form) {
        jQuery
          .ajax({
            url: fShop.ajaxurl,
            type: "POST",
            data: loginForm.serialize(),
            beforeSend: function () {
              loginForm
                .find(".form-info")
                .fadeOut()
                .removeClass("bg-danger")
                .html("");
              loginForm.find(".fs-preloader").fadeIn();
            },
          })
          .done(function (result) {
            var data = JSON.parse(result);
            loginForm.find(".fs-preloader").fadeOut();
            if (data.status == 0) {
              loginForm
                .find(".fs-form-info")
                .addClass("bg-danger")
                .fadeIn()
                .html(data.error);
            } else {
              if (data.redirect == false) {
                location.reload();
              } else {
                location.href = data.redirect;
              }
            }
          });
      },
    });
  }

  /*  Обработка формы сброса пароля */
  $(document).on("submit", "[name=fs-lostpassword]", function (event) {
    event.preventDefault();
    let form = $(this);
    let formData = form.serialize();
    $.ajax({
      type: "POST",
      url: fShop.ajaxurl,
      data: formData,
      success: function (result) {
        if (result.success) {
          form
            .find(".fs-form-info")
            .removeClass("bg-danger")
            .addClass("bg-success")
            .fadeIn()
            .html(result.data.msg);
          // если операция прошла успешно - очищаем поля
          form.find("input").each(function () {
            if (jQuery(this).attr("type") != "hidden") {
              jQuery(this).val("");
            }
          });
        } else {
          form
            .find(".fs-form-info")
            .removeClass("bg-success")
            .addClass("bg-danger")
            .fadeIn()
            .html(result.data.msg);
        }
      },
    });

    return false;
  });

  /*
   * Общий обрабочик форм
   * TODO:в дальнейшем убрать все обработчики, использовать только этот
   */
  $("form[data-ajax=on]").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();

    $.ajax({
      type: "POST",
      url: fShop.ajaxurl,
      data: formData,
      success: function (response) {
        if (response.success) {
          iziToast.show({
            theme: "light",
            title: fShop.getLang("success"),
            message: response.data.msg,
            position: "topCenter",
          });
        } else {
          iziToast.show({
            theme: "light",
            title: fShop.getLang("error"),
            message: response.data.msg,
            position: "topCenter",
          });
        }

        if (response.data.redirect) {
          window.location.href = response.data.redirect;
        }
      },
    });

    return false;
  });

  (function ($) {
    // Product rating
    $('[data-fs-element="rating"]').on(
      "click",
      '[data-fs-element="rating-item"]',
      function (e) {
        e.preventDefault();

        let ratingVal = $(this).data("rating");
        let wrapper = $(this).parents('[data-fs-element="rating"]');
        let productId = wrapper.find("input").data("product-id");
        wrapper.find("input").val(ratingVal);

        if (localStorage.getItem("fs_user_voted_" + productId)) {
          iziToast.warning({
            theme: "light",
            title: fShop.getLang("error", ""),
            message: fShop.getLang("ratingError"),
            position: "topCenter",
          });
          return;
        }

        if (!localStorage.getItem("fs_user_voted_" + productId)) {
          wrapper
            .find('[data-fs-element="rating-item"]')
            .each(function (index, value) {
              if ($(this).data("rating") <= ratingVal) {
                $(this).addClass("active");
              } else {
                $(this).removeClass("active");
              }
            });
          jQuery.ajax({
            type: "POST",
            url: fShop.ajaxurl,
            data: {
              action: "fs_set_rating",
              value: ratingVal,
              product: productId,
            },
            cache: false,
            success: function (response) {
              if (response.success) {
                localStorage.setItem("fs_user_voted_" + productId, 1);
                iziToast.success({
                  theme: "light",
                  title: response.data.title,
                  message: response.data.msg,
                  position: "topCenter",
                });
              }
            },
          });
        }
      }
    );
  })(jQuery);

  // Активирует радио кнопки на странице оформления покупки
  $(".fs-field-wrap").each(function () {
    $(this)
      .find(".radio")
      .first()
      .addClass("active")
      .find("input")
      .prop("checked", true);
  });

  // Это сработает сразу после переключения input radio
  $(".fs-field-wrap").on("change", '[type="radio"]', function () {
    $(this).parents(".fs-field-wrap").find(".radio").removeClass("active");

    $(this).parents(".radio").addClass("active");
  });

  /**
   * функция транслитерации
   */
  function fs_transliteration(text) {
    // Символ, на который будут заменяться все спецсимволы
    var space = "-";
    // переводим в нижний регистр
    text = text.toLowerCase();

    // Массив для транслитерации
    var transl = {
      а: "a",
      б: "b",
      в: "v",
      г: "g",
      д: "d",
      е: "e",
      ё: "e",
      ж: "zh",
      з: "z",
      и: "i",
      й: "j",
      к: "k",
      л: "l",
      м: "m",
      н: "n",
      о: "o",
      п: "p",
      р: "r",
      с: "s",
      т: "t",
      у: "u",
      ф: "f",
      х: "h",
      ц: "c",
      ч: "ch",
      ш: "sh",
      щ: "sh",
      ъ: space,
      ы: "y",
      ь: space,
      э: "e",
      ю: "yu",
      я: "ya",
      " ": space,
      _: space,
      "`": space,
      "~": space,
      "!": space,
      "@": space,
      "#": space,
      $: space,
      "%": space,
      "^": space,
      "&": space,
      "*": space,
      "(": space,
      ")": space,
      "-": space,
      "=": space,
      "+": space,
      "[": space,
      "]": space,
      "\\": space,
      "|": space,
      "/": space,
      ".": space,
      ",": space,
      "{": space,
      "}": space,
      "'": space,
      '"': space,
      ";": space,
      ":": space,
      "?": space,
      "<": space,
      ">": space,
      "№": space,
    };

    var result = "";
    var curent_sim = "";

    for (var i = 0; i < text.length; i++) {
      // Если символ найден в массиве то меняем его
      if (transl[text[i]] != undefined) {
        if (curent_sim != transl[text[i]] || curent_sim != space) {
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
    s = s.replace(/^-/, "");
    return s.replace(/-$/, "");
  }

  // проверяет является ли переменная числом
  function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
  }

  // проверяет является ли строка JSON объектом
  function IsJsonString(str) {
    try {
      JSON.parse(str);
    } catch (e) {
      console.log(str);
      return false;
    }
    return true;
  }

  // очищает от пустых элементов массива
  Array.prototype.clean = function (deleteValue) {
    for (var i = 0; i < this.length; i++) {
      if (this[i] == deleteValue) {
        this.splice(i, 1);
        i--;
      }
    }
    return this;
  };

  // строковая фнкция, которая позволяет производить поиск и замену сразу по нескольким значениям переданным в виде объекта
  String.prototype.allReplace = function (obj) {
    var retStr = this;
    for (var x in obj) {
      retStr = retStr.replace(new RegExp(x, "g"), obj[x]);
    }
    return retStr;
  };

  // Событие срабатывает перед добавлением товара в корзину
  document.addEventListener(
    "fs_before_add_product",
    function (event) {
      // Set loading state
      if (typeof Alpine !== "undefined") {
        Alpine.store("FS").loading = true;
      }

      // действие которое инициирует событие, здесь может быть любой ваш код
      if (event.detail.button) {
        const button = event.detail.button;
        button
          .find(".fs-atc-preloader")
          .fadeIn()
          .html(
            '<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif" alt="preloader" width="16">'
          );
      }
      event.preventDefault();
    },
    false
  );

  // Срабатывает если покупатель пытается добавить отсуствующий товар в корзину
  document.addEventListener("fsBuyNoAvailable", function (event) {
    // Check if we have Alpine.js
    if (typeof Alpine !== "undefined") {
      Alpine.store("FS").noAvailableProductData = event.detail;
      Alpine.store("FS").showNoAvailableModal = true;
    } else {
      // Fallback to iziToast if Alpine is not available
      iziToast.show({
        theme: "light",
        timeout: false,
        maxWidth: 340,
        overlay: true,
        title: "Сообщить о наличии",
        message:
          "Товара &laquo;" +
          event.detail.name +
          "&raquo; нет на складе.<br> Оставьте Ваш E-mail и мы сообщим когда товар будет в наличии. <br>",
        position: "topCenter",
        id: "report-availability",
        buttons: [
          [
            '<input type="email" name="userEmail" placeholder="Ваш E-mail">',
            function (instance, toast) {
              // Using the name of input to get the value
            },
            "input",
          ],
          [
            "<button>Отправить</button>",
            function (instance, toast) {
              const userEmail = toast.querySelector('[name="userEmail"]').value;

              if (typeof Alpine !== "undefined") {
                Alpine.store("FS")
                  .post("fs_report_availability", {
                    email: userEmail,
                    product_id: event.detail["product-id"],
                    product_name: event.detail.name,
                    product_url: event.detail.url,
                    variation: event.detail.variation,
                    count: event.detail.count,
                  })
                  .then((result) => {
                    if (result.success) {
                      toast
                        .querySelector(".iziToast-message")
                        .classList.add("success");
                      toast.querySelector(".iziToast-message").innerHTML =
                        result.data.msg;
                      toast.querySelector(".iziToast-buttons").style.display =
                        "none";
                    } else {
                      toast
                        .querySelector(".iziToast-message")
                        .classList.add("error");
                      toast.querySelector(".iziToast-message").innerHTML =
                        result.data.msg;
                    }
                  });
              } else {
                // Fallback to jQuery if Alpine is not available
                jQuery.ajax({
                  type: "POST",
                  url: fShop.ajaxurl,
                  data: fShop.ajaxData("fs_report_availability", {
                    email: userEmail,
                    product_id: event.detail["product-id"],
                    product_name: event.detail.name,
                    product_url: event.detail.url,
                    variation: event.detail.variation,
                    count: event.detail.count,
                  }),
                  success: function (result) {
                    if (result.success) {
                      jQuery(toast)
                        .find(".iziToast-message")
                        .addClass("success")
                        .html(result.data.msg);
                      jQuery(toast).find(".iziToast-buttons").hide();
                    } else {
                      jQuery(toast)
                        .find(".iziToast-message")
                        .addClass("error")
                        .html(result.data.msg);
                    }
                  },
                });
              }
            },
          ],
        ],
      });
    }
  });

  $(document).on("click", ".fs-toast-close", function (event) {
    event.preventDefault();
    $(".iziToast").fadeOut(400);
  });
});
