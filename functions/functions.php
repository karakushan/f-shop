<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * 	выводит группы свойств товара в виде опций select или обычного ul списка
 * @param  string $group   название группы свойств
 * @param  string $post_id id поста к которому нужно вывести свойства(по умолчанию текущий пост, если в цикле)
 * @param  string $type    тип вывода: 'option' - опции select, 'list' обычный список
 * @return  выводит или группу опций или маркированый список
 */
function fs_attr_group($group,$post_id="",$type='option',$option_default='',$class='form-control'){
    global $post;
    $config=new \FS\FS_Config();
    $post_id=(empty($post_id) ? $post->ID : (int)$post_id);

    $fs_atributes_post=get_post_meta($post_id,$config->meta['attributes'],false);
    $fs_atributes_post=$fs_atributes_post[0];

    $fs_atributes=get_option('fs-attr-group');
    if ($fs_atributes_post) {
        switch ($type) {
            case 'option':
                echo '<select name="'.$group.'" data-fs-element="attr" class="'.$class.'" data-product-id="'.$post_id.'">';
                echo '<option value="">'.$option_default.'</option>';
                foreach ($fs_atributes_post[$group] as $key => $fs_atribute) {
                    if (!$fs_atribute) continue;
                    echo "<option value=\"".$key."\">".$fs_atributes[$group]['attributes'][$key]."</option>";
                }
                echo '</select>';
                break;
            case 'list':
                foreach ($fs_atributes_post[$group] as $key => $fs_atribute) {
                    if (!$fs_atribute) continue;
                    echo "<li>".$fs_atributes[$group]['attributes'][$key]."</li>";
                }
                break;

            default:
                foreach ($fs_atributes_post[$group] as $key => $fs_atribute) {
                    if (!$fs_atribute) continue;
                    echo "<option value=\"".$fs_atributes_post[$group]['slug'].":".$key."\">".$fs_atributes[$group]['attributes'][$key]."</option>";
                }
                break;
        }

    }
}

/**
 * @param string $post_id
 * @param string $args
 */
function fs_lightslider($post_id='', $args='')
{
    $galery=new FS\FS_Images_Class();
    global $post;
    $post_id=(empty($post_id) ? $post->ID : (int)$post_id);

    $galery=$galery->fs_galery_list($post_id,array(90,90));
    if (!$galery) {
        echo "string";
    }else{
        echo "<ul id=\"product_slider\">";
        echo $galery;
        echo "</ul>";

        echo "<script> var product_sc={";
        echo $args;
        echo "}; 
		jQuery(document).ready(function($) {
			$('#product_slider').lightSlider(product_sc); 
		});
	</script>";

    }

}

//Получает текущую цену с учётом скидки
/**
 * @param string $post_id
 * @return int|mixed
 */
function fs_get_price($post_id='')
{
    global $post;
    $config=new \FS\FS_Config();
    $post_id=( empty( $post_id) ? $post->ID : (int)$post_id );
    $price=get_post_meta( $post_id, $config->meta['price'], true );
    $price=(empty($price) ? 0 :(int)$price);
    $price=round($price,2);
    return $price;
}

//Отображает общую сумму продуктов с одним артикулом
/**
 * @param $post_id
 * @param $count
 * @param bool $curency
 * @param string $cur_tag_before
 * @param string $cur_tag_after
 * @return int|mixed|string
 */
function fs_row_price($post_id, $count, $curency=true, $cur_tag_before=' <span>', $cur_tag_after='</span>')
{
    global $post;
    $post_id=( empty( $product) ? $post->ID : (int)$post_id );
    $price=fs_get_price($post_id)*$count;
    $price=number_format($price, 2, fs_option('currency_delimiter','.'), ' ');

    if ($curency) {
        $cur_symb=fs_currency();
        $price=$price.$cur_tag_before.$cur_symb.$cur_tag_after;
    }
    return $price;
}

//Выводит текущую цену с символом валюты и с учётом скидки
function fs_the_price($post_id='',$curency=true,$cur_tag_before=' <span>',$cur_tag_after='</span>')
{
    global $post;
    $cur_symb=fs_currency();
    if($post_id=='') $post_id=$post->ID;
    $price=get_post_meta( $post_id, 'fs_price', true );
    $action=get_post_meta( $post_id, 'fs_discount', true );
    $displayed_price=get_post_meta($post->ID, 'fs_displayed_price', true);
    if (!$action) {
        $action=0;
    }
    if (!$price) {
        $price=0;
    }
    $price=round($price-($price*$action/100),2);
    $price=number_format($price,2,fs_option('currency_delimiter','.'),'');

    if ($displayed_price!="") {
        $displayed_price=str_replace('%d', '%01.2f', $displayed_price);
        printf($displayed_price,$price,$cur_symb);
    } else {
        echo $price.$cur_tag_before.$cur_symb.$cur_tag_after;
    }


}

/**
 * Получает общую сумму всех продуктов в корзине
 * @param  boolean $show       показывать (по умолчанию) или возвращать
 * @param  string  $cur_before html перед символом валюты
 * @param  string  $cur_after  html после символа валюты
 * @return возвращает или показывает общую сумму с валютой
 */
function fs_total_amount($show=true,$cur_before=' <span>',$cur_after='</span>')
{
    $price=0;
    if (count($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $count){
            $all_price[$key]=$count['count']*fs_get_price($key);
        }
        $price=round(array_sum($all_price),2);
        $price=number_format($price,2,fs_option('currency_delimiter','.'),'');

    }

    if ($show==false) {
        return $price;
    } else {
        echo $price;
        echo  $cur_before;
        echo fs_currency();
        echo $cur_after;
    }

}

/**
 * Получаем содержимое корзины в виде массива
 * @return массив элементов корзины в виде:
 *         'id' - id товара,
 *         'name' - название товара,
 *         'count' - количество единиц одного продукта,
 *         'price' - цена за единицу,
 *         'all_price' - общая цена
 */
function fs_get_cart()
{
    if (!isset($_SESSION['cart'])) return;

    $products = array();
    $cur_symb=fs_currency();
    if (count($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $count){
            $price=fs_get_price($key);
            $count=(int)$count['count'];

            $all_price=$price*$count;
            $all_price=number_format($all_price, 2,fs_option('currency_delimiter','.'), ' ');
            $products[$key]=array(
                'id'=>$key,
                'name'=>get_the_title($key),
                'count'=>$count,
                'link'=>get_permalink($key),

                'price'=>$price.' <span>'.$cur_symb.'</span>',
                'all_price'=>$all_price.' <span>'.$cur_symb.'</span>'
            );
        }
    }
    return $products;
}

/**
 * Отображает ссылку для удаления товара
 * @param  [type] $product_id id удаляемого товара
 * @param string $text
 * @param string $type
 * @param string $attr
 */
function fs_delete_position($product_id,$text='',$type='link',$class='')
{
    switch ($type){
        case 'link':
            echo '<a href="#" class="'.$class.'"  data-fs-type="product-delete" data-fs-id="'.$product_id.'" data-fs-name="'.get_the_title($product_id).'">'.$text.'</a>';
            break;
        case 'button':
            echo '<button type="button" class="'.$class.'"  data-fs-type="product-delete" data-fs-id="'.$product_id.'" data-fs-name="'.get_the_title($product_id).'">'.$text.'</button>';
            break;
        default:
            echo '<a href="#" class="'.$class.'"  data-fs-type="product-delete" data-fs-id="'.$product_id.'" data-fs-name="'.get_the_title($product_id).'">'.$text.'</a>';

            break;
    }


}


/**
 * Получает к-во всех товаров в корзине
 * @param  boolean $show [description]
 * @return [type]        [description]
 */
function fs_product_count($show=false)
{
    $count=0;
    $all_count=array();
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $count){
            $all_count[$key]=$count['count'];
        }
        $count=array_sum($all_count);
    }
    $count=(int)$count;
    if ($show==false) {
        return $count;
    } else {
        echo $count;
    }
}


//Выводит текущую цену с символом валюты без учёта скидки
function fs_old_price($post_id='',$curency=true,$cur_tag_before=' <span>',$cur_tag_after='</span>')
{
    global $post;
    $post_id=empty($post_id) ? $post->ID : $post_id;

    $action=get_post_meta( $post_id, 'fs_discount', true );
    if($action=='' || $action<=0) return;

    $price=get_post_meta( $post_id, 'fs_price', true );
    if (!$price) {
        $price=0;
    }
    if ($curency) {
        $cur_symb=fs_currency();
    }
    echo $price.$cur_tag_before.$cur_symb.$cur_tag_after;
}


/**
 * [Отображает кнопку "в корзину" со всеми необходимыми атрибутамии]
 * @param  string $post_id [id поста (оставьте пустым в цикле wordpress)]
 * @param  string $label [надпись на кнопке]
 * @param  string $attr [атрибуты тега button такие как класс и прочее]
 * @param  string $preloader [html код прелоадера]
 * @param  string $send_icon [html код иконки успешного добавления в корзину]
 * @param string $json
 */
function fs_add_to_cart($post_id='',$label='',$attr='',$preloader='',$send_icon='',$json='')
{
    global $post;
    $post_id=empty($post_id) ? $post->ID : $post_id;

    if ($preloader=='') $preloader='<div class="cssload-container"><div class="cssload-speeding-wheel"></div></div>';

    if ($label=='') {
        $label=__( 'Add to cart', 'fast-shop' );
    }

    //Добавляем к json свои значения
    $js=json_decode($json,true);
    $js['post_id']=$post_id;
    $js['action']='add_to_cart';
    $js['count']=1;
    $js['attr']=array('count'=>1);

    $json=json_encode($js);

    echo "<button data-fs-action=\"add-to-cart\" data-product-id=\"$post_id\" data-product-name=\"".get_the_title($post_id)."\" data-json='".$json."' $attr id=\"fs-atc-$post_id\">$label <div class=\"send_ok\">$send_icon</div><span class=\"fs-preloader\">$preloader</span></button> ";
}

//Отображает кнопку сабмита формы заказа
function fs_order_send($label='Отправить заказ',$attr='',$preloader='<div class="cssload-container"><div class="cssload-speeding-wheel"></div></div>')
{
    echo "<button type=\"submit\" $attr data-fs-action=\"order-send\">$label <span class=\"fs-preloader\">$preloader</span></button>";
}

//Получает количество просмотров статьи
function fs_post_views($post_id='')
{
    global $post;
    $post_id=empty($post_id) ? $post->ID : $post_id;

    $views=get_post_meta( $post_id, 'views', true );

    if (!$views) {
        $views=0;
    }
    return $views;
}

/**
 * показывает вижет корзины в шаблоне

 * @return показывает виджет корзины
 */
function fs_cart_widget()
{

    $template_theme=TEMPLATEPATH.'/fast-shop/cart-widget/widget.php';
    $template=plugin_dir_path( __FILE__ ).'templates/front-end/cart-widget/widget.php';

    if (file_exists($template_theme)) {
        $template=$template_theme;

    }
    echo "<div id=\"fs_cart_widget\">";
    require $template;
    echo "</div>";
}

// Показывает ссылку на страницу корзины
function fs_cart_url($show=true)
{
    $cart_page=get_permalink(fs_option('page_cart',0));
    if ($show==true) {
        echo $cart_page;
    }else{
        return $cart_page;
    }
}

/**
 * показывает ссылку на страницу оформления заказа или оплаты
 * @param  boolean $show показывать (по умолчанию) или возвращать
 * @return строку содержащую ссылку на соответствующую страницу
 */
function fs_checkout_url($show=true)
{
    $checkout_page_id=fs_option('page_payment',0);
    if ($show==true) {
        echo get_permalink($checkout_page_id);
    }else{
        return get_permalink($checkout_page_id);
    }
}


//Показывает наличие продукта
/**
 * @param string $post_id
 * @param string $aviable_text
 * @param string $no_aviable_text
 */
function fs_aviable_product($post_id='', $aviable_text='', $no_aviable_text='')
{
    global $post;
    $product_id=empty($post_id) ? $post->ID : (int)$post_id;

    $availability=get_post_meta($product_id,'fs_availability',true);
    if ($availability==1) {
        echo $aviable_text;
    }else{
        echo $no_aviable_text;
    }
}

/**
 * Отоюражает поле для ввода количества добавляемых продуктов в корзину
 * @param  string $product_id - id продукта
 * @param string $type - тип поля input type="number" (по умолчанию) или input type="text"
 *
 */
function fs_quantity_product($product_id='',$type='number')
{
    global $post;
    $product_id=!empty($product_id)?$product_id : $post->ID;
    switch ($type){
        case 'number':
            echo '<input type="number" name="count"  value="1" min="1" data-fs-element="attr" data-product-id="'.$product_id.'">';
            break;
        case 'text':
            echo '<input type="text" name="count"  value="1" min="1" data-fs-element="attr" data-product-id="'.$product_id.'">';
            break;
        default:
            echo '<input type="number" name="count"  value="1" min="1" data-fs-element="attr" data-product-id="'.$product_id.'">';
            break;
    }

}

/**
 * Парсит урл и возвращает всё что находится до знака ?
 * @param  string $url строка url которую нужно спарсить
 * @return string      возвращает строку урл
 */
function fs_parse_url($url='')
{
    $url=(filter_var($url, FILTER_VALIDATE_URL)) ? $url : $_SERVER['REQUEST_URI'];
    $parse=explode('?',$url);
    return $parse[0];
}

/**
 * @param string $post_id
 * @return bool|mixed
 */
function fs_action($post_id=""){
    global $post;
    $post_id = (empty($post_id) ? $post->ID : (int)$post_id);
    $config=new \FS\FS_Config();
    $action=get_post_meta($post_id,$config->meta['action'],1);
    $action=(empty($action)?false:true);
    return $action;
}


/**
 * Возвращает массив просмотренных товаров или записейr
 * @return array
 */
function fs_user_viewed(){
    $viewed=isset($_SESSION['fs_user_settings']['viewed_product']) ? $_SESSION['fs_user_settings']['viewed_product'] : array();
    return $viewed;
}

/**
 * Получаем симовол валюты
 * @return string
 */
function fs_currency(){
    $config=new \FS\FS_Config();
    $currency=!empty($config->options['currency_symbol']) ? $config->options['currency_symbol'] : '$';
    return $currency;
}

/**
 * Возвращает данные опции
 * @param $option_name - название опции
 * @param $default      - значение по умолчанию
 * @return string
 */
function fs_option($option_name,$default=''){
    $config=new \FS\FS_Config();
    $options=$config->options;
    $option=!empty($options[$option_name]) ? $options[$option_name] : $default;
    return $option;
}

/**
 * @return bool|массив
 */
function fs_products_loop(){
    $cart=fs_get_cart();
    if ($cart){
        return $cart;
    }else{
        return false;
    }
}

