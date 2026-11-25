<?php $title = get_field('title') ?: get_the_title(); // the ring and orb are decorative (theme asset) ?>
<section class="page-hero"<?= mc_anchor($block) ?>>
  <nav class="h5 page-hero__crumbs" aria-label="Breadcrumb" data-delay="0"><a href="<?= esc_url(home_url('/')) ?>">Home</a> / <span aria-current="page"><?= esc_html($title) ?></span></nav>
  <h1 class="h1 page-hero__title" data-delay="0.2"><?= esc_html($title) ?></h1>
  <div class="page-hero__orb" aria-hidden="true" data-reveal data-delay="0.5">
    <svg viewBox="0 0 165 165"><defs><linearGradient id="page-ring" x1="0.6" y1="0" x2="0.3" y2="1"><stop offset="0" stop-color="#fff"/><stop offset="0.6" stop-color="#fff" stop-opacity="0.55"/><stop offset="1" stop-color="#fff" stop-opacity="0.08"/></linearGradient></defs><circle cx="82.5" cy="82.5" r="81.75" stroke="url(#page-ring)"/></svg>
    <img src="<?= get_theme_file_uri('assets/myconsult-orb.png') ?>" alt="" width="254" height="254">
  </div>
  <?php if (get_field('heading')): ?><h2 class="h3 page-hero__heading" data-delay="0.7"><?= esc_html(get_field('heading')) ?></h2><?php endif; ?>
  <?php if (get_field('text')): ?><p class="body page-hero__text" data-delay="1.1"><?= wp_kses_post(get_field('text')) ?></p><?php endif; ?>
</section>
