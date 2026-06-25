'use client';

import React, { createContext, useCallback, useContext, useEffect, useState } from 'react';
import { useStore } from './store';
import type { ProductCard } from '@/lib/ps';

type WishlistCtx = {
  ids: number[];
  products: ProductCard[];
  ready: boolean;
  has: (id: number) => boolean;
  toggle: (id: number) => Promise<void>;
  refresh: () => Promise<void>;
};

const Ctx = createContext<WishlistCtx | null>(null);

export function useWishlist(): WishlistCtx {
  const c = useContext(Ctx);
  if (!c) throw new Error('useWishlist must be used within WishlistProvider');
  return c;
}

export function WishlistProvider({ children }: { children: React.ReactNode }) {
  const { customer } = useStore();
  const [ids, setIds] = useState<number[]>([]);
  const [products, setProducts] = useState<ProductCard[]>([]);
  const [ready, setReady] = useState(false);

  const refresh = useCallback(async () => {
    try {
      const r = await fetch('/api/wishlist');
      const d = await r.json();
      setIds(d.ids ?? []);
      setProducts(d.products ?? []);
    } catch {
      setIds([]);
      setProducts([]);
    } finally {
      setReady(true);
    }
  }, []);

  // Recharge à chaque changement d'auth (connexion / déconnexion).
  useEffect(() => {
    if (customer) {
      refresh();
    } else {
      setIds([]);
      setProducts([]);
      setReady(true);
    }
  }, [customer, refresh]);

  const toggle = useCallback(
    async (id: number) => {
      // Optimiste : on bascule l'état tout de suite, puis on confirme côté serveur.
      setIds((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
      try {
        const r = await fetch('/api/wishlist', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'toggle', id_product: id }),
        });
        const d = await r.json();
        if (d.ids) {
          setIds(d.ids);
          setProducts(d.products ?? []);
        }
      } catch {
        /* on garde l'état optimiste */
      }
    },
    []
  );

  const value: WishlistCtx = {
    ids,
    products,
    ready,
    has: (id) => ids.includes(id),
    toggle,
    refresh,
  };

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
}
