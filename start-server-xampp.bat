@echo off
echo Использование PHP из XAMPP...
echo.

set PHP_PATH=C:\xampp\php\php.exe

if not exist "%PHP_PATH%" (
    echo ОШИБКА: PHP не найден по пути %PHP_PATH%
    echo Проверьте, что XAMPP установлен в C:\xampp
    echo.
    pause
    exit /b 1
)

echo PHP найден: %PHP_PATH%
echo.

echo Создание базы данных...
"%PHP_PATH%" storage/seed.php
if %errorlevel% neq 0 (
    echo ОШИБКА при создании базы данных!
    pause
    exit /b 1
)

echo.
echo Запуск сервера на http://localhost:8000
echo Для остановки нажмите Ctrl+C
echo.
"%PHP_PATH%" -S localhost:8000 -t public public/router.php
