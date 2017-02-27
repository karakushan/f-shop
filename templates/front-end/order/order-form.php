<?php $user = wp_get_current_user(); ?>
<span class="h2">
                форма обратной связи
            </span>
<p class="text-center">
    Оставьте, пожалуйста, свои координаты и сообщение. Мы свяжемся с Вами в ближайшее время!
</p>
<div class="width">
    <div class="row">
        <div class="col">
            <?php fs_form_field('fs_first_name') ?>
            <label>Имя</label>
        </div>
        <div class="col">
            <?php fs_form_field('fs_email') ?>
            <label>E-mail</label>
        </div>
        <div class="col">
            <?php fs_form_field('fs_phone') ?>
            <label>Телефон</label>
        </div>
        <div class="col">
            <?php fs_form_field('fs_city') ?>
            <label>Город</label>
        </div>
        <div class="col">
            <?php fs_form_field('fs_delivery_number') ?>
            <label>Номер отделения</label>
        </div>
        <div class="col">
            <?php fs_form_field('fs_comment') ?>
            <label>Сообщение</label>
        </div>
    </div>
</div>
<div class="width">

    <p class="choice">Выберите способ доставки:</p>
    <?php $shipping_methods = get_terms('fs-delivery-methods', array('hide_empty' => false)) ?>
    <?php if ($shipping_methods): ?>
        <?php foreach ($shipping_methods as $key => $shipping): ?>
            <div class="choice">
                <?php fs_form_field('fs_delivery_methods',
                    array(
                        'id' => 'radio' . $shipping->term_id,
                        'class' => 'css-checkbox',
                        'checked' => checked($key, 0, 0),
                        'value' => $shipping->term_id
                    )
                ) ?>
                <label for="radio<?php echo $shipping->term_id ?>"
                       class="css-label"><?php echo $shipping->name ?></label>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<div class="width">
    <p class="choice">Выберите способ оплаты:</p>
    <?php $shipping_methods = get_terms('fs-payment-methods', array('hide_empty' => false)) ?>
    <?php if ($shipping_methods): ?>
        <?php foreach ($shipping_methods as $key => $shipping): ?>
            <div class="choice">
                <?php fs_form_field('fs_payment_methods',
                    array(
                        'id' => 'radio' . $shipping->term_id,
                        'class' => 'css-checkbox',
                        'checked' => checked($key, 0, 0),
                        'value' => $shipping->term_id
                    )
                ) ?>
                <label for="radio<?php echo $shipping->term_id ?>"
                       class="css-label"><?php echo $shipping->name ?></label>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<div class="width text-center">
    <?php fs_order_send('Отправить'); ?>
</div>
