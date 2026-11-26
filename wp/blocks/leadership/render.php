<?php
// Two cards side by side: photo on the left (cropped to cover), then name, role, bio and optional social icon links.
// The icons are theme assets (assets/icons/*.svg); a link shows only when its field is filled, and opens in a new tab.
$socials = ['linkedin' => 'LinkedIn', 'youtube' => 'YouTube'];
?>
<section class="leaders"<?= mc_anchor($block) ?>>
  <h2 class="h2"><?= esc_html(get_field('title')) ?></h2>
  <div class="leaders__grid">
    <?php foreach (get_field('people') ?: [] as $i => $p): $photo = $p['photo'] ?? null; ?>
    <article class="leader" data-reveal<?= $i % 2 ? ' data-delay="0.15"' : '' ?>>
      <?php if (!empty($photo['url'])): ?>
      <img class="leader__photo" src="<?= esc_url($photo['url']) ?>" alt="<?= esc_attr($p['name']) ?>" width="<?= (int) $photo['width'] ?>" height="<?= (int) $photo['height'] ?>" loading="lazy">
      <?php else: ?>
      <span class="leader__photo"></span>
      <?php endif; ?>
      <div class="leader__body">
        <h3 class="h3"><?= esc_html($p['name']) ?></h3>
        <p class="leader__role"><?= wp_kses_post($p['role']) ?></p>
        <p class="body leader__bio"><?= esc_html($p['bio']) ?></p>
        <?php if (array_filter(array_intersect_key($p, $socials))): ?>
        <div class="leader__social">
          <?php foreach ($socials as $key => $label): if (empty($p[$key])) continue; ?>
          <a class="social" href="<?= esc_url($p[$key]) ?>" target="_blank" rel="noopener" aria-label="<?= esc_attr($p['name'] . ' on ' . $label) ?>"><img src="<?= get_theme_file_uri("assets/icons/$key.svg") ?>" alt="" width="24" height="24"></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
