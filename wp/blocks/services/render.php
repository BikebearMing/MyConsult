<?php
// Three cards in a row. Each card picks a decorative card pair for its top (parts/visual-*.php).
?>
<section class="services"<?= mc_anchor($block) ?>>
  <span class="chip"><?= esc_html(get_field('chip')) ?></span>
  <i class="stem" aria-hidden="true" style="--h: 4.583vw"></i>
  <h2 class="h2 services__title"><?= esc_html(get_field('title')) ?></h2>
  <p class="body services__text"><?= esc_html(get_field('text')) ?></p>
  <div class="services__grid">
    <?php foreach (get_field('slides') ?: [] as $s): ?>
    <article class="service">
      <?php get_template_part('parts/visual', $s['visual'] ?: 'dashboard'); ?>
      <h3 class="h3 service__title"><?= esc_html($s['title']) ?></h3>
      <?php if ($s['points']): ?>
      <ul class="service__points">
        <?php foreach ($s['points'] as $p): ?>
        <li class="body"><?= mc_arrow('service__bullet') ?><span><?= esc_html($p['text']) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <?= mc_btn($s['button'], 'btn service__btn') ?>
    </article>
    <?php endforeach; ?>
  </div>
</section>
