import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

const canvas = document.querySelector('.emblem-canvas');
const stage = canvas.closest('.emblem');
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');

function studioEnvironment(renderer) {
  const studio = new THREE.Scene();
  studio.background = new THREE.Color().setScalar(0.055);

  // Softboxes are visible in the metal's reflections, outside the camera scene.
  const softboxes = [
    { position: [-2, 3, 4], size: [5, 3], strength: 2.4 },
    { position: [3, 0.5, 4], size: [2, 5], strength: 1.45 },
    { position: [0, -3, 3], size: [5, 1], strength: 0.75 },
    { position: [-3, 1, -2], size: [2, 4], strength: 1.8 },
  ];
  for (const { position, size, strength } of softboxes) {
    const box = new THREE.Mesh(
      new THREE.PlaneGeometry(...size),
      new THREE.MeshBasicMaterial({ color: new THREE.Color().setScalar(strength) }),
    );
    box.position.set(...position);
    box.lookAt(0, 0, 0);
    studio.add(box);
  }

  const pmrem = new THREE.PMREMGenerator(renderer);
  const environment = pmrem.fromScene(studio, 0.05);
  pmrem.dispose();
  for (const box of studio.children) {
    box.geometry.dispose();
    box.material.dispose();
  }
  return environment;
}

async function showEmblem() {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
  try {
    const { scene: model } = await new GLTFLoader().loadAsync(canvas.dataset.src);
    const bounds = new THREE.Box3().setFromObject(model);
    const size = bounds.getSize(new THREE.Vector3());
    model.position.sub(bounds.getCenter(new THREE.Vector3()));

    const pivot = new THREE.Group();
    pivot.add(model);
    const scene = new THREE.Scene();
    scene.add(pivot);
    const key = new THREE.DirectionalLight(0xfff6f0, 1.65);
    key.position.set(-3, 4, 6);
    scene.add(key);

    let environment;
    function lightScene() {
      environment?.dispose();
      environment = studioEnvironment(renderer);
      scene.environment = environment.texture;
    }
    lightScene();

    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0.1, 20);
    camera.position.z = 7;

    function render() {
      renderer.render(scene, camera);
    }

    function resize() {
      const { clientWidth: width, clientHeight: height } = canvas;
      if (!width || !height) return;

      renderer.setSize(width, height, false);
      const aspect = width / height;
      const halfHeight = Math.max(size.y / 0.86, size.x / 0.84 / aspect) / 2;
      camera.left = -halfHeight * aspect;
      camera.right = halfHeight * aspect;
      camera.top = halfHeight;
      camera.bottom = -halfHeight;
      camera.updateProjectionMatrix();
      render();
    }

    function settle() {
      renderer.setAnimationLoop(null);
      pivot.rotation.set(0, 0, 0);
      render();
    }

    new ResizeObserver(resize).observe(canvas);
    reducedMotion.addEventListener('change', () => {
      if (reducedMotion.matches) settle();
    });

    canvas.addEventListener('webglcontextlost', () => {
      renderer.setAnimationLoop(null);
      stage.classList.remove('is-rendered');
    });
    canvas.addEventListener('webglcontextrestored', () => {
      lightScene();
      settle();
      stage.classList.add('is-rendered');
    });

    const duration = 2400;
    pivot.rotation.set(0.06, -0.38, 0);
    if (reducedMotion.matches) pivot.rotation.set(0, 0, 0);
    resize();
    stage.classList.add('is-rendered');

    // Open from a slight angle to show the relief, then leave the panels front-on.
    if (!reducedMotion.matches) {
      const start = performance.now();
      renderer.setAnimationLoop((now) => {
        const turn = 1 - THREE.MathUtils.smootherstep(now - start, 0, duration);
        pivot.rotation.set(0.06 * turn, -0.38 * turn, 0);
        if (turn === 0) settle();
        else render();
      });
    }
  } catch (error) {
    renderer.dispose();
    throw error;
  }
}

showEmblem().catch(() => {
  // The reference image is already visible if the model or WebGL is unavailable.
});
