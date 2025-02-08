# Функции шаблона

В этом разделе описаны функции, которые можно использовать в шаблонах темы для отображения данных f-shop.

## Функции товара

### `f_shop_get_product($product_id)`

Получает объект товара по ID.

```php
$product = f_shop_get_product(123);
echo $product->get_title();
echo $product->get_price();
```

### `f_shop_the_price($product_id)`

Выводит отформатированную цену товара.

```php
<div class="price">
    <?php f_shop_the_price($product_id); ?>
</div>
```

### `f_shop_the_product_gallery($product_id)`

Выводит галерею изображений товара.

```php
<div class="gallery">
    <?php f_shop_the_product_gallery($product_id); ?>
</div>
```

## Функции категорий

### `f_shop_get_product_categories($args = [])`

Получает список категорий товаров.

```php
$categories = f_shop_get_product_categories([
    'parent' => 0,
    'orderby' => 'name'
]);
```

### `f_shop_the_category_list($product_id)`

Выводит список категорий товара.

```php
<div class="categories">
    <?php f_shop_the_category_list($product_id); ?>
</div>
```

## Функции корзины

### `f_shop_cart_total()`

Выводит общую сумму корзины.

```php
<div class="cart-total">
    Итого: <?php f_shop_cart_total(); ?>
</div>
```

### `f_shop_cart_count()`

Возвращает количество товаров в корзине.

```php
<div class="cart-count">
    В корзине: <?php echo f_shop_cart_count(); ?> товаров
</div>
```

### `f_shop_add_to_cart_button($product_id)`

Выводит кнопку "Добавить в корзину".

```php
<div class="add-to-cart">
    <?php f_shop_add_to_cart_button($product_id); ?>
</div>
```

## Функции заказа

### `f_shop_get_order($order_id)`

Получает объект заказа по ID.

```php
$order = f_shop_get_order(456);
echo $order->get_total();
echo $order->get_status();
```

### `f_shop_order_status_label($status)`

Возвращает название статуса заказа.

```php
<div class="order-status">
    Статус: <?php echo f_shop_order_status_label($order->get_status()); ?>
</div>
```

## Функции пользователя

### `f_shop_get_customer_orders($user_id)`

Получает список заказов пользователя.

```php
$orders = f_shop_get_customer_orders(get_current_user_id());
foreach ($orders as $order) {
    echo $order->get_id();
    echo $order->get_total();
}
```

### `f_shop_get_customer_total_spent($user_id)`

Возвращает общую сумму покупок пользователя.

```php
<div class="total-spent">
    Всего покупок: <?php echo f_shop_get_customer_total_spent(get_current_user_id()); ?>
</div>
```

## Функции вывода форм

### `f_shop_checkout_form()`

Выводит форму оформления заказа.

```php
<div class="checkout">
    <?php f_shop_checkout_form(); ?>
</div>
```

### `f_shop_login_form()`

Выводит форму входа.

```php
<div class="login">
    <?php f_shop_login_form(); ?>
</div>
```

## Вспомогательные функции

### `f_shop_format_price($price)`

Форматирует цену согласно настройкам магазина.

```php
echo f_shop_format_price(99.99);
```

### `f_shop_get_currency_symbol()`

Возвращает символ валюты магазина.

```php
echo f_shop_get_currency_symbol();
```

## Примеры использования

### Вывод списка товаров категории

```php
<?php
$products = f_shop_get_products([
    'category' => 'electronics',
    'limit' => 10
]);

foreach ($products as $product) : ?>
    <div class="product">
        <h2><?php echo $product->get_title(); ?></h2>
        <div class="price"><?php f_shop_the_price($product->get_id()); ?></div>
        <?php f_shop_add_to_cart_button($product->get_id()); ?>
    </div>
<?php endforeach; ?>
```

### Вывод мини-корзины

```php
<div class="mini-cart">
    <div class="cart-count">
        <?php echo f_shop_cart_count(); ?> товаров
    </div>
    <div class="cart-total">
        Итого: <?php f_shop_cart_total(); ?>
    </div>
    <?php
    $cart_items = f_shop_get_cart_items();
    foreach ($cart_items as $item) : ?>
        <div class="cart-item">
            <?php echo $item['title']; ?> x <?php echo $item['quantity']; ?>
        </div>
    <?php endforeach; ?>
</div>
```
