import { NextRequest, NextResponse } from 'next/server';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL, cdnCacheControl } from '@/lib/cacheContract';

// Proxy contenu CMS : transmet action/link_rewrite/id_lang/locale au bridge PS.
// CACHEABLE (public) : tag "content", TTL 3600s.
export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string> = {};
  for (const k of ['action', 'link_rewrite', 'id_cms', 'id_lang', 'locale']) {
    const v = sp.get(k);
    if (v) params[k] = v;
  }
  const data = await bridgeGetCached('content', params, {
    ttl: CACHE_TTL.content,
    tags: [CACHE_TAGS.content],
  });
  return NextResponse.json(data, {
    headers: { 'Cache-Control': cdnCacheControl(CACHE_TTL.content) },
  });
}
