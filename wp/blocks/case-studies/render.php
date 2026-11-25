<?php
// Top edge is a dark dome over the blue glow at the bottom of the services section.
?>
<section class="cases"<?= mc_anchor($block) ?>>
  <span class="chip"><?= esc_html(get_field('chip')) ?></span>
  <i class="stem" aria-hidden="true" style="--h: 2.535vw"></i>
  <h2 class="h2"><?= esc_html(get_field('title')) ?></h2>
  <?php foreach (get_field('cases') ?: [] as $c): ?>
  <article class="case">
    <?= $c['image'] ? wp_get_attachment_image($c['image'], 'large', false, ['class' => 'case__img', 'loading' => 'lazy']) : '' ?>
    <div class="case__body">
      <h3 class="h3"><?= esc_html($c['title']) ?></h3>
      <p class="body case__text"><?= esc_html($c['text']) ?></p>
      <div class="case__cols">
        <div class="case__col">
          <span class="chip">Solution</span>
          <p class="body"><?= esc_html($c['solution']) ?></p>
          <?= mc_btn($c['link']) ?>
        </div>
        <i class="case__divider" aria-hidden="true"></i>
        <div class="case__col">
          <span class="chip">Results</span>
          <p class="body"><?= esc_html($c['results']) ?></p>
        </div>
      </div>
    </div>
  </article>
  <?php endforeach; ?>
</section>
