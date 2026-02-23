# Деплой на SpaceWeb

## 1. GitHub

Код хранится в репозитории и загружается на хостинг оттуда.

## 2. SpaceWeb: виртуальный хостинг

На виртуальном хостинге SpaceWeb обычно нет прямого доступа к Git. Возможные варианты:

### Вариант A: Загрузка через FTP/SFTP

1. Скачайте архив с GitHub (Code → Download ZIP) или клонируйте локально.
2. Залейте файлы в корень сайта:
   - **Document Root** должен указывать на папку `public` проекта.
   - Либо загрузите содержимое `public` в `public_html` (или `www`), а папки `app`, `storage`, `img` — на уровень выше.
3. Структура на сервере:
   ```
   /home/username/
   ├── app/
   ├── storage/         (должна быть доступна для записи!)
   ├── img/
   ├── public/
   │   ├── index.php
   │   ├── assets/
   │   ├── uploads/
   │   └── files/
   ```
   Document Root = `/home/username/public`

### Вариант B: VPS (если есть)

1. Подключитесь по SSH.
2. `git clone https://github.com/moiseev1991-stack/lenta-nerzhavejushhaja-aisi.git`
3. Укажите Document Root Nginx/Apache на папку `public`.

## 3. Настройка

- **storage/** — права 755 или 775, должна быть доступна для записи (SQLite + миграции).
- **public/uploads/** — права 755/775 (загрузка картинок товаров).
- **База SQLite** — при первом запуске создаётся `storage/database.sqlite`. Можно выполнить миграции вручную.

### Переменные окружения (если хостинг поддерживает)

- `SITE_URL` — полный URL сайта (https://ваш-домен.ru)
- `ADMIN_USER` / `ADMIN_PASS_HASH` — логин и хеш пароля админки
- `BASE_PATH` — оставьте пустым, если Document Root = `public`
- `AMO_FORM_IFRAME_SRC` — URL формы amoCRM (если отличается от стандартного)
