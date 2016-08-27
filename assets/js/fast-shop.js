jQuery(document).ready(function($) {
	//добавление товара в корзину (сессию)
	$('[data-fs-action=add-to-cart]').live('click', function(event) {
		event.preventDefault();
		var curent=$(this);
		var productName=$(this).data('product-name');
		var productObject=$(this).data('json');
		$.ajax({
			url: ajaxurl,
			data: productObject,
			beforeSend:function () {
				curent.find('.fs-preloader ').fadeIn('slow');
			}
		})
		.done(function(result) {
			// console.log(result);
			$('#fs_cart_widget').replaceWith(result);
			curent.find('.fs-preloader ').fadeOut('fast');
			curent.find('.send_ok').fadeIn('slow');
			$('#curent_product').html(productName);
			$('#modal-product').modal();

		})
		.fail(function() {
			console.log("error");
		})
		.always(function() {
			console.log("complete");
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


//Плавное появление корзины при ховере
$('.cart').mouseenter(function() {
	$('.cart  .cart-info').fadeIn('slow');
});
$('.close').live('click',function() {

	$('.cart .cart-info').fadeOut('fast');

});

// валидация формы заказа
$(".order-send").validate({
	rules : {
		name : {required : true},
		telefon : {required : true},
		
		city : {required : true},
		email: {
			required: true,
			email: true
		}

	},
	messages : {
		name : {
			required : "Введите ваше имя",
		},				
		telefon : {
			required : "Введите ваш номер телефона",
		},				
		
		city : {
			required : "Укажите город",
		},
		email: {
			required: "Заполните поле E-mail",
			email: "Поле E-mail имеет недопустимый формат"
		}
	},
	submitHandler: function(form) {
		var formData=$('.order-send').serialize();
		$.ajax({
			url: ajaxurl,
			dataType: 'html',
			data:formData,
			beforeSend:function () {
				$('button[data-fs-action=order-send]').find('.fs-preloader').fadeIn('slow');
			}
		})
		.done(function(result) {
			$('button[data-fs-action=order-send]').find('.fs-preloader').fadeOut('slow');
			// console.log(result);
			// console.log(fs_succes);
			document.location.href=fs_succes;
			
		})
		.fail(function() {
			console.log("error");
		})
		.always(function() {
			console.log("complete");
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
			url: ajaxurl,
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
				url: ajaxurl,
				type: 'POST',
				dataType: 'html',
				data: {
					action: 'delete_product',
					product:productId
				},
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

(function ($) {
	$( "#slider-range" ).slider({
		range: true,
		min: 0,
		max: 2500,
		values: [ 0, 2500 ],
		slide: function( event, ui ) {
			$( "#amount_show" ).html( ui.values[ 0 ] + "грн. - " + ui.values[ 1 ]+' грн.' );
		},
		change: function( event, ui ) {
			var uri={
				fs_filter:1,
				price_start:ui.values[ 0 ],
				price_end:ui.values[ 1 ]
			}
			window.location.search = jQuery.param(uri);
		}
	});
	$( "#amount_show" ).html( $( "#slider-range" ).slider( "values", 0 ) +
		" грн. " + $( "#slider-range" ).slider( "values", 1 )+" грн." );
})(jQuery)