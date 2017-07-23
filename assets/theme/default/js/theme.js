jQuery(document).ready(function($) {
	// табы
	$('#fs-metabox ul a').on('click', function(event) {
		event.preventDefault();
		var target=$(this).attr('href');
		$.cookie( 'fs_active_tab', $(this).data('tab'),{
            expires : 10
        });
		$('.fs-tabs .fs-tab').each(function(index, el) {
			$(this).removeClass('fs-tab-active');
		});
		$(target).addClass('fs-tab-active');
		$('#fs-metabox>ul>li').each(function(index, el) {
			$(this).removeClass('fs-link-active');
		});
		$(this).parent('li').addClass('fs-link-active');
	});
// добавление атрибута
$('#fs-add-attr').on('click', function(event) {
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
        this.window.on('select', function() {
        	var images = self.window.state().get('selection').toJSON();
        	for(var key in images){
        		if (images[key].type!='image') continue;
        		var image='<div class="fs-col-4"> <div class="fs-remove-img"></div> <input type="hidden" name="fs_galery[]" value="'+images[key].id+'"> <img src="'+images[key].url+'" alt="fs gallery image #'+images[key].id+'"> </div>';
        		$('#fs-gallery-wrapper').append(image);
        		console.log(images[key]);
        	}
        });
    }

    this.window.open();
    return false;
}
// удалем одно изображение из галереи
$('#fs-gallery-wrapper .fs-remove-img').on('click', function(event) {
	event.preventDefault();
	$(this).parent('div').remove();
});
});

