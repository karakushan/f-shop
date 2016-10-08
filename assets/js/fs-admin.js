jQuery(function($){
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
    $('#fs_attr_type').on('change', function(event) {
        event.preventDefault();
        if($(this).val()=='image'){
            $('#fs_attr_type_block').fadeIn(800);
        }else{
            $('#fs_attr_type_block').fadeOut(800);
        }
    });
    //вызываем стандартный загрузчик изображений
    $('#fs_select_image').on('click',function(){
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        wp.media.editor.send.attachment = function(props, attachment) {
            $(button).next().val( attachment.id);
            $(button).css({
                'background-image': 'url('+attachment.url+')'
            });
            $(button).find('button').text('изменить');
            wp.media.editor.send.attachment = send_attachment_bkp;
        }
        wp.media.editor.open(button);
        return false;
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
    $( "#fs-options-tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#fs-options-tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
    $(".fs-metabox input[type='radio']").checkboxradio();
})(jQuery);