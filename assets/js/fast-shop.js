jQuery(document).ready(function($) {

	//добавление товара в корзину (сессию)
	$('[data-fs-action=add-to-cart]').live('click', function(event) {
		event.preventDefault();
		var thisB=$(this);
		var productName=thisB.data('product-name');
		$.ajax({
			url: ajaxurl,
			data: {
				action: 'add_to_cart',
				product_id:$(this).data('product-id'),
				count:$(this).data('count')
			},
			beforeSend:function () {
				thisB.find('.fs-preloader ').fadeIn('slow');
			}
		})
		.done(function(result) {
			$('#fs_cart_widget').replaceWith(result);
			thisB.find('.fs-preloader ').fadeOut('fast');
			thisB.find('.send_ok').fadeIn('slow');
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
		delivery : {required : true},
		billing_city : {required : true},
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
		delivery : {
			required : "Необходимо выбрать способ доставки",
		},
		billing_city : {
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



