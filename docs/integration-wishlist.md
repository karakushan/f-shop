# Подключение списка желаний

В этом разделе описано, как интегрировать список желаний f-shop в вашу тему WordPress.

## Создание шаблона списка желаний

Создайте файл `template-wishlist.php` в корневой директории вашей темы:

```php
<?php
/*
 * Template Name: Wishlist
 */
get_header();
?>

<main>
    <?php get_template_part('part/breadcrumbs') ?>

    <section class="wishlist-section">
        <div class="container">
            <h1><?php _e('Список желаний', 'f-shop') ?></h1>

            <?php echo do_shortcode('[fs_wishlist wrapper_class="products-grid" template="wishlist/wishlist-product"]') ?>
        </div>
    </section>
</main>

<?php get_footer() ?>
```

## Создание шаблона товара в списке желаний

Создайте файл `wishlist/wishlist-product.php` в директории вашей темы:

```php
<article class="wishlist-item">
    <div class="product-thumbnail">
        <a href="<?php the_permalink() ?>">
            <?php fs_product_thumbnail() ?>
        </a>
    </div>

    <div class="product-content">
        <h2 class="product-title">
            <a href="<?php the_permalink() ?>"><?php the_title() ?></a>
        </h2>

        <div class="product-meta">
            <div class="product-price">
                <?php fs_product_price() ?>
            </div>
            
            <div class="product-stock">
                <?php fs_product_stock_status() ?>
            </div>
        </div>

        <div class="product-actions">
            <?php fs_add_to_cart_button() ?>
            <?php fs_remove_from_wishlist_button() ?>
        </div>
    </div>
</article>
```

## Доступные шорткоды

### Вывод списка желаний

```php
[fs_wishlist]
```

Параметры:

- `wrapper_class` - класс обертки списка (по умолчанию: 'fs-wislist-poducts row')
- `empty_text` - текст при пустом списке (по умолчанию: 'Список желаний пуст')
- `template` - путь к шаблону товара (по умолчанию: 'wishlist/wishlist-product')

## Основные функции

### `fs_add_to_wishlist_button()`

Выводит кнопку "Добавить в список желаний".

### `fs_remove_from_wishlist_button()`

Выводит кнопку "Удалить из списка желаний".

### `fs_get_wishlist()`

Возвращает массив товаров в списке желаний.

### `fs_is_in_wishlist($product_id)`

Проверяет, находится ли товар в списке желаний.

### `fs_wishlist_count()`

Возвращает количество товаров в списке желаний.

## Хуки и фильтры

### Действия (Actions)

```php
// Перед добавлением в список желаний
do_action('fs_before_add_to_wishlist', $product_id);

// После добавления в список желаний
do_action('fs_after_add_to_wishlist', $product_id);

// Перед удалением из списка желаний
do_action('fs_before_remove_from_wishlist', $product_id);

// После удаления из списка желаний
do_action('fs_after_remove_from_wishlist', $product_id);
```

### Фильтры (Filters)

```php
// Модификация данных товара в списке желаний
add_filter('fs_wishlist_item_data', function($item_data, $product_id) {
    // Ваши изменения
    return $item_data;
}, 10, 2);
```

## Пример кастомизации

### Добавление счетчика товаров в меню

```php
function add_wishlist_count_to_menu($items, $args) {
    if ($args->theme_location == 'primary') {
        $count = fs_wishlist_count();
        $items .= '<li class="menu-item wishlist-count">';
        $items .= '<a href="' . fs_get_wishlist_url() . '">';
        $items .= __('Список желаний', 'f-shop') . ' (' . $count . ')';
        $items .= '</a></li>';
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'add_wishlist_count_to_menu', 10, 2);
```

### Изменение текста кнопок

```php
add_filter('fs_add_to_wishlist_text', function($text) {
    return __('В избранное', 'your-theme');
});

add_filter('fs_remove_from_wishlist_text', function($text) {
    return __('Удалить из избранного', 'your-theme');
});
```

## AJAX обновление

F-shop автоматически обрабатывает AJAX запросы для списка желаний. Для кастомизации используйте следующие события:

```javascript
// Добавление товара в список желаний
document.addEventListener('fs_wishlist_item_added', function(e) {
    console.log('Товар добавлен в список желаний', e.detail);
});

// Удаление товара из списка желаний
document.addEventListener('fs_wishlist_item_removed', function(e) {
    console.log('Товар удален из списка желаний', e.detail);
});
```

## Стилизация

Для стилизации списка желаний используйте следующие классы:

```css
.wishlist-section { /* Секция списка желаний */ }
.wishlist-item { /* Элемент списка желаний */ }
.wishlist-empty { /* Пустой список желаний */ }
.fs-add-to-wishlist { /* Кнопка добавления в список желаний */ }
.fs-remove-from-wishlist { /* Кнопка удаления из списка желаний */ }
```
