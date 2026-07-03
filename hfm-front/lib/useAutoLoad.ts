'use client';

import { useEffect, useRef, useState } from 'react';

// Chargement progressif des listings : n'affiche que `pageSize` produits,
// puis en révèle une page de plus dès que la sentinelle approche du viewport.
export function useAutoLoad(total: number, pageSize = 20) {
  const [visible, setVisible] = useState(pageSize);
  const sentinelRef = useRef<HTMLDivElement | null>(null);

  // Retour à la première page quand la liste change (filtre, recherche…).
  useEffect(() => {
    setVisible(pageSize);
  }, [total, pageSize]);

  useEffect(() => {
    const el = sentinelRef.current;
    if (!el || visible >= total) return;
    const io = new IntersectionObserver(
      (entries) => {
        if (entries.some((e) => e.isIntersecting)) setVisible((v) => Math.min(v + pageSize, total));
      },
      { rootMargin: '600px 0px' },
    );
    io.observe(el);
    return () => io.disconnect();
  }, [visible, total, pageSize]);

  return { visible, sentinelRef };
}
