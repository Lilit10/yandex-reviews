# Интеграция с Яндекс.Картами — отзывы и рейтинг

Веб-приложение на **Laravel + Vue (Inertia)** для просмотра отзывов и рейтинга организации из карточки Яндекс.Карт.

## Функции

- **Авторизация** — вход по email/паролю и регистрация (Laravel Breeze).
- **Настройки** — страница, на которой можно вставить ссылку на карточку организации в Яндекс.Картах.
- **Отзывы** — вывод всех отзывов из карточки (по возможности из разметки страницы).
- **Рейтинг** — отображение рейтинга компании и общего количества отзывов.

## Требования

- PHP 8.2+
- Composer
- Node.js 18+ (рекомендуется 20+)
- SQLite / MySQL / PostgreSQL

## Установка (локально)

```bash
# Клонирование (или копирование проекта)
git clone <URL_РЕПОЗИТОРИЯ> yandex-reviews
cd yandex-reviews

# Зависимости PHP
composer install

# Переменные окружения
cp .env.example .env
php artisan key:generate

# База данных (по умолчанию SQLite)
touch database/database.sqlite
php artisan migrate

# Зависимости фронтенда (при конфликте версий используйте --legacy-peer-deps)
npm install
# или: npm install --legacy-peer-deps

# Сборка фронтенда
npm run build
```

## Запуск

```bash
# Сервер приложения
php artisan serve

# В другом терминале — сборка фронта в режиме разработки (если нужно)
npm run dev
```

Откройте в браузере: **http://localhost:8000**

- **Регистрация** — создайте учётную запись.
- **Настройки Яндекс** — укажите ссылку на организацию, например:  
  `https://yandex.ru/maps/org/название_организации/1234567890`
- **Отзывы** — просмотр рейтинга и списка отзывов, кнопка «Обновить данные» для повторной загрузки.

## Деплой на VDS / хостинг

1. **Сервер**: PHP 8.2+, расширения для Laravel (openssl, pdo, mbstring, tokenizer, xml, ctype, json, bcmath).
2. **Веб-сервер**: Nginx или Apache с точкой входа `public/`.
3. **База**: создайте БД (MySQL/PostgreSQL) и укажите в `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_HOST`
4. **Команды на сервере**:
   ```bash
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --force
   npm ci && npm run build
   php artisan config:cache
   php artisan route:cache
   ```
5. **Права**: `storage` и `bootstrap/cache` должны быть доступны для записи веб-серверу.
6. **Очереди (по желанию)**: `php artisan queue:work` — если позже добавятся фоновые задачи.

Пример конфигурации Nginx (корень сайта — `public`):

```nginx
root /var/www/yandex-reviews/public;
index index.php;
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## Важно про отзывы Яндекс.Карт

У Яндекса **нет публичного API** для получения отзывов организаций. В проекте реализована загрузка страницы карточки по ссылке и извлечение данных из HTML (JSON-LD и разметка). Поэтому:

- Количество и полнота отзывов зависят от того, что есть на странице.
- При изменении вёрстки Яндекса парсинг может потребовать доработки.
- Рекомендуется не злоупотреблять частыми запросами к страницам Яндекса.

## Ссылки

- **Репозиторий (GitHub)**: укажите здесь URL вашего репозитория после публикации.
- **Демо / хостинг**: укажите URL после деплоя на VDS или хостинг.

## Стек

- Laravel 12
- Vue 3 + Inertia.js
- Laravel Breeze (аутентификация)
- Tailwind CSS
- Vite

## Лицензия

MIT.
