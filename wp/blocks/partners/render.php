<?php
// Groups side by side with a dotted divider between them, each a heading + note and a column of logo tiles;
// then the certification bar (shown when it has text).
$cert = get_field('cert_logo');
?>
<section class="partners"<?= mc_anchor($block) ?>>
  <h2 class="h2"><?= esc_html(get_field('title')) ?></h2>
  <div class="partners__groups">
    <?php foreach (get_field('groups') ?: [] as $g): ?>
    <div class="partner-group">
      <div class="partner-group__text">
        <h3 class="h3"><?= wp_kses_post($g['title']) ?></h3>
        <p class="body"><?= wp_kses_post($g['text']) ?></p>
      </div>
      <ul class="partner-group__list">
        <?php foreach ($g['logos'] ?: [] as $l): $logo = $l['logo'] ?? null; ?>
        <li class="partner"><span class="partner__logo"><?php if (!empty($logo['url'])): ?><img src="<?= esc_url($logo['url']) ?>" alt="" width="<?= (int) $logo['width'] ?>" height="<?= (int) $logo['height'] ?>" loading="lazy"><?php endif; ?></span><span class="partner__name"><?= esc_html($l['name']) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>
  </div>
  <?php if (get_field('cert_text')): ?>
  <div class="cert">
    <?php if (!empty($cert['url'])): ?>
    <img class="cert__logo" src="<?= esc_url($cert['url']) ?>" alt="<?= esc_attr($cert['alt']) ?>" width="<?= (int) $cert['width'] ?>" height="<?= (int) $cert['height'] ?>" loading="lazy">
    <?php endif; ?>
    <div class="cert__text">
      <p class="h5 cert__label"><?= esc_html(get_field('cert_label')) ?></p>
      <p class="h3"><?= esc_html(get_field('cert_text')) ?></p>
    </div>
  </div>
  <?php endif; ?>
</section>
