# Подключение корзины

В этом разделе описано, как интегрировать корзину f-shop в вашу тему WordPress.

## Создание шаблона корзины

Создайте файл `template-cart.php` в корневой директории вашей темы:

```php
<?php
/*
 * Template Name: Cart
 */
get_header();
?>

<main>
    <?php get_template_part('part/breadcrumbs') ?>

    <section class="cart-section">
        <div class="container">
            <h1><?php _e('Корзина', 'f-shop') ?></h1>

            <?php if (fs_cart_has_items()): ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Фото', 'f-shop') ?></th>
                                    <th><?php _e('Товар', 'f-shop') ?></th>
                                    <th><?php _e('Артикул', 'f-shop') ?></th>
                                    <th><?php _e('Цена', 'f-shop') ?></th>
                                    <th><?php _e('Количество', 'f-shop') ?></th>
                                    <th><?php _e('Сумма', 'f-shop') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (fs_get_cart_items() as $item): ?>
                                    <?php get_template_part('part/cart-item', '', ['item' => $item]) ?>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="cart-totals">
                        <?php fs_cart_totals() ?>
                    </div>

                    <div class="cart-actions">
                        <a href="<?php fs_catalog_url() ?>" class="button">
                            <?php _e('Продолжить покупки', 'f-shop') ?>
                        </a>
                        <a href="<?php fs_checkout_url() ?>" class="button checkout">
                            <?php _e('Оформить заказ', 'f-shop') ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-empty">
                    <p><?php _e('Ваша корзина пуста', 'f-shop') ?></p>
                    <a href="<?php fs_catalog_url() ?>" class="button">
                        <?php _e('Перейти в каталог', 'f-shop') ?>
                    </a>
                </div>
            <?php endif ?>
        </div>
    </section>
</main>

<?php get_footer() ?>
```

## Создание элемента корзины

Создайте файл `part/cart-item.php`:

```php
<?php
$item = $args['item'];
?>
<tr class="cart-item" data-item-id="<?php echo $item['id'] ?>">
    <td class="item-thumbnail">
        <?php fs_cart_item_thumbnail($item) ?>
    </td>
    <td class="item-name">
        <a href="<?php echo get_permalink($item['product_id']) ?>">
            <?php echo $item['title'] ?>
        </a>
    </td>
    <td class="item-sku">
        <?php echo $item['sku'] ?>
    </td>
    <td class="item-price">
        <?php fs_cart_item_price($item) ?>
    </td>
    <td class="item-quantity">
        <?php fs_cart_item_quantity($item) ?>
    </td>
    <td class="item-subtotal">
        <?php fs_cart_item_subtotal($item) ?>
    </td>
    <td class="item-remove">
        <?php fs_cart_item_remove_button($item) ?>
    </td>
</tr>
```

## Основные функции

### `fs_cart_has_items()`

Проверяет, есть ли товары в корзине.

### `fs_get_cart_items()`

Возвращает массив товаров в корзине.

### `fs_cart_totals()`

Выводит итоговую информацию корзины (сумма, скидки, доставка).

### `fs_catalog_url()`

Возвращает URL каталога товаров.

### `fs_checkout_url()`

Возвращает URL страницы оформления заказа.

### `fs_cart_item_thumbnail()`

Выводит миниатюру товара в корзине.

### `fs_cart_item_price()`

Выводит цену товара в корзине.

### `fs_cart_item_quantity()`

Выводит поле для изменения количества товара.

### `fs_cart_item_subtotal()`

Выводит промежуточную сумму для товара.

### `fs_cart_item_remove_button()`

Выводит кнопку удаления товара из корзины.

## Хуки и фильтры

### Действия (Actions)

```php
// Перед выводом корзины
do_action('fs_before_cart');

// После вывода корзины
do_action('fs_after_cart');

// Перед выводом товара в корзине
do_action('fs_before_cart_item', $item);

// После вывода товара в корзине
do_action('fs_after_cart_item', $item);
```

### Фильтры (Filters)

```php
// Модификация данных товара в корзине
add_filter('fs_cart_item_data', function($item_data, $product_id) {
    // Ваши изменения
    return $item_data;
}, 10, 2);

// Модификация итоговой суммы корзины
add_filter('fs_cart_total', function($total) {
    // Ваши изменения
    return $total;
});
```

## Мини-корзина

### Добавление мини-корзины в шапку сайта

```php
<div class="mini-cart">
    <a href="<?php fs_cart_url() ?>" class="mini-cart-link">
        <span class="mini-cart-count"><?php fs_cart_items_count() ?></span>
        <span class="mini-cart-total"><?php fs_cart_total() ?></span>
    </a>

    <div class="mini-cart-content">
        <?php if (fs_cart_has_items()): ?>
            <div class="mini-cart-items">
                <?php foreach (fs_get_cart_items() as $item): ?>
                    <div class="mini-cart-item">
                        <?php fs_cart_item_thumbnail($item) ?>
                        <div class="item-details">
                            <div class="item-name"><?php echo $item['title'] ?></div>
                            <div class="item-price"><?php fs_cart_item_price($item) ?></div>
                        </div>
                        <?php fs_cart_item_remove_button($item) ?>
                    </div>
                <?php endforeach ?>
            </div>

            <div class="mini-cart-footer">
                <div class="mini-cart-total">
                    <?php _e('Итого:', 'f-shop') ?> <?php fs_cart_total() ?>
                </div>
                <a href="<?php fs_cart_url() ?>" class="button view-cart">
                    <?php _e('Просмотр корзины', 'f-shop') ?>
                </a>
                <a href="<?php fs_checkout_url() ?>" class="button checkout">
                    <?php _e('Оформить заказ', 'f-shop') ?>
                </a>
            </div>
        <?php else: ?>
            <p><?php _e('Корзина пуста', 'f-shop') ?></p>
        <?php endif ?>
    </div>
</div>
```

## Стилизация

Для стилизации корзины используйте следующие классы:

```css
.cart-section { /* Секция корзины */ }
.cart-table { /* Таблица корзины */ }
.cart-item { /* Элемент корзины */ }
.cart-totals { /* Итоги корзины */ }
.mini-cart { /* Мини-корзина */ }
.mini-cart-content { /* Содержимое мини-корзины */ }
```

## AJAX обновление

F-shop автоматически обрабатывает AJAX запросы для корзины. Для кастомизации используйте следующие события:

```javascript
// Обновление корзины
document.addEventListener('fs_cart_updated', function(e) {
    // Ваш код
    console.log('Корзина обновлена', e.detail);
});

// Добавление товара в корзину
document.addEventListener('fs_cart_item_added', function(e) {
    // Ваш код
    console.log('Товар добавлен', e.detail);
});

// Удаление товара из корзины
document.addEventListener('fs_cart_item_removed', function(e) {
    // Ваш код
    console.log('Товар удален', e.detail);
});
```
