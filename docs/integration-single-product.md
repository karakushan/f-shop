# Подключение страницы товара

В этом разделе описано, как интегрировать страницу отдельного товара в вашу тему WordPress.

## Создание файла шаблона

Создайте файл `single-product.php` в корневой директории вашей темы:

```php
<?php get_header() ?>

<main <?php fs_before_product_atts() ?>>
    <?php get_template_part('part/breadcrumbs') ?>

    <?php while (have_posts()): the_post() ?>
        <?php get_template_part('part/content-product') ?>
    <?php endwhile ?>

    <?php
    // Получение похожих товаров из той же категории
    global $post;
    $post_terms = wp_get_object_terms($post->ID, 'catalog', array('orderby' => 'name'));
    $post_terms_ids = wp_list_pluck($post_terms, 'term_id');
    $related_products = get_posts([
        'post_type' => 'product',
        'post__not_in' => [$post->ID],
        'posts_per_page' => 16,
        'tax_query' => [
            [
                'taxonomy' => 'catalog',
                'field' => 'term_id',
                'terms' => $post_terms_ids
            ]
        ]
    ]);

    // Вывод слайдера с похожими товарами
    get_template_part('part/product-slider', '', [
        'title' => 'Похожие товары',
        'items' => $related_products
    ]);
    ?>

    <?php
    // Вывод просмотренных товаров
    get_template_part('part/product-slider', '', [
        'title' => 'Просмотренные товары',
        'items' => fs_user_viewed()
    ]);
    ?>
</main>

<?php get_footer() ?>
```

## Создание шаблона контента товара

Создайте файл `part/content-product.php`:

```php
<article class="product-single">
    <div class="product-gallery">
        <?php fs_product_gallery() ?>
    </div>

    <div class="product-info">
        <h1><?php the_title() ?></h1>
        
        <div class="product-meta">
            <div class="product-sku">
                Артикул: <?php fs_product_sku() ?>
            </div>
            
            <div class="product-price">
                <?php fs_product_price() ?>
            </div>
            
            <div class="product-stock">
                <?php fs_product_stock_status() ?>
            </div>
        </div>

        <div class="product-description">
            <?php the_content() ?>
        </div>

        <div class="product-actions">
            <?php fs_add_to_cart_button() ?>
            <?php fs_add_to_wishlist_button() ?>
        </div>

        <div class="product-attributes">
            <?php fs_product_attributes() ?>
        </div>
    </div>
</article>
```

## Основные функции

### `fs_before_product_atts()`

Выводит необходимые атрибуты для обертки товара.

### `fs_product_gallery()`

Выводит галерею изображений товара.

### `fs_product_sku()`

Выводит артикул товара.

### `fs_product_price()`

Выводит цену товара.

### `fs_product_stock_status()`

Выводит статус наличия товара.

### `fs_add_to_cart_button()`

Выводит кнопку "Добавить в корзину".

### `fs_add_to_wishlist_button()`

Выводит кнопку "Добавить в избранное".

### `fs_product_attributes()`

Выводит атрибуты товара.

### `fs_user_viewed()`

Возвращает массив просмотренных пользователем товаров.

## Хуки и фильтры

### Действия (Actions)

```php
// Перед выводом товара
do_action('fs_before_single_product');

// После вывода товара
do_action('fs_after_single_product');

// Перед выводом галереи
do_action('fs_before_product_gallery');

// После вывода галереи
do_action('fs_after_product_gallery');
```

### Фильтры (Filters)

```php
// Модификация данных товара
add_filter('fs_product_data', function($data) {
    // Ваши изменения
    return $data;
});

// Модификация цены товара
add_filter('fs_product_price', function($price) {
    // Ваши изменения
    return $price;
});
```

## Пример кастомизации

### Изменение расположения элементов

```php
// Удаляем стандартный вывод цены
remove_action('fs_product_meta', 'fs_product_price', 10);

// Добавляем цену в другое место
add_action('fs_product_title', 'fs_product_price', 20);
```

### Добавление своего контента

```php
add_action('fs_after_product_meta', function() {
    echo '<div class="custom-content">';
    // Ваш контент
    echo '</div>';
});
```

## Стилизация

Для стилизации страницы товара используйте следующие классы:

```css
.product-single { /* Обертка товара */ }
.product-gallery { /* Галерея */ }
.product-info { /* Информация о товаре */ }
.product-meta { /* Метаданные товара */ }
.product-price { /* Цена */ }
.product-actions { /* Кнопки действий */ }
.product-attributes { /* Атрибуты товара */ }
```
