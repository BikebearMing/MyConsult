<?php
// Three cards in a row. Each card has a decorative pair of dashboard images at its top: a back image and a front image,
// both picked in the card's fields. Where each one sits (position, width, tilt) depends on the card's place in the row
// (css/style.css); the pair fades in when the cards scroll into view (js/main.js).
?>
<section class="what"<?= mc_anchor($block) ?>>
  <span class="chip"><?= esc_html(get_field('chip')) ?></span>
  <i class="stem" aria-hidden="true"></i>
  <h2 class="h2"><?= esc_html(get_field('title')) ?></h2>
  <div class="what__grid">
    <?php foreach (get_field('cards') ?: [] as $c): ?>
    <article class="what-card">
      <div class="what-card__visual" aria-hidden="true">
        <?php foreach (['back', 'front'] as $slot): $img = $c["image_$slot"] ?? null; if (empty($img['url'])) continue; ?>
        <img class="what-card__img what-card__img--<?= $slot ?>" src="<?= esc_url($img['url']) ?>" alt="" width="<?= (int) $img['width'] ?>" height="<?= (int) $img['height'] ?>" loading="lazy">
        <?php endforeach; ?>
      </div>
      <h3 class="h3 what-card__title"><?= wp_kses_post($c['title']) ?></h3>
      <?php if ($c['links']): ?>
      <div class="what-card__links">
        <?php foreach ($c['links'] as $l) echo mc_arrow_link($l['link']); ?>
      </div>
      <?php endif; ?>
      <?php if ($c['note']): ?><p class="what-card__note"><?= wp_kses_post($c['note']) ?></p><?php endif; ?>
    </article>
    <?php endforeach; ?>
  </div>
</section>
