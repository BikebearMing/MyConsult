<?php
// Hero. Side cards and the arcs drawing are decorative; the orb + particles are js/arcs.js + js/orb.js.
?>
<section class="hero"<?= mc_anchor($block) ?>>
  <p class="h5 hero__eyebrow" data-delay="0"><?= esc_html(get_field('eyebrow')) ?></p>
  <h1 class="h1 hero__title" data-delay="0.2"><?= esc_html(get_field('title')) ?></h1>
  <p class="body hero__text" data-delay="0.8"><?= esc_html(get_field('text')) ?></p>

  <div class="hero__stack hero__stack--left" aria-hidden="true">
    <div class="stat stat--orders" data-reveal data-delay="1.1">
      <div class="stat__label">Total orders</div>
      <div class="stat__value">1054</div>
      <div class="stat__delta">+330 today</div>
      <svg class="stat__chart" viewBox="0 0 184 94"><path d="M114 10V83"/><path d="M104.5 77.5Q109 79 114 77C124 71 134 50 146 38C156 29 164 27 172 27.5Q176 27.8 178.5 28"/><circle cx="114" cy="77" r="2"/></svg>
      <span class="stat__tag">20 total</span>
    </div>
    <div class="stat stat--sales" data-reveal data-delay="1.4">
      <div class="stat__label">Net sales</div>
      <div class="stat__row"><span class="stat__value">$19,750</span><span class="stat__delta">+15%</span></div>
      <div class="bars"><i style="--h:50%"></i><i class="c" style="--h:45%"></i><i class="m" style="--h:55%"></i><i class="c" style="--h:45%"></i><i style="--h:50%"></i><i style="--h:55%"></i><i style="--h:63%"></i><i class="m" style="--h:79%"></i><i class="m" style="--h:87%"></i><i class="c" style="--h:68%"></i><i style="--h:74%"></i><i class="m" style="--h:100%"></i></div>
    </div>
  </div>

  <div class="hero__stack hero__stack--right" aria-hidden="true">
    <div class="ai-card" data-reveal data-delay="1.25">
      <div class="ai-card__head"><i class="ai-orb"></i><span class="ai-card__bar"><i></i></span></div>
      <div class="ai-card__body">
        <div class="ai-card__hint">Helping you with...</div>
        <div class="ai-card__text">Summarizing your account datas and history...</div>
      </div>
    </div>
    <div class="ai-pill" data-reveal data-delay="1.55"><i class="ai-orb"></i>Analyzing data...</div>
  </div>

  <div class="hero__arcs">
    <svg class="arcs" viewBox="0 0 1440 638" aria-hidden="true">
      <defs>
        <linearGradient id="arc-l" gradientUnits="userSpaceOnUse" x1="32" x2="760">
          <stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset="0.24" stop-color="#fff" stop-opacity="0.25"/>
          <stop offset="0.43" stop-color="#fff"/><stop offset="0.8" stop-color="#fff"/><stop offset="0.92" stop-color="#CBECFD" stop-opacity="0.35"/><stop offset="1" stop-color="#CBECFD" stop-opacity="0"/>
        </linearGradient>
        <linearGradient id="arc-r" gradientUnits="userSpaceOnUse" x1="680" x2="1408">
          <stop offset="0" stop-color="#CBECFD" stop-opacity="0"/><stop offset="0.08" stop-color="#CBECFD" stop-opacity="0.35"/><stop offset="0.2" stop-color="#fff"/>
          <stop offset="0.57" stop-color="#fff"/><stop offset="0.76" stop-color="#fff" stop-opacity="0.25"/><stop offset="1" stop-color="#fff" stop-opacity="0"/>
        </linearGradient>
        <linearGradient id="ring" x1="0.6" y1="0" x2="0.3" y2="1" gradientTransform="rotate(90 0.5 0.5)">
          <stop offset="0" stop-color="#fff"/><stop offset="0.6" stop-color="#fff" stop-opacity="0.55"/><stop offset="1" stop-color="#fff" stop-opacity="0.08"/>
        </linearGradient>
      </defs>
      <circle class="arcs__dome" cx="720" cy="2811" r="2322"/>
      <circle class="arcs__ring" cx="720" cy="485.5" r="132.5" stroke="url(#ring)" transform="rotate(-90 720 485.5)"/>
      <path d="M32 220A420 420 0 0 1 760 205.7" stroke="url(#arc-l)"/>
      <path d="M680 205.7A420 420 0 0 1 1408 220" stroke="url(#arc-r)"/>
      <g class="arcs__mover arcs__mover--pink" transform="rotate(-61.25 400 422)"><circle class="arcs__dot arcs__dot--pink" cx="400" cy="2" r="3.5"/></g>
      <g class="arcs__mover arcs__mover--blue" transform="rotate(61.25 1040 422)"><circle class="arcs__glow" cx="1040" cy="2" r="7"/><circle class="arcs__dot arcs__dot--blue" cx="1040" cy="2" r="3.75"/></g>
    </svg>
    <p class="arcs__label arcs__label--left"><?= esc_html(get_field('arc_left_label')) ?></p>
    <p class="arcs__label arcs__label--right"><?= esc_html(get_field('arc_right_label')) ?></p>
    <canvas class="arcs__particles" role="img" aria-label="MyConsult orb"></canvas>
  </div>
</section>
