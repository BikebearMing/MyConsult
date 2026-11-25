// Orb: assets/myconsult-orb.glb for three.js. createOrb(renderer) builds its scene; js/arcs.js renders it
// on the hero's shared canvas (one WebGL context, so the particles can reveal it). Visible size: the model's
// ink is 113.5/259.2 of the square viewport it is drawn into. The colour drifts across the surface
// in-shader (hue rotation driven by a flowing 3D wave over object space), so the gradient moves
// inside the ball while geometry, shading, highlights and silhouette stay put.
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { GUI } from 'three/addons/libs/lil-gui.module.min.js';

// Tuned values. Open the page with ?lab for sliders, press "copy values", paste them here.
var P = {
  density: 3.5,   // higher = smaller colour patches
  speed: 1.41,    // overall time scale
  flow: 1.97,     // how fast the pattern slides across the surface
  morph: 1.53,    // how much the pattern changes shape as it moves
  swing: 0.16,    // hue range, radians (0 = no colour change)
  bias: -0.02,    // shifts the whole range: negative = toward violet, positive = toward gold
  saturation: 1.0,
  contrast: 1.0,  // >1 deepens the midtones, whites stay white
  roughness: 0.2,
  clearcoat: 1,
  clearcoatRoughness: 0.12,
  reflections: 1.2, // environment reflection strength
  exposure: 1.25,
  sway: 0.5       // camera sway in degrees so highlights glide
};

// Studio lighting: a dark room with a few large soft emitters. Round lights reflect as round
// highlights; the stock RoomEnvironment's rectangles showed up as polygonal glares.
function studio() {
  var s = new THREE.Scene(), geo = new THREE.SphereGeometry(1, 32, 16);
  s.background = new THREE.Color(0x7a7f96); // bright ambient keeps the white ribbons white
  [[0xffffff, 8, -4, 6, 4, 3.0],   // key: big white above left
   [0xffd9c0, 4, 5, 2, 3, 2.0],    // warm fill right
   [0xffffff, 3, 0, -5, 3, 1.6],   // bounce from below
   [0xff6fd8, 2.5, 4, 4, -5, 1.8]  // magenta rim from behind
  ].forEach(function (l) {
    var m = new THREE.Mesh(geo, new THREE.MeshBasicMaterial({ color: new THREE.Color(l[0]).multiplyScalar(l[1]) }));
    m.position.set(l[2], l[3], l[4]); m.scale.setScalar(l[5]); s.add(m);
  });
  return s;
}
export function createOrb(renderer) {
  renderer.toneMapping = THREE.NeutralToneMapping; // ACES desaturates bright pinks
  var scene = new THREE.Scene();
  scene.environment = new THREE.PMREMGenerator(renderer).fromScene(studio(), 0.04).texture;
  var camera = new THREE.PerspectiveCamera(30, 1, 0.1, 50);
  var U = { uTime: { value: 0 }, uDensity: { value: 0 }, uFlow: { value: 0 }, uMorph: { value: 0 }, uSwing: { value: 0 }, uBias: { value: 0 }, uSat: { value: 1 }, uContrast: { value: 1 } };
  var mat, pivot = new THREE.Group();

  var loaded; // resolves once the model is in the scene
  var ready = new Promise(function (res) { loaded = res; });
  new GLTFLoader().load(new URL('../assets/myconsult-orb.glb', import.meta.url).href, function (gltf) { // module-relative, so it also works inside the WP theme
    gltf.scene.traverse(function (o) {
      if (!o.isMesh) return;
      mat = new THREE.MeshPhysicalMaterial({ map: o.material.map });
      mat.onBeforeCompile = function (s) {
        Object.assign(s.uniforms, U);
        s.vertexShader = s.vertexShader
          .replace('#include <common>', '#include <common>\nvarying vec3 vPos;')
          .replace('#include <begin_vertex>', '#include <begin_vertex>\nvPos = position;');
        s.fragmentShader = s.fragmentShader
          .replace('#include <common>', [
            '#include <common>',
            'uniform float uTime, uDensity, uFlow, uMorph, uSwing, uBias, uSat, uContrast; varying vec3 vPos;',
            // Rotate RGB around the grey axis: changes hue only, so whites and highlights stay white.
            'vec3 hueShift(vec3 c, float a) { const vec3 k = vec3(0.57735); float s = sin(a), co = cos(a);',
            '  return c * co + cross(k, c) * s + k * dot(k, c) * (1.0 - co); }'
          ].join('\n'))
          .replace('#include <map_fragment>', [
            '#include <map_fragment>',
            'vec3 q = vPos * uDensity + vec3(1.0, -0.6, 0.3) * uTime * uFlow;', // slide the field across the surface
            'float m = uTime * uMorph;',
            'float n = 0.5 * sin(q.x + sin(q.y * 1.3 - m)) + 0.5 * sin(q.y * 0.9 + sin(q.z * 1.1 + m));',
            'vec3 c = hueShift(diffuseColor.rgb, n * uSwing + uBias);',
            'c = mix(vec3(dot(c, vec3(0.2126, 0.7152, 0.0722))), c, uSat);', // saturation
            'diffuseColor.rgb = clamp(pow(c, vec3(uContrast)), 0.0, 1.0);'      // contrast
          ].join('\n'));
      };
      o.material = mat;
    });
    gltf.scene.position.sub(new THREE.Box3().setFromObject(gltf.scene).getCenter(new THREE.Vector3()));
    pivot.add(gltf.scene); // turn about the model's centre
    scene.add(pivot);
    loaded();
  });

  var still = matchMedia('(prefers-reduced-motion: reduce)').matches, sec = 0;
  // Advance and pose the orb; the caller renders orb.scene with orb.camera into a square viewport.
  // spin (radians) comes from the hero scroll.
  function update(dt, spin) {
    if (!still) { sec += dt; U.uTime.value += dt * P.speed; }
    U.uDensity.value = P.density; U.uFlow.value = P.flow; U.uMorph.value = P.morph; U.uSwing.value = P.swing; U.uBias.value = P.bias;
    U.uSat.value = P.saturation; U.uContrast.value = P.contrast;
    if (mat) { mat.roughness = P.roughness; mat.clearcoat = P.clearcoat; mat.clearcoatRoughness = P.clearcoatRoughness; mat.envMapIntensity = P.reflections; }
    renderer.toneMappingExposure = P.exposure;
    pivot.rotation.y = spin; // the hero scroll turns it; it rests facing front (0)
    var a = Math.sin(sec * 0.4) * P.sway * Math.PI / 180;
    camera.position.set(Math.sin(a) * 3.96, 1.06, Math.cos(a) * 3.96);
    camera.lookAt(0, 0, 0);
  }

  // ---------- lab (?lab in the URL) ----------
  if (new URLSearchParams(location.search).has('lab')) {
    var gui = new GUI({ title: 'Orb lab' });
    var f = gui.addFolder('movement');
    f.add(P, 'density', 0.5, 12, 0.1); f.add(P, 'speed', 0, 2, 0.01); f.add(P, 'flow', 0, 3, 0.01); f.add(P, 'morph', 0, 2, 0.01);
    f.add(P, 'swing', 0, 3, 0.01); f.add(P, 'bias', -2, 2, 0.01); f.add(P, 'sway', 0, 15, 0.5);
    f = gui.addFolder('look');
    f.add(P, 'saturation', 0, 2.5, 0.01); f.add(P, 'contrast', 0.5, 2.5, 0.01); f.add(P, 'roughness', 0, 1, 0.01);
    f.add(P, 'clearcoat', 0, 1, 0.01); f.add(P, 'clearcoatRoughness', 0, 1, 0.01); f.add(P, 'reflections', 0, 3, 0.01); f.add(P, 'exposure', 0.3, 2.5, 0.01);
    gui.add({ copy: function () {
      var txt = JSON.stringify(P, null, 2);
      (navigator.clipboard ? navigator.clipboard.writeText(txt) : Promise.reject()).catch(function () { console.log(txt); });
    } }, 'copy').name('copy values');
  }

  return { scene: scene, camera: camera, update: update, ready: ready };
}
