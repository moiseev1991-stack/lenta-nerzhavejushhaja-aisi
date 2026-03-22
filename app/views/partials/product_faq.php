<?php
$faqItems = function_exists('get_product_faq') ? get_product_faq($product) : [];
if (empty($faqItems)) return;
$faqItems = array_slice($faqItems, 0, 6);
?>
<section class="product-faq-accordion" aria-labelledby="product-faq-title" itemscope itemtype="https://schema.org/FAQPage">
    <h2 class="product-faq-accordion__title" id="product-faq-title">Вопросы по заказу</h2>
    <div class="product-faq-accordion__list">
        <?php foreach ($faqItems as $i => $item): ?>
        <div class="product-faq-accordion__item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <button type="button" class="product-faq-accordion__trigger" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="faq-answer-<?= $i ?>" id="faq-trigger-<?= $i ?>" data-faq-toggle>
                <span itemprop="name"><?= e($item['question']) ?></span>
                <span class="product-faq-accordion__chevron" aria-hidden="true"></span>
            </button>
            <div class="product-faq-accordion__answer" id="faq-answer-<?= $i ?>" aria-labelledby="faq-trigger-<?= $i ?>" itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer" <?= $i === 0 ? '' : 'hidden' ?>>
                <p itemprop="text"><?= e($item['answer']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<script>
(function() {
    var triggers = document.querySelectorAll('[data-faq-toggle]');
    triggers.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var expanded = btn.getAttribute('aria-expanded') === 'true';
            var answerId = btn.getAttribute('aria-controls');
            var answer = document.getElementById(answerId);
            btn.setAttribute('aria-expanded', !expanded);
            if (answer) answer.hidden = expanded;
        });
    });
})();
</script>
