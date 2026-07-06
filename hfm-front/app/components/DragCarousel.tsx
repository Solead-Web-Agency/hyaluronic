'use client';

import { useRef } from 'react';

// Drag-to-scroll à la souris (le tactile garde le défilement natif), avec inertie
// au relâchement. Un vrai drag (> 5 px) avale le clic pour ne pas naviguer.
// Pas de scroll-snap sur les rails : il se bat avec le drag (mouvement saccadé).
export function useDragScroll() {
  const ref = useRef<HTMLDivElement>(null);
  const state = useRef({ down: false, startX: 0, startLeft: 0, dragged: false, lastX: 0, lastT: 0, v: 0, raf: 0 });

  const stopInertia = () => {
    if (state.current.raf) {
      cancelAnimationFrame(state.current.raf);
      state.current.raf = 0;
    }
  };

  const glide = () => {
    const s = state.current;
    const el = ref.current;
    if (!el || Math.abs(s.v) < 0.03) {
      s.raf = 0;
      return;
    }
    el.scrollLeft -= s.v * 14; // vitesse en px/ms sur ~un frame
    s.v *= 0.94; // décélération douce
    if (el.scrollLeft <= 0 || el.scrollLeft >= el.scrollWidth - el.clientWidth) {
      s.v = 0;
    }
    s.raf = requestAnimationFrame(glide);
  };

  const end = () => {
    const s = state.current;
    const el = ref.current;
    if (el) {
      el.style.cursor = 'grab';
      el.style.userSelect = '';
    }
    if (s.down && s.dragged) {
      s.raf = requestAnimationFrame(glide); // élan au relâchement
    }
    s.down = false;
  };

  const handlers = {
    onPointerDown: (e: React.PointerEvent) => {
      if (e.pointerType !== 'mouse') return;
      const el = ref.current;
      if (!el) return;
      stopInertia();
      state.current = { down: true, startX: e.clientX, startLeft: el.scrollLeft, dragged: false, lastX: e.clientX, lastT: performance.now(), v: 0, raf: 0 };
      el.style.userSelect = 'none';
    },
    onPointerMove: (e: React.PointerEvent) => {
      const s = state.current;
      const el = ref.current;
      if (!s.down || !el) return;
      const dx = e.clientX - s.startX;
      if (!s.dragged && Math.abs(dx) > 5) {
        s.dragged = true;
        el.style.cursor = 'grabbing';
      }
      if (s.dragged) {
        el.scrollLeft = s.startLeft - dx;
        // Vitesse instantanée (px/ms) pour l'inertie, lissée avec la précédente.
        const now = performance.now();
        const dt = Math.max(1, now - s.lastT);
        s.v = 0.8 * ((e.clientX - s.lastX) / dt) + 0.2 * s.v;
        s.lastX = e.clientX;
        s.lastT = now;
      }
    },
    onPointerUp: end,
    onPointerLeave: end,
    onClickCapture: (e: React.MouseEvent) => {
      if (state.current.dragged) {
        e.preventDefault();
        e.stopPropagation();
        state.current.dragged = false;
      }
    },
    onDragStart: (e: React.DragEvent) => e.preventDefault(),
  };

  return { ref, handlers };
}

// Rail défilant clé en main pour les composants serveur (children rendus côté serveur).
export default function DragCarousel({ children, className, style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) {
  const { ref, handlers } = useDragScroll();

  return (
    <div ref={ref} {...handlers} className={className} style={{ cursor: 'grab', ...style }}>
      {children}
    </div>
  );
}
