export function makeDraggable(wrapper, overlayInfo, container) {
  function startDrag(e) {
    if (e.target.classList.contains('resize-handle')) return;
    e.preventDefault();

    const isTouch = e.type.startsWith('touch');
    const startX = isTouch ? e.touches[0].clientX : e.clientX;
    const startY = isTouch ? e.touches[0].clientY : e.clientY;
    const startLeft = parseFloat(wrapper.style.left);
    const startTop = parseFloat(wrapper.style.top);
    const bounds = container.getBoundingClientRect();

    function onMove(ev) {
      const clientX = ev.type.startsWith('touch') ? ev.touches[0].clientX : ev.clientX;
      const clientY = ev.type.startsWith('touch') ? ev.touches[0].clientY : ev.clientY;

      const dx = clientX - startX;
      const dy = clientY - startY;
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
    }

    function endDrag() {
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseup', endDrag);
      document.removeEventListener('touchmove', onMove);
      document.removeEventListener('touchend', endDrag);
    }

    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup', endDrag);
    document.addEventListener('touchmove', onMove, { passive: false });
    document.addEventListener('touchend', endDrag);
  }

  wrapper.addEventListener('mousedown', startDrag);
  wrapper.addEventListener('touchstart', startDrag, { passive: false });
}


export function addResizeHandles(wrapper, overlayInfo, container) {
  const positions = ['tl', 'tr', 'bl', 'br', 't', 'b', 'l', 'r'];
  const cursors = {
    tl: 'nwse-resize', tr: 'nesw-resize', bl: 'nesw-resize', br: 'nwse-resize',
    t: 'ns-resize', b: 'ns-resize', l: 'ew-resize', r: 'ew-resize'
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
      transform: 'translate(-50%, -50%)'
    });

    if (pos.includes('t')) handle.style.top = '0';
    if (pos.includes('b')) handle.style.bottom = '0';
    if (pos.includes('l')) handle.style.left = '0';
    if (pos.includes('r')) handle.style.right = '0';

    wrapper.appendChild(handle);

    function startResize(e) {
      e.preventDefault();
      e.stopPropagation();

      const isTouch = e.type.startsWith('touch');
      const startX = isTouch ? e.touches[0].clientX : e.clientX;
      const startY = isTouch ? e.touches[0].clientY : e.clientY;
      const startW = parseFloat(wrapper.style.width);
      const startH = parseFloat(wrapper.style.height);
      const startL = parseFloat(wrapper.style.left);
      const startT = parseFloat(wrapper.style.top);
      const bounds = container.getBoundingClientRect();

      function onMove(ev) {
        const clientX = ev.type.startsWith('touch') ? ev.touches[0].clientX : ev.clientX;
        const clientY = ev.type.startsWith('touch') ? ev.touches[0].clientY : ev.clientY;

        const dx = (clientX - startX) / bounds.width * 100;
        const dy = (clientY - startY) / bounds.height * 100;

        let newW = startW;
        let newH = startH;
        let newL = startL;
        let newT = startT;

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

        Object.assign(wrapper.style, {
          width: `${newW}%`,
          height: `${newH}%`,
          left: `${newL}%`,
          top: `${newT}%`
        });

        Object.assign(overlayInfo, {
          width: newW, height: newH, left: newL, top: newT
        });
      }

      function endResize() {
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', endResize);
        document.removeEventListener('touchmove', onMove);
        document.removeEventListener('touchend', endResize);
      }

      document.addEventListener('mousemove', onMove);
      document.addEventListener('mouseup', endResize);
      document.addEventListener('touchmove', onMove, { passive: false });
      document.addEventListener('touchend', endResize);
    }

    handle.addEventListener('mousedown', startResize);
    handle.addEventListener('touchstart', startResize, { passive: false });
  });
}