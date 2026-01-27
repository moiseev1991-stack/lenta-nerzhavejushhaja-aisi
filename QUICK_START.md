# Быстрый запуск сервера

## Шаг 1: Проверьте наличие PHP

Откройте PowerShell и выполните:

```powershell
php -v
```

### ✅ Если PHP установлен:
Вы увидите что-то вроде:
```
PHP 8.3.x (cli) ...
```

**Переходите к Шагу 2**

### ❌ Если PHP НЕ установлен:
Вы увидите ошибку:
```
php : Имя "php" не распознано...
```

**Что нужно установить:**
1. **PHP 8.0 или выше** — обязательно!
   - Скачать: https://windows.php.net/download/
   - Выбрать: PHP 8.x, Thread Safe, ZIP
   - Распаковать в `C:\php`
   - Добавить `C:\php` в PATH (см. INSTALL_PHP.md)

2. **SQLite расширения** (обычно включены в PHP)
   - После установки PHP проверьте `php.ini`
   - Убедитесь, что раскомментированы:
     - `extension=sqlite3`
     - `extension=pdo_sqlite`

---

## Шаг 2: Запустите сервер

### Вариант A: Автоматический запуск (рекомендуется)

```powershell
cd C:\cod\lenta-nerzhavejushhaja-aisi
.\start-server.ps1
```

Скрипт автоматически:
- Проверит наличие PHP
- Создаст базу данных
- Запустит сервер на http://localhost:8000

### Вариант B: Ручной запуск

```powershell
cd C:\cod\lenta-nerzhavejushhaja-aisi

# 1. Создать базу данных
php storage/seed.php

# 2. Запустить сервер
php -S localhost:8000 -t public public/router.php
```

---

## Шаг 3: Откройте в браузере

После успешного запуска откройте:

- **Главная:** http://localhost:8000/
- **Админка:** http://localhost:8000/admin/login
  - Логин: `admin`
  - Пароль: `admin123`

---

## Возможные ошибки и решения

### Ошибка: "PHP не найден"
**Решение:** Установите PHP (см. INSTALL_PHP.md)

### Ошибка: "sqlite3 extension not found"
**Решение:**
1. Откройте `C:\php\php.ini`
2. Найдите и уберите `;` перед:
   ```
   extension=sqlite3
   extension=pdo_sqlite
   ```
3. Перезапустите PowerShell

### Ошибка: "Port 8000 is already in use"
**Решение:** Используйте другой порт:
```powershell
php -S localhost:8001 -t public public/router.php
```

### Ошибка при создании базы данных
**Решение:**
- Убедитесь, что папка `storage/` существует и доступна для записи
- Проверьте права доступа к папке

---

## Остановка сервера

Нажмите `Ctrl + C` в окне PowerShell, где запущен сервер.
