<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="header">
  <a class="header__logo" href="<?= esc_url(home_url('/')) ?>"><img src="<?= get_theme_file_uri('assets/myconsult-logo.svg') ?>" alt="<?= esc_attr(get_bloginfo('name')) ?>" width="114" height="33"></a>
  <nav class="header__nav" aria-label="Main">
    <ul>
      <?php foreach (mc_menu('primary') as $i => $item): ?>
      <li><a href="<?= esc_url($item->url) ?>" class="button_label" data-delay="<?= 0.3 + $i * 0.08 ?>"><?= esc_html($item->title) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>
  <?= mc_btn(get_field('header_cta', 'option'), 'btn header__btn', '0.6') ?>
</header>
