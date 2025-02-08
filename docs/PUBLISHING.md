# Публикация документации на GitHub Pages

## Настройка документации в репозитории плагина

1. Убедитесь, что все файлы документации находятся в директории `docs/` в корне репозитория плагина:

```
wp-content/plugins/f-shop/docs/
├── _config.yml
├── .nojekyll
├── index.md
├── installation.md
├── usage.md
├── configuration.md
├── integration.md
├── integration-single-product.md
├── integration-archive-product.md
├── integration-cart.md
├── integration-wishlist.md
├── integration-account.md
├── developers.md
├── hooks.md
├── template-functions.md
└── api.md
```

2. Создайте файл `_config.yml` в директории `docs/`:

```yaml
remote_theme: pages-themes/cayman@v0.2.0
plugins:
- jekyll-remote-theme
title: F-Shop Documentation
description: Документация WordPress плагина F-Shop
show_downloads: false
baseurl: "/f-shop"  # Измените на имя вашего репозитория
```

3. Создайте файл `.nojekyll` в директории `docs/`:

```bash
touch docs/.nojekyll
```

## Настройка GitHub Pages

1. Перейдите в настройки репозитория на GitHub (Settings)
2. В левом меню найдите раздел "Pages"
3. В разделе "Build and deployment":
   - Source: Deploy from a branch
   - Branch: main (или master)
   - Folder: /docs
4. Нажмите "Save"

GitHub автоматически опубликует документацию по адресу:

```
https://your-username.github.io/f-shop/
```

## Обновление документации

1. Внесите необходимые изменения в файлы документации
2. Закоммитьте и отправьте изменения:

```bash
git add docs/
git commit -m "Update documentation"
git push origin main
```

GitHub Pages автоматически обновит сайт с документацией.

## Кастомизация

### Добавление поиска

1. Добавьте в `docs/_config.yml`:

```yaml
search_enabled: true
```

### Кастомизация темы

1. Создайте файл `docs/assets/css/style.scss`:

```scss
@import "{{ site.theme }}";

// Ваши стили
.main-content {
    max-width: 1200px;
    margin: 0 auto;
}
```

### Добавление навигации

1. Создайте файл `docs/_includes/nav.html`:

```html
<nav class="site-nav">
    <a href="{{ site.baseurl }}/">Главная</a>
    <a href="{{ site.baseurl }}/installation">Установка</a>
    <a href="{{ site.baseurl }}/usage">Использование</a>
    <a href="{{ site.baseurl }}/configuration">Настройка</a>
    <a href="{{ site.baseurl }}/integration">Интеграция</a>
    <a href="{{ site.baseurl }}/developers">Для разработчиков</a>
</nav>
```

## Советы

1. Все ссылки в документации должны быть относительными и учитывать baseurl:

```markdown
[Установка]({{ site.baseurl }}/installation)
```

2. Проверяйте корректность ссылок перед публикацией
3. Используйте изображения из директории `docs/assets/images/`
4. Регулярно обновляйте документацию при внесении изменений в плагин

## Полезные ссылки

- [GitHub Pages Documentation](https://docs.github.com/en/pages)
- [Jekyll Documentation](https://jekyllrb.com/docs/)
- [Markdown Guide](https://www.markdownguide.org/)
- [GitHub Flavored Markdown](https://github.github.com/gfm/)
