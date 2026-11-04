import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

const canvas = document.querySelector('.emblem-canvas');
const stage = canvas.closest('.emblem');
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');

function studioEnvironment(renderer) {
  const studio = new THREE.Scene();
  studio.background = new THREE.Color().setScalar(0.025);

  // Softboxes are visible in the metal's reflections, outside the camera scene.
  const softboxes = [
    { position: [0, 0.35, 6], size: [10, 8], strength: 0.60 },
    { position: [0, 3.5, 1.4], size: [4.5, 1.3], strength: 3.6 },
    { position: [-3.1, -2.6, 1.1], size: [3.4, 1.1], strength: 3 },
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

function lightEmblem(scene) {
  const top = new THREE.DirectionalLight(0xfffaf5, 1.45);
  top.position.set(0.15, 3.8, 2.6);
  top.castShadow = true;
  top.shadow.mapSize.set(1024, 1024);
  Object.assign(top.shadow.camera, { left: -1.65, right: 1.65, top: 1.85, bottom: -1.85, near: 2, far: 7 });
  top.shadow.normalBias = 0.008;
  top.shadow.bias = -0.00015;
  top.shadow.radius = 2;

  const lowerLeft = new THREE.PointLight(0xe6edff, 2.8, 4.5);
  lowerLeft.position.set(-1.53, -1.30, 0.72);

  const inset = new THREE.SpotLight(0xfff5f1, 4.8, 5, 0.62, 0.94);
  inset.position.set(-0.06, -0.26, 1.42);
  inset.target.position.set(0.02, -0.22, 0);
  scene.add(top, lowerLeft, inset, inset.target);
}

async function showEmblem() {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
  try {
    const [{ scene: model }, enamelBump] = await Promise.all([
      new GLTFLoader().loadAsync(canvas.dataset.src),
      new THREE.TextureLoader().loadAsync(canvas.dataset.bumpSrc),
    ]);
    // This is a linear height texture in glTF's UV orientation.
    enamelBump.flipY = false;
    const bounds = new THREE.Box3().setFromObject(model);
    const size = bounds.getSize(new THREE.Vector3());
    model.position.sub(bounds.getCenter(new THREE.Vector3()));
    model.traverse((part) => {
      if (!part.isMesh) return;
      part.castShadow = true;
      part.receiveShadow = true;
      if (part.material.userData.enamelRelief) {
        part.material.bumpMap = enamelBump;
        part.material.bumpScale = 0.017;
      }
    });

    const pivot = new THREE.Group();
    pivot.add(model);
    const scene = new THREE.Scene();
    scene.add(pivot);
    lightEmblem(scene);

    let environment;
    function lightScene() {
      environment?.dispose();
      environment = studioEnvironment(renderer);
      scene.environment = environment.texture;
    }
    lightScene();

    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
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

await showEmblem();
