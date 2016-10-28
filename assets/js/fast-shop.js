/* Можно использовать глобальный объект FastShopData
    ajaxurl - ссылка на ajax обрабочик,
    fs_slider_max - максимальная цена установленная на сайте
    fs_currency - символ установленной валюты на текущий момент
    fs_lang - текущая локаль
    */

    
    var fs_message;
// переводы сообщений
var FastShopLang={
	uk:{
		confirm_text:"Вы точно хочете видалити позицію «%s» із списку бажань?",
		wishText:"Товар успішно доданий в список бажань!",
		delete_text:"Вы точно хочете видалити товар «%s» із кошика?",
		delete_all_text:"Ви точно хочете видалити всі товари із кошика?"

	},
	ru_RU:{
		confirm_text:"Вы точно хотите удалить позицию «%s» из списка желаний?",
		wishText:"Товар успешно добавлен в список желаний!",
		delete_text:"Вы точно хотите удалить продукт «%s» из корзины?",
		delete_all_text:"Вы точно хотите удалить все товары из корзины?"
	}

}
    //переключатель сообщений в зависимости от локали
    switch(FastShopData.fs_lang){
    	case "ru_RU":
    	fs_message=FastShopLang.ru_RU;
    	break;
    	case "uk":
    	fs_message=FastShopLang.uk;
    	break;
    	default:
    	fs_message=FastShopLang.ru_RU;
    }

    jQuery(function($) {
    	$('.search-results .close-search').live('click', function(event) {
    		event.preventDefault();
    		$(this).parents('.search-results').fadeOut(0);
    	});
		//живой поиск по сайту
		$('#fs-livesearch').on('keyup focus click input', function(event) {
			event.preventDefault();
			var search_input=$(this);
			var search=$(this).val();
			var parents_form=search_input.parents('form');
			var results_div=parents_form.find('.search-results');
			if(search.length>1){
				$.ajax({
					url: FastShopData.ajaxurl,
					type: 'POST',
					data: {action: 'fs_livesearch',s:search},
				})
				.done(function(data) {
					results_div.fadeIn(800).html(data);
				})
				.fail(function() {
					console.log("error");
				})
				.always(function() {
					console.log("complete");
				});
			}else{
				results_div.fadeOut(800).html('');
			}
			
			
		});
    	//удаление товара из списка желаний
    	$('[data-fs-action="wishlist-delete-position"]').live('click', function(event) {
    		var product_id=$(this).data('product-id'); 
    		var product_name=$(this).data('product-name'); 
    		var parents=$(this).parents('li');
    		
    		if(confirm(fs_message.confirm_text.replace('%s',product_name))){
    			$.ajax({
    				url: FastShopData.ajaxurl,
    				data: {
    					action:'fs_del_wishlist_pos',
    					position:product_id
    				},
    			})
    			.done(function(success) {
    				var data=jQuery.parseJSON(success);
    				$('#fs-wishlist').html(data.body);
    			});
    			
    			
    		}
    	});
	//добавление товара в список желаний
	$('[data-fs-action="wishlist"]').on('click', function(event) {
		event.preventDefault();
		var product_id=$(this).data('product-id');
		var curentBlock=$(this);
		$.ajax({
			url: FastShopData.ajaxurl,
			data: {action: 'fs_addto_wishlist',product_id:product_id},
			beforeSend:function () {
				curentBlock.find('.icon').addClass('wheel');
			}
		})
		.done(function(success) {
			var data=jQuery.parseJSON(success);

			$('#fs-wishlist').html(data.body);
			curentBlock.find('.whishlist-message').fadeIn('400', function() {
			}).text(fs_message.wishText);
			
			
		}).always(function() {
			curentBlock.find('.icon').removeClass('wheel');
			setTimeout(function() { 
				curentBlock.find('.whishlist-message').fadeOut(1500); 
			}, 2500);
		});
		
		
		
	});
	//добавление товара в корзину (сессию)
	$('[data-fs-action=add-to-cart]').live('click', function(event) {
		event.preventDefault();
		var curent=$(this);
		var productName=$(this).data('product-name');
		var productObject=$(this).data('json');
		$.ajax({
			url: FastShopData.ajaxurl,
			data: productObject,
			beforeSend:function () {
				curent.find('.fs-preloader ').fadeIn('slow');
			}
		})
		.done(function(result) {
			// console.log(result);
			$('#fs_cart_widget,.fs_cart_widget').replaceWith(result);
			curent.find('.fs-preloader ').fadeOut('fast');
			curent.find('.send_ok').fadeIn('slow');
			$('#curent_product').html(productName);
			$('#modal-product').modal();

		}).always(function(){
			setTimeout(function() { 
				curent.find('.send_ok').fadeOut(1500); 
			}, 2500);
		});
		
	});

//прибавлем количество товара на единицу
$('.c-up').on('click', function(event) {
	event.preventDefault();
	var parCont=$(this).parents('.c-tovar');
	var inputVal=parCont.find('input:first').val();
	inputVal=+inputVal;
	inputVal=inputVal+1;

	if (inputVal>0) { parCont.find('input:first').val(inputVal);}

	$('.in-cart').data('count',inputVal);
});	


$('[data-fs-action=change_count]').on('change', function(event) {
	event.preventDefault();

	var product=$(this).data('count-id');
	var count=$(this).val();
	if (count<1) { $(this).val(1) }
		$('[data-product-id='+product+']').data('count',count);
});

$('.up').click(function(event) {
	event.preventDefault();

	var parent=$(this).parents('.count_wrap');
	var parentValue=parent.find('input').val();
	parentValue++;
	parent.find('input').val(parentValue);
	parent.find('input').change();
	return false;
});
$('.down').click(function(event) {
	event.preventDefault();

	var parent=$(this).parents('.count_wrap');
	var parentValue=parent.find('input').val();
	parentValue--;
	if(parentValue>=1){
		parent.find('input').val(parentValue);
		parent.find('input').change();
		return false;
	}
	
});


var validator =$("#order-send")
// валидация и отправка формы заказа
validator.validate({
	ignore: [] ,
	rules: {
		name: {
			required: true
		}
	},
	submitHandler: function(form) {
		$.ajax({
			url: FastShopData.ajaxurl,
			dataType: 'html',
			type: 'POST',
			data:validator.serialize(),
			beforeSend:function () {
				$('button[data-fs-action=order-send]').find('.fs-preloader').fadeIn('slow');
			}
		})
		.done(function(result) {
			$('button[data-fs-action=order-send]').find('.fs-preloader').fadeOut('slow');
			console.log(result);
			var jsonData=JSON.parse(result);

			if(jsonData.wpdb_error){
				console.log(jsonData.wpdb_error);
			}
			document.location.href=jsonData.redirect;

		});


	}
});

//Изменение к-ва добавляемых продуктов
$('[data-fs-action=change_count]').on('change', function(event) {
	event.preventDefault();
	/* Act on the event */
	var productId=$(this).data('count-id');
	var count=$(this).val();
	var cartButton=$('[data-product-id='+productId+']');
	cartButton.data('count', count);
	cartButton.attr('data-count', count);
});


});

// Увеличиваем значение input на единицу
jQuery(document).ready(function($) {
	$('.fs_product_minus').click(function () {
		var $input = $(this).parent().find('input');
		var count = parseInt($input.val()) - 1;
		count = count < 1 ? 1 : count;
		$input.val(count);
		$input.change();
		return false;
	});
	$('.fs_product_plus').click(function () {
		var $input = $(this).parent().find('input');
		$input.val(parseInt($input.val()) + 1);
		$input.change();
		return false;
	});
});

//Изменение количества продуктов в корзине
jQuery(document).ready(function($) {
	$('[data-fs-type="cart-quantity"]').on('change', function(event) {
		event.preventDefault();
		var productId = $(this).data('fs-id');
		var productCount = $(this).val();

		$.ajax({
			url: FastShopData.ajaxurl,
			type: 'POST',
			dataType: 'html',
			data: {
				action: 'update_cart',
				product:productId,
				count:productCount
			}
		})
		.done(function() {
			location.reload();
		})
		.fail(function() {
			console.log("ошибка обновления количества товаров в корзине");
		})
		.always(function() {
			
		});
		

	});
});

//Удаление продукта из корзины
jQuery(document).ready(function($) {
	$('[data-fs-type="product-delete"]').on('click', function(event) {
		event.preventDefault();
		var productId = $(this).data('fs-id');
		var productName = $(this).data('fs-name');
		if (confirm(fs_message.delete_text.replace('%s',productName))) {
			$.ajax({
				url: FastShopData.ajaxurl,
				type: 'POST',
				dataType: 'html',
				data: {
					action: 'delete_product',
					product:productId
				}
			})
			.done(function() {
				location.reload();
			})
			.fail(function() {
				console.log("ошибка удаления товара из корзины");
			})
			.always(function() {

			});
		}
	});
});

//очищаем корзину
jQuery(document).ready(function($) {
	$('[data-fs-type="delete-cart"]').on('click', function(event) {
		event.preventDefault();
		console.log('click');
		if (confirm(fs_message.delete_all_text)) {
			document.location.href=$(this).data('url');
		}
	});
});

jQuery(document).ready(function($) {
	//Образует js объект с данными о продукте и помещает в кнопку добавления в корзину в атрибут 'data-json'
	$('[data-fs-element="attr"]').on('change', function(event) {
		event.preventDefault();
		var productId=$(this).data('product-id');
		var cartbutton=$('#fs-atc-'+productId);
		var productObject=cartbutton.data('json');
		var attrName=$(this).attr('name');
		var attrVal=$(this).val();
		productObject.count=attrVal;
		productObject.attr[attrName]=attrVal;
		var jsontostr=JSON.stringify(productObject);
		cartbutton.attr('data-json',jsontostr);
	});
});

/**
 * Add a URL parameter (or changing it if it already exists)
 * @param {search} string  this is typically document.location.search
 * @param {key}    string  the key to set
 * @param {val}    string  value
 */
 var addUrlParam = function(search, key, val){
 	var newParam = key + '=' + val,
 	params = '&' + newParam;

	// If the "search" string exists, then build params from it
	if (search) {
		// Try to replace an existance instance
		params = search.replace(new RegExp('([?&])' + key + '[^&]*'), '$1' + newParam);

		// If nothing was replaced, then add the new param to the end
		if (params === search) {
			params += '&' + newParam;
		}
	}

	return params;
};

//слайдер диапазона цены
(function ($) {

	var u  = new Url;
	var p_start=u.query.price_start==undefined ? 0 : u.query.price_start;
	var p_end=u.query.price_end==undefined ? FastShopData.fs_slider_max : u.query.price_end;

	$( '[data-fs-element="range-slider"]' ).slider({
		range: true,
		min:0,
		max:FastShopData.fs_slider_max,
		values: [ p_start, p_end],
		slide: function( event, ui ) {
			$( '[data-fs-element="range-end"] ').html(ui.values[ 1 ]+' '+FastShopData.fs_currency );
			$( '[data-fs-element="range-start"] ').html(ui.values[ 0 ]+' '+FastShopData.fs_currency );
		},
		change: function( event, ui ) {

			u.query.fs_filter=1;
			u.query.price_start=ui.values[ 0 ];
			u.query.price_end=ui.values[ 1 ];
            // console.log(u.toString());
            window.location.href=u.toString();


        }
    });
	$( '[data-fs-element="range-end"] ').html(p_end+' '+FastShopData.fs_currency);
	$( '[data-fs-element="range-start"] ').html(p_start+' '+FastShopData.fs_currency);

//Переадресовываем все фильтры на значение, которое они возвращают
$('[data-fs-action="filter"]').on('change',function (e) {
	e.preventDefault();
	window.location.href=$(this).val();
})
})(jQuery)