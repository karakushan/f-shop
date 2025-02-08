# Подключение архива товаров

В этом разделе описано, как интегрировать страницу архива товаров в вашу тему WordPress.

## Создание файла шаблона

Создайте файл `archive-product.php` в корневой директории вашей темы:

```php
<?php get_header() ?>

<main>
    <?php get_template_part('part/breadcrumbs') ?>

    <section class="products-archive">
        <div class="container">
            <header class="archive-header">
                <h1><?php fs_archive_title() ?></h1>
                <div class="archive-description">
                    <?php fs_archive_description() ?>
                </div>
            </header>

            <div class="archive-content">
                <div class="sidebar">
                    <?php get_template_part('part/catalog-filters') ?>
                </div>

                <div class="products-grid">
                    <?php if (have_posts()): ?>
                        <div class="products-toolbar">
                            <?php
                            fs_products_ordering();
                            fs_products_per_page();
                            fs_products_view_switcher();
                            ?>
                        </div>

                        <div class="products-list">
                            <?php while (have_posts()): the_post() ?>
                                <?php get_template_part('part/product-card') ?>
                            <?php endwhile ?>
                        </div>

                        <?php fs_pagination() ?>
                    <?php else: ?>
                        <p><?php _e('Товары не найдены', 'f-shop') ?></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer() ?>
```

## Создание карточки товара

Создайте файл `part/product-card.php`:

```php
<article class="product-card">
    <div class="product-thumbnail">
        <a href="<?php the_permalink() ?>">
            <?php fs_product_thumbnail() ?>
        </a>
        <?php fs_product_labels() ?>
    </div>

    <div class="product-content">
        <h2 class="product-title">
            <a href="<?php the_permalink() ?>"><?php the_title() ?></a>
        </h2>

        <div class="product-meta">
            <div class="product-sku">
                <?php fs_product_sku() ?>
            </div>
            
            <div class="product-price">
                <?php fs_product_price() ?>
            </div>
            
            <div class="product-stock">
                <?php fs_product_stock_status() ?>
            </div>
        </div>

        <div class="product-actions">
            <?php fs_add_to_cart_button() ?>
            <?php fs_add_to_wishlist_button() ?>
        </div>
    </div>
</article>
```

## Создание фильтров каталога

Создайте файл `part/catalog-filters.php`:

```php
<div class="catalog-filters">
    <form action="<?php fs_catalog_form_action() ?>" method="get">
        <?php
        // Фильтр по категориям
        fs_categories_filter();

        // Фильтр по цене
        fs_price_filter();

        // Фильтр по атрибутам
        fs_attributes_filter();

        // Фильтр по наличию
        fs_stock_filter();
        ?>

        <button type="submit"><?php _e('Применить', 'f-shop') ?></button>
        <button type="reset"><?php _e('Сбросить', 'f-shop') ?></button>
    </form>
</div>
```

## Основные функции

### `fs_archive_title()`

Выводит заголовок архива товаров.

### `fs_archive_description()`

Выводит описание архива товаров.

### `fs_products_ordering()`

Выводит сортировку товаров.

### `fs_products_per_page()`

Выводит выбор количества товаров на странице.

### `fs_products_view_switcher()`

Выводит переключатель вида отображения товаров.

### `fs_pagination()`

Выводит пагинацию.

### `fs_product_thumbnail()`

Выводит миниатюру товара.

### `fs_product_labels()`

Выводит метки товара (скидка, новинка и т.д.).

### `fs_catalog_form_action()`

Возвращает URL для формы фильтров.

## Хуки и фильтры

### Действия (Actions)

```php
// Перед выводом списка товаров
do_action('fs_before_products_loop');

// После вывода списка товаров
do_action('fs_after_products_loop');

// Перед выводом фильтров
do_action('fs_before_catalog_filters');

// После вывода фильтров
do_action('fs_after_catalog_filters');
```

### Фильтры (Filters)

```php
// Модификация параметров запроса товаров
add_filter('fs_products_query_args', function($args) {
    // Ваши изменения
    return $args;
});

// Модификация количества товаров на странице
add_filter('fs_products_per_page', function($per_page) {
    return 24; // Ваше значение
});
```

## Пример кастомизации

### Изменение количества колонок

```php
add_filter('fs_products_columns', function($columns) {
    return 4; // Ваше количество колонок
});
```

### Добавление своего фильтра

```php
add_action('fs_before_catalog_filters', function() {
    ?>
    <div class="custom-filter">
        <h4><?php _e('Мой фильтр', 'your-theme') ?></h4>
        <!-- Ваш код фильтра -->
    </div>
    <?php
});
```

## Стилизация

Для стилизации архива товаров используйте следующие классы:

```css
.products-archive { /* Обертка архива */ }
.archive-header { /* Шапка архива */ }
.products-toolbar { /* Панель инструментов */ }
.products-grid { /* Сетка товаров */ }
.product-card { /* Карточка товара */ }
.catalog-filters { /* Фильтры каталога */ }
```
