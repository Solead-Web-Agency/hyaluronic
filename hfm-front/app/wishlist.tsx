'use client';

import React, { createContext, useCallback, useContext, useEffect, useRef, useState } from 'react';
import { useLocale } from 'next-intl';
import { useStore } from './store';
import type { ProductCard } from '@/lib/ps';
import { idLangFor } from '@/lib/i18n-config';

type WishlistCtx = {
  ids: number[];
  products: ProductCard[];
  ready: boolean;
  /** true si les favoris vivent dans le navigateur (visiteur sans compte), fusionnés à la connexion. */
  local: boolean;
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

// Favoris d'un visiteur NON connecté : gardés dans le navigateur (le favori n'est plus perdu quand
// on clique le cœur avant de se connecter), puis fusionnés dans le compte à la connexion.
// Stockage peut-être indisponible (navigation privée, cookies bloqués) : tout accès est protégé.
const LOCAL_KEY = 'hfm_wishlist_local';
// Plafond aligné sur le lot de cartes que sert le bridge pour une liste d'ids (`products?ids=`).
const LOCAL_MAX = 24;

function readLocal(): number[] {
  try {
    const raw = localStorage.getItem(LOCAL_KEY);
    const v = raw ? JSON.parse(raw) : [];
    return Array.isArray(v) ? v.map(Number).filter((n) => Number.isInteger(n) && n > 0).slice(0, LOCAL_MAX) : [];
  } catch {
    return [];
  }
}

function writeLocal(ids: number[]) {
  try {
    if (ids.length) localStorage.setItem(LOCAL_KEY, JSON.stringify(ids.slice(0, LOCAL_MAX)));
    else localStorage.removeItem(LOCAL_KEY);
  } catch {
    /* stockage bloqué : le favori vit le temps de la page */
  }
}

async function postWishlist(body: Record<string, unknown>) {
  const r = await fetch('/api/wishlist', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  });
  if (!r.ok) throw new Error(`wishlist ${r.status}`);
  return r.json();
}

export function WishlistProvider({ children }: { children: React.ReactNode }) {
  const { customer, authReady } = useStore();
  const locale = useLocale();
  // Un client « invité » (commande sans compte) n'a pas de vrai compte : favoris locaux aussi.
  const account = !!customer && !customer.is_guest;
  const [ids, setIds] = useState<number[]>([]);
  const [products, setProducts] = useState<ProductCard[]>([]);
  const [ready, setReady] = useState(false);
  const merging = useRef(false);

  // Cartes produit des favoris locaux (même source que « Déjà vus » / comparateur).
  const loadLocalProducts = useCallback(async (list: number[]) => {
    if (!list.length) {
      setProducts([]);
      return;
    }
    try {
      const r = await fetch(`/api/products?ids=${list.join(',')}&id_lang=${idLangFor(locale)}`);
      const d = await r.json();
      setProducts(Array.isArray(d?.products) ? d.products : []);
    } catch {
      setProducts([]);
    }
  }, [locale]);

  const refresh = useCallback(async () => {
    if (!account) {
      const list = readLocal();
      setIds(list);
      await loadLocalProducts(list);
      setReady(true);
      return;
    }
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
  }, [account, loadLocalProducts]);

  // Recharge à chaque changement d'auth. À la connexion d'un vrai compte, les favoris locaux sont
  // ajoutés au compte (action `add` : idempotente, ne retire jamais un favori déjà présent).
  useEffect(() => {
    if (!authReady) {
      // Avant la réponse d'auth : on montre déjà les favoris locaux (cœurs pleins), sans réseau.
      setIds(readLocal());
      return;
    }
    if (!account) {
      // Déconnexion : on ne garde que les cartes des favoris locaux (jamais celles du compte).
      const list = readLocal();
      setIds(list);
      setProducts((prev) => prev.filter((p) => list.includes(Number(p.id_product))));
      setReady(true);
      return;
    }
    const pending = readLocal();
    if (!pending.length || merging.current) {
      refresh();
      return;
    }
    merging.current = true;
    (async () => {
      const left: number[] = [];
      for (const id of pending) {
        try {
          await postWishlist({ action: 'add', id_product: id });
        } catch {
          left.push(id); // réessayé à la prochaine connexion / au prochain chargement
        }
      }
      writeLocal(left);
      merging.current = false;
      await refresh();
    })();
  }, [authReady, account, refresh]);

  // Plusieurs onglets ouverts : le cœur reste synchronisé pour un visiteur non connecté.
  useEffect(() => {
    if (account) return;
    const onStorage = (e: StorageEvent) => {
      if (e.key === LOCAL_KEY) setIds(readLocal());
    };
    window.addEventListener('storage', onStorage);
    return () => window.removeEventListener('storage', onStorage);
  }, [account]);

  const toggle = useCallback(
    async (id: number) => {
      if (!account) {
        const cur = readLocal();
        const next = cur.includes(id) ? cur.filter((x) => x !== id) : [id, ...cur].slice(0, LOCAL_MAX);
        writeLocal(next);
        setIds(next);
        setProducts((prev) => prev.filter((p) => next.includes(Number(p.id_product))));
        return;
      }
      // Optimiste : on bascule l'état tout de suite, puis on confirme côté serveur.
      setIds((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
      try {
        const d = await postWishlist({ action: 'toggle', id_product: id });
        if (d.ids) {
          setIds(d.ids);
          setProducts(d.products ?? []);
        }
      } catch {
        /* on garde l'état optimiste */
      }
    },
    [account]
  );

  const value: WishlistCtx = {
    ids,
    products,
    ready,
    local: !account,
    has: (id) => ids.includes(id),
    toggle,
    refresh,
  };

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
}
