<?php
$o = fn($k) => get_field($k, 'option');
$phone = $o('phone'); $email = $o('email');
?>
<footer class="footer">
  <div class="footer__brand" data-reveal>
    <img class="footer__orb" src="<?= get_theme_file_uri('assets/footer-orb.png') ?>" alt="" width="205" height="205">
    <img class="footer__logo" src="<?= get_theme_file_uri('assets/myconsult-logo.svg') ?>" alt="<?= esc_attr(get_bloginfo('name')) ?>" width="114" height="33">
  </div>

  <div class="footer__main">
    <div class="footer__card" data-reveal>
      <div class="footer__cta">
        <h2 class="h2"><?= wp_kses_post($o('cta_title')) ?></h2>
        <p class="body"><?= wp_kses_post($o('cta_address')) ?></p>
        <?= mc_btn($o('cta_button')) ?>
      </div>
      <div class="footer__contact">
        <span class="chip">Contact details</span>
        <p class="body">
          <?php if ($phone): ?><a href="tel:<?= esc_attr(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= esc_html($phone) ?></a><br><?php endif; ?>
          <?php if ($email): ?><a href="mailto:<?= esc_attr(antispambot($email)) ?>"><?= esc_html(antispambot($email)) ?></a><?php endif; ?>
        </p>
        <p class="body"><?= wp_kses_post($o('contact_address')) ?></p>
      </div>
    </div>

    <div class="footer__links" data-reveal data-delay="0.15">
      <span class="chip">Solutions</span>
      <ul class="footer__solutions">
        <?php foreach (mc_menu('footer-solutions') as $item): ?>
        <li><a class="h3" href="<?= esc_url($item->url) ?>"><?= esc_html($item->title) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <ul class="footer__nav">
        <?php foreach (mc_menu('footer') as $item): ?>
        <li><a class="button_label" href="<?= esc_url($item->url) ?>"><?= esc_html($item->title) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="footer__legal">
    <p class="body">&copy; <?= date('Y') ?> <?= esc_html($o('copyright')) ?></p>
    <?php foreach (mc_menu('legal') as $item): ?>
    <a class="body" href="<?= esc_url($item->url) ?>"><?= esc_html($item->title) ?></a>
    <?php endforeach; ?>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
