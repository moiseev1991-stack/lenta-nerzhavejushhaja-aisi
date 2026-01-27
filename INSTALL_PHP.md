# Инструкция по установке PHP для Windows

## Способ 1: Установка вручную (Рекомендуется)

### Шаг 1: Скачать PHP
1. Откройте в браузере: **https://windows.php.net/download/**
2. Найдите последнюю версию **PHP 8.x** (например, 8.3.x)
3. Выберите **Thread Safe** версию
4. Скачайте **ZIP архив** (не installer!)

### Шаг 2: Распаковать PHP
1. Создайте папку `C:\php` (если её нет)
2. Распакуйте содержимое ZIP архива в `C:\php`
3. Должна получиться структура: `C:\php\php.exe`, `C:\php\php.ini`, и т.д.

### Шаг 3: Добавить PHP в PATH
1. Нажмите **Win + R**
2. Введите `sysdm.cpl` и нажмите Enter
3. Перейдите на вкладку **"Дополнительно"**
4. Нажмите **"Переменные среды"**
5. В разделе **"Системные переменные"** найдите переменную `Path`
6. Нажмите **"Изменить"**
7. Нажмите **"Создать"**
8. Введите: `C:\php`
9. Нажмите **OK** во всех окнах

### Шаг 4: Проверить установку
1. **Закройте** все окна PowerShell
2. Откройте **новое** окно PowerShell
3. Выполните:
   ```powershell
   php -v
   ```
4. Должна отобразиться версия PHP (например, `PHP 8.3.x`)

### Шаг 5: Настроить PHP (опционально)
1. В папке `C:\php` найдите файл `php.ini-development`
2. Скопируйте его и переименуйте в `php.ini`
3. Откройте `php.ini` в текстовом редакторе
4. Найдите строку `;extension=sqlite3` и уберите точку с запятой в начале:
   ```
   extension=sqlite3
   ```
5. Найдите строку `;extension=pdo_sqlite` и уберите точку с запятой:
   ```
   extension=pdo_sqlite
   ```
6. Сохраните файл

---

## Способ 2: Через Chocolatey

Если у вас установлен Chocolatey:

```powershell
choco install php
```

После установки перезапустите PowerShell.

---

## Способ 3: Готовые пакеты

### XAMPP
- Сайт: https://www.apachefriends.org/
- После установки PHP будет в: `C:\xampp\php\php.exe`
- Добавьте `C:\xampp\php` в PATH

### OpenServer
- Сайт: https://ospanel.io/
- После установки используйте встроенный PHP из панели

---

## После установки

Запустите сервер:

```powershell
cd C:\cod\lenta-nerzhavejushhaja-aisi
.\start-server.ps1
```

Или вручную:

```powershell
php storage/seed.php
php -S localhost:8000 -t public public/router.php
```

---

## Проблемы?

### PHP не найден после установки
- Убедитесь, что добавили `C:\php` в PATH
- **Перезапустите PowerShell** (важно!)
- Проверьте: `php -v`

### Ошибка "sqlite3 extension not found"
- Откройте `C:\php\php.ini`
- Найдите и раскомментируйте (уберите `;`):
  - `extension=sqlite3`
  - `extension=pdo_sqlite`
- Перезапустите PowerShell
