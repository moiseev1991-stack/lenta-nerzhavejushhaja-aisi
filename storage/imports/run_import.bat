@echo off
cd /d "%~dp0..\.."
php storage/imports/import_products.php storage/imports/products.xlsx
pause
