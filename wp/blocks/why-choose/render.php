<?php
// Dome-topped section: intro, then one card of up to three columns.
?>
<section class="why"<?= mc_anchor($block) ?>>
  <span class="chip"><?= esc_html(get_field('chip')) ?></span>
  <i class="stem" aria-hidden="true"></i>
  <h2 class="h2"><?= esc_html(get_field('title')) ?></h2>
  <p class="body why__text"><?= esc_html(get_field('text')) ?></p>
  <?php if (get_field('list_chip')): ?><span class="chip why__chip"><?= esc_html(get_field('list_chip')) ?></span><?php endif; ?>
  <div class="why__card">
    <?php foreach (get_field('items') ?: [] as $i): ?>
    <article class="why__item">
      <h3 class="h3"><?= wp_kses_post($i['title']) ?></h3>
      <p class="body"><?= esc_html($i['text']) ?></p>
    </article>
    <?php endforeach; ?>
  </div>
</section>
