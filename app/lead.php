<?php
/**
 * Приём заявок с формы сайта.
 *
 * Схема: форма (модальное окно) → POST /api/lead → проверка reCAPTCHA → Roistat «проксилид» → CRM.
 * Инструкция Roistat: «Передача заявок в CRM через Roistat (проксилид)», способ через PHP-код.
 * Запрос уходит только с сервера: ключ интеграции в браузер не попадает.
 *
 * Код совместим с PHP 7.x (без стрелочных функций и типизированных свойств).
 */

if (!function_exists('lead_settings')) {
    /** Настройки заявок из app/config.php (+ storage/config.local.php). */
    function lead_settings(array $config) {
        return isset($config['lead']) && is_array($config['lead']) ? $config['lead'] : [];
    }
}

if (!function_exists('lead_is_configured')) {
    /**
     * Форма работает, когда заданы оба ключа reCAPTCHA и есть хотя бы один канал доставки:
     * письмо на notify_email и/или Roistat (ключ интеграции).
     */
    function lead_is_configured(array $config) {
        $s = lead_settings($config);
        $hasChannel = !empty($s['roistat_key']) || (isset($s['notify_email']) && trim((string) $s['notify_email']) !== '');
        return !empty($s['recaptcha_site']) && !empty($s['recaptcha_secret']) && $hasChannel;
    }
}

if (!function_exists('lead_csrf_token')) {
    /** Токен защиты формы от отправки с чужих сайтов (хранится в сессии). */
    function lead_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (empty($_SESSION['lead_csrf'])) {
            $_SESSION['lead_csrf'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['lead_csrf'];
    }
}

if (!function_exists('lead_json')) {
    function lead_json($code, array $payload) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('lead_clean')) {
    /**
     * Обрезает пробелы и управляющие символы, ограничивает длину.
     * Поля в одну строку ($multiline = false) сжимаются в одну строку: переводы строк в них не нужны.
     */
    function lead_clean($value, $maxLen, $multiline = false) {
        $value = is_string($value) ? $value : '';
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = (string) @iconv('UTF-8', 'UTF-8//IGNORE', $value); // выбрасываем битые байты, а не теряем поле целиком
        }
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        $value = $clean === null ? '' : $clean;
        if (!$multiline) {
            $flat = preg_replace('/\s+/u', ' ', $value);
            $value = $flat === null ? '' : $flat;
        }
        return mb_substr(trim($value), 0, $maxLen);
    }
}

if (!function_exists('lead_normalize_phone')) {
    /** «8 (916) 123-45-67» → «+79161234567». Возвращает '' если номер не похож на телефон. */
    function lead_normalize_phone($raw) {
        $digits = preg_replace('/\D+/', '', (string) $raw);
        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        } elseif (strlen($digits) === 10) {
            $digits = '7' . $digits;
        }
        if (strlen($digits) < 11 || strlen($digits) > 15) {
            return '';
        }
        return '+' . $digits;
    }
}

if (!function_exists('lead_client_ip')) {
    /** IP посетителя: если сайт за прокси, берём последний адрес из X-Forwarded-For (его добавил прокси хостинга). */
    function lead_client_ip() {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            $last = end($parts);
            if ($last !== '' && filter_var($last, FILTER_VALIDATE_IP)) {
                $ip = $last;
            }
        }
        return $ip;
    }
}

if (!function_exists('lead_rate_exceeded')) {
    /** Ограничение частоты: не более $limit заявок с одного IP за $window секунд. Учитывает текущую попытку. */
    function lead_rate_exceeded($ip, $limit, $window) {
        $file = __DIR__ . '/../storage/lead_rate.json';
        $now = time();
        $key = md5($ip);
        $fh = @fopen($file, 'c+');
        if (!$fh) {
            return false; // не мешаем работе, если нельзя записать файл
        }
        flock($fh, LOCK_EX);
        $raw = stream_get_contents($fh);
        $all = $raw ? json_decode($raw, true) : [];
        if (!is_array($all)) {
            $all = [];
        }
        foreach ($all as $k => $times) {
            $all[$k] = array_values(array_filter((array) $times, function ($t) use ($now, $window) {
                return $t > $now - $window;
            }));
            if (empty($all[$k])) {
                unset($all[$k]);
            }
        }
        $times = isset($all[$key]) ? $all[$key] : [];
        $exceeded = count($times) >= $limit;
        if (!$exceeded) {
            $times[] = $now;
            $all[$key] = $times;
        }
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($all));
        flock($fh, LOCK_UN);
        fclose($fh);
        return $exceeded;
    }
}

if (!function_exists('lead_http')) {
    /**
     * HTTP-запрос с таймаутом. $postFields === null → GET, иначе POST (application/x-www-form-urlencoded).
     * Возвращает [строка_ответа|null, код_http].
     */
    function lead_http($url, $postFields = null, $timeout = 10) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            if ($postFields !== null) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
            }
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return [$body === false ? null : $body, $code];
        }
        $opts = ['http' => ['method' => 'GET', 'timeout' => $timeout, 'ignore_errors' => true]];
        if ($postFields !== null) {
            $opts['http']['method'] = 'POST';
            $opts['http']['header'] = "Content-Type: application/x-www-form-urlencoded\r\n";
            $opts['http']['content'] = http_build_query($postFields);
        }
        $body = @file_get_contents($url, false, stream_context_create($opts));
        $code = 0;
        if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
            $code = (int) $m[1];
        }
        return [$body === false ? null : $body, $code];
    }
}

if (!function_exists('lead_log_system')) {
    /** Служебный журнал для администратора (ошибки настройки и связи, без персональных данных). */
    function lead_log_system($message) {
        $file = __DIR__ . '/../storage/lead_errors.log';
        if (is_file($file) && filesize($file) > 200000) {
            @file_put_contents($file, ''); // не даём файлу расти бесконечно
        }
        @file_put_contents($file, date('Y-m-d H:i:s') . ' ' . $message . "\n", FILE_APPEND | LOCK_EX);
        error_log('[lead] ' . $message);
    }
}

if (!function_exists('lead_verify_recaptcha')) {
    /**
     * Проверка ответа reCAPTCHA на сервере Google.
     * Возвращает: 'ok' | 'invalid' (не прошла проверку) | 'misconfigured' (неверные ключи) | 'unavailable' (нет связи с Google).
     */
    function lead_verify_recaptcha(array $settings, $token, $ip) {
        if ($token === '') {
            return 'invalid';
        }
        $url = !empty($settings['recaptcha_verify_url']) ? $settings['recaptcha_verify_url'] : 'https://www.recaptcha.net/recaptcha/api/siteverify';
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            list($body) = lead_http($url, [
                'secret'   => $settings['recaptcha_secret'],
                'response' => $token,
                'remoteip' => $ip,
            ], 10);
            $data = $body !== null ? json_decode($body, true) : null;
            if (!is_array($data)) {
                continue; // нет ответа или не JSON: пробуем ещё раз
            }
            if (!empty($data['success'])) {
                if (isset($settings['recaptcha_version']) && $settings['recaptcha_version'] === 'v3') {
                    // v3 возвращает оценку 0.0 (бот) ... 1.0 (человек) и название действия, заданное на странице.
                    $min = isset($settings['recaptcha_min_score']) ? (float) $settings['recaptcha_min_score'] : 0.5;
                    $score = isset($data['score']) ? (float) $data['score'] : 0.0;
                    $action = isset($data['action']) ? (string) $data['action'] : '';
                    if ($action !== 'lead' || $score < $min) {
                        lead_log_system('reCAPTCHA v3: проверка не пройдена (оценка ' . $score . ', порог ' . $min . ', действие "' . $action . '")');
                        return 'invalid';
                    }
                }
                return 'ok';
            }
            $codes = isset($data['error-codes']) ? (array) $data['error-codes'] : [];
            if (in_array('invalid-input-secret', $codes, true) || in_array('missing-input-secret', $codes, true)) {
                lead_log_system('reCAPTCHA: неверный секретный ключ (' . implode(',', $codes) . '). Проверьте storage/config.local.php.');
                return 'misconfigured';
            }
            $hint = strlen($token) > 500 ? ' (токен выглядит настоящим: если так у всех посетителей, проверьте секретный ключ и что ключи от reCAPTCHA v2 «Я не робот» для этого домена)' : '';
            lead_log_system('reCAPTCHA отклонила ответ: ' . ($codes ? implode(',', $codes) : 'без кода') . $hint);
            return 'invalid';
        }
        lead_log_system('reCAPTCHA: нет ответа от сервера проверки (' . $url . ')');
        return 'unavailable';
    }
}

if (!function_exists('lead_send_roistat')) {
    /**
     * Отправка заявки в Roistat по инструкции «проксилид» (PHP-вариант):
     * GET https://cloud.roistat.com/api/proxy/1.0/leads/add?roistat=…&key=…&title=…&name=…&phone=…&email=…&comment=…
     * Возвращает [успех, текст_для_лога].
     */
    function lead_send_roistat(array $settings, array $lead) {
        $comment = $lead['comment'] !== '' ? $lead['comment'] . "\n\n" : '';
        if ($lead['inn'] !== '') {
            $comment .= 'ИНН / физлицо: ' . $lead['inn'] . "\n";
        }
        $comment .= 'Страница: ' . $lead['page'];
        if ($lead['subject'] !== '') {
            $comment .= "\nТема: " . $lead['subject'];
        }
        $comment .= "\nСогласие на обработку персональных данных: да";

        $fields = ['charset' => 'UTF-8'];
        if (!empty($settings['roistat_fields']) && is_array($settings['roistat_fields'])) {
            $fields = array_merge($fields, $settings['roistat_fields']);
        }

        $data = [
            'roistat'               => isset($_COOKIE['roistat_visit']) ? $_COOKIE['roistat_visit'] : 'nocookie',
            'key'                   => $settings['roistat_key'],
            'title'                 => 'Заявка с сайта' . ($lead['subject'] !== '' ? ': ' . $lead['subject'] : ''),
            'comment'               => $comment,
            'name'                  => $lead['name'],
            'email'                 => $lead['email'],
            'phone'                 => $lead['phone'],
            'order_creation_method' => 'Форма заявки на сайте',
            'is_need_callback'      => '0',
            'sync'                  => '0',
            'fields'                => $fields,
        ];
        $url = !empty($settings['roistat_url']) ? $settings['roistat_url'] : 'https://cloud.roistat.com/api/proxy/1.0/leads/add';

        list($body, $code) = lead_http($url . '?' . http_build_query($data), null, 10);
        if ($body === null) {
            return [false, 'нет ответа от Roistat (HTTP ' . $code . ')'];
        }
        $json = json_decode($body, true);
        // Roistat отвечает JSON; при ошибке — {"status":"error","data":"…"} (код HTTP при этом 200).
        if (!is_array($json)) {
            return [false, 'неожиданный ответ Roistat (HTTP ' . $code . '): ' . mb_substr($body, 0, 200)];
        }
        if (isset($json['status']) && $json['status'] === 'error') {
            return [false, 'Roistat вернул ошибку: ' . (isset($json['data']) && is_string($json['data']) ? $json['data'] : json_encode($json, JSON_UNESCAPED_UNICODE))];
        }
        return [true, 'ok'];
    }
}

if (!function_exists('lead_send_email')) {
    /**
     * Копия заявки письмом на адрес(а) из настроек notify_email (через mail() хостинга).
     * Возвращает [true|false|null, текст]: null — канал выключен (адрес не задан).
     */
    function lead_send_email(array $settings, array $lead) {
        $toList = isset($settings['notify_email']) ? trim((string) $settings['notify_email']) : '';
        if ($toList === '') {
            return [null, 'отключено'];
        }
        $recipients = array_filter(array_map('trim', explode(',', $toList)));
        foreach ($recipients as $r) {
            if (!filter_var($r, FILTER_VALIDATE_EMAIL)) {
                return [false, 'некорректный адрес в notify_email: ' . $r];
            }
        }
        $to = implode(', ', $recipients);
        $from = !empty($settings['notify_from']) ? trim((string) $settings['notify_from']) : reset($recipients);
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return [false, 'некорректный адрес отправителя notify_from: ' . $from];
        }

        $siteName = 'lenta-nerzhavejushhaja-aisi.ru';
        $subjectLine = 'Заявка с сайта' . ($lead['subject'] !== '' ? ': ' . $lead['subject'] : '') . ' (' . $lead['phone'] . ')';
        $subjectLine = mb_substr(preg_replace('/[\r\n]+/', ' ', $subjectLine), 0, 150);

        $lines = [
            'Новая заявка с сайта ' . $siteName,
            '',
            'Имя: ' . ($lead['name'] !== '' ? $lead['name'] : 'не указано'),
            'Телефон: ' . $lead['phone'],
            'Email: ' . ($lead['email'] !== '' ? $lead['email'] : 'не указан'),
            'ИНН / физлицо: ' . ($lead['inn'] !== '' ? $lead['inn'] : 'не указано'),
            'Комментарий: ' . ($lead['comment'] !== '' ? $lead['comment'] : 'нет'),
            '',
            'Страница: ' . $lead['page'],
            'Тема: ' . ($lead['subject'] !== '' ? $lead['subject'] : 'не указана'),
            'Визит Roistat: ' . (isset($_COOKIE['roistat_visit']) ? preg_replace('/[^\w\-]/', '', $_COOKIE['roistat_visit']) : 'нет'),
            'Время: ' . (new DateTime('now', new DateTimeZone('Europe/Moscow')))->format('Y-m-d H:i:s') . ' (МСК)',
            'Согласие на обработку персональных данных: да',
        ];
        $body = implode("\r\n", $lines);

        $headers = [
            'From: =?UTF-8?B?' . base64_encode('Сайт ' . $siteName) . '?= <' . $from . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            'X-Mailer: lead-form',
        ];
        if ($lead['email'] !== '' && filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $lead['email'];
        }
        $subject = '=?UTF-8?B?' . base64_encode($subjectLine) . '?=';
        $encodedBody = chunk_split(base64_encode($body));
        $headerStr = implode("\r\n", $headers);

        // Многие хостинги требуют адрес отправителя в конверте (-f). Если с ним не вышло, пробуем без него.
        $ok = @mail($to, $subject, $encodedBody, $headerStr, '-f' . $from);
        if (!$ok) {
            $ok = @mail($to, $subject, $encodedBody, $headerStr);
        }
        return [$ok ? true : false, $ok ? 'ok' : 'mail() вернул false (хостинг не принял письмо)'];
    }
}

if (!function_exists('lead_save_failed')) {
    /** Заявка не дошла до Roistat: сохраняем, чтобы её можно было обработать вручную. Файл закрыт от web-доступа. */
    function lead_save_failed(array $lead, $reason) {
        $line = json_encode([
            'time'   => date('c'),
            'reason' => $reason,
            'lead'   => $lead,
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents(__DIR__ . '/../storage/leads_failed.jsonl', $line, FILE_APPEND | LOCK_EX);
        lead_log_system('заявка не ушла в Roistat и сохранена в storage/leads_failed.jsonl: ' . $reason);
    }
}

if (!function_exists('lead_status')) {
    /** GET /api/lead/status: что настроено на сервере (только да/нет, без значений ключей). Нужно для проверки без доступа к серверу. */
    function lead_status(array $config) {
        $s = lead_settings($config);
        $storage = realpath(__DIR__ . '/../storage');
        $expected = ($storage ? $storage : __DIR__ . '/../storage') . DIRECTORY_SEPARATOR . 'config.local.php';
        lead_json(200, [
            'form_enabled'     => lead_is_configured($config),
            'config_local_php' => is_file($expected) ? 'найден' : 'НЕ НАЙДЕН (создайте по этому пути)',
            'expected_path'    => $expected,
            'recaptcha_version' => isset($s['recaptcha_version']) ? $s['recaptcha_version'] : 'v2',
            'recaptcha_site'   => !empty($s['recaptcha_site']),
            'recaptcha_secret' => !empty($s['recaptcha_secret']),
            'roistat_key'      => !empty($s['roistat_key']),
            'notify_email'     => !empty($s['notify_email']),
        ]);
    }
}

if (!function_exists('lead_handle')) {
    /** Обработчик POST /api/lead. Всегда отвечает JSON и завершает скрипт. */
    function lead_handle(array $config) {
        $settings = lead_settings($config);
        $company = isset($config['company']) ? $config['company'] : [];
        $contactsHint = ' Позвоните ' . (isset($company['phone']) ? $company['phone'] : '+7 (800) 200-39-43')
            . ' или напишите ' . (isset($company['email']) ? $company['email'] : 'info@lenta-nerzhavejushhaja-aisi.ru') . '.';

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            lead_json(405, ['ok' => false, 'error' => 'Метод не поддерживается.']);
        }
        if (!lead_is_configured($config)) {
            lead_json(503, ['ok' => false, 'error' => 'Форма временно недоступна.' . $contactsHint]);
        }

        // Запрос должен прийти с нашего сайта.
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            $originHost = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
            $ownHost = preg_replace('/:\d+$/', '', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
            if (!$originHost || strcasecmp($originHost, $ownHost) !== 0) {
                lead_json(403, ['ok' => false, 'error' => 'Запрос отклонён.']);
            }
        }
        $csrf = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
        if ($csrf === '' || !hash_equals(lead_csrf_token(), $csrf)) {
            lead_json(403, ['ok' => false, 'error' => 'Страница устарела. Обновите её и отправьте форму ещё раз.']);
        }

        // Скрытое поле-ловушка: человек его не видит и не заполняет. Боту отвечаем «успех», ничего не отправляя.
        if (!empty($_POST['website'])) {
            lead_json(200, ['ok' => true]);
        }

        $ip = lead_client_ip();
        $limit = isset($settings['rate_limit']) ? (int) $settings['rate_limit'] : 10;
        $window = isset($settings['rate_window']) ? (int) $settings['rate_window'] : 600;
        if (lead_rate_exceeded($ip, $limit, $window)) {
            lead_json(429, ['ok' => false, 'error' => 'Слишком много заявок подряд. Попробуйте позже.' . $contactsHint]);
        }

        $lead = [
            'name'    => lead_clean(isset($_POST['name']) ? $_POST['name'] : '', 100),
            'phone'   => lead_normalize_phone(isset($_POST['phone']) ? $_POST['phone'] : ''),
            'email'   => lead_clean(isset($_POST['email']) ? $_POST['email'] : '', 254),
            'inn'     => lead_clean(isset($_POST['inn']) ? $_POST['inn'] : '', 50),
            'comment' => lead_clean(isset($_POST['comment']) ? $_POST['comment'] : '', 2000, true),
            'page'    => lead_clean(isset($_POST['page']) ? $_POST['page'] : '', 500),
            'subject' => lead_clean(isset($_POST['subject']) ? $_POST['subject'] : '', 200),
        ];

        if ($lead['phone'] === '') {
            lead_json(400, ['ok' => false, 'field' => 'phone', 'error' => 'Укажите телефон в формате +7 (900) 123-45-67.']);
        }
        if ($lead['email'] !== '' && !filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
            lead_json(400, ['ok' => false, 'field' => 'email', 'error' => 'Проверьте адрес электронной почты.']);
        }
        if (empty($_POST['consent'])) {
            lead_json(400, ['ok' => false, 'field' => 'consent', 'error' => 'Нужно согласие на обработку персональных данных.']);
        }

        $captcha = isset($_POST['g-recaptcha-response']) ? (string) $_POST['g-recaptcha-response'] : '';
        $captchaResult = lead_verify_recaptcha($settings, $captcha, $ip);
        if ($captchaResult === 'invalid') {
            $isV3 = isset($settings['recaptcha_version']) && $settings['recaptcha_version'] === 'v3';
            lead_json(400, ['ok' => false, 'field' => 'captcha', 'error' => $isV3
                ? 'Не удалось пройти проверку на робота. Попробуйте ещё раз.' . $contactsHint
                : 'Подтвердите, что вы не робот.']);
        }
        if ($captchaResult !== 'ok') {
            lead_json(503, ['ok' => false, 'error' => 'Не удалось проверить защиту от роботов. Повторите через минуту.' . $contactsHint]);
        }

        // Каналы доставки: письмо на почту сайта и Roistat (если задан ключ интеграции). Достаточно одного успешного.
        list($mailOk, $mailInfo) = lead_send_email($settings, $lead);
        if (!empty($settings['roistat_key'])) {
            list($roistatOk, $roistatInfo) = lead_send_roistat($settings, $lead);
        } else {
            $roistatOk = null;
            $roistatInfo = 'ключ интеграции не задан';
        }

        if ($mailOk !== true && $roistatOk !== true) {
            lead_save_failed($lead, 'письмо: ' . $mailInfo . '; Roistat: ' . $roistatInfo);
            lead_json(502, ['ok' => false, 'error' => 'Не удалось отправить заявку.' . $contactsHint]);
        }
        if ($mailOk === false) {
            lead_log_system('заявка принята, но письмо не отправлено: ' . $mailInfo);
        }
        if ($roistatOk === false) {
            lead_log_system('заявка принята, но в Roistat не ушла: ' . $roistatInfo);
        }
        lead_json(200, ['ok' => true]);
    }
}
