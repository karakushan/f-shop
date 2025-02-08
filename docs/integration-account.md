# Подключение личного кабинета

В этом разделе описано, как интегрировать личный кабинет f-shop в вашу тему WordPress.

## Доступные шорткоды

### Личный кабинет

```php
[fs_user_cabinet]
```

Выводит полный интерфейс личного кабинета пользователя.

### Форма входа

```php
[fs_login]
```

Выводит форму входа для пользователей.

### Форма регистрации

```php
[fs_register]
```

Выводит форму регистрации новых пользователей.

### Форма восстановления пароля

```php
[fs_lostpassword]
```

Выводит форму восстановления пароля.

### Информация о пользователе

```php
[fs_user_info]
```

Выводит информацию о текущем пользователе.

### Редактирование профиля

```php
[fs_profile_edit]
```

Выводит форму редактирования профиля.

### Список заказов пользователя

```php
[fs_user_orders]
```

Выводит список заказов текущего пользователя.

## Создание страниц личного кабинета

### Страница входа/регистрации

Создайте файл `template-login.php`:

```php
<?php
/*
 * Template Name: Login/Register
 */
get_header();
?>

<main>
    <?php get_template_part('part/breadcrumbs') ?>

    <section class="account-section">
        <div class="container">
            <?php if (!is_user_logged_in()): ?>
                <div class="account-forms">
                    <div class="login-form">
                        <h2><?php _e('Вход', 'f-shop') ?></h2>
                        <?php echo do_shortcode('[fs_login]') ?>
                    </div>

                    <div class="register-form">
                        <h2><?php _e('Регистрация', 'f-shop') ?></h2>
                        <?php echo do_shortcode('[fs_register]') ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="account-logged">
                    <p><?php _e('Вы уже вошли в систему.', 'f-shop') ?></p>
                    <a href="<?php echo fs_get_account_url() ?>" class="button">
                        <?php _e('Перейти в личный кабинет', 'f-shop') ?>
                    </a>
                </div>
            <?php endif ?>
        </div>
    </section>
</main>

<?php get_footer() ?>
```

### Страница личного кабинета

Создайте файл `template-account.php`:

```php
<?php
/*
 * Template Name: Account
 */
get_header();
?>

<main>
    <?php get_template_part('part/breadcrumbs') ?>

    <section class="account-section">
        <div class="container">
            <?php if (is_user_logged_in()): ?>
                <?php echo do_shortcode('[fs_user_cabinet]') ?>
            <?php else: ?>
                <div class="account-login-required">
                    <p><?php _e('Для доступа к личному кабинету необходимо войти.', 'f-shop') ?></p>
                    <a href="<?php echo fs_get_login_url() ?>" class="button">
                        <?php _e('Войти', 'f-shop') ?>
                    </a>
                </div>
            <?php endif ?>
        </div>
    </section>
</main>

<?php get_footer() ?>
```

## Основные функции

### `fs_get_account_url()`

Возвращает URL личного кабинета.

### `fs_get_login_url()`

Возвращает URL страницы входа.

### `fs_get_register_url()`

Возвращает URL страницы регистрации.

### `fs_get_logout_url()`

Возвращает URL для выхода из системы.

### `fs_get_user_orders()`

Возвращает массив заказов текущего пользователя.

### `fs_get_user_info()`

Возвращает информацию о текущем пользователе.

## Хуки и фильтры

### Действия (Actions)

```php
// После входа пользователя
do_action('fs_after_user_login', $user_id);

// После регистрации пользователя
do_action('fs_after_user_register', $user_id);

// После обновления профиля
do_action('fs_after_profile_update', $user_id);

// После выхода пользователя
do_action('fs_after_user_logout');
```

### Фильтры (Filters)

```php
// Модификация полей регистрации
add_filter('fs_register_fields', function($fields) {
    // Добавление своего поля
    $fields['phone'] = [
        'type' => 'text',
        'label' => __('Телефон', 'your-theme'),
        'required' => true
    ];
    return $fields;
});

// Модификация полей профиля
add_filter('fs_profile_fields', function($fields) {
    return $fields;
});
```

## Пример кастомизации

### Добавление своей вкладки в личный кабинет

```php
add_filter('fs_account_tabs', function($tabs) {
    $tabs['custom'] = [
        'title' => __('Моя вкладка', 'your-theme'),
        'callback' => 'your_custom_tab_content'
    ];
    return $tabs;
});

function your_custom_tab_content() {
    // Ваш контент
    echo '<div class="custom-tab-content">';
    echo 'Содержимое вкладки';
    echo '</div>';
}
```

### Изменение редиректа после входа

```php
add_filter('fs_login_redirect', function($redirect_url, $user) {
    // Ваша логика редиректа
    return $redirect_url;
}, 10, 2);
```

## Стилизация

Для стилизации личного кабинета используйте следующие классы:

```css
.account-section { /* Секция личного кабинета */ }
.account-forms { /* Формы входа/регистрации */ }
.account-navigation { /* Навигация в личном кабинете */ }
.account-content { /* Контент личного кабинета */ }
.account-orders { /* Список заказов */ }
.account-profile { /* Профиль пользователя */ }
```

## AJAX обновление

F-shop автоматически обрабатывает AJAX запросы в личном кабинете. Для кастомизации используйте следующие события:

```javascript
// Успешный вход
document.addEventListener('fs_login_success', function(e) {
    console.log('Пользователь вошел', e.detail);
});

// Успешная регистрация
document.addEventListener('fs_register_success', function(e) {
    console.log('Пользователь зарегистрирован', e.detail);
});

// Обновление профиля
document.addEventListener('fs_profile_updated', function(e) {
    console.log('Профиль обновлен', e.detail);
});
```
