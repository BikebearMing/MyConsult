<?php
// The strip scrolls forever: js/main.js repeats the set to fill two screens, CSS moves it by half.
// Each logo is the uploaded image (white, exported at 3x and shown at a third of its pixel size), or its name as text until one is uploaded.
?>
<section class="logos"<?= mc_anchor($block) ?>>
  <p class="body logos__label"><?= esc_html(get_field('label')) ?></p>
  <div class="logos__marquee">
    <ul class="logos__track">
      <?php foreach (get_field('logos') ?: [] as $l): ?>
      <li class="logo" style="--gap: <?= (int) ($l['gap'] ?: 92) ?>"><?= $l['logo'] ? wp_get_attachment_image($l['logo'], 'full', false, ['alt' => $l['name'], 'style' => '--h: ' . round(wp_get_attachment_metadata($l['logo'])['height'] / 3, 2)]) : esc_html($l['name']) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
