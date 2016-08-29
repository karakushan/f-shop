jQuery(function($){

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