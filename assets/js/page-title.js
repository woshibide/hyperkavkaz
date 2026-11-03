// Page titles (.page-title): one line of script, as wide as the page column,
// but never taller than MAX_HEIGHT of the screen.
// A title too long to reach MIN_SIZE on one line (a post title, say) wraps at MIN_SIZE instead.
// Script swashes reach far outside the letters' advance box, so the title is fitted
// and centred by its ink, measured on a canvas, and its box is made exactly as tall as the ink.
// Without JavaScript the CSS size in main.css applies.

const MAX_HEIGHT = 0.5; // share of the viewport height the title's ink may take
const MIN_SIZE = 64; // px, the smallest one-line size before the title wraps
const SAMPLE = 100; // px, the font size measured at

const titles = document.querySelectorAll('.page-title');
const context = document.createElement('canvas').getContext('2d');

function fit(title) {
  const style = getComputedStyle(title);
  const text = title.textContent.trim();
  context.font = `${style.fontWeight} ${SAMPLE}px ${style.fontFamily}`;
  const ink = context.measureText(text);
  const inkWidth = ink.actualBoundingBoxLeft + ink.actualBoundingBoxRight;
  const inkHeight = ink.actualBoundingBoxAscent + ink.actualBoundingBoxDescent;

  const scale = Math.min(title.clientWidth / inkWidth, (innerHeight * MAX_HEIGHT) / inkHeight);

  if (SAMPLE * scale < MIN_SIZE) {
    Object.assign(title.style, { fontSize: `${MIN_SIZE}px`, lineHeight: '', whiteSpace: '', translate: '' });
    return;
  }

  const lineHeight = inkHeight * scale;

  // Centred text centres the advance box; move the ink's centre there instead.
  const dx = (ink.width / 2 - (ink.actualBoundingBoxRight - ink.actualBoundingBoxLeft) / 2) * scale;
  // The line box centres the font's own ascent and descent; move the ink's top to the box's top.
  const baseline = lineHeight / 2 + ((ink.fontBoundingBoxAscent - ink.fontBoundingBoxDescent) * scale) / 2;
  const dy = ink.actualBoundingBoxAscent * scale - baseline;

  Object.assign(title.style, {
    fontSize: `${SAMPLE * scale}px`,
    lineHeight: `${lineHeight}px`,
    whiteSpace: 'nowrap',
    translate: `${dx}px ${dy}px`,
  });
}

function fitAll() {
  titles.forEach(fit);
}

if (titles.length > 0) {
  // Measuring needs the real font, not the fallback.
  const fonts = [...titles].map((title) => {
    const style = getComputedStyle(title);
    return document.fonts.load(`${style.fontWeight} ${SAMPLE}px ${style.fontFamily}`, title.textContent);
  });
  await Promise.all(fonts);
  fitAll();
  addEventListener('resize', fitAll);
}
