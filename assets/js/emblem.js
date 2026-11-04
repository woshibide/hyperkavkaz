import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

const canvas = document.querySelector('.emblem-canvas');
const stage = canvas.closest('.emblem');
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
const spotLighting = 'getSpotLightInfo( spotLight, geometryPosition, directLight );';

function excludeInsetLight(shader) {
  // The inset is the only spotlight. Preserve the leather's other lights and reflections.
  shader.fragmentShader = shader.fragmentShader.replace(
    '#include <lights_fragment_begin>',
    THREE.ShaderChunk.lights_fragment_begin.replace(spotLighting, spotLighting + "\n\t\tdirectLight.color = vec3( 0.0 );"),
  );
}

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
      if (part.material.name === 'Black pebbled leather') {
        part.material.onBeforeCompile = excludeInsetLight;
      }
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

    const tilt = new THREE.Vector2();
    const targetTilt = new THREE.Vector2();
    const duration = 2400;
    let revealStart = null;
    let previousFrame = null;
    let contextLost = false;

    function stopAnimation() {
      renderer.setAnimationLoop(null);
      previousFrame = null;
    }

    function animate(now) {
      const elapsed = previousFrame === null ? 1 / 60 : Math.min((now - previousFrame) / 1000, 0.1);
      previousFrame = now;
      tilt.lerp(targetTilt, 1 - Math.exp(-8 * elapsed));
      const turn = revealStart === null ? 0 : 1 - THREE.MathUtils.smootherstep(now - revealStart, 0, duration);

      if (turn === 0 && tilt.distanceToSquared(targetTilt) < 0.0000001) {
        revealStart = null;
        tilt.copy(targetTilt);
        stopAnimation();
      }

      pivot.rotation.set(0.06 * turn + tilt.x, -0.38 * turn + tilt.y, 0);
      render();
    }

    function settle() {
      stopAnimation();
      revealStart = null;
      tilt.set(0, 0);
      targetTilt.set(0, 0);
      pivot.rotation.set(0, 0, 0);
      render();
    }

    stage.addEventListener('pointermove', (event) => {
      if (event.pointerType !== 'mouse' || reducedMotion.matches || contextLost) return;

      const { left, top, width, height } = stage.getBoundingClientRect();
      const x = THREE.MathUtils.clamp(2 * (event.clientX - left) / width - 1, -1, 1);
      const y = THREE.MathUtils.clamp(2 * (event.clientY - top) / height - 1, -1, 1);
      targetTilt.set(y * 0.05, x * 0.07);
      renderer.setAnimationLoop(animate);
    });

    function resetPointerTilt() {
      if (targetTilt.lengthSq() === 0 || reducedMotion.matches || contextLost) return;
      targetTilt.set(0, 0);
      renderer.setAnimationLoop(animate);
    }

    stage.addEventListener('pointerleave', resetPointerTilt);
    window.addEventListener('blur', resetPointerTilt);
    new ResizeObserver(resize).observe(canvas);
    reducedMotion.addEventListener('change', () => {
      if (reducedMotion.matches && !contextLost) settle();
    });

    canvas.addEventListener('webglcontextlost', () => {
      contextLost = true;
      stopAnimation();
      stage.classList.remove('is-rendered');
    });
    canvas.addEventListener('webglcontextrestored', () => {
      contextLost = false;
      lightScene();
      settle();
      stage.classList.add('is-rendered');
    });

    // Open at an angle to show the relief, then ease into the pointer tilt.
    if (!reducedMotion.matches) {
      revealStart = performance.now();
      pivot.rotation.set(0.06, -0.38, 0);
      renderer.setAnimationLoop(animate);
    }
    resize();
    stage.classList.add('is-rendered');
  } catch (error) {
    renderer.dispose();
    throw error;
  }
}

await showEmblem();
