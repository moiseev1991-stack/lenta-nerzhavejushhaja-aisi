# Проверка наличия PHP
$phpPath = Get-Command php -ErrorAction SilentlyContinue

# Если не найден в PATH, проверяем стандартные пути
if (-not $phpPath) {
    $possiblePaths = @(
        "C:\php\php.exe",
        "C:\xampp\php\php.exe",
        "C:\OpenServer\modules\php\*\php.exe",
        "$env:ProgramFiles\PHP\php.exe",
        "$env:ProgramFiles(x86)\PHP\php.exe"
    )
    
    $foundPath = $null
    foreach ($path in $possiblePaths) {
        if (Test-Path $path -ErrorAction SilentlyContinue) {
            $foundPath = (Get-Item $path).FullName
            break
        }
    }
    
    if ($foundPath) {
        Write-Host "PHP найден по пути: $foundPath" -ForegroundColor Yellow
        Write-Host "Используем его для запуска..." -ForegroundColor Yellow
        $phpPath = $foundPath
    } else {
        Write-Host "ОШИБКА: PHP не найден!" -ForegroundColor Red
        Write-Host ""
        Write-Host "=== УСТАНОВКА PHP ===" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Способ 1 (Рекомендуется): Установка вручную" -ForegroundColor Cyan
        Write-Host "1. Откройте: https://windows.php.net/download/"
        Write-Host "2. Скачайте PHP 8.x (Thread Safe, ZIP архив)"
        Write-Host "3. Распакуйте в C:\php"
        Write-Host "4. Добавьте C:\php в PATH (Win+R -> sysdm.cpl -> Переменные среды -> Path)"
        Write-Host "5. Перезапустите PowerShell"
        Write-Host ""
        Write-Host "Способ 2: Через Chocolatey (если установлен)" -ForegroundColor Cyan
        Write-Host "choco install php"
        Write-Host ""
        Write-Host "Способ 3: Готовый пакет" -ForegroundColor Cyan
        Write-Host "XAMPP: https://www.apachefriends.org/"
        Write-Host "OpenServer: https://ospanel.io/"
        Write-Host ""
        Read-Host "Нажмите Enter для выхода"
        exit 1
    }
}

# Определяем команду PHP
if ($phpPath -is [System.Management.Automation.ApplicationInfo]) {
    $phpCmd = "php"
} else {
    $phpCmd = $phpPath
}

Write-Host "PHP найден!" -ForegroundColor Green
Write-Host ""

Write-Host "Создание базы данных..." -ForegroundColor Cyan
& $phpCmd storage/seed.php
if ($LASTEXITCODE -ne 0) {
    Write-Host "ОШИБКА при создании базы данных!" -ForegroundColor Red
    Read-Host "Нажмите Enter для выхода"
    exit 1
}

Write-Host ""
Write-Host "Запуск сервера на http://localhost:8000" -ForegroundColor Green
Write-Host "Для остановки нажмите Ctrl+C" -ForegroundColor Yellow
Write-Host ""
& $phpCmd -S localhost:8000 -t public public/router.php
