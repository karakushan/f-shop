/* todo: перписать все функции используя контекст класса FS */
class FS {
    constructor(jq) {
        this.$ = jq;
        this.activateTab()
    }

    activateTab() {
        let activeTab = window.localStorage.getItem('fs_active_tab')
        let context = this;

        if (!activeTab) {
            activeTab = this.$('#fs-metabox .tab-header li:first-child a').attr('href');
            window.localStorage.setItem('fs_active_tab', activeTab)
        }
        this.$('#fs-metabox .tab-header li.fs-link-active').removeClass('fs-link-active')
        this.$('#fs-metabox .tab-header a[href="' + activeTab + '"]')
            .parent('li')
            .addClass('fs-link-active');
        context.$('#fs-metabox .fs-tab').removeClass('fs-tab-active')
        context.$(activeTab).addClass('fs-tab-active')

        this.$('#fs-metabox .tab-header').on('click', 'a', function (event) {
            event.preventDefault();
            let href = context.$(this).attr('href');
            window.localStorage.setItem('fs_active_tab', href)
            context.$('#fs-metabox .tab-header li').removeClass('fs-link-active')
            context.$(this).parent('li').addClass('fs-link-active')
            context.$('#fs-metabox .fs-tab').removeClass('fs-tab-active')
            context.$(href).addClass('fs-tab-active')
        });
    }
}

window.FS = new FS(jQuery);


jQuery(function ($) {
    $(window).off('beforeunload');
    window.fShop = {
        // запускает прогресс бар в самом верху сайта
        showMetaboxPreloader: function () {
            $(".fs-mb-preloader").css("display", "block");
        },
        getApiKey: function (e) {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'fs_get_api_key'
                },

                success: function (data) {
                    let json = JSON.parse(data)
                    if (json.success) {
                        $('[name="fs_api[api_token]"]').val(json.api_key);
                    } else {
                        alert(json.msg());
                    }

                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log('error...', xhr);
                    //error logging
                },
                complete: function () {
                    //afer ajax call is completed
                }
            });
        },
        // скрывает прогрес бар
        hideMetaboxPreloader: function () {
            $(".fs-mb-preloader").fadeOut();
        },
        // скрывает раскрывает аккордеоны
        toggleCollapse: function (el, toggleClass) {
            if (typeof toggleClass == "undefined") toggleClass = "active";
            $(el).each(function () {
                $(this).toggleClass(toggleClass);
            });

        }
    }

    if (typeof inlineEditPost !== 'undefined') {
        // we create a copy of the WP inline edit post function
        var $wp_inline_edit = inlineEditPost.edit;

        // and then we overwrite the function with our own code
        inlineEditPost.edit = function (id) {

            // "call" the original WP edit function
            // we don't want to leave WordPress hanging
            $wp_inline_edit.apply(this, arguments);

            // now we take care of our business

            // get the post ID
            let $post_id = 0;
            if (typeof (id) == 'object') {
                $post_id = parseInt(this.getId(id));
            }

            if ($post_id > 0) {
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    //data: JSON.stringify(parameters),
                    data: {
                        action: 'fs_quick_edit_values',
                        post_id: $post_id,
                        fields: [
                            'fs_price',
                            'fs_articul',
                            'fs_remaining_amount'
                        ]
                    },
                    success: function (data) {
                        if (data.success) {
                            for (const dataKey in data.data.fields) {
                                $('#edit-' + $post_id + ' [name="' + dataKey + '"]').val(data.data.fields[dataKey])
                            }
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log('error...', xhr);
                        //error logging
                    },
                    complete: function () {
                        //afer ajax call is completed
                    }
                });
            }
        };
    }


    $('.fs-select-field').select2({
        placeholder: "Выбрать"
    });

    $(document).on('click', '.fs-collapse-all', function (event) {
        event.preventDefault();
        fShop.toggleCollapse("#fs-variants-wrapper .fs-rule")
    });
    // изменяет позиции товаров при перетаскивании
    $('body.post-type-product .wp-list-table tbody').sortable({
            placeholder: 'ui-state-highlight ui-sort-position',
            helper: 'clone',
            axis: "y",
            handle: '.fs_menu_order',
            update: function (event, ui) {
                var postIds = [];
                $(this).find("tr").each(function (index, value) {
                    postIds[index] = $(this).attr('id').replace("post-", "");
                });
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    //data: JSON.stringify(parameters),
                    data: {action: "fs_update_position", ids: postIds},
                    // dataType: 'json',
                    cache: false,
                    success: function (data) {
                        // console.log(data);
                        // do something with ajax data

                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log('error...', xhr);
                        //error logging
                    },
                    complete: function () {
                        //afer ajax call is completed
                    }
                });
            }
        }
    );
    /*
    Это универсальный загрузчик медиафайлов из медиатеки
    вызывается в php методе render_field() из класа FS_Form_Class при типе поля image
    TODO: остальные загрузчики, которые остались от прежних версий плагина необходимо удалять и переделывать
    */
    $(document).on('click', '[data-fs-action="select-image"]', function (event) {
        event.preventDefault();
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        var parent = button.parents("figure");
        wp.media.editor.open(button);
        wp.media.editor.send.attachment = function (props, attachment) {
            parent.find('input').val(attachment.id);
            parent.find('button').fadeIn();
            parent.css({
                'background-image': 'url(' + attachment.url + ')'
            });
        };

    });
    /*
    * Удаляет изображение прикреплённое к полю типа image
    * */
    $(document).on('click', '[data-fs-action="delete-image"]', function (event) {
        event.preventDefault();
        var button = $(this);
        var parent = button.parents("figure");
        if (confirm(button.data("text"))) {
            parent.find('input').val("")
            parent.css({
                'background-image': 'url(' + button.data("noimage") + ')'
            });
        }
        button.fadeOut();
    });

    // Подсказки в настройках плагина
    $('.tooltip').tooltipster({
        theme: 'tooltipster-light',
        trigger: 'hover'
    });

    // Добавление кастомных атрибутов
    $(document).on('click', '[data-fs-element="add-custom-attribute"]', function (event) {
        event.preventDefault();
        let el = $(this);
        let postId = $(this).data('post-id');
        let row = $(this).parents('[data-fs-element="item"]');
        let name = row.find('[data-fs-element="attribute-name"]').val();
        let value = row.find('[data-fs-element="attribute-value"]').val();

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'fs_add_custom_attribute',
                post_id: postId,
                name: name,
                value: value,
            },
            cache: false,
            success: function (data) {
                let event = new CustomEvent('fs_changed_attribute', {
                    detail: {
                        post_id: postId
                    }
                });
                window.dispatchEvent(event);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            },
            complete: function () {
                //afer ajax call is completed
            }
        });
    });

    // Обновляет таблицу атрибутов товара
    function refresh_product_attributes(post_id) {
        let table = $(".fs-atts-list-table");

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            beforeSend: function () {
                table.css({
                    opacity: .5
                })
            },
            data: {
                action: 'fs_get_admin_attributes_table',
                post_id: post_id,
                is_ajax: 1
            },
            cache: false,
            success: function (data) {
                if (data.success) {
                    table.html(data.data);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            },
            complete: function () {
                table.css({
                    opacity: 1
                })
            }
        });
    }

    $(window).on('fs_changed_attribute', function (event) {
        refresh_product_attributes(event.detail.post_id)
    });


    // === АТРИБУТЫ НА ВКЛАДКЕ РЕДАКТИРОВАНИЯ ТОВАРА ===
    $(document).on('click', '[data-fs-action="add-atts-from"]', function (event) {
        event.preventDefault();
        let el = $(this);
        let postId = el.data('post');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'fs_add_att',
                term: el.prev().val(),
                post: postId
            },
            cache: false,
            success: function (result) {
                if (result.success) {
                    let event = new CustomEvent('fs_changed_attribute', {
                        detail: {
                            post_id: postId
                        }
                    });
                    window.dispatchEvent(event);
                } else {
                    console.log(result);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            },
            complete: function () {
                //afer ajax call is completed
            }
        });
    });

    // тип атрибута в редкатировании атрибутов
    $(".fs-color-select").spectrum({
        color: $(this).val(),
        showInput: true,
        className: "full-spectrum",
        showInitial: true,
        showPalette: true,
        showSelectionPalette: true,
        maxSelectionSize: 10,
        preferredFormat: "hex",
        localStorageKey: "spectrum.demo",
        palette: [
            ["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)",
                "rgb(204, 204, 204)", "rgb(217, 217, 217)", "rgb(255, 255, 255)"],
            ["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
                "rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
            ["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
                "rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
                "rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
                "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
                "rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
                "rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
                "rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
                "rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
                "rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
                "rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
        ]
    });

    //показываем скрываем кнопку загрузки изображения в зависимости от типа добавляемого атрибута
    $('#fs_att_type').on('change', function (event) {
        event.preventDefault();
        $('.fs-att-values').css({'display': 'none'});
        $(".fs-att-" + $(this).val()).fadeIn();
    });

    //вызываем стандартный загрузчик изображений
    $('.select_file').on('click', function () {
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        wp.media.editor.open(button);
        wp.media.editor.send.attachment = function (props, attachment) {
            $(button).next().val(attachment.id);
            $(button).prev().css({
                'background-image': 'url(' + attachment.url + ')'
            }).removeClass('hidden');
            $(button).text('изменить изображение');
            wp.media.editor.send.attachment = send_attachment_bkp;
            button.parents('.fs-fields-container').find('.delete_file').fadeIn(400);
        };

        return false;
    });
    $('.delete_file').on('click', function () {
        if (confirm('Вы точно хотите удалить изображение?')) {
            $(this).parents('.fs-fields-container').find('input').val('');
            $(this).parents('.fs-fields-container').find('.fs-selected-image').css({
                'background-image': 'none'
            }).addClass('hidden');
            $(this).parents('.fs-fields-container').find('.select_file').text('выбрать изображение');
            $(this).fadeOut(400);

        }

    });


    /*
     * действие при нажатии на кнопку загрузки изображения
     * вы также можете привязать это действие к клику по самому изображению
     */
    $('.upload-mft').on('click', function () {
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        wp.media.editor.send.attachment = function (props, attachment) {
            $(button).parents('.mmf-image').find('.img-url').val(attachment.id);
            $(button).parents('.mmf-image').find('.image-preview').attr('src', attachment.url);

            $(button).prev().val(attachment.id);
            wp.media.editor.send.attachment = send_attachment_bkp;
        };
        wp.media.editor.open(button);
        return false;
    });

    /*
     * удаляем значение произвольного поля
     * если быть точным, то мы просто удаляем value у input type="hidden"
     */
    $('.remove_image_button').click(function () {
        var r = confirm("Уверены?");
        if (r == true) {
            var src = $(this).parent().prev().attr('data-src');
            $(this).parent().prev().attr('src', src);
            $(this).prev().prev().val('');
        }
        return false;
    });

    var nImg = '<div class="mmf-image"><img src="" alt="" width="164" height="133" class="image-preview"><input type="hidden" name="fs_galery[]" value="" class="img-url"><button type="button" class="upload-mft">Загрузить</button><button type="button" class="remove-tr" onclick="btn_view(this)">удалить</button></div>';
    jQuery('#new_image').click(function (event) {
        event.preventDefault();
        if (jQuery('#mmf-1 .mmf-image').length > 0) {
            jQuery('#mmf-1 .mmf-image:last').after(nImg);
        } else {
            jQuery('#mmf-1').html(nImg);
        }
    });
});

function btn_view(e) {
    jQuery(e).parents('.mmf-image').remove();
}

jQuery(document).ready(function ($) {
    //действия в админке
    $('[data-fs-action*=admin_]').on('click', function (event) {
        event.preventDefault();
        var thisButton = $(this);
        var buttonContent = $(this).text();
        var buttonPreloader = '<img src="/wp-content/plugins/f-shop/assets/img/preloader-1.svg">';
        if ($(this).data('fs-confirm').length > 0) {
            if (confirm($(this).data('fs-confirm'))) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    beforeSend: function () {
                        thisButton.find('div').remove();
                        thisButton.html(buttonPreloader + buttonContent);
                    },
                    data: {action: $(this).data('fs-action')},
                })
                    .done(function (result) {
                        result = jQuery.parseJSON(result);
                        thisButton.find('img').fadeOut(600).remove();
                        if (result.status == true) {
                            thisButton.html('<div class="success">' + result.message + '</div>' + buttonContent);
                            if (result.action == 'refresh') {
                                setTimeout(function () {
                                    location.reload();
                                }, 2000);
                            }
                        } else {
                            thisButton.html('<div class="error">' + result.message + '</div>' + buttonContent);
                        }
                    })
                    .fail(function () {
                        console.log("error");
                    })
                    .always(function () {
                        console.log("complete");
                    });

            }
        }

    });
    $('[data-fs-action="enabled-select"]').on('click', function (event) {
        event.preventDefault();
        $(this).next().fadeIn();
    });
    $('#tab-4').on('change', '[data-fs-action="select_related"]', function (event) {
        event.preventDefault();
        var thisVal = $(this).val();
        var text;
        $(this).find('option').each(function (index, el) {
            if (thisVal == $(this).attr('value')) {
                text = $(this).text();
            }


        });
        $('#tab-4 .related-wrap').append('<li class="single-rel"><span>' + text + '</span> <button type="button" data-fs-action="delete_parents" class="related-delete" data-target=".single-rel">удалить</button><input type="hidden" name="fs_related_products[]" value="' + thisVal + '"></li>');
        $(this).fadeOut().remove();

    });
    $('body').on('click', '[data-fs-action="delete_parents"]', function (event) {
        $(this).parents($(this).data('target')).remove();
    });

    // получаем посты термина во вкладке связанные в редактировании товара
    $('#tab-4').on('change', '[data-fs-action="get_taxonomy_posts"]', function (event) {
        var term = $(this).val();
        var thisSel = $(this);
        var postExclude = $(this).data('post');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {action: 'fs_get_taxonomy_posts', 'term_id': term, 'post': postExclude},
        })
            .done(function (data) {
                var json = $.parseJSON(data);
                thisSel.prop('selectedIndex', 0);
                thisSel.hide();
                thisSel.parent().append(json.body);
            });

    });

    $(".fs-sortable-items").sortable();

    // удаление свойства на вкладке "Атрибуты"
    $('.fs-atts-list-table').on('click', '[data-action="remove-att"]', function (event) {
        event.preventDefault();
        let el = $(this);
        let postId = el.data('product-id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'fs_remove_product_term',
                term_id: el.data('category-id'),
                product_id: postId
            },
        })
            .done(function (data) {
                if (data.success == true) {
                    let event = new CustomEvent('fs_changed_attribute', {
                        detail: {
                            post_id: postId
                        }
                    });
                    window.dispatchEvent(event);
                }
            });

    });
    // клонирует свойство вариативного товара
    $(document).on('click', '[data-fs-element="clone-att"]', function (event) {
        event.preventDefault();
        let parent = $(this).parents('.fs-rule');
        let index = $(this).parents('.fs-rule').data('index');
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {action: "fs_get_template_part", index: index},
            beforeSend: fShop.showMetaboxPreloader(),
            success: function (res) {
                fShop.hideMetaboxPreloader();
                if (res.success) {
                    parent.find('.fs-prop-group').append(res.data.template);
                }

            }
        });

    });
    // удаляет свойство у вариативного товара
    $(document).on('click', '[data-fs-element=\'remove-var-prop\']', function (event) {
        $(this).parent().remove();
    });

    $(document).on('click', '[data-fs-element="toggle-accordeon"]', function (event) {
        $(this).parents('.fs-rule').toggleClass("active");
    });

    $(document).on('click', '#fs-add-variant', function (event) {
        console.log($("#tab-variants .fs-rule").length);
        var count = $(".fs-rule").length;
        if ($("#tab-variants .fs-rule").length) {
            count = $("#tab-variants .fs-rule").last().data('index');
            count = Number(count) + 1;
        }
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            beforeSend: function () {
                fShop.showMetaboxPreloader();
            },
            data: {
                action: "fs_add_variant",
                index: count
            },
            success: function (result) {
                fShop.hideMetaboxPreloader();
                if (result.success) {
                    $("#fs-variants-wrapper").append(result.data.template);
                }
            },
        });

    });
    $(document).on('click', '#fs-variants-wrapper .fs-remove-variant', function (event) {
        event.preventDefault();
        $(this).parents('.fs-rule').remove();
        $(".fs-rule").each(function (index, value) {
            $(this).attr('data-index', index);
            $(this).find('select').attr('name', 'fs_variant[' + index + '][]');
            $(this).find('input').attr('name', 'fs_variant_price[' + index + ']');
            $(this).find('.index').text(index + 1);
        });

    });

});

jQuery(document).ready(function ($) {
    // Up sell dialog window
    $('#fs-upsell-dialog').dialog({
        title: 'Список товаров',
        dialogClass: 'wp-dialog',
        autoOpen: false,
        draggable: false,
        width: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: "center",
            at: "center",
            of: window
        },
        open: function () {
            $('#fs-upsell-dialog li').removeClass('active');
            // close dialog by clicking the overlay behind it
            $('.ui-widget-overlay').bind('click', function () {
                $('#fs-upsell-dialog').dialog('close');
            })
        },
        create: function () {
            // style fix for WordPress admin
            $('.ui-dialog-titlebar-close').addClass('ui-button');
        },
    });
    // bind a button or a link to open the dialog
    $('.fs-metabox').on('click', '.fs-add-upsell', function (e) {
        e.preventDefault();
        $("#fs-upsell-dialog .add-product").attr('data-field', $(this).attr('data-field'));
        $('#fs-upsell-dialog').dialog('open');
    });

    $(".fs-select-products-dialog").on('click', '.add-product', function (e) {
            e.preventDefault();
            let el = $(this);
            let data = el.data();

            if (el.parent().hasClass('active')) return false;

            let parentLi = el.parent().clone();
            parentLi.find('button').remove();
            parentLi.removeClass('active');
            parentLi.append('<button class="button button-cancel remove-product">&times;</button>' +
                '<input type="hidden" name="' + data.field + '[]" value="' + data.id + '">');
            $(this).parent().toggleClass('active');
            $(".fs-tab-active .fs-upsell-wrapper").append(parentLi);
        }
    );

    $(".fs-upsell-wrapper ").on('click', '.remove-product', function (e) {
            e.preventDefault();
            $(this).parent().remove();

        }
    );

// добавление атрибута
    $('#fs-add-attr').on('click', function (event) {
        event.preventDefault();
        $('#fs-attr-select').fadeIn(600);
    });
// добавление галереи изображений
    $('#fs-add-gallery').click(open_media_window);

    function open_media_window() {
        if (this.window === undefined) {
            this.window = wp.media({
                title: 'Добавление изображений в галерею',
                library: {type: 'image'},
                multiple: true,
                button: {text: 'добавить в галерею'}
            });

            var self = this; // Needed to retrieve our variable in the anonymous function below
            this.window.on('select', function () {
                var images = self.window.state().get('selection').toJSON();
                for (var key in images) {
                    if (images[key].type != 'image') continue;
                    var image = '<div class="fs-col-4"> <div class="fs-remove-img"></div> <input type="hidden" name="fs_galery[]" value="' + images[key].id + '"> <img src="' + images[key].url + '" alt="fs gallery image #' + images[key].id + '"> </div>';
                    $('#fs-gallery-wrapper').append(image);
                }
            });
        }

        this.window.open();
        return false;
    }

// удалем одно изображение из галереи
    $(document).on('click', '#fs-gallery-wrapper .fs-remove-img', function (event) {
        event.preventDefault();
        $(this).parent('div').remove();
    });

    // табы мета полей
    $(document).on('click', '.fs-tabs .fs-tabs__title', function (event) {
        event.preventDefault();

        let wrapper = $(this).parents('.fs-tabs');

        wrapper.find('.fs-tabs__title').removeClass('nav-tab-active');
        wrapper.find('.fs-tabs__body').removeClass('fs-tab-active');

        $(this).addClass('nav-tab-active');
        $($(this).attr('href')).addClass('fs-tab-active');
    });
});

jQuery(document).on('change', '.fs_select_variant', function (event) {
    event.preventDefault();
    if (jQuery(this).val() == '-1') {
        if (confirm('подтверждаете?')) {
            jQuery(this).fadeOut().remove();
        }
    }
});




