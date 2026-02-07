# Деплой на Space Web через GitHub Actions

После настройки обновление сайта: правки в Cursor → **git push** → через 1–2 минуты сайт обновлён. FileZilla не нужен.

---

## Один раз: добавить секрет в GitHub

1. Открой репозиторий на **github.com**.
2. **Settings** → слева **Secrets and variables** → **Actions**.
3. **New repository secret**:
   - **Name:** `SFTP_PASSWORD`
   - **Value:** пароль от аккаунта Space Web (тот же, что для SSH/FTP, логин `infogkmeta`).
4. Сохранить.

Папка на сервере должна уже существовать: `/home/infogkmeta/lenta-nerzhavejushhaja-aisi` (если раньше заходил по SFTP — она там есть).

---

## Как деплоить

1. В Cursor правишь код.
2. В терминале:
   ```bash
   git add .
   git commit -m "описание изменений"
   git push
   ```
3. На GitHub: вкладка **Actions** — там видно запуск «Deploy to Space Web» и результат (успех/ошибка).

При **push в ветку `main`** workflow сам подключается к хостингу по SFTP и заливает файлы. Логин и хост прописаны в `.github/workflows/deploy.yml`, пароль берётся из секрета `SFTP_PASSWORD`.

---

## Если деплой падает

- **Permission denied** — проверь пароль в секрете `SFTP_PASSWORD` (без лишних пробелов).
- **No such file** — на сервере должна быть папка `/home/infogkmeta/lenta-nerzhavejushhaja-aisi` (создай через файловый менеджер панели, если нет).
- Логи по шагам смотри во вкладке **Actions** → выбери последний запуск → шаг «Deploy via SFTP».

---

## Вариант Б: Git на сервере (если есть полноценный SSH)

Если при подключении по SSH у тебя **есть оболочка** (приглашение вроде `infogkmeta@server:~$` и команды выполняются), можно обновлять сайт через `git pull` на сервере. Инструкция от Space Web:

1. Подключись по SSH: `ssh infogkmeta@77.222.40.49 -p 22`
2. Перейди в папку сайта, например: `cd ~/public_html` (или ту, где лежит сайт)
3. Один раз клонировать репозиторий:  
   `git clone git@github.com:твой_логин/lenta-nerzhavejushhaja-aisi.git .`  
   (точка в конце — файлы в текущую папку)
4. Чтобы GitHub не спрашивал пароль, на сервере создай SSH-ключ и добавь его в GitHub:
   - На сервере: `ssh-keygen -t rsa` (на вопросы — Enter)
   - Показать ключ: `cat ~/.ssh/id_rsa.pub`
   - В GitHub: **Settings** → **SSH and GPG keys** → **New SSH key** → вставить ключ
   - Проверка: `ssh -T git@github.com`
5. Дальше для обновления: зайти по SSH, перейти в папку сайта и выполнить `git pull`

**Если при SSH сессия сразу закрывается** (нет приглашения и нельзя вводить команды) — тогда доступен только **Вариант А** (GitHub Actions по SFTP). В панели Space Web можно проверить, есть ли пункт «Терминал» или «SSH-консоль» — там часто дают нормальную оболочку.
