# API плагина

F-Shop предоставляет REST API для интеграции с внешними системами и приложениями.

## Аутентификация

API использует стандартную аутентификацию WordPress REST API. Вы можете использовать:

- Basic Auth
- OAuth 1.0a
- JWT Authentication

### Получение токена

```bash
curl -X POST https://your-site.com/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{"username": "your_username", "password": "your_password"}'
```

## Endpoints

### Товары

#### Получение списка товаров

```http
GET /wp-json/f-shop/v1/products
```

Параметры:

- `page` - номер страницы
- `per_page` - количество товаров на странице
- `category` - ID категории
- `search` - поисковый запрос

Пример ответа:

```json
{
    "products": [
        {
            "id": 123,
            "title": "Название товара",
            "price": "99.99",
            "description": "Описание товара",
            "images": [...],
            "categories": [...]
        }
    ],
    "total": 100,
    "pages": 10
}
```

#### Получение информации о товаре

```http
GET /wp-json/f-shop/v1/products/{id}
```

### Заказы

#### Создание заказа

```http
POST /wp-json/f-shop/v1/orders
```

Тело запроса:

```json
{
    "customer": {
        "first_name": "Иван",
        "last_name": "Иванов",
        "email": "ivan@example.com"
    },
    "items": [
        {
            "product_id": 123,
            "quantity": 2
        }
    ],
    "shipping_method": "flat_rate",
    "payment_method": "bacs"
}
```

#### Получение информации о заказе

```http
GET /wp-json/f-shop/v1/orders/{id}
```

#### Обновление статуса заказа

```http
PUT /wp-json/f-shop/v1/orders/{id}
```

Тело запроса:

```json
{
    "status": "completed"
}
```

### Корзина

#### Получение корзины

```http
GET /wp-json/f-shop/v1/cart
```

#### Добавление товара в корзину

```http
POST /wp-json/f-shop/v1/cart/add
```

Тело запроса:

```json
{
    "product_id": 123,
    "quantity": 1
}
```

#### Очистка корзины

```http
DELETE /wp-json/f-shop/v1/cart
```

## Примеры использования

### PHP

```php
// Получение списка товаров
$response = wp_remote_get('https://your-site.com/wp-json/f-shop/v1/products', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token
    ]
]);
$products = json_decode(wp_remote_retrieve_body($response));

// Создание заказа
$response = wp_remote_post('https://your-site.com/wp-json/f-shop/v1/orders', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json'
    ],
    'body' => json_encode([
        'customer' => [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'email' => 'ivan@example.com'
        ],
        'items' => [
            [
                'product_id' => 123,
                'quantity' => 2
            ]
        ]
    ])
]);
```

### JavaScript

```javascript
// Получение списка товаров
fetch('https://your-site.com/wp-json/f-shop/v1/products', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
})
.then(response => response.json())
.then(data => console.log(data));

// Добавление товара в корзину
fetch('https://your-site.com/wp-json/f-shop/v1/cart/add', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        product_id: 123,
        quantity: 1
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

## Обработка ошибок

API возвращает стандартные HTTP коды состояния:

- 200 - успешный запрос
- 400 - неверный запрос
- 401 - не авторизован
- 404 - ресурс не найден
- 500 - внутренняя ошибка сервера

Пример ответа с ошибкой:

```json
{
    "code": "error_code",
    "message": "Описание ошибки",
    "data": {
        "status": 400
    }
}
```
