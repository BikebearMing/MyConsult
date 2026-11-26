<?php
// Native <details name="faq">: one answer open at a time, keyboard accessible, no JS.
$toggle = '<span class="faq__toggle" aria-hidden="true"><svg viewBox="-10 -10 20 20"><g><circle cx="-6" r="1.4"/><circle cx="-3" r="1.4"/><circle r="1.4"/><circle cx="3" r="1.4"/><circle cx="6" r="1.4"/></g><g class="faq__v"><circle cy="-6" r="1.4" style="--i:1"/><circle cy="-3" r="1.4" style="--i:0"/><circle cy="3" r="1.4" style="--i:0"/><circle cy="6" r="1.4" style="--i:1"/></g></svg></span>';
?>
<section class="faq"<?= mc_anchor($block) ?>>
  <h2 class="h2"><?= wp_kses_post(get_field('title')) ?></h2>
  <div class="faq__list">
    <?php foreach (get_field('items') ?: [] as $q): ?>
    <details class="faq__item" name="faq" data-reveal<?= $q['open'] ? ' open' : '' ?>>
      <summary><span class="h4"><?= esc_html($q['question']) ?></span><?= $toggle ?></summary>
      <p class="faq__answer"><?= esc_html($q['answer']) ?></p>
    </details>
    <?php endforeach; ?>
  </div>
  <?php if (get_field('cta_title')): // optional box under the questions ?>
  <div class="faq__cta" data-reveal>
    <h2 class="h2"><?= wp_kses_post(get_field('cta_title')) ?></h2>
    <div class="faq__cta-body">
      <p class="body"><?= esc_html(get_field('cta_text')) ?></p>
      <?= mc_btn(get_field('cta_button')) ?>
    </div>
  </div>
  <?php endif; ?>
</section>
