import type { Metadata } from 'next';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';
import ComparePage from './ComparePage';

// Page de comparaison (parité ancien site : iqitcompare).
// La sélection vit côté client (localStorage) -> page purement statique, aucune donnée visiteur
// côté serveur. `noindex` : le contenu dépend d'une sélection personnelle, il n'a rien à faire
// dans l'index (l'ancien site la bloquait aussi via robots.txt).
export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: 'compare' });
  return { title: `${t('title')} — Hyaluronic Filler Market`, robots: { index: false, follow: true } };
}

export default async function Comparaison({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const t = await getTranslations('compare');

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <main className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '44px 28px 120px' }}>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,4vw,40px)', color: '#2B2B2B', margin: '0 0 26px' }}>{t('title')}</h1>
        <ComparePage />
      </main>
      <Footer />
    </div>
  );
}
