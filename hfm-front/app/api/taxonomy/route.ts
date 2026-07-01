import { NextRequest, NextResponse } from 'next/server';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL, cdnCacheControl } from '@/lib/cacheContract';

// Proxy taxonomie : ?action=categories|manufacturers|countries[&id_parent=]
// CACHEABLE (public) : tag "taxonomy", TTL 3600s. Purge on-demand via /api/revalidate.
export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string> = {};
  for (const k of ['action', 'id_parent', 'id_lang']) {
    const v = sp.get(k);
    if (v) params[k] = v;
  }
  const data = await bridgeGetCached('taxonomy', params, {
    ttl: CACHE_TTL.taxonomy,
    tags: [CACHE_TAGS.taxonomy],
  });
  return NextResponse.json(data, {
    headers: { 'Cache-Control': cdnCacheControl(CACHE_TTL.taxonomy) },
  });
}
