import type { Metadata } from 'next';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { alternatesFor } from '@/lib/seo';
import { fetchBlogList, fetchBlogCategories } from '@/lib/blog';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';
import BlogListView from './BlogListView';

const PER_PAGE = 9;

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: 'blog' });
  return {
    title: t('title'),
    description: t('lead'),
    alternates: alternatesFor(locale, '/blog'),
  };
}

export default async function BlogPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ page?: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);
  const sp = await searchParams;
  const page = Math.max(1, parseInt(sp.page ?? '1', 10) || 1);

  const [list, categories] = await Promise.all([
    fetchBlogList(locale, { page, limit: PER_PAGE }),
    fetchBlogCategories(locale),
  ]);

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <BlogListView locale={locale} list={list} categories={categories} activeCategory={null} />
      <Footer />
    </div>
  );
}
