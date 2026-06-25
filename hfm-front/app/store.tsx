'use client';

import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
} from 'react';
import type { Card } from '@/lib/cardModel';

type CartLine = {
  id_product: number;
  quantity: number;
  name: string;
  reference: string;
  image: string | null;
  link_rewrite: string;
  unit_price_excl_tax: number;
  unit_price_incl_tax: number;
  total_excl_tax: number;
  total_incl_tax: number;
};

type CartData = {
  id_cart: number | null;
  products: CartLine[];
  total_excl_tax: number;
  total_incl_tax: number;
  shipping_incl_tax: number;
  grand_total_incl_tax: number;
  rpps_required: boolean;
};

export type Customer = {
  id_customer: number;
  email: string;
  firstname: string;
  lastname: string;
};

type AuthResult = { ok: boolean; error?: string };

// La passerelle PS renvoie { products[], totals:{ products_excl_tax, products_incl_tax } }.
function normalize(d: {
  id_cart?: number;
  products?: CartLine[];
  rpps_required?: boolean;
  totals?: {
    products_excl_tax?: number;
    products_incl_tax?: number;
    shipping_incl_tax?: number;
    total_incl_tax?: number;
  };
}): Omit<CartData, 'id_cart'> & { id_cart?: number } {
  const productsIncl = d.totals?.products_incl_tax ?? 0;
  return {
    id_cart: d.id_cart,
    products: d.products ?? [],
    total_excl_tax: d.totals?.products_excl_tax ?? 0,
    total_incl_tax: productsIncl,
    shipping_incl_tax: d.totals?.shipping_incl_tax ?? 0,
    grand_total_incl_tax: d.totals?.total_incl_tax ?? productsIncl,
    rpps_required: d.rpps_required ?? false,
  };
}

type StoreCtx = {
  cartOpen: boolean;
  searchOpen: boolean;
  menuOpen: boolean;
  quickProduct: Card | null;
  cart: CartData;
  cartCount: number;
  openCart: () => void;
  closeCart: () => void;
  openSearch: () => void;
  closeSearch: () => void;
  toggleMenu: () => void;
  closeMenu: () => void;
  openQuick: (p: Card) => void;
  closeQuick: () => void;
  addToCart: (p: { id: number; quantity?: number }) => Promise<void>;
  updateLine: (id_product: number, quantity: number) => Promise<void>;
  removeLine: (id_product: number) => Promise<void>;
  refreshCart: () => Promise<void>;
  // Auth client
  customer: Customer | null;
  authReady: boolean;
  login: (email: string, password: string) => Promise<AuthResult>;
  register: (data: { email: string; password: string; firstname: string; lastname: string }) => Promise<AuthResult>;
  guestCheckout: (data: { email: string; firstname: string; lastname: string }) => Promise<AuthResult>;
  logout: () => Promise<void>;
};

const EMPTY_CART: CartData = {
  id_cart: null,
  products: [],
  total_excl_tax: 0,
  total_incl_tax: 0,
  shipping_incl_tax: 0,
  grand_total_incl_tax: 0,
  rpps_required: false,
};

const Ctx = createContext<StoreCtx | null>(null);

export function useStore(): StoreCtx {
  const c = useContext(Ctx);
  if (!c) throw new Error('useStore must be used within StoreProvider');
  return c;
}

export function StoreProvider({ children }: { children: React.ReactNode }) {
  const [cartOpen, setCartOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [quickProduct, setQuickProduct] = useState<Card | null>(null);
  const [cart, setCart] = useState<CartData>(EMPTY_CART);
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [authReady, setAuthReady] = useState(false);

  const loadCart = useCallback(async (id_cart: number) => {
    try {
      const r = await fetch(`/api/cart?id_cart=${id_cart}`);
      const d = await r.json();
      const n = normalize(d);
      setCart({ ...n, id_cart: n.id_cart ?? id_cart });
    } catch {
      /* ignore */
    }
  }, []);

  useEffect(() => {
    const stored = localStorage.getItem('id_cart');
    if (stored) loadCart(Number(stored));
  }, [loadCart]);

  const refreshCart = useCallback(async () => {
    const stored = localStorage.getItem('id_cart');
    if (stored) await loadCart(Number(stored));
  }, [loadCart]);

  const applyCart = (raw: Parameters<typeof normalize>[0]) => {
    const n = normalize(raw);
    if (n.id_cart) localStorage.setItem('id_cart', String(n.id_cart));
    setCart((prev) => ({ ...n, id_cart: n.id_cart ?? prev.id_cart }));
  };

  const addToCart = useCallback(
    async (p: { id: number; quantity?: number }) => {
      const id_cart = localStorage.getItem('id_cart');
      const body: Record<string, unknown> = {
        action: 'add',
        id_product: p.id,
        qty: p.quantity ?? 1,
      };
      if (id_cart) body.id_cart = Number(id_cart);
      try {
        const r = await fetch('/api/cart', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body),
        });
        const d = await r.json();
        if (d.error === 'out_of_stock') return; // produit en rupture : on n'ajoute pas
        applyCart(d);
        setCartOpen(true);
      } catch {
        /* ignore */
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const updateLine = useCallback(
    async (id_product: number, quantity: number) => {
      const id_cart = localStorage.getItem('id_cart');
      if (!id_cart) return;
      const r = await fetch('/api/cart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update',
          id_cart: Number(id_cart),
          id_product,
          qty: quantity,
        }),
      });
      applyCart(await r.json());
    },
    []
  );

  const removeLine = useCallback(async (id_product: number) => {
    const id_cart = localStorage.getItem('id_cart');
    if (!id_cart) return;
    const r = await fetch('/api/cart', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'remove',
        id_cart: Number(id_cart),
        id_product,
      }),
    });
    applyCart(await r.json());
  }, []);

  // ----- Auth client -----
  useEffect(() => {
    fetch('/api/auth')
      .then((r) => r.json())
      .then((d) => setCustomer(d.customer ?? null))
      .catch(() => {})
      .finally(() => setAuthReady(true));
  }, []);

  // Rattache le panier invité au client après connexion (id_customer injecté côté serveur).
  const attachCart = useCallback(async () => {
    const id_cart = localStorage.getItem('id_cart');
    if (!id_cart) return;
    try {
      const r = await fetch('/api/cart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'attach', id_cart: Number(id_cart) }),
      });
      applyCart(await r.json());
    } catch {
      /* ignore */
    }
  }, []);

  const login = useCallback(
    async (email: string, password: string): Promise<AuthResult> => {
      const r = await fetch('/api/auth', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'login', email, password }),
      });
      const d = await r.json();
      if (!r.ok || !d.customer) return { ok: false, error: d.error || 'bad_credentials' };
      setCustomer(d.customer);
      await attachCart();
      return { ok: true };
    },
    [attachCart]
  );

  const register = useCallback(
    async (data: { email: string; password: string; firstname: string; lastname: string }): Promise<AuthResult> => {
      const r = await fetch('/api/auth', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'register', ...data }),
      });
      const d = await r.json();
      if (!r.ok || !d.customer) return { ok: false, error: d.error || 'register_failed' };
      setCustomer(d.customer);
      await attachCart();
      return { ok: true };
    },
    [attachCart]
  );

  const guestCheckout = useCallback(
    async (data: { email: string; firstname: string; lastname: string }): Promise<AuthResult> => {
      const r = await fetch('/api/auth', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'guest', ...data }),
      });
      const d = await r.json();
      if (!r.ok || !d.customer) return { ok: false, error: d.error || 'guest_failed' };
      setCustomer(d.customer);
      await attachCart();
      return { ok: true };
    },
    [attachCart]
  );

  const logout = useCallback(async () => {
    await fetch('/api/auth', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'logout' }),
    });
    setCustomer(null);
  }, []);

  const cartCount = cart.products.reduce((t, l) => t + l.quantity, 0);

  const value: StoreCtx = {
    cartOpen,
    searchOpen,
    menuOpen,
    quickProduct,
    cart,
    cartCount,
    openCart: () => {
      setCartOpen(true);
      setMenuOpen(false);
    },
    closeCart: () => setCartOpen(false),
    openSearch: () => {
      setSearchOpen(true);
      setMenuOpen(false);
    },
    closeSearch: () => setSearchOpen(false),
    toggleMenu: () => setMenuOpen((m) => !m),
    closeMenu: () => setMenuOpen(false),
    openQuick: (p) => setQuickProduct(p),
    closeQuick: () => setQuickProduct(null),
    addToCart,
    updateLine,
    removeLine,
    refreshCart,
    customer,
    authReady,
    login,
    register,
    guestCheckout,
    logout,
  };

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
}
