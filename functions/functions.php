<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 *  выводит группы свойств товара в виде опций select или обычного ul списка
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

    $fs_atributes_post=isset($fs_atributes_post[0])?$fs_atributes_post[0]:array();
    $fs_atributes_all=get_option('fs-attributes')!=false?get_option('fs-attributes'):array();

    /* echo "<pre>";

     print_r($fs_atributes_post);
     echo "</pre>";
     echo "<pre>";

     print_r($fs_atributes_all);
     echo "</pre>";*/


     if ($fs_atributes_post) {
        switch ($type) {


            case 'radio':
            foreach ($fs_atributes_post[$group] as $key => $fs_atribute) {
                if ($fs_atribute==0) continue;
                $checked=$key==0?"checked":"";
                if ($fs_atributes_all[$group][$key]['type']=='image') {
                    $img_url=wp_get_attachment_thumb_url($fs_atributes_all[$group][$key]['value']);
                    echo "<li><label><img src=\"$img_url\" width=\"90\" height=\"90\"><input type=\"radio\"  name=\"$group\" value=\"$key\" data-fs-element=\"attr\" data-product-id=\"$post_id\" $checked></label></li>";
                }else{
                    echo "<li><label>". $fs_atributes_all[$group][$key]['name']."</label><input type=\"radio\" name=\"$group\" value=\"$key\" $checked></li>";
                }

            }
            break;



            default:

            break;
        }

    }
}

/**
 * @param integer $post_id - id записи
 * @param array $args - массив аргументов: http://sachinchoolur.github.io/lightslider/settings.html
 */
function fs_lightslider($post_id=0, $args=array())
{
    $galery=new FS\FS_Images_Class();
    global $post;
    $post_id=empty($post_id) ? $post->ID : (int)$post_id;

    $default=array(
        "gallery"=>true,
        "item"=>1,
        "vertical"=>false,
        "thumbItem"=>3
        );
    $args=wp_parse_args($args,$default);

    $galery=$galery->fs_galery_list($post_id);
    if (!$galery) {
        echo "string";
    }else{
        echo "<script>";
        echo "var fs_lightslider_options=".json_encode( $args);
        echo "</script>";
        echo "<ul id=\"product_slider\">";
        echo $galery;
        echo "</ul>";
    }

}

//Получает текущую цену с учётом скидки
/**
 * @param string $post_id
 * @return int|mixed
 */
function fs_get_price($post_id='')
{
    $config=new \FS\FS_Config();//класс основных настроек плагина

    // устанавливаем id поста
    global $post;
    $post_id=empty($post_id) ? $post->ID : (int)$post_id;

    //узнаём какой тип скидки активирован в настройках (% или фикс)
    $action_type=isset($config->options['action_count'])&&$config->options['action_count']==1?1:0;

    // получаем возможные типы цен
    $base_price=get_post_meta( $post_id, $config->meta['price'], true );//базовая и главная цена
    $action_price=get_post_meta( $post_id,$config->meta['action_price'], true );//акионная цена
    $action_base=get_post_meta( $post_id,$config->meta['discount'], true );//размер скидки общий для всех товаров

    // создаём возможность модифицировать базовую цену через фильтры
    $base_price=apply_filters('fs_base_price',$base_price,$post_id);
    $price=empty($base_price) ? 0 : (float)$base_price;

    // создаём возможность модифицировать акционную цену через фильтры
    $action_price=apply_filters('fs_action_price',$action_price,$post_id);
    $action_price=empty($action_price) ? 0 :(float)$action_price;

    //получаем размер скидки из общих настроек (в процентах или в фиксированной сумме)
    $action_base=apply_filters('fs_action_base',$action_base,$post_id);
    $action_base=empty($action_base)?0:(float)$action_base;

    //если поле акционной цены заполнено иначе ...
    if ($action_price>0) {
        $price=$action_price;
    }else{
        if ($action_base>0) {
            if($action_type==1){
                //расчёт цены если скидка в процентах
                $price=$base_price-($base_price*$action_base/100);
            }else{
                //расчёт цены если скидка в фикс. к-ве
                $price=$base_price-$action_base;
            }
        }
    }
    return (float)$price;
}

//Отображает общую сумму продуктов с одним артикулом
/**
 * @param $post_id - id
 * @param $count - к-во товаров
 * @param bool $curency
 * @param string $wpap формат отображения цены вместе с валютой

 * @return int|mixed|string
 */
function fs_row_price($post_id, $count, $curency=true, $wrap='%s <span>%s</span>')
{
    global $post;
    $post_id=empty($post_id) ? $post->ID : (int)$post_id;
    $price=fs_get_price($post_id)*$count;
    if ($curency) {
        $price=apply_filters('fs_price_format',$price,fs_option('currency_delimiter','.'),' ');
        $price=sprintf($wrap,$price,fs_currency());
    }
    return $price;
}


/**
 * Выводит текущую цену с учётом скидки
 * @param string $post_id  - id товара
 * @param string $wrap     - html обёртка для цены
 */
function fs_the_price($post_id='',$wrap="<span>%s</span>")
{
    global $post;
    $config=new \FS\FS_Config();
    $cur_symb=fs_currency();
    $post_id=empty($post_id)?$post->ID:$post_id;
    $displayed_price=get_post_meta($post_id,$config->meta['displayed_price'],1);
    $displayed_price=!empty($displayed_price) ? $displayed_price : '';
    $price=fs_get_price($post_id);
    $currency_delimiter=fs_option('currency_delimiter','.');
    $price=apply_filters('fs_price_format', $price,$currency_delimiter,' ');
    if ($displayed_price!="") {
        $displayed_price=str_replace('%d', '%01.2f', $displayed_price);
        printf($displayed_price,$price,$cur_symb);
    } else {
        printf($wrap,$price.' <span>'.$cur_symb.'</span>');
    }

}

/**
 * Получает общую сумму всех продуктов в корзине
 * @param  boolean $show       показывать (по умолчанию) или возвращать
 * @param  string  $cur_before html перед символом валюты
 * @param  string  $cur_after  html после символа валюты
 * @return возвращает или показывает общую сумму с валютой
 */
function fs_total_amount($products=array(),$show=true,$wrap='%s <span>%s</span>')
{
    $all_price=array();
    $products=!empty($_SESSION['cart'])?$_SESSION['cart']:$products;
    foreach ($products as $key => $count){
        $all_price[$key]=$count['count']*fs_get_price($key);
    }
    $price=array_sum($all_price);
    if ($show==false) {
        return $price;
    } else {
        $price=apply_filters('fs_price_format',$price,fs_option('currency_delimiter','.'),' ');
        printf($wrap,$price,fs_currency());
    }
}

/**
 * возвращает к-во всех товаров в корзине
 * @return [type] [description]
 */
function fs_total_count()
{
    $count=array();
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $product){
            $count[]=$product['count'];
        }
    }
    $all_count=array_sum($count);
    return  $all_count;

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
    if (!isset($_SESSION['cart'])) return false;

    $products = array();
    $cur_symb=fs_currency();
    $currency_delimiter=fs_option('currency_delimiter','.');
    if (count($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $count){
            $price=fs_get_price($key);
            $price_show=apply_filters('fs_price_format',$price, $currency_delimiter,' ');
            $count=(int)$count['count'];
            $all_price=$price*$count;
            $all_price=apply_filters('fs_price_format',$all_price, $currency_delimiter,' ');
            $products[$key]=array(
                'id'=>$key,
                'name'=>get_the_title($key),
                'count'=>$count,
                'link'=>get_permalink($key),
                'price'=> $price_show.' <span>'.$cur_symb.'</span>',
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
function fs_delete_position($product_id,$text='&#10005;',$type='link',$class='')

{
    $title='title="'.__('Remove items','fast-shop').' '.get_the_title($product_id).'"';
    $class="class=\"$class\"";
    switch ($type){
        case 'link':
        echo '<a href="#" '.$class.' '.$title.'  data-fs-type="product-delete" data-fs-id="'.$product_id.'" data-fs-name="'.get_the_title($product_id).'">'.$text.'</a>';
        break;
        case 'button':
        echo '<button type="button" '.$class.' '.$title.'  data-fs-type="product-delete" data-fs-id="'.$product_id.'" data-fs-name="'.get_the_title($product_id).'">'.$text.'</button>';
        break;
        default:
        echo '<a href="#" '.$class.' '.$title.'  data-fs-type="product-delete" data-fs-id="'.$product_id.'" data-fs-name="'.get_the_title($product_id).'">'.$text.'</a>';

        break;
    }


}


/**
 * Получает к-во всех товаров в корзине
 * @param  boolean $show [description]
 * @return [type]        [description]
 */
function fs_product_count($products=array())
{
    $all_count=array();
    if (!empty($_SESSION['cart']) || !is_array($products)){
        $products=isset($_SESSION['cart'])?$_SESSION['cart']:array();
    }
    if (count($products)){
        foreach ($products as $key => $count){
            $all_count[$key]=$count['count'];
        }
    }
    $count=array_sum($all_count);
    return (int)$count;
}


//Выводит текущую цену с символом валюты без учёта скидки
/**
 * @param int $post_id - id товара
 * @param bool $echo - вывести или возвратить (по умолчанию вывести)
 * @param string $wrap - html обёртка для цены
 * @return mixed выводит отформатированную цену или возвращает её для дальнейшей обработки
 */
function fs_base_price($post_id=0,$echo=true, $wrap='<span>%s</span>')
{
    global $post;
    $config=new \FS\FS_Config();
    $post_id=empty($post_id) ? $post->ID : $post_id;

    $price=get_post_meta( $post_id, $config->meta['price'], 1);
    $price=apply_filters('fs_base_price', $price,$post_id);

    if ( $price==fs_get_price($post_id)) return;
    $price=empty($price) ? 0 : (float)$price;

    $price_float=$price;

    $price=number_format($price,2,fs_option('currency_delimiter','.'),' ');
    $cur_symb=fs_currency();

    if ($echo===true){
        printf($wrap,$price.' <span>'.$cur_symb.'</span>');
    }else{
        return $price_float;
    }
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
    echo "<div id=\"fs_cart_widget\" class=\"fs_cart_widget\">";
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
    $config=new FS\FS_Config;
    $product_id=empty($post_id) ? $post->ID : (int)$post_id;
    $availability=get_post_meta($product_id,$config->meta['availability'],true);
    $aviable=$availability==1?$aviable_text:$no_aviable_text;
    echo $aviable;
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
        echo '<div class="count-error" style="display: none"></div><input type="number" name="count"  value="1" min="1" data-fs-element="attr" data-product-id="'.$product_id.'">';
        break;
        case 'text':
        echo '<div class="count-error" style="display: none"></div><input type="text" name="count"  value="1" min="1" data-fs-element="attr" data-product-id="'.$product_id.'">';
        break;
        default:
        echo '<div class="count-error" style="display: none"></div><input type="number" name="count"  value="1" min="1" data-fs-element="attr" data-product-id="'.$product_id.'">';
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
function fs_action($post_id=0){
    global $post;
    $post_id = empty($post_id) ? $post->ID : (int)$post_id;
    if(fs_base_price($post_id,false)>fs_get_price($post_id)){
        $action=true;
    }else{
        $action=false;
    }
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
    $option=wp_unslash($option);
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

/**
 * Эта функция выводит кнопку удаления всех товаров в корзине
 * @param string $text - надпись на кнопке (мультиязык)
 * @param string $class - класс присваемый кнопке
 */
function fs_delete_cart($text='Remove all items', $class=''){
    echo '<button class="'.sanitize_html_class( $class,'').'" data-fs-type="delete-cart" data-url="'.wp_nonce_url(add_query_arg(array("fs_action"=>"delete-cart")),"fs_action").'">'.__($text,'fast-shop').'</button> ';
}

/**
 * Выводит процент или сумму скидки(в зависимости от настрорек)
 * @param  string $product_id - id товара(записи)
 * @param  string $wrap - html обёртка для скидки
 * @return выводит или возвращает скидку если таковая имеется или пустая строка
 */
function fs_amount_discount($product_id='',$echo=true,$wrap='<span>%s</span>'){
    global $post;
    $config=new FS\FS_Config;
    $product_id=empty($product_id)?$post->ID:$product_id;
    $action_symbol=isset($config->options['action_count']) && $config->options['action_count']==1 ? '<span>%</span>':'<span>'.fs_currency().'</span>';
    $discount_meta=(float)get_post_meta($product_id,$config->meta['discount'],1);
    $discount=empty($discount_meta)?'': sprintf($wrap,$discount_meta.' '.$action_symbol);
    $discount_return=empty($discount_meta)? 0 :$discount_meta;
    if ($echo) {
        echo $discount;
    }else{
        return $discount_return;
    }

}

/**
 * @param array $post_count
 * @param bool $echo
 * @return bool|string
 */
function fs_per_page_filter($post_count=array(), $echo=true)
{
    $filters=new FS\FS_Filters;
    if (count($post_count)==0 ){
        $post_count=array(12,24,36,48,60,100);
    }
    $page_filter=$filters->posts_per_page_filter($post_count);
    if (true === $echo){
        echo $page_filter;
    }else{
        return $page_filter;
    }
}


/**
 * Добавляет возможность фильтрации по определёному атрибуту
 * @param string $group             название группы (slug)
 * @param string $type              тип фильтра 'option' (список опций в теге "select",по умолчанию) или обычный список "ul"
 * @param string $option_default    первая опция (текст) если выбран 2 параметр "option"
 */
function fs_attr_group_filter($group, $type='option', $option_default='Выберите значение')
{
    $fs_filter=new FS\FS_Filters;
    echo $fs_filter->attr_group_filter($group,$type,$option_default);
}

/**
 * @param int $price_max
 */
function fs_range_slider()
{

    $price_max=fs_price_max();
    $curency=fs_currency();
    $slider='<div class="slider">
    <div data-fs-element="range-slider" id="range-slider"></div>
    <div class="fs-price-show">
        <span data-fs-element="range-start">0 <span>'.$curency.'</span></span>
        <span data-fs-element="range-end">'.$price_max.' <span>'.$curency.'</span>
    </span>
</div>
</div>';
echo $slider;
}//end range_slider()

/**
 * Функция получает значение максимальной цены установленной на сайте
 * @return float|int|null|string
 */
function fs_price_max($filter=true){
    global $wpdb;
    $config=new FS\FS_Config();
    $meta_field=$config->meta['price'];
    $meta_value_max = $wpdb->get_var("SELECT (meta_value + 0.01 ) AS meta_values FROM $wpdb->postmeta WHERE meta_key='$meta_field' ORDER BY meta_values DESC ");
    $meta_value_max=!is_null($meta_value_max)?(float)$meta_value_max:20000;
    if ($filter) {
        $max=apply_filters('fs_price_format',$meta_value_max);
    }else{
        $max=$meta_value_max;
    }
    return $max;
}

/**
 * функция отображает кнопку "добавить в список желаний"
 * @param  integer $post_id  - id записи
 * @param  string  $content  - внутренний html кнопки
 * @return [type]           [description]
 */
function fs_wishlist_button($post_id=0,$args='')
{
    global $post;
    $post_id=empty($post_id)?$post->ID:$post_id;
    // определим параметры по умолчанию
    $defaults = array(
        'attr' => '',
        'content' => '',
        );

    $args = wp_parse_args( $args, $defaults );
    extract($args);

    echo '<button data-fs-action="wishlist" '.$attr.'  data-product-id="'.$post_id.'"><span class="whishlist-message"></span>'.$content.'</button>';
}

/**
 * Функция транслитерации русских букв
 * @param $s
 * @return mixed|string
 */
function fs_transliteration($s) {
    $s = (string) $s; // преобразуем в строковое значение
    $s = strip_tags($s); // убираем HTML-теги
    $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
    $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
    $s = trim($s); // убираем пробелы в начале и конце строки
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
    $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
    $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
    return $s; // возвращаем результат
}

/**
 * Подключает шаблон $template из директории темы, если шаблон остсуствует ищет в папке "/templates/front-end/" плагина
 * @param $template - название папки и шаблона без расширения
 */
function fs_frontend_template($template,$args=array()){
    extract(wp_parse_args($args,array(
        'user'=>wp_get_current_user()
        )));

    $template_plugin=PLUGIN_PATH.'/templates/front-end/'.$template.'.php';
    $template_theme=TEMPLATEPATH.'/fast-shop/'.$template.'.php';
    ob_start();
    if (file_exists($template_theme)){
        include ($template_theme);
    }elseif(file_exists($template_plugin)){
        include ($template_plugin);
    }else{
        echo 'файл шаблона '.$template.' не найден в функции '.__FUNCTION__;
    }
    $template=ob_get_clean();
    ob_end_flush();
    return apply_filters('fs_frontend_template',$template);
}

/**
 * Получает шаблон формы входа
 * @return mixed|void
 */
function fs_login_form(){
    ob_start();
    $template=fs_frontend_template('auth/login');
    return apply_filters('fs_login_form',$template);
}

/**
 * Получает шаблон формы регистрации
 * @return mixed|void
 */
function fs_register_form(){
    ob_start();
    $template=fs_frontend_template('auth/register');
    return apply_filters('fs_register_form',$template);
}

/**
 * Получает шаблон формы входа
 * @return mixed|void
 */
function fs_user_cabinet(){
    $template= fs_frontend_template('auth/cabinet');;
    return apply_filters('fs_user_cabinet',$template);
}

function fs_page_content(){
    if (empty($_GET['fs-page'])) $page='profile';
    $page= filter_input(INPUT_GET, 'fs-page', FILTER_SANITIZE_URL);
    $template='';
    $pages=array('profile','conditions');
    if(in_array($page,$pages)){
        $template=fs_frontend_template('auth/'.$page);
    } else{
        $template=fs_frontend_template('auth/profile');
    }
   
    echo $template;
}