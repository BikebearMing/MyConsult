<?php
// MyConsult theme. One ACF block per section (blocks/*), field groups in acf-json/ (ACF loads them from there automatically).

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  register_nav_menus([
    'primary'          => 'Header',
    'footer-solutions' => 'Footer: solutions',
    'footer'           => 'Footer: links',
    'legal'            => 'Footer: legal',
  ]);
});

// Front end and editor canvas both get the site styles, so block previews look like the site.
add_action('enqueue_block_assets', function () {
  $u = get_template_directory_uri();
  wp_enqueue_style('myconsult-fonts', 'https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter+Tight:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap', [], null);
  wp_enqueue_style('myconsult', "$u/css/style.css", [], mc_ver('css/style.css'));
});

add_action('wp_enqueue_scripts', function () {
  $u = get_template_directory_uri();
  foreach (['wp-block-library', 'global-styles', 'classic-theme-styles'] as $h) wp_dequeue_style($h); // only ACF blocks here

  $cdn = 'https://cdn.jsdelivr.net/npm';
  wp_enqueue_script('lenis', "$cdn/lenis@1.3.27/dist/lenis.min.js", [], null, true);
  wp_enqueue_script('gsap', "$cdn/gsap@3.15.0/dist/gsap.min.js", [], null, true);
  wp_enqueue_script('gsap-st', "$cdn/gsap@3.15.0/dist/ScrollTrigger.min.js", ['gsap'], null, true);
  wp_enqueue_script('gsap-split', "$cdn/gsap@3.15.0/dist/SplitText.min.js", ['gsap'], null, true);
  wp_enqueue_script('myconsult', "$u/js/main.js", ['lenis', 'gsap-st', 'gsap-split'], mc_ver('js/main.js'), true);

  // three.js (orb + particles) only where the hero is; WP prints the importmap for these ids.
  if (!is_singular() || !has_block('acf/hero')) return;
  $three = "$cdn/three@0.170.0";
  wp_register_script_module('three', "$three/build/three.module.js", [], null);
  wp_register_script_module('three/addons/loaders/GLTFLoader.js', "$three/examples/jsm/loaders/GLTFLoader.js", ['three'], null);
  wp_register_script_module('three/addons/libs/lil-gui.module.min.js', "$three/examples/jsm/libs/lil-gui.module.min.js", [], null);
  wp_enqueue_script_module('myconsult-arcs', "$u/js/arcs.js", ['three', 'three/addons/loaders/GLTFLoader.js', 'three/addons/libs/lil-gui.module.min.js'], mc_ver('js/arcs.js'));
});

add_action('init', function () {
  foreach (glob(__DIR__ . '/blocks/*/block.json') as $f) register_block_type(dirname($f));
});

add_action('acf/init', function () {
  acf_add_options_page(['page_title' => 'Site settings', 'menu_slug' => 'site-settings', 'icon_url' => 'dashicons-admin-site-alt3', 'position' => 59]);
});

add_action('after_switch_theme', function () {
  if (!get_option('myconsult_seeded')) require __DIR__ . '/inc/seed.php';
});

// ---------- template helpers ----------

// Cache-busting ?ver= from the file's mtime, so a re-uploaded theme is picked up without bumping Version.
function mc_ver($file) { return filemtime(get_theme_file_path($file)); }

function mc_arrow($class = 'btn__arrow') {
  return '<svg class="' . $class . '" viewBox="-4 -4 210 168" aria-hidden="true"><circle cx="15" cy="80" r="15.5" opacity="0.25" style="--i:0"/><circle cx="58" cy="80" r="15.5" opacity="0.45" style="--i:1"/><circle cx="100" cy="80" r="15.5" opacity="0.7" style="--i:2"/><circle cx="143" cy="16" r="15.5" style="--i:3"/><circle cx="143" cy="80" r="15.5" style="--i:3"/><circle cx="143" cy="144" r="15.5" style="--i:3"/><circle cx="165" cy="48" r="15.5" style="--i:4"/><circle cx="165" cy="112" r="15.5" style="--i:4"/><circle cx="186" cy="80" r="15.5" style="--i:5"/></svg>';
}

// ACF link field -> the site's dotted-arrow button.
function mc_btn($link, $class = 'btn', $delay = '') {
  if (empty($link['url'])) return '';
  return sprintf('<a class="%s" href="%s"%s><span class="button_label"%s>%s</span>%s</a>',
    esc_attr($class), esc_url($link['url']),
    $link['target'] ? ' target="' . esc_attr($link['target']) . '" rel="noopener"' : '',
    $delay ? ' data-delay="' . esc_attr($delay) . '"' : '',
    esc_html($link['title']), mc_arrow());
}

function mc_menu($location) {
  $l = get_nav_menu_locations();
  return empty($l[$location]) ? [] : (wp_get_nav_menu_items($l[$location]) ?: []);
}

// id="" from the block's Advanced > HTML anchor, for #links.
function mc_anchor($block) {
  return empty($block['anchor']) ? '' : ' id="' . esc_attr($block['anchor']) . '"';
}
