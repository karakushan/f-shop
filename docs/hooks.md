# Хуки и фильтры

F-Shop предоставляет множество хуков действий (actions) и фильтров (filters) для расширения функциональности плагина.

## Хуки действий (Actions)

### Товары

#### `f_shop_before_product_init`

Выполняется перед инициализацией товара.

```php
do_action('f_shop_before_product_init', $product_id);
```

#### `f_shop_after_product_save`

Выполняется после сохранения товара.

```php
do_action('f_shop_after_product_save', $product_id, $product_data);
```

#### `f_shop_before_product_delete`

Выполняется перед удалением товара.

```php
do_action('f_shop_before_product_delete', $product_id);
```

### Заказы

#### `f_shop_before_order_create`

Выполняется перед созданием заказа.

```php
do_action('f_shop_before_order_create', $order_data);
```

#### `f_shop_after_order_status_change`

Выполняется после изменения статуса заказа.

```php
do_action('f_shop_after_order_status_change', $order_id, $new_status, $old_status);
```

### Корзина

#### `f_shop_before_cart_add`

Выполняется перед добавлением товара в корзину.

```php
do_action('f_shop_before_cart_add', $product_id, $quantity);
```

#### `f_shop_after_cart_clear`

Выполняется после очистки корзины.

```php
do_action('f_shop_after_cart_clear');
```

## Фильтры (Filters)

### Товары

#### `f_shop_product_price`

Модификация цены товара.

```php
$price = apply_filters('f_shop_product_price', $price, $product_id);
```

#### `f_shop_product_data`

Модификация данных товара перед сохранением.

```php
$product_data = apply_filters('f_shop_product_data', $product_data, $product_id);
```

### Заказы

#### `f_shop_order_total`

Модификация итоговой суммы заказа.

```php
$total = apply_filters('f_shop_order_total', $total, $order_id);
```

#### `f_shop_order_statuses`

Модификация списка доступных статусов заказа.

```php
$statuses = apply_filters('f_shop_order_statuses', $statuses);
```

### Шаблоны

#### `f_shop_template_path`

Изменение пути к шаблону.

```php
$template_path = apply_filters('f_shop_template_path', $template_path, $template_name);
```

#### `f_shop_product_thumbnail`

Модификация HTML кода миниатюры товара.

```php
$thumbnail_html = apply_filters('f_shop_product_thumbnail', $thumbnail_html, $product_id);
```

## Примеры использования

### Добавление своего статуса заказа

```php
add_filter('f_shop_order_statuses', 'add_custom_order_status');
function add_custom_order_status($statuses) {
    $statuses['custom'] = 'Мой статус';
    return $statuses;
}
```

### Модификация цены товара

```php
add_filter('f_shop_product_price', 'modify_product_price', 10, 2);
function modify_product_price($price, $product_id) {
    // Скидка 10% для определенной категории
    if (has_term('sale', 'product_cat', $product_id)) {
        $price = $price * 0.9;
    }
    return $price;
}
```

### Действие после изменения статуса заказа

```php
add_action('f_shop_after_order_status_change', 'custom_status_change_action', 10, 3);
function custom_status_change_action($order_id, $new_status, $old_status) {
    if ($new_status === 'completed') {
        // Отправить уведомление
        f_shop_send_custom_notification($order_id);
    }
}
