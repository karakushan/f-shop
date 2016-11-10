jQuery(function($){
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
                "rgb(204, 204, 204)", "rgb(217, 217, 217)","rgb(255, 255, 255)"],
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

    //автоматический перевод в транслит группы атрибутов при добавлении
    $('#fs_attr_group_name').on('keyup',function () {
        var thisValue=fs_transliteration($(this).val());
        $('#fs_attr_group_name_en').val(thisValue);
    });
    //Удаление отдельного свойства
    $('[data-fs-action="delete-attr-single"]').on('click', function(event) {
        event.preventDefault();
        if(confirm('Удалить свойство '+$(this).data('name')+'?')){
            var attrGroup=$(this).data('fs-attr-group');
            var attrID=$(this).data('fs-attr-id');
            var attrParent=$(this).parent();
            $.ajax({
                url: ajaxurl,

                data: {action: "attr_single_remove", attr_id:attrID,attr_group:attrGroup},
            })
                .done(function(data) {
                    attrParent.remove();

                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });

        }
    });
    //удаление группы атрибутов
    $('[data-fs-action="delete-attr-group"]').on('click', function(event) {
        event.preventDefault();
        if(confirm('Удалить группу '+$(this).data('name')+'?')){
            var attr=$(this).data('fs-attr-group');
            var attrParent=$(this).parent();
            $.ajax({
                url: ajaxurl,

                data: {action: "attr_group_remove", attr:attr},
            })
                .done(function(data) {
                    // console.log(data);
                    $('#fs_attr_group').html(data);
                    attrParent.remove();
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });

        }
    });
    //добавление группы атрибутов
    $('#fs_add_attr_group button').on('click', function(event) {
        event.preventDefault();
        var inputStatus=true;

        $('#fs_add_attr_group input').each(function(index, el) {
            if ($(this).val().length<1) {
                inputStatus=false;
                $(this).addClass('fs_error_input');
                $(this).next().text('заполните поле');

            }else{
                $(this).removeClass('fs_error_input');
                $(this).next().text('');
            }
        });

        if (inputStatus!=false) {
            $.ajax({
                url: ajaxurl,
                data: {
                    action: "attr_group_edit",
                    name: $('#fs_attr_group_name').val(),
                    slug: $('#fs_attr_group_name_en').val(),
                },
            })
                .done(function(data) {
                    $('#fs_attr_group').html(data);
                    $('#fs_add_attr_group').fadeOut('800');
                    $('#fs_add_attr_group input').each(function(index, el) {
                        $(this).val('');
                    });
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });
        }

    });
    $('#fs_add_group_link').on('click', function(event) {
        event.preventDefault();
        $('#fs_add_attr_group').fadeIn(800);
    });
    //Отправка нового атрибута в базу
    $('#fs_attr_form').submit(function(event) {
        $.ajax({
            url: ajaxurl,
            data: $('#fs_attr_form').serialize(),
        })
            .done(function(data) {
                console.log(data);
                $('#fs_attr_form_i').html('<span class="fs_form_succes">Свойство товара добавлено. Вы можете добавить ещё.</span>');
                setTimeout(function() { $('#fs_attr_form_i').html(''); }, 3000);
                $('#fs_attr_form')[0].reset();
                $('#fs_select_image').css({
                    'background-image': 'none'
                });
                $('#fs_attr_type_block').fadeOut('800');

            });
        return false;
    });


    //показываем скррываем кнопку загрузки изображения в зависимости от типа добавляемого атрибута
    $('#fs_att_type').on('change', function(event) {
        event.preventDefault();
        $('.fs-att-values').css({'display':'none'});
        switch ($(this).val()){
            case "color":
                $('#fs-att-color').css({'display':'table-row'});
                break;
            case "image":
                $('#fs-att-image').css({'display':'table-row'});
                break;
        }

    });
    //вызываем стандартный загрузчик изображений
    $('.select_file').on('click',function(){
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        wp.media.editor.open(button);
        wp.media.editor.send.attachment = function(props, attachment) {
            $(button).next().val( attachment.id);
            $(button).prev().css({
                'background-image': 'url('+attachment.url+')'
            });
            $(button).text('изменить изображение');
            wp.media.editor.send.attachment = send_attachment_bkp;
            button.parents('.fs-fields-container').find('.delete_file').fadeIn(400);
        }

        return false;
    });
    $('.delete_file').on('click',function () {
        if(confirm('Вы точно хотите удалить изображение?')){
            $(this).parents('.fs-fields-container').find('input').val('');
            $(this).parents('.fs-fields-container').find('.fs-selected-image').css({
                'background-image': 'none'
            });
            $(this).parents('.fs-fields-container').find('.select_file').text('выбрать изображение');
            $(this).fadeOut(400);

        }

    });



    /*
     * действие при нажатии на кнопку загрузки изображения
     * вы также можете привязать это действие к клику по самому изображению
     */
    $('.upload-mft').live('click',function(){
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        wp.media.editor.send.attachment = function(props, attachment) {
            $(button).parents('.mmf-image').find('.img-url').val( attachment.id);
            $(button).parents('.mmf-image').find('.image-preview').attr( 'src',attachment.url);

            $(button).prev().val(attachment.id);
            wp.media.editor.send.attachment = send_attachment_bkp;
        }
        wp.media.editor.open(button);
        return false;
    });
    /*
     * удаляем значение произвольного поля
     * если быть точным, то мы просто удаляем value у input type="hidden"
     */
    $('.remove_image_button').click(function(){
        var r = confirm("Уверены?");
        if (r == true) {
            var src = $(this).parent().prev().attr('data-src');
            $(this).parent().prev().attr('src', src);
            $(this).prev().prev().val('');
        }
        return false;
    });

    var nImg='<div class="mmf-image"><img src="" alt="" width="164" height="133" class="image-preview"><input type="hidden" name="fs_galery[]" value="" class="img-url"><button type="button" class="upload-mft">Загрузить</button><button type="button" class="remove-tr" onclick="btn_view(this)">удалить</button></div>';
    jQuery('#new_image').click(function(event) {
        event.preventDefault();
        if (jQuery('#mmf-1 .mmf-image').length>0) {
            jQuery('#mmf-1 .mmf-image:last').after(nImg);
        }else{
            jQuery('#mmf-1').html(nImg);
        }
    });
});

function btn_view(e) {
    jQuery(e).parents('.mmf-image').remove();
}
(function($) {
    $( "#fs-tabs" ).tabs( {
        active: 0
    });


   $( "#fs-options-tabs" ).tabs({
         
    }).addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#fs-options-tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
    $(".fs-metabox input[type='radio']").checkboxradio();
})(jQuery);
