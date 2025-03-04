<?php

/**
 * Файл для исправления проблем с локализацией в AJAX-запросах
 */

// Функция для установки правильной локали
function fs_ensure_correct_locale_for_ajax()
{
    // Проверяем, является ли запрос AJAX-запросом
    if (defined('DOING_AJAX') && DOING_AJAX) {
        $current_locale = get_locale();
        if ($current_locale) {
            // Принудительно переключаем на текущую локаль
            switch_to_locale($current_locale);
            // Перезагружаем текстовый домен плагина
            $path = dirname(plugin_basename(FS_PLUGIN_FILE));
            load_plugin_textdomain('f-shop', false, $path . '/languages');
        }
    }
}

// Добавляем действие на ранний хук, до обработки AJAX
add_action('plugins_loaded', 'fs_ensure_correct_locale_for_ajax', 5);

// Также добавляем на init для случаев, когда plugins_loaded может быть пропущен
add_action('init', 'fs_ensure_correct_locale_for_ajax', 1);

// Хук для ajax-запросов в админке
add_action('admin_init', 'fs_ensure_correct_locale_for_ajax', 1);
