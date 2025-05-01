export function makeDraggable(wrapper, overlayInfo, container) {
  wrapper.addEventListener('mousedown', (e) => {
    if (e.target.classList.contains('resize-handle') || e.target.tagName === 'BUTTON') return;
    e.preventDefault();

    const startX = e.clientX;
    const startY = e.clientY;
    const startLeft = parseFloat(wrapper.style.left);
    const startTop = parseFloat(wrapper.style.top);
    const bounds = container.getBoundingClientRect();

    const onMouseMove = (e) => {
      const dx = e.clientX - startX;
      const dy = e.clientY - startY;
      const dxPercent = (dx / bounds.width) * 100;
      const dyPercent = (dy / bounds.height) * 100;

      let newLeft = startLeft + dxPercent;
      let newTop = startTop + dyPercent;
      newLeft = Math.min(Math.max(newLeft, 0), 100 - parseFloat(wrapper.style.width));
      newTop = Math.min(Math.max(newTop, 0), 100 - parseFloat(wrapper.style.height));

      wrapper.style.left = `${newLeft}%`;
      wrapper.style.top = `${newTop}%`;
      overlayInfo.left = newLeft;
      overlayInfo.top = newTop;
    };

    const onMouseUp = () => {
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', onMouseUp);
    };

    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
  });

  wrapper.addEventListener('touchstart', (e) => {
    if (e.target.classList.contains('resize-handle') || e.target.tagName === 'BUTTON') return;
    const touch = e.touches[0];
    const startX = touch.clientX;
    const startY = touch.clientY;
    const startLeft = parseFloat(wrapper.style.left);
    const startTop = parseFloat(wrapper.style.top);
    const bounds = container.getBoundingClientRect();

    const onTouchMove = (e) => {
      const touch = e.touches[0];
      const dx = touch.clientX - startX;
      const dy = touch.clientY - startY;
      const dxPercent = (dx / bounds.width) * 100;
      const dyPercent = (dy / bounds.height) * 100;

      let newLeft = startLeft + dxPercent;
      let newTop = startTop + dyPercent;
      newLeft = Math.min(Math.max(newLeft, 0), 100 - parseFloat(wrapper.style.width));
      newTop = Math.min(Math.max(newTop, 0), 100 - parseFloat(wrapper.style.height));

      wrapper.style.left = `${newLeft}%`;
      wrapper.style.top = `${newTop}%`;
      overlayInfo.left = newLeft;
      overlayInfo.top = newTop;
    };

    const onTouchEnd = () => {
      document.removeEventListener('touchmove', onTouchMove);
      document.removeEventListener('touchend', onTouchEnd);
    };

    document.addEventListener('touchmove', onTouchMove);
    document.addEventListener('touchend', onTouchEnd);
  }, { passive: false });
}

export function addResizeHandles(wrapper, overlayInfo, container) {
  const positions = ['tl', 'tr', 'bl', 'br', 't', 'b', 'l', 'r'];
  const cursors = {
    tl: 'nwse-resize',
    tr: 'nesw-resize',
    bl: 'nesw-resize',
    br: 'nwse-resize',
    t: 'ns-resize',
    b: 'ns-resize',
    l: 'ew-resize',
    r: 'ew-resize'
  };

  const transforms = {
    tl: 'translate(-50%, -50%)',
    tr: 'translate(50%, -50%)',
    bl: 'translate(-50%, 50%)',
    br: 'translate(50%, 50%)',
    t: 'translate(-50%, -50%)',
    b: 'translate(-50%, 50%)',
    l: 'translate(-50%, -50%)',
    r: 'translate(50%, -50%)'
  };

  positions.forEach(pos => {
    const handle = document.createElement('div');
    handle.className = `resize-handle ${pos}`;
    Object.assign(handle.style, {
      position: 'absolute',
      width: '12px',
      height: '12px',
      background: 'rgba(255,255,255,0.9)',
      border: '1px solid #333',
      borderRadius: '9999px',
      boxShadow: '0 0 2px rgba(0,0,0,0.5)',
      zIndex: '1002',
      cursor: cursors[pos],
      transform: transforms[pos],
      touchAction: 'none'
    });

    if (pos.includes('t')) handle.style.top = '0';
    if (pos.includes('b')) handle.style.bottom = '0';
    if (pos.includes('l')) handle.style.left = '0';
    if (pos.includes('r')) handle.style.right = '0';

    wrapper.appendChild(handle);

    let startX, startY, startW, startH, startL, startT;

    const onMove = (clientX, clientY) => {
      const bounds = container.getBoundingClientRect();
      const dx = (clientX - startX) / bounds.width * 100;
      const dy = (clientY - startY) / bounds.height * 100;

      let newW = startW, newH = startH, newL = startL, newT = startT;

      if (pos.includes('r')) newW = Math.max(5, startW + dx);
      if (pos.includes('l')) {
        newW = Math.max(5, startW - dx);
        newL = Math.max(0, startL + dx);
      }
      if (pos.includes('b')) newH = Math.max(5, startH + dy);
      if (pos.includes('t')) {
        newH = Math.max(5, startH - dy);
        newT = Math.max(0, startT + dy);
      }

      newW = Math.min(newW, 100 - newL);
      newH = Math.min(newH, 100 - newT);

      wrapper.style.width = `${newW}%`;
      wrapper.style.height = `${newH}%`;
      wrapper.style.left = `${newL}%`;
      wrapper.style.top = `${newT}%`;

      overlayInfo.width = newW;
      overlayInfo.height = newH;
      overlayInfo.left = newL;
      overlayInfo.top = newT;
    };

    const onMouseMove = e => onMove(e.clientX, e.clientY);
    const onTouchMove = e => {
      if (e.touches.length > 0) onMove(e.touches[0].clientX, e.touches[0].clientY);
    };

    const stop = () => {
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', stop);
      document.removeEventListener('touchmove', onTouchMove);
      document.removeEventListener('touchend', stop);
    };

    const start = e => {
      e.stopPropagation();
      e.preventDefault();

      const pointer = e.touches?.[0] || e;
      startX = pointer.clientX;
      startY = pointer.clientY;
      startW = parseFloat(wrapper.style.width);
      startH = parseFloat(wrapper.style.height);
      startL = parseFloat(wrapper.style.left);
      startT = parseFloat(wrapper.style.top);

      document.addEventListener('mousemove', onMouseMove);
      document.addEventListener('mouseup', stop);
      document.addEventListener('touchmove', onTouchMove);
      document.addEventListener('touchend', stop);
    };

    handle.addEventListener('mousedown', start);
    handle.addEventListener('touchstart', start, { passive: false });
  });
}