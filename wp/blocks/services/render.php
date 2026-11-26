<?php
// Three cards in a row. Each card has a decorative pair of dashboard images at its top: a back image and a front image,
// both picked in the card's fields. Where each one sits (position, width, tilt) depends on the card's place in the row
// (css/style.css); the pair fades in when the cards scroll into view (js/main.js).
?>
<section class="services"<?= mc_anchor($block) ?>>
  <span class="chip"><?= esc_html(get_field('chip')) ?></span>
  <i class="stem" aria-hidden="true" style="--h: 4.583vw"></i>
  <h2 class="h2 services__title"><?= esc_html(get_field('title')) ?></h2>
  <p class="body services__text"><?= esc_html(get_field('text')) ?></p>
  <div class="services__grid">
    <?php foreach (get_field('slides') ?: [] as $s): ?>
    <article class="service">
      <div class="service__visual" aria-hidden="true">
        <?php foreach (['back', 'front'] as $slot): $img = $s["image_$slot"] ?? null; if (empty($img['url'])) continue; ?>
        <img class="service__img service__img--<?= $slot ?>" src="<?= esc_url($img['url']) ?>" alt="" width="<?= (int) $img['width'] ?>" height="<?= (int) $img['height'] ?>" loading="lazy">
        <?php endforeach; ?>
      </div>
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
