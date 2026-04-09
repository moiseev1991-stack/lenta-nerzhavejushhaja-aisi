# Файлы для скачивания

PDF **«КП по контрактным поставкам»** (имя в `app/config.php` → `catalog_pdf`, по умолчанию `kp-po-kontraktnym-postavkam.pdf`):

1. **`public/files/kp-po-kontraktnym-postavkam.pdf`** — предпочтительно для продакшена  
2. **корень репозитория** (`…/lenta-nerzhavejushhaja-aisi/kp-po-kontraktnym-postavkam.pdf`) — тоже подхватывается; URL всё равно `/files/kp-po-kontraktnym-postavkam.pdf`

Файл без расширения `.pdf` в конфиге по-прежнему отдаётся как `application/pdf`, если расширение в имени опущено намеренно.

Имя файла можно изменить в `app/config.php` → ключ `catalog_pdf`.
