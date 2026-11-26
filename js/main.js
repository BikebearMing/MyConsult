(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !window.gsap || !window.Lenis) return;

  gsap.registerPlugin(ScrollTrigger, SplitText);
  document.documentElement.classList.add('js');

  // ---------- lenis ----------
  // anchors: true makes Lenis handle #links; set scroll-padding-top on html for the sticky-header offset.
  var lenis = window.lenis = new Lenis({ anchors: true });
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
  // Lag smoothing stays on (Lenis' docs turn it off): the orb's load and shader compile freeze the main
  // thread, and with it off every intro tween running at the time would jump to its end.

  // ---------- reveals the page can't scroll far enough to reach ----------
  // A reveal near the bottom of the page can have its start below the furthest scroll. Reaching the bottom plays those.
  // (clamp() would fix that too, but it also clamps the hero's starts to 0, which only fire after the first scroll.)
  var reveals = [];
  function late(anim) { reveals.push(anim); return anim; }
  ScrollTrigger.create({ start: function () { return ScrollTrigger.maxScroll(window) - 1; }, onEnter: function () {
    reveals.forEach(function (a) { if (a.scrollTrigger && a.scrollTrigger.start > ScrollTrigger.maxScroll(window) - 1) a.play(); });
  } });

  // Reveals play once without `once: true`: the default toggleActions (play none none none) never reverse.
  // `once` kills each trigger as it fires, and on a reload part-way down the page every trigger above the fold
  // fires during ScrollTrigger's refresh loop, shrinking the list under it and crashing it (the rest of this file then never ran).

  // ---------- serif heading reveal ----------
  // Characters sharpen out of a heavy blur, sweeping left to right along each line.
  // Scroll-triggered, plays once. Applies to every Instrument Serif heading automatically.
  // data-delay="seconds" on any revealed element offsets it, for sequencing (see the hero).
  gsap.utils.toArray('h1, h2, h3, .h1, .h2, .h3').forEach(function (el) {
    var d = +el.dataset.delay || 0;
    SplitText.create(el, {
      type: 'words,chars', // words keep chars from wrapping mid-word
      wordsClass: 'word',
      charsClass: 'char',
      autoSplit: true, // waits for fonts, re-splits on resize
      onSplit: function (self) {
        // Line each character's gradient slice up with its position in the heading.
        var left = el.getBoundingClientRect().left, width = el.offsetWidth;
        self.chars.forEach(function (c) {
          c.style.backgroundSize = width + 'px 100%';
          c.style.backgroundPosition = (left - c.getBoundingClientRect().left) + 'px 0';
        });
        // Resting text colour (resolves var(--text)); chars start fully transparent so the gradient shows through.
        var rgb = gsap.utils.splitColor(getComputedStyle(el).color);
        var solid = 'rgb(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ')';
        gsap.set(self.chars, { color: 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',0)' });
        gsap.set(el, { visibility: 'visible' });

        var stagger = Math.min(0.02, 1 / self.chars.length); // per-char, but the whole sweep never exceeds ~1s
        return late(gsap.timeline({ scrollTrigger: { trigger: el, start: 'top 85%' } }))
          .from(self.chars, { filter: 'blur(20px)', autoAlpha: 0, duration: 1.2, ease: 'power2.out', stagger: stagger }, d)
          .to(self.chars, { color: solid, duration: 0.8, ease: 'power1.inOut', stagger: stagger }, d + 0.6) // gradient -> plain
          .set(self.chars, { backgroundImage: 'none' }); // nothing left behind
      }
    });
  });

  // ---------- sans text reveal (.h5, .body, .button_label) ----------
  // Letters swing up into place line by line as each line scrolls in. Plays once.
  // Adapted from madewithgsap effect 058; the original also swings lines back out at the top.
  document.fonts.ready.then(function () {
    gsap.utils.toArray('h5, .h5, .body, .button_label').forEach(function (el) {
      // services body text is not animated, just shown (its slides carry a lot of text)
      if (el.matches('.services .body')) return gsap.set(el, { visibility: 'visible' });
      var split = SplitText.create(el, { type: 'words,chars', mask: 'chars', charsClass: 'char' });
      gsap.set(el, { visibility: 'visible' });

      // Group characters by line (same top edge). No line wrappers, so the text still reflows on resize.
      var lines = {};
      split.chars.forEach(function (c) {
        var top = Math.round(c.parentNode.getBoundingClientRect().top);
        (lines[top] = lines[top] || []).push(c);
      });

      var swing = { rotate: -80, xPercent: -14, duration: 0.4, stagger: 0.007, ease: 'back.out(1.1)' };
      // With data-delay: one trigger for the element, lines follow each other. Without: each line triggers as it scrolls in.
      if ('delay' in el.dataset) {
        var tl = late(gsap.timeline({ scrollTrigger: { trigger: el, start: 'top 90%' } }));
        Object.keys(lines).forEach(function (top, i) { tl.from(lines[top], swing, +el.dataset.delay + i * 0.12); });
      } else Object.keys(lines).forEach(function (top) {
        late(gsap.from(lines[top], Object.assign({ scrollTrigger: { trigger: lines[top][0], start: 'bottom 80%' } }, swing)));
      });
    });
  });

  // ---------- [data-reveal] blocks (hero cards) ----------
  // Block rises out of a blur, then its insides animate. Delays go in the timeline, not the tween:
  // ScrollTrigger skips a tween's own delay.
  gsap.utils.toArray('[data-reveal]').forEach(function (el) {
    var d = +el.dataset.delay || 0;
    var tl = late(gsap.timeline({ scrollTrigger: { trigger: el, start: 'top 90%' } }))
      .from(el, { autoAlpha: 0, y: '2vw', filter: 'blur(12px)', duration: 1.1, ease: 'power3.out', clearProps: 'filter' }, d); // a leftover blur(0) keeps a filter layer
    function part(sel, vars, at) { var t = el.querySelectorAll(sel); if (t.length) tl.from(t, vars, d + at); }
    part('.bars i', { scaleY: 0, transformOrigin: '50% 100%', duration: 0.8, ease: 'power3.out', stagger: 0.04, clearProps: 'transform' }, 0.3);
    part('.stat__chart', { clipPath: 'inset(0 45% 0 0)', duration: 1.2, ease: 'power2.inOut' }, 0.3);
    part('.stat__tag', { autoAlpha: 0, y: 4, duration: 0.5, ease: 'power2.out' }, 1.1);
    part('.ai-card__bar i', { width: 0, duration: 1.4, ease: 'power2.out', clearProps: 'width' }, 0.4);
  });

  // ---------- header ----------
  // Drops in on load; its labels then swing in (data-delay, sans reveal above). clearProps: a leftover
  // filter would switch off the bar's backdrop blur.
  gsap.from('.header', { y: '-1.5vw', autoAlpha: 0, filter: 'blur(10px)', duration: 1, ease: 'power3.out', clearProps: 'filter' });

  // hero arcs scroll animation lives in js/arcs.js (it needs three.js)

  // the logo strip only runs while it is on screen (see .is-in in style.css)
  gsap.utils.toArray('.logos').forEach(function (el) { ScrollTrigger.create({ trigger: el, toggleClass: 'is-in' }); });

  // ---------- card images (home services cards, about what-we-do cards) ----------
  // Each card's two dashboard images fade in one after the other (back first) when the card scrolls into view,
  // a beat after the card to its left.
  gsap.utils.toArray('.service, .what-card').forEach(function (card) {
    var i = Array.prototype.indexOf.call(card.parentNode.children, card);
    var imgs = card.querySelectorAll('.service__img, .what-card__img');
    if (imgs.length) late(gsap.timeline({ scrollTrigger: { trigger: card, start: 'top 75%' } }))
      .from(imgs, { autoAlpha: 0, y: '1.2vw', duration: 1.1, ease: 'power2.out', stagger: 0.25 }, i % 3 * 0.15);
  });

  // ---------- logos marquee ----------
  // Repeat the set until half the track is at least a screen wide; CSS then moves the track left by half and loops.
  // Sizes are all vw, so the count never changes on resize. Copies are hidden from screen readers.
  gsap.utils.toArray('.logos__track').forEach(function (track) {
    var set = Array.prototype.slice.call(track.children), copies = (Math.ceil(innerWidth / track.scrollWidth) + 1) * 2; // +1 spare: font loading can shift widths
    for (var n = 1; n < copies; n++) set.forEach(function (li) {
      var c = li.cloneNode(true); c.setAttribute('aria-hidden', 'true'); track.appendChild(c);
    });
  });
})();
