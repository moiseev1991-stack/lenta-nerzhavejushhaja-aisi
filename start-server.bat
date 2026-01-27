@echo off
echo Проверка PHP...
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo ОШИБКА: PHP не найден в PATH!
    echo.
    echo Установите PHP одним из способов:
    echo 1. Через winget: winget install PHP.PHP
    echo 2. Вручную: https://windows.php.net/download/
    echo.
    pause
    exit /b 1
)

echo PHP найден!
echo.

echo Создание базы данных...
php storage/seed.php
if %errorlevel% neq 0 (
    echo ОШИБКА при создании базы данных!
    pause
    exit /b 1
)

echo.
echo Запуск сервера на http://localhost:8000
echo Для остановки нажмите Ctrl+C
echo.
php -S localhost:8000 -t public public/router.php
