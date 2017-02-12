/* Можно использовать глобальный объект FastShopData
    ajaxurl - ссылка на ajax обрабочик,
    fs_slider_max - максимальная цена установленная на сайте
    fs_currency - символ установленной валюты на текущий момент
    fs_lang - текущая локаль
    */

    
    var fs_message;
    var event;
// переводы сообщений
var FastShopLang={
	uk:{
		confirm_text:"Вы точно хочете видалити позицію «%s» із списку бажань?",
		wishText:"Товар успішно доданий в список бажань!",
		delete_text:"Вы точно хочете видалити товар «%s» із кошика?",
		delete_all_text:"Ви точно хочете видалити всі товари із кошика?",
		count_error:"к-сть товарів не може бути меньше 1"


	},
	ru_RU:{
		confirm_text:"Вы точно хотите удалить позицию «%s» из списка желаний?",
		wishText:"Товар успешно добавлен в список желаний!",
		delete_text:"Вы точно хотите удалить продукт «%s» из корзины?",
		delete_all_text:"Вы точно хотите удалить все товары из корзины?",
		count_error:"к-во товаров не может быть меньше единицы"

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
    	// обработка кнопки быстрого заказа
    	$('[data-fs-action="quick_order_button"]').on('click', function(event) {
    		event.preventDefault();
    		var pName=$(this).data('product-name');
    		var pId=$(this).data('product-id');
    		console.log();
    		$('[name="fs_cart[product_name]"]').val(pName);
    		$('[name="fs_cart[product_id]"]').val(pId);
    	});

		//живой поиск по сайту
		$('form[name="live-search"]').on('click','.close-search', function(event) {
			event.preventDefault();
			$(this).parents('.search-results').fadeOut(0);
		});
		$('form[name="live-search"] input[name="s"]').on('keyup focus click input', function(event) {
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
					beforeSend:function () {
						search_input.next().addClass('search-animate');
					}
				})
				.done(function(data) {
					results_div.fadeIn(800).html(data);

				})
				.always(function() {
					search_input.next().removeClass('search-animate');
				});
			}else{
				results_div.fadeOut(800).html('');
			}
			
			
		});
    	//удаление товара из списка желаний
    	$('[data-fs-action="wishlist-delete-position"]').on('click', function(event) {
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
		var product_name=$(this).data('name');
		var curentBlock=$(this);
		$.ajax({
			url: FastShopData.ajaxurl,
			data: {action: 'fs_addto_wishlist',product_id:product_id},
			beforeSend:function () {
				curentBlock.find('.icon').addClass('wheel');
			}
		})
		.done(function(success) {
			// генерируем событие добавления в список желаний
			var add_to_wishlist = new CustomEvent("fs_add_to_wishlist", {
				detail: { id: product_id,name:product_name}
			});
			document.dispatchEvent(add_to_wishlist);

			var data=jQuery.parseJSON(success);
			$('[data-fs-element="whishlist-widget"]').html(data.body);
		});
	});

	//добавление товара в корзину (сессию)
	$('[data-action=add-to-cart]').on('click', function(event) {
		event.preventDefault();
		var curent=$(this);
		var productName=$(this).data('product-name');
		var product_id=curent.data('product-id');
		var product_name=curent.data('product-name');
		var attr=curent.data('attr');

		var productObject={
			"action": 'add_to_cart',
			"attr": attr,
			'post_id': product_id
		};
		$.ajax({
			url: FastShopData.ajaxurl,
			data: productObject,
			beforeSend:function () {
				curent.find('.fs-preloader ').fadeIn('slow');
			}
		})
		.done(function(result) {
			

			$('#fs_cart_widget,.fs_cart_widget,[data-fs-element="cart-widget"]').replaceWith(result);
			curent.find('.fs-preloader ').fadeOut('fast',function(){
				// создаём событие
				var add_to_cart = new CustomEvent("fs_add_to_cart", {
					detail: { id: product_id,name:product_name,attr:attr}
				});
				document.dispatchEvent(add_to_cart);
			});
			
		});
		
	});



	var validator =$('form[name="fs-order-send"]');
	var tabClick=false;
	$('[data-toggle="tab"]').on('click', function(event) {
		event.preventDefault();

		var activeTab=$(this).attr('href');
		$(activeTab).find('form[name="fs-order-send"]').validate({
			ignore: [],
			submitHandler: function(form) {
				$.ajax({
					url: FastShopData.ajaxurl,
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
					if(jsonData.redirect.length>0) document.location.href=jsonData.redirect;

				});


			}
		});

	});

// валидация и отправка формы заказа
validator.validate({
	ignore: [],
	submitHandler: function(form) {
		$.ajax({
			url: FastShopData.ajaxurl,
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
			if(jsonData.redirect.length>0) document.location.href=jsonData.redirect;

		});


	}
});

// валидация формы редактирования личных данных
var userInfoEdit=$('form[name="fs-profile-edit"]');
userInfoEdit.validate({
	rules:{
		"fs-password":{
			minlength: 6
		},
		"fs-repassword":{
			equalTo:"#fs-password"
		}
	},
	messages:{
		"fs-repassword":{
			equalTo:"пароль и повтор пароля не совпадают"
		},
		"fs-password":{
			minlength: "минимальная длина 6 символов"
		},
	},
	submitHandler: function(form) {
		$.ajax({
			url: FastShopData.ajaxurl,
			type: 'POST',
			data:userInfoEdit.serialize(),
			beforeSend:function () {
				userInfoEdit.find('.fs-preloader').fadeIn();
			}
		})
		.done(function(result) {
			userInfoEdit.find('.fs-preloader').fadeOut();
			var data=JSON.parse(result);
			if (data.status==1) {
				userInfoEdit.find('.form-info').removeClass('bg-danger').addClass('bg-success').show().text(data.message);
				setTimeout(function() {
					userInfoEdit.find('.form-info').fadeOut(800);
				},3000);

			}else{
				userInfoEdit.find('.form-info').removeClass('bg-success').addClass('bg-danger').show().text(data.message);
				setTimeout(function() {
					userInfoEdit.find('.form-info').fadeOut(800);
					
				},3000);

			}
			
		});
	}
});

// регистрация пользователя
var userProfileCreate=$('form[name="fs-profile-create"]');
userProfileCreate.validate({
	rules:{
		"fs-password":{
			minlength: 6
		},
		"fs-repassword":{
			equalTo:"#fs-password"
		}
	},
	messages:{
		"fs-repassword":{
			equalTo:"пароль и повтор пароля не совпадают"
		},
		"fs-password":{
			minlength: "минимальная длина 6 символов"
		},
	},
	submitHandler: function(form) {
		$.ajax({
			url: FastShopData.ajaxurl,
			type: 'POST',
			data:userProfileCreate.serialize(),
			beforeSend:function () {
				userProfileCreate.find('.fs-preloader').fadeIn();
			}
		})
		.done(function(result) {
			userProfileCreate.find('.fs-preloader').fadeOut();
			var data=JSON.parse(result);
			if (data.status==1) {
				userProfileCreate.find('.form-info').removeClass('bg-danger').addClass('bg-success').show().text(data.message);
				setTimeout(function() {
					userProfileCreate.find('.form-info').fadeOut(800);
					location.href=data.redirect;
				},3000);

			}else{
				userProfileCreate.find('.form-info').removeClass('bg-success').addClass('bg-danger').show().text(data.message);
				setTimeout(function() {
					userProfileCreate.find('.form-info').fadeOut(800);
					
				},3000);

			}
			
		});
	}
});

// авторизация пользователя
var loginForm=$('form[name="fs-login"]');
loginForm.validate({
	submitHandler: function(form) {
		$.ajax({
			url: FastShopData.ajaxurl,
			type: 'POST',
			data:loginForm.serialize(),
			beforeSend:function () {
				loginForm.find('.fs-preloader').fadeIn();
			}
		})
		.done(function(result) {
			var data=JSON.parse(result);
			loginForm.find('.fs-preloader').fadeOut();

			if(data.status==0){
				loginForm.find('.form-info-login').addClass('form-error text-danger').html(data.error);
			}else{
				location.reload();
			}

		});


	}
});
});




// Квантификатор товара
jQuery(document).ready(function($) {
	$('[data-fs-count="minus"]').on('click', function () {
		var $input =$($(this).data('target'));
		var count = parseInt($input.val()) - 1;
		count = count < 1 ? 1 : count;
		$input.val(count);
		$input.change();
		return false;
	});
	$('[data-fs-count="pluss"]').click(function () {
		var $input =$($(this).data('target'));
		$input.val(parseInt($input.val()) + 1);
		$input.change();
		return false;
	});
	//Изменение к-ва добавляемых продуктов
	$('[data-fs-action="change_count"]').on('change input', function(event) {
		event.preventDefault();
		/* Act on the event */
		var productId=$(this).data('fs-product-id');
		var count=$(this).val();
		if (count<1) { $(this).val(1); count=1;  }
		var cartButton=$('#fs-atc-'+productId);
		var cartButtonAttr=cartButton.data('attr');
		cartButtonAttr.count=count;
		cartButton.attr('data-attr',JSON.stringify(cartButtonAttr)); 
		// создаём событие
		var change_count = new CustomEvent("fs_change_count", {
			detail: { count: count}
		});
		document.dispatchEvent(change_count);
	});
});



//Изменение количества продуктов в корзине
jQuery(document).ready(function($) {
	$('[data-fs-type="cart-quantity"]').on('change input', function(event) {
		event.preventDefault();
		var productId = $(this).data('product-id');
		var productCount = $(this).val();

		//если покупатель вбил неправильное к-во товаров
		if ( !isNumeric(productCount) ||  productCount<=0) {
			$(this).val(1);
			productCount=1;
			$(this).parent().css({'position':'relative'});
			$(this).prev('.count-error').text(fs_message.count_error).fadeIn(400);
		}else{
			$(this).prev('.count-error').text('').fadeOut(800);
		}

		$.ajax({
			url: FastShopData.ajaxurl,
			type: 'POST',
			data: {
				action: 'update_cart',
				product:productId,
				count:productCount
			}
		})
		.done(function() {
			location.reload();
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
	$('[data-fs-element="attr"]').on('change input', function(event) {
		event.preventDefault();
		var productId=$(this).data('product-id');
		var cartbutton=$('#fs-atc-'+productId);
		var productObject=cartbutton.data('json');
		var attrName=$(this).attr('name');
		var attrVal=$(this).val();
		//если покупатель вбил неправильное к-во товаров
		if($(this).attr('name')=='count'){
			if ( !isNumeric(attrVal) ||  attrVal<=0) {
				$(this).val(1);
				attrVal=1;
				$(this).parent().css({'position':'relative'});
				$(this).prev('.count-error').text(fs_message.count_error).fadeIn(400);
			}else{
				$(this).prev('.count-error').text('').fadeOut(800);
			}
		}
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

// слайдер товара
if (typeof fs_lightslider_options!="undefined") { 
	$('#product_slider').lightSlider(fs_lightslider_options);
}
})(jQuery)

// проверяет является ли переменная числом
function isNumeric(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}