// Hero arcs: two colour streams of particles meet, swirl into a sphere, and the sphere becomes the orb.
// Everything draws on one WebGL canvas (.arcs__particles) over the stage. Coordinates are design px of the 1440x638 stage.
// One scrubbed progress value p (0..1). The orb story runs on t = p / 0.85; the ring draws over the rest (0.87-1):
//  0.00-0.50  dots ride their arcs to the crossing (720,150); from 0.08 they shed particles that trail behind (comet tails)
//  then       each particle pours through the crossing and swirls onto a spinning sphere (pink clockwise, blue anticlockwise)
//  0.50-1.00  the sphere falls into the ring (centre y 485.5), growing r 30 -> 113.5 (the orb's size)
//  no fade:   where a particle lands it opens a crisp patch that grows and uncovers the real orb underneath, while the
//             particle shrinks into it. The orb is only ever shown through these patches; by ~0.98 they cover the whole ball.
//  ring:     once the orb has landed it draws itself around from the top, both ways, closing at the bottom.
// Arcs and labels: on first view each arc draws itself in after the hero copy, and its label sharpens in as the arc passes over it.
// Reduced motion: no animation, just the finished orb and outlines.
import * as THREE from 'three';
import { createOrb } from './orb.js';

var stage = document.querySelector('.hero__arcs');
if (stage && window.gsap) init();

function init() {
  var still = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var cv = stage.querySelector('.arcs__particles'), movers = stage.querySelectorAll('.arcs__mover'); // movers: [pink, blue]
  var ARCS = [{ cx: 400, from: -61.25, to: 49.6, dir: 1, rgb: [1, 0.35, 0.65] },   // angles about the circle centre (cx, 422), r 420
              { cx: 1040, from: 61.25, to: -49.6, dir: -1, rgb: [0.44, 0.78, 1] }];
  var clamp01 = gsap.utils.clamp(0, 1), TAU = Math.PI * 2, RAD = Math.PI / 180;
  var smooth = function (a, b, x) { x = clamp01((x - a) / (b - a)); return x * x * (3 - 2 * x); };
  var angleAt = function (arc, t) { return arc.from + (arc.to - arc.from) * clamp01(t / 0.5); };
  var sphereAt = function (t) { var u = clamp01((t - 0.5) / 0.5); return { x: 720, y: 150 + 335.5 * u * u, r: 30 + 83.5 * u, spin: TAU * (1 - (1 - t) * (1 - t)) }; }; // one full turn that slows to a stop facing front as it lands

  var parts = [];
  ARCS.forEach(function (arc) {
    for (var i = 0; i < 700; i++) {
      var e = 0.08 + 0.42 * Math.random(), f = 0.7 + 0.3 * Math.random(), D = 0.1 + 0.1 * Math.random(); // shed time, speed vs the dot, swirl time
      var c = e + (0.5 - e) / f;                                              // reaches the crossing (<= 0.68), lands at c + D (<= 0.88)
      var z = 2 * Math.random() - 1, ph = TAU * Math.random(), ring = Math.sqrt(1 - z * z); // uniform point on a unit sphere
      parts.push({ arc: arc, e: e, f: f, c: c, D: D, land: c + D,
        side: Math.random() < 0.5 ? -1 : 1, j0: 3 * Math.random(), spread: 10 + 50 * Math.random(),
        turns: 0.75 + 0.75 * Math.random(), size: 2 + 2.8 * Math.random(),  // dot diameter, design px
        sx: ring * Math.cos(ph), sy: z, sz: ring * Math.sin(ph) });
    }
  });

  // ---------- GPU ----------
  var renderer = new THREE.WebGLRenderer({ canvas: cv, antialias: true, alpha: true });
  renderer.autoClear = false; renderer.setClearColor(0x000000, 0);
  var orb = createOrb(renderer);
  var cam2d = new THREE.OrthographicCamera(0, 1440, 0, 638, -1, 1), quadCam = new THREE.Camera(); // design px, y down
  var N = parts.length;
  function pointsLayer(fragment, blending) {
    var g = new THREE.BufferGeometry();
    g.setAttribute('position', new THREE.BufferAttribute(new Float32Array(N * 3), 3));
    g.setAttribute('aColor', new THREE.BufferAttribute(new Float32Array(N * 3), 3));
    g.setAttribute('aSize', new THREE.BufferAttribute(new Float32Array(N), 1));
    g.setAttribute('aAlpha', new THREE.BufferAttribute(new Float32Array(N), 1));
    var m = new THREE.Points(g, new THREE.ShaderMaterial(Object.assign({
      uniforms: { uScale: { value: 1 } }, depthTest: false, depthWrite: false, transparent: true,
      vertexShader: 'attribute vec3 aColor; attribute float aSize, aAlpha; uniform float uScale; varying vec3 vC; varying float vA;\n' +
        'void main() { vC = aColor; vA = aAlpha; gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); gl_PointSize = aSize * uScale; }',
      fragmentShader: 'varying vec3 vC; varying float vA;\nvoid main() { float r = length(gl_PointCoord * 2.0 - 1.0); if (r > 1.0) discard;\n' + fragment + '\n}'
    }, blending)));
    m.frustumCulled = false;
    var s = new THREE.Scene(); s.add(m);
    return { scene: s, mesh: m, a: g.attributes };
  }
  // the visible particles: glowing dots
  var dots = pointsLayer('  float a = (1.0 - smoothstep(0.5, 1.0, r)) * vA; gl_FragColor = vec4(vC, a);', { blending: THREE.AdditiveBlending });
  // the reveal mask: one crisp disc per landed particle
  var discs = pointsLayer('  gl_FragColor = vec4(1.0, 1.0, 1.0, (1.0 - smoothstep(0.85, 1.0, r)) * vA);',
    { blending: THREE.CustomBlending, blendSrc: THREE.OneFactor, blendDst: THREE.OneFactor, blendSrcAlpha: THREE.OneFactor, blendDstAlpha: THREE.OneFactor });
  var maskRT = new THREE.WebGLRenderTarget(1, 1, { depthBuffer: false });
  // multiplies what is already drawn (the orb) by the mask: dst *= mask
  var cut = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.ShaderMaterial({
    uniforms: { tM: { value: maskRT.texture }, uRes: { value: new THREE.Vector2() } },
    vertexShader: 'void main() { gl_Position = vec4(position.xy, 0.0, 1.0); }',
    fragmentShader: 'uniform sampler2D tM; uniform vec2 uRes;\nvoid main() { gl_FragColor = vec4(0.0, 0.0, 0.0, smoothstep(0.35, 0.65, texture2D(tM, gl_FragCoord.xy / uRes).a)); }',
    depthTest: false, depthWrite: false, transparent: true,
    blending: THREE.CustomBlending, blendSrc: THREE.ZeroFactor, blendDst: THREE.SrcAlphaFactor, blendSrcAlpha: THREE.ZeroFactor, blendDstAlpha: THREE.SrcAlphaFactor
  }));
  var cutScene = new THREE.Scene(); cutScene.add(cut);

  var state = { p: still ? 1 : 0 }, W = 1, H = 1, ring = stage.querySelector('.arcs__ring'), RING = TAU * 132.5, ringShown = -1;
  function pose(t, S) {
    var cs = Math.cos(S.spin), sn = Math.sin(S.spin), D = dots.a, M = discs.a;
    parts.forEach(function (p, i) {
      D.aAlpha.array[i] = 0; M.aAlpha.array[i] = 0;
      if (t < p.e) return;
      var x = p.sx * cs + p.sz * sn;                                         // surface point spun about the vertical axis
      // spread evenly over the disc (a projected sphere crowds its rim), so the reveal fills evenly
      var rho = Math.hypot(x, p.sy), even = rho > 1e-4 ? Math.sqrt(1 - Math.sqrt(Math.max(0, 1 - rho * rho))) / rho : 1;
      var tx = x * even * S.r, ty = p.sy * even * S.r, X, Y, s = 0;
      if (t < p.c) { // comet tail: along the arc behind the dot, drifting off the line as it ages
        var th = (angleAt(p.arc, p.e) + (p.arc.to - p.arc.from) * 2 * p.f * (t - p.e)) * RAD;
        var R = 420 + p.side * (p.j0 + p.spread * (t - p.e));
        X = p.arc.cx + R * Math.sin(th); Y = 422 - R * Math.cos(th);
      } else if (t < p.land) { // swirl from the crossing onto the sphere: radius and angle about its centre, with extra turns
        s = (t - p.c) / p.D;
        var u = 1 - Math.pow(1 - s, 3), vx = 720 + p.side * p.j0 - S.x, vy = 150 - S.y;
        var r0 = Math.hypot(vx, vy), a0 = Math.atan2(vy, vx), a1 = Math.atan2(ty, tx);
        var r = r0 + (Math.hypot(tx, ty) - r0) * u, an = a0 + (Math.atan2(Math.sin(a1 - a0), Math.cos(a1 - a0)) + p.arc.dir * p.turns * TAU) * u;
        X = S.x + r * Math.cos(an); Y = S.y + r * Math.sin(an);
      } else {        // landed: rides the spinning sphere, shrinks into the orb, and its patch grows
        s = 1; X = S.x + tx; Y = S.y + ty;
        var age = t - p.land;
        M.position.array[i * 3] = S.x + tx * 1.2; M.position.array[i * 3 + 1] = S.y + ty * 1.2; // patches reach past the ball: the orb's ribbons poke out of a perfect circle
        M.aSize.array[i] = S.r * (0.06 + 0.3 * smooth(0, 0.12, age)); M.aAlpha.array[i] = 1;
      }
      var grow = t < p.land ? 1 : 1 - smooth(0, 0.1, t - p.land);
      // colour drifts from the stream's colour toward the orb's pink (top) / orange (bottom) as it lands
      var k = smooth(0.6, 1, s) * 0.8, ot = (p.sy + 1) / 2, c = p.arc.rgb;
      D.position.array[i * 3] = X; D.position.array[i * 3 + 1] = Y;
      D.aColor.array[i * 3] = c[0] + (1 - c[0]) * k; D.aColor.array[i * 3 + 1] = c[1] + (0.31 + 0.29 * ot - c[1]) * k; D.aColor.array[i * 3 + 2] = c[2] + (0.63 - 0.29 * ot - c[2]) * k;
      D.aSize.array[i] = p.size * grow; D.aAlpha.array[i] = smooth(0, 0.04, t - p.e) * 0.9;
    });
    ['position', 'aColor', 'aSize', 'aAlpha'].forEach(function (n) { D[n].needsUpdate = true; M[n].needsUpdate = true; });
  }

  // Draw only when something on the canvas changes: the scroll moved (p), or the orb is showing (its colour drifts).
  // At the top of the page (p = 0) the canvas is empty and the loop does nothing.
  var last = performance.now(), visible = true, lastP = -1;
  var firstShed = Math.min.apply(null, parts.map(function (q) { return q.e; })), firstLand = Math.min.apply(null, parts.map(function (q) { return q.land; }));
  orb.ready.then(function () { lastP = -1; });                          // redraw once the model is in
  new IntersectionObserver(function (en) { visible = en[0].isIntersecting; }).observe(stage);
  function frame() {
    var now = performance.now(), dt = Math.min((now - last) / 1000, 0.1); last = now;
    if (!visible) return;
    var t = clamp01(state.p / 0.85), S = sphereAt(t), real = t > 0.995; // real: the reveal is complete, just the orb
    var moved = state.p !== lastP, orbShown = t >= firstLand; lastP = state.p;
    if (!moved && (!orbShown || still)) return;                          // canvas unchanged: keep the last frame
    if (moved) {
      var rp = smooth(0.87, 1, state.p);                                 // ring: one dash growing both ways from the top (path starts there)
      if (rp !== ringShown) { ringShown = rp; ring.style.strokeDasharray = rp * RING + ' ' + (1 - rp) * RING; ring.style.strokeDashoffset = rp * RING / 2; }
      ARCS.forEach(function (arc, k) {
        movers[k].setAttribute('transform', 'rotate(' + angleAt(arc, t) + ' ' + arc.cx + ' 422)');
        movers[k].style.opacity = 1 - smooth(0.42, 0.5, t);
      });
    }
    renderer.setRenderTarget(null); renderer.setScissorTest(false); renderer.setViewport(0, 0, W, H); renderer.clear();
    if (t < firstShed) return;                                           // nothing to draw yet
    if (!real && moved) { pose(t, S); renderer.setRenderTarget(maskRT); renderer.clear(); renderer.render(discs.scene, cam2d); renderer.setRenderTarget(null); }
    if (!orbShown) { renderer.render(dots.scene, cam2d); return; }       // streams only, no orb patches yet
    // the orb, in its square viewport (the model's ink is 113.5/259.2 of it)
    orb.update(dt, S.spin);
    var k = W / 1440, size = 259.2 * S.r / 113.5 * k, x = S.x * k - size / 2, y = H - (S.y * k + size / 2);
    renderer.setViewport(x, y, size, size); renderer.setScissor(x, y, size, size); renderer.setScissorTest(true);
    renderer.render(orb.scene, orb.camera);
    if (!real) renderer.render(cutScene, quadCam);
    renderer.setScissorTest(false); renderer.setViewport(0, 0, W, H);
    if (!real) renderer.render(dots.scene, cam2d);
  }
  function size() {
    var dpr = Math.min(devicePixelRatio, 2); W = stage.clientWidth; H = stage.clientHeight;
    renderer.setPixelRatio(dpr); renderer.setSize(W, H, false);
    maskRT.setSize(Math.round(W * dpr / 2), Math.round(H * dpr / 2)); // mask at half resolution
    cut.material.uniforms.uRes.value.set(W * dpr, H * dpr);
    dots.mesh.material.uniforms.uScale.value = discs.mesh.material.uniforms.uScale.value = W / 1440 * dpr;
    lastP = -1; // resizing clears the canvas and the mask
  }
  window.addEventListener('resize', size); size();
  gsap.ticker.add(frame);
  if (still) return;

  // Outlines start once the orb is loaded and its shaders compiled off the main thread: loading freezes the page briefly, and the
  // draw-in would otherwise stall half-drawn through it. They never start before 1s after page load.
  orb.ready.then(function () { return renderer.compileAsync(orb.scene, orb.camera); }).catch(function () {}).then(function () {
    drawOutlines(stage.querySelectorAll('.arcs path'), stage.querySelectorAll('.arcs__label'), Math.max(0, 1 - performance.now() / 1000));
  });

  // refreshPriority: main.js's reveal triggers are created before this module runs; the pin must be measured
  // first so their starts include its 100% of pin spacing (otherwise everything below fires a screen early).
  var pin = ScrollTrigger.create({ trigger: '.hero', start: 'clamp(bottom bottom)', end: '+=100%', pin: true, refreshPriority: 1 });
  gsap.to(state, { p: 1, ease: 'none',
    scrollTrigger: { trigger: stage, start: 'clamp(top bottom)', end: function () { return pin.end; }, scrub: 1, refreshPriority: 1 } });
  ScrollTrigger.sort();
  ScrollTrigger.refresh();
}

// The arcs draw in once the stage is in view, and each label sharpens in as its arc passes over it
// (CSS hides both until then, see .arcs__label in style.css).
function drawOutlines(arcs, labels, at) {
  var tl = gsap.timeline({ scrollTrigger: { trigger: '.hero__arcs', start: 'top 85%' } });
  // arcs: from the outer end, over the top, to the crossing. The left path starts at its outer end; the right path ends there.
  arcs.forEach(function (p, i) {
    var L = p.getTotalLength();
    tl.fromTo(p, { strokeDasharray: L, strokeDashoffset: i ? -L : L }, { strokeDashoffset: 0, duration: 1.6, ease: 'power2.inOut' }, at + i * 0.15);
  });
  // labels: the same blur-to-sharp letter sweep as the serif headings (js/main.js), Accounting left to right, A.I. right to left
  labels.forEach(function (el, i) {
    var chars = SplitText.create(el, { type: 'chars' }).chars;
    gsap.set(el, { visibility: 'visible' });
    tl.from(chars, { filter: 'blur(12px)', autoAlpha: 0, duration: 1, ease: 'power2.out', stagger: { each: 0.05, from: i ? 'end' : 'start' } }, at + 0.6 + i * 0.15);
  });
}
