'use client';

import { useCallback, useEffect, useRef, useState } from 'react';
import { toCard, type Card } from '@/lib/cardModel';
import type { ProductCard } from '@/lib/ps';

// VRAIE pagination serveur (infinite scroll) : la page 1 vient du rendu serveur (SEO) ; les pages
// suivantes sont demandées à /api/products (page=N) quand le sentinel approche du viewport, puis
// APPENDÉES. Le total (renvoyé par le bridge) borne le chargement (hasMore = chargés < total).
// Un changement de tri repart en page 1 (reload) et REMPLACE la liste.
export const PRODUCT_PAGE_SIZE = 48;

type Filters = {
  id_category?: string | number | null;
  id_manufacturer?: string | number | null;
  q?: string | null;
  filter?: string | null;
};

export function useProductPagination(opts: {
  initial: ProductCard[];
  total: number;
  idLang: number;
  filters: Filters;
  initialOrder?: string;
}) {
  const { idLang } = opts;
  const idCategory = opts.filters.id_category ?? null;
  const idManufacturer = opts.filters.id_manufacturer ?? null;
  const q = opts.filters.q ?? null;
  const filter = opts.filters.filter ?? null;

  const [items, setItems] = useState<Card[]>(() => opts.initial.map(toCard));
  const [total, setTotal] = useState(opts.total);
  const [loading, setLoading] = useState(false);
  const [hasMore, setHasMore] = useState(opts.initial.length < opts.total);

  // Refs = source de vérité SYNCHRONE pour l'observer (évite les closures périmées et les
  // doubles fetch). loadedRef = nombre de produits déjà accumulés.
  const loadedRef = useRef(opts.initial.length);
  const pageRef = useRef(1);
  const orderRef = useRef(opts.initialOrder ?? 'position');
  const loadingRef = useRef(false);
  const hasMoreRef = useRef(opts.initial.length < opts.total);
  const sentinelRef = useRef<HTMLDivElement | null>(null);

  const buildUrl = useCallback(
    (pageN: number, order: string) => {
      const p = new URLSearchParams();
      p.set('page', String(pageN));
      p.set('limit', String(PRODUCT_PAGE_SIZE));
      p.set('order', order);
      p.set('id_lang', String(idLang));
      if (idCategory) p.set('id_category', String(idCategory));
      else if (idManufacturer) p.set('id_manufacturer', String(idManufacturer));
      else if (q) p.set('q', q);
      else if (filter) p.set('filter', filter);
      return `/api/products?${p.toString()}`;
    },
    [idLang, idCategory, idManufacturer, q, filter],
  );

  const fetchPage = useCallback(
    async (pageN: number, order: string, replace: boolean) => {
      if (loadingRef.current) return;
      loadingRef.current = true;
      setLoading(true);
      try {
        const res = await fetch(buildUrl(pageN, order));
        if (!res.ok) throw new Error('bridge');
        const data = await res.json();
        const cards = ((data.products ?? []) as ProductCard[]).map(toCard);
        const nextTotal = Number.isFinite(Number(data.total)) ? Number(data.total) : loadedRef.current;
        const prevLen = replace ? 0 : loadedRef.current;
        const loadedNow = prevLen + cards.length;
        loadedRef.current = loadedNow;
        pageRef.current = pageN;
        orderRef.current = order;
        // Une page VIDE arrête l'auto-load même si total > chargés (garde-fou contre un total
        // légèrement surestimé -> pas de boucle de fetch infinie).
        const more = cards.length > 0 && loadedNow < nextTotal;
        hasMoreRef.current = more;
        setItems((prev) => (replace ? cards : [...prev, ...cards]));
        setTotal(nextTotal);
        setHasMore(more);
      } catch {
        // Panne réseau/bridge : on stoppe l'auto-load (pas de boucle), la page reste utilisable.
        hasMoreRef.current = false;
        setHasMore(false);
      } finally {
        loadingRef.current = false;
        setLoading(false);
      }
    },
    [buildUrl],
  );

  // Sentinel : charge la page suivante à son approche (rootMargin = préchargement doux).
  useEffect(() => {
    const el = sentinelRef.current;
    if (!el) return;
    const io = new IntersectionObserver(
      (entries) => {
        if (entries.some((e) => e.isIntersecting) && hasMoreRef.current && !loadingRef.current) {
          fetchPage(pageRef.current + 1, orderRef.current, false);
        }
      },
      { rootMargin: '600px 0px' },
    );
    io.observe(el);
    return () => io.disconnect();
  }, [fetchPage]);

  // Rechargement depuis la page 1 avec un nouveau tri : REMPLACE la liste.
  const reload = useCallback(
    (order: string) => {
      loadedRef.current = 0;
      pageRef.current = 1;
      fetchPage(1, order, true);
    },
    [fetchPage],
  );

  return { items, total, loading, hasMore, sentinelRef, reload };
}
