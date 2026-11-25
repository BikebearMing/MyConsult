<section class="about"<?= mc_anchor($block) ?>>
  <h2 class="h2 about__title"><?= wp_kses_post(get_field('title')) ?></h2>
  <p class="body about__text"><?= esc_html(get_field('text')) ?></p>
  <?= mc_btn(get_field('button')) ?>
</section>
