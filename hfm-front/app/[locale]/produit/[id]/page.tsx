import { bridgeGet } from '@/lib/ps';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { idLangFor } from '@/lib/i18n-config';
import Chrome from '../../../components/Chrome';
import Footer from '../../../components/Footer';
import ProductDetail, { type ProductView } from './ProductDetail';

type RawFeature = { name?: string; value?: string };

export default async function ProductPage({ params }: { params: Promise<{ locale: string; id: string }> }) {
  const { locale, id } = await params;
  setRequestLocale(locale);
  const t = await getTranslations('product');
  const data = await bridgeGet('products', { id_product: id, id_lang: idLangFor(locale) });
  const p = data.product ?? {};

  const view: ProductView = {
    id: Number(id),
    name: p.name ?? t('fallbackName'),
    reference: p.reference ?? '',
    brand: p.manufacturer || p.brand || null,
    ht: p.price_excl_tax ?? 0,
    ttc: p.price_incl_tax ?? 0,
    inStock: p.available != null ? !!p.available : (p.quantity ?? 0) > 0,
    description: p.description || p.description_short || '',
    images: Array.isArray(p.images) ? p.images : [],
    features: Array.isArray(p.features)
      ? (p.features as RawFeature[]).map((f) => ({ name: f.name ?? '', value: f.value ?? '' })).filter((f) => f.name)
      : [],
    rpps_required: !!p.rpps_required,
  };

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <ProductDetail product={view} />
      <Footer />
    </div>
  );
}
