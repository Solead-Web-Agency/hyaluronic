'use client';

import Header from './Header';
import CartDrawer from './CartDrawer';
import SearchOverlay from './SearchOverlay';
import QuickView from './QuickView';

export default function Chrome() {
  return (
    <>
      <Header />
      <CartDrawer />
      <SearchOverlay />
      <QuickView />
    </>
  );
}
