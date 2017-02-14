<div class="width text-center">
    <img src="/wp-content/themes/clothes/images/img-thanks.png" alt=""/>
    <ul>
        <li>
            <b>Заказ оформлен на следующие контактные данные:</b>
        </li>
        <li>
            Ваше имя 
            <span><?php echo $order_info->name; ?></span>
        </li>
        <li>
            Электронная почта 
            <span><?php echo $order_info->email; ?></span>
        </li>
        <li>
            Номер телефона  
            <span><?php echo $order_info->telephone; ?></span>
        </li>
        <li>
            Город 
            <span><?php echo $order_info->city; ?></span>
        </li>
        <li>
            Тип доставки 
            <span><?php echo $order_info->delivery_name; ?></span>
        </li>
        <li>
            Тип оплаты 
            <span><?php echo $order_info->payment_name; ?></span>
        </li>                    
    </ul>
</div>