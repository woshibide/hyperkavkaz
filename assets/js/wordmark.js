// Home page: the HYPERKAVKAZ wordmark, extruded from its SVG and spinning around the vertical axis.
// The material colour is the canvas's CSS `color`, so it follows the stylesheet.

import * as THREE from 'three';
import { SVGLoader } from 'three/addons/loaders/SVGLoader.js';

const SECONDS_PER_TURN = 12;
const DEPTH = 40; // in SVG units; the wordmark is 749 units wide
const FILL = 0.8; // share of the viewport width the wordmark may take

const canvas = document.querySelector('canvas.wordmark');
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');

const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
renderer.setPixelRatio(Math.min(devicePixelRatio, 2));

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(30, 1, 1, 10000);
scene.add(new THREE.HemisphereLight(0xffffff, 0x808080, 1.5));
const light = new THREE.DirectionalLight(0xffffff, 2.5);
light.position.set(1, 1, 2);
scene.add(light);

const svg = await new SVGLoader().loadAsync(canvas.dataset.src);
const shapes = svg.paths.flatMap((path) => path.toShapes());
const geometry = new THREE.ExtrudeGeometry(shapes, {
  depth: DEPTH,
  curveSegments: 16,
  bevelEnabled: true,
  bevelThickness: 2,
  bevelSize: 0.6,
  bevelSegments: 4,
});
geometry.center();
geometry.computeBoundingBox();
const size = geometry.boundingBox.getSize(new THREE.Vector3());

const mesh = new THREE.Mesh(
  geometry,
  new THREE.MeshStandardMaterial({
    color: new THREE.Color(getComputedStyle(canvas).color),
    roughness: 0.35,
    metalness: 0.1,
  }),
);
mesh.scale.y = -1; // SVG y points down, three.js y points up
scene.add(mesh);

// Move the camera back until the wordmark fits: FILL of the width, and never taller than the viewport.
function resize() {
  const { clientWidth: width, clientHeight: height } = canvas;
  renderer.setSize(width, height, false);
  camera.aspect = width / height;
  const halfHeight = Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
  const distance = Math.max(
    size.x / 2 / FILL / (halfHeight * camera.aspect),
    size.y / 2 / FILL / halfHeight,
  );
  camera.position.set(0, 0, distance);
  camera.updateProjectionMatrix();
  renderer.render(scene, camera);
}

// The loop runs on requestAnimationFrame, so it pauses in hidden tabs.
// The timer is connected to the document so the spin resumes without a jump.
const timer = new THREE.Timer();
timer.connect(document);

function animate(time) {
  timer.update(time);
  mesh.rotation.y += (timer.getDelta() / SECONDS_PER_TURN) * Math.PI * 2;
  renderer.render(scene, camera);
}

function toggleMotion() {
  timer.reset();
  renderer.setAnimationLoop(reducedMotion.matches ? null : animate);
}

new ResizeObserver(resize).observe(canvas);
reducedMotion.addEventListener('change', toggleMotion);
resize();
toggleMotion();
