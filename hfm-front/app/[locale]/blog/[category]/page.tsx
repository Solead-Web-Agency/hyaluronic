import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { alternatesFromSlugs } from '@/lib/seo';
import { fetchBlogList, fetchBlogCategories } from '@/lib/blog';
import Chrome from '../../../components/Chrome';
import Footer from '../../../components/Footer';
import BlogListView from '../BlogListView';

const PER_PAGE = 9;

export async function generateMetadata({ params }: { params: Promise<{ locale: string; category: string }> }): Promise<Metadata> {
  const { locale, category } = await params;
  const t = await getTranslations({ locale, namespace: 'blog' });
  const list = await fetchBlogList(locale, { limit: 1, category });
  const name = list.category?.name;
  const alt = list.category?.alternates;
  return {
    title: name ? `${name} — ${t('title')}` : t('title'),
    description: t('lead'),
    // hreflang : le slug de catégorie blog varie par langue (repli langue par défaut).
    alternates: alternatesFromSlugs(locale, `/blog/${category}`, (idLang) => {
      const s = alt?.[idLang];
      return s ? `/blog/${s}` : null;
    }),
  };
}

export default async function BlogCategoryPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string; category: string }>;
  searchParams: Promise<{ page?: string }>;
}) {
  const { locale, category } = await params;
  setRequestLocale(locale);
  const sp = await searchParams;
  const page = Math.max(1, parseInt(sp.page ?? '1', 10) || 1);

  const [list, categories] = await Promise.all([
    fetchBlogList(locale, { page, limit: PER_PAGE, category }),
    fetchBlogCategories(locale),
  ]);

  // Catégorie inconnue -> 404.
  if (!list.category) notFound();

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <BlogListView locale={locale} list={list} categories={categories} activeCategory={category} />
      <Footer />
    </div>
  );
}
