<?php
/**
 * Модальное окно «Оставить заявку».
 * Отправка: fetch POST /api/lead (app/lead.php) → проверка reCAPTCHA → Roistat (проксилид) → CRM.
 * Открывается любой кнопкой с классом .js-open-request-modal.
 * Если ключи (Roistat, reCAPTCHA) не заданы, вместо формы показываются телефон и почта.
 */
require_once __DIR__ . '/../../lead.php';

$_leadCompany = isset($config['company']) ? $config['company'] : [];
$_leadPhone = isset($_leadCompany['phone']) ? $_leadCompany['phone'] : '+7 (800) 200-39-43';
$_leadTel = preg_replace('/[^\d+]/', '', $_leadPhone);
$_leadEmail = isset($_leadCompany['email']) ? $_leadCompany['email'] : 'info@lenta-nerzhavejushhaja-aisi.ru';
$_leadReady = lead_is_configured($config);
$_leadSite = $_leadReady ? $config['lead']['recaptcha_site'] : '';
?>
    <div class="request-modal-overlay" id="requestModalOverlay" aria-hidden="true" role="presentation"></div>
    <div class="request-modal" id="requestModal" role="dialog" aria-modal="true" aria-labelledby="requestModalTitle" aria-hidden="true">
        <div class="request-modal__box">
            <div class="request-modal__header">
                <h2 class="request-modal__title" id="requestModalTitle" tabindex="-1">Оставить заявку</h2>
                <button type="button" class="request-modal__close" id="requestModalClose" aria-label="Закрыть">&times;</button>
            </div>
            <div class="request-modal__body request-modal__body--lead">
                <?php if ($_leadReady): ?>
                <form class="lead-form" id="leadForm" novalidate autocomplete="on">
                    <p class="lead-form__intro">Оставьте контакты: подтвердим наличие, назовём цену и подготовим счёт. Ответим за 15 минут в рабочее время.</p>

                    <div class="lead-form__field">
                        <label class="lead-form__label" for="leadName">Имя</label>
                        <input class="lead-form__input" type="text" id="leadName" name="name" maxlength="100" autocomplete="name" placeholder="Как к вам обращаться">
                    </div>

                    <div class="lead-form__field">
                        <label class="lead-form__label" for="leadPhone">Телефон <span class="lead-form__req" aria-hidden="true">*</span></label>
                        <input class="lead-form__input" type="tel" id="leadPhone" name="phone" maxlength="30" required inputmode="tel" autocomplete="tel" placeholder="+7 (900) 123-45-67" aria-describedby="leadError">
                    </div>

                    <div class="lead-form__field">
                        <label class="lead-form__label" for="leadEmail">Электронная почта</label>
                        <input class="lead-form__input" type="email" id="leadEmail" name="email" maxlength="254" autocomplete="email" placeholder="name@company.ru">
                    </div>

                    <div class="lead-form__field">
                        <label class="lead-form__label" for="leadInn">ИНН или «физлицо»</label>
                        <input class="lead-form__input" type="text" id="leadInn" name="inn" maxlength="50" autocomplete="off" placeholder="Необязательно">
                    </div>

                    <div class="lead-form__field">
                        <label class="lead-form__label" for="leadComment">Что нужно</label>
                        <textarea class="lead-form__input lead-form__textarea" id="leadComment" name="comment" rows="3" maxlength="2000" placeholder="Марка, толщина и ширина, длина или вес"></textarea>
                    </div>

                    <!-- Ловушка для ботов: люди это поле не видят -->
                    <div class="lead-form__trap" aria-hidden="true">
                        <label>Не заполняйте это поле <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <label class="lead-form__consent">
                        <input type="checkbox" id="leadConsent" name="consent" value="1" required>
                        <span>Согласен(на) на <a href="<?= e(base_url('consent/')) ?>" target="_blank" rel="noopener">обработку персональных данных</a> и принимаю <a href="<?= e(base_url('privacy-policy/')) ?>" target="_blank" rel="noopener">политику конфиденциальности</a></span>
                    </label>

                    <div class="lead-form__captcha" id="leadCaptcha"></div>

                    <p class="lead-form__error" id="leadError" role="alert" aria-live="assertive" hidden></p>

                    <input type="hidden" name="csrf" value="<?= e(lead_csrf_token()) ?>">
                    <input type="hidden" name="page" value="">
                    <input type="hidden" name="subject" value="">

                    <button type="submit" class="btn btn--primary btn--large lead-form__submit" id="leadSubmit">Отправить заявку</button>
                </form>

                <div class="lead-done" id="leadDone" hidden tabindex="-1">
                    <h3 class="lead-done__title">Спасибо, заявка отправлена</h3>
                    <p class="lead-done__text">Менеджер свяжется с вами в течение 15 минут в рабочее время. Если срочно, позвоните: <a href="tel:<?= e($_leadTel) ?>"><?= e($_leadPhone) ?></a>.</p>
                    <button type="button" class="btn btn--ghost" id="leadDoneClose">Закрыть</button>
                </div>
                <?php else: ?>
                <div class="lead-done lead-done--fallback">
                    <h3 class="lead-done__title">Свяжитесь с нами</h3>
                    <p class="lead-done__text">Форма временно недоступна. Позвоните или напишите: ответим за 15 минут в рабочее время.</p>
                    <div class="request-modal__error-actions">
                        <a href="tel:<?= e($_leadTel) ?>" class="request-modal__error-btn request-modal__error-btn--primary"><?= e($_leadPhone) ?></a>
                        <a href="mailto:<?= e($_leadEmail) ?>" class="request-modal__error-btn request-modal__error-btn--secondary"><?= e($_leadEmail) ?></a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
    (function () {
        var overlay = document.getElementById('requestModalOverlay');
        var modal = document.getElementById('requestModal');
        var closeBtn = document.getElementById('requestModalClose');
        if (!modal || !overlay) return;

        var form = document.getElementById('leadForm');
        var done = document.getElementById('leadDone');
        var doneClose = document.getElementById('leadDoneClose');
        var errorEl = document.getElementById('leadError');
        var submitBtn = document.getElementById('leadSubmit');
        var SITE_KEY = <?= json_encode($_leadSite) ?>;
        var ENDPOINT = '/api/lead';
        var savedScrollY = 0;
        var captchaId = null;
        var captchaState = 'idle'; // idle | loading | ready | failed
        var sending = false;

        function lockBodyScroll() {
            savedScrollY = window.scrollY || document.documentElement.scrollTop;
            document.body.style.position = 'fixed';
            document.body.style.top = '-' + savedScrollY + 'px';
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
        }
        function unlockBodyScroll() {
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';
            window.scrollTo(0, savedScrollY);
        }

        function showError(text) {
            if (!errorEl) return;
            errorEl.textContent = text;
            errorEl.hidden = false;
            if (errorEl.scrollIntoView) errorEl.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
        function clearError() {
            if (!errorEl) return;
            errorEl.textContent = '';
            errorEl.hidden = true;
        }

        // reCAPTCHA v2 («Я не робот») подгружаем при первом открытии окна.
        function loadCaptcha() {
            if (!form || captchaState !== 'idle') return;
            captchaState = 'loading';
            window.leadCaptchaReady = function () {
                try {
                    captchaId = window.grecaptcha.render('leadCaptcha', { sitekey: SITE_KEY });
                    captchaState = 'ready';
                } catch (e) {
                    captchaState = 'failed';
                }
            };
            var s = document.createElement('script');
            s.src = 'https://www.recaptcha.net/recaptcha/api.js?onload=leadCaptchaReady&render=explicit&hl=ru';
            s.async = true;
            s.defer = true;
            s.onerror = function () { captchaState = 'failed'; };
            document.head.appendChild(s);
        }
        function resetCaptcha() {
            try { if (captchaId !== null && window.grecaptcha) window.grecaptcha.reset(captchaId); } catch (e) {}
        }
        function captchaToken() {
            try { return captchaId !== null && window.grecaptcha ? window.grecaptcha.getResponse(captchaId) : ''; } catch (e) { return ''; }
        }

        function resetFormView() {
            if (!form) return;
            if (done) done.hidden = true;
            form.hidden = false;
            clearError();
        }

        function openRequestModal(trigger) {
            lockBodyScroll();
            modal.setAttribute('aria-hidden', 'false');
            overlay.setAttribute('aria-hidden', 'false');
            modal.classList.add('request-modal--open');
            overlay.classList.add('request-modal-overlay--open');
            if (form) {
                resetFormView();
                var h1 = document.querySelector('h1');
                var subject = (trigger && trigger.getAttribute('data-request-subject')) || (h1 ? h1.textContent.replace(/\s+/g, ' ').trim() : document.title);
                form.elements['subject'].value = subject.slice(0, 200);
                form.elements['page'].value = window.location.href.slice(0, 500);
                loadCaptcha();
                var first = document.getElementById('leadName');
                if (first) setTimeout(function () { first.focus(); }, 50);
            } else if (closeBtn) {
                closeBtn.focus();
            }
        }
        function closeRequestModal() {
            modal.classList.remove('request-modal--open');
            overlay.classList.remove('request-modal-overlay--open');
            modal.setAttribute('aria-hidden', 'true');
            overlay.setAttribute('aria-hidden', 'true');
            unlockBodyScroll();
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (sending) return;
                clearError();

                var phoneDigits = form.elements['phone'].value.replace(/\D+/g, '');
                if (phoneDigits.length < 10) {
                    showError('Укажите телефон в формате +7 (900) 123-45-67.');
                    form.elements['phone'].focus();
                    return;
                }
                if (!form.elements['consent'].checked) {
                    showError('Нужно согласие на обработку персональных данных.');
                    return;
                }
                var token = captchaToken();
                if (!token) {
                    showError(captchaState === 'failed'
                        ? 'Не загрузилась проверка «Я не робот». Обновите страницу или позвоните нам.'
                        : 'Подтвердите, что вы не робот.');
                    return;
                }

                sending = true;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Отправляем…';

                fetch(ENDPOINT, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                }).then(function (r) {
                    return r.json().then(function (j) { return j; }, function () { return { ok: false, error: 'Сервер вернул неожиданный ответ. Позвоните нам.' }; });
                }).then(function (res) {
                    if (res && res.ok) {
                        form.reset();
                        resetCaptcha();
                        form.hidden = true;
                        if (done) { done.hidden = false; done.focus(); }
                        try { window.dispatchEvent(new CustomEvent('lead:sent')); } catch (err) {}
                    } else {
                        showError((res && res.error) || 'Не удалось отправить заявку. Позвоните нам.');
                        resetCaptcha();
                    }
                }).catch(function () {
                    showError('Нет связи с сервером. Проверьте интернет или позвоните нам.');
                    resetCaptcha();
                }).then(function () {
                    sending = false;
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Отправить заявку';
                });
            });
        }

        document.querySelectorAll('.js-open-request-modal').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openRequestModal(el);
            });
        });
        if (closeBtn) closeBtn.addEventListener('click', closeRequestModal);
        if (doneClose) doneClose.addEventListener('click', closeRequestModal);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeRequestModal(); });
        modal.addEventListener('click', function (e) { if (e.target === modal) closeRequestModal(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('request-modal--open')) closeRequestModal();
        });
    })();
    </script>
