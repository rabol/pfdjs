import { makeDraggable, addResizeHandles } from './interactions';

export const overlaysData = [];

export function insertOverlay(overlayInfo) {
  console.log('[insertOverlay] inserting overlay:', overlayInfo);

  const overlayLayer = document.querySelector(`.overlay-layer[data-page-number="${overlayInfo.pageNumber}"]`);
  if (!overlayLayer) {
    console.warn(`[insertOverlay] No overlay layer found for page ${overlayInfo.pageNumber}`);
    return;
  }

  const wrapper = document.createElement('div');
  wrapper.className = 'overlay-wrapper';
  Object.assign(wrapper.style, {
    position: 'absolute',
    left: `${overlayInfo.left}%`,
    top: `${overlayInfo.top}%`,
    width: `${overlayInfo.width}%`,
    height: `${overlayInfo.height}%`,
    border: '2px dashed red',
    zIndex: '1000',
    cursor: 'move',
    pointerEvents: 'auto',
    background: '#f9f9f9'
  });

  const img = new Image();
  img.src = overlayInfo.src;
  Object.assign(img.style, {
    width: '100%',
    height: '100%',
    pointerEvents: 'none'
  });

  const deleteButton = document.createElement('button');
  deleteButton.innerText = '✖';
  Object.assign(deleteButton.style, {
    position: 'absolute',
    top: '0',
    right: '0',
    background: 'red',
    color: 'white',
    border: 'none',
    padding: '2px 5px',
    cursor: 'pointer',
    zIndex: '1001',
    fontSize: '14px'
  });

  deleteButton.addEventListener('click', (event) => {
    event.stopPropagation();
    const index = overlaysData.indexOf(overlayInfo);
    if (index !== -1) overlaysData.splice(index, 1);
    wrapper.remove();
  });

  wrapper.appendChild(deleteButton);
  wrapper.appendChild(img);
  overlayLayer.appendChild(wrapper);

  makeDraggable(wrapper, overlayInfo, overlayLayer);
  addResizeHandles(wrapper, overlayInfo, overlayLayer);
}

function getClosestPage() {
  const container = document.getElementById('viewerContainer');
  const pages = Array.from(container.querySelectorAll('.page-wrapper'));
  const containerTop = container.scrollTop;
  const containerCenter = containerTop + container.clientHeight / 2;
  return pages.reduce((closest, page) => {
    const pageTop = page.offsetTop;
    const pageHeight = page.offsetHeight;
    const pageCenter = pageTop + pageHeight / 2;
    const distance = Math.abs(pageCenter - containerCenter);
    return distance < closest.distance ? { el: page, distance } : closest;
  }, { el: null, distance: Infinity }).el;
}

export function restoreOverlays() {
  console.log('[restoreOverlays] called');
  console.log('[restoreOverlays] window.initialOverlays:', window.initialOverlays);

  overlaysData.push(...(window.initialOverlays || []));
  overlaysData.forEach(insertOverlay);
}

window.addEventListener('overlayUploaded', e => {
  const imagePath = e.detail.path;
  const wrapper = getClosestPage();
  if (!wrapper) return;

  const overlayLayer = wrapper.querySelector('.overlay-layer');
  const overlayInfo = {
    pageNumber: parseInt(overlayLayer.dataset.pageNumber, 10),
    left: 40,
    top: 40,
    width: 20,
    height: 20,
    src: imagePath,
  };

  overlaysData.push(overlayInfo);
  insertOverlay(overlayInfo);
});