export function scaleOverlayLayers() {
  const wrappers = document.querySelectorAll('.page-wrapper');
  wrappers.forEach(wrapper => {
    const canvas = wrapper.querySelector('canvas');
    const overlayLayer = wrapper.querySelector('.overlay-layer');
    const canvasRect = canvas.getBoundingClientRect();
    const scaleFactor = canvasRect.width / canvas.width;
    overlayLayer.style.transformOrigin = 'top left';
    overlayLayer.style.transform = `scale(${scaleFactor})`;
  });
}
