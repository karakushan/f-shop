/* Можно использовать глобальный объект FastShopData
    ajaxurl - ссылка на ajax обрабочик,
    fs_slider_max - максимальная цена установленная на сайте
    fs_currency - символ установленной валюты на текущий момент
*/

jQuery(function($) {
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
		if (confirm('Вы точно хотите удалить продукт "'+productName+'" из корзины?')) {
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
		if (confirm('Вы точно хотите удалить все товары из корзины?')) {
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



