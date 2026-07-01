import { NextRequest, NextResponse } from 'next/server';
import { purgeTags } from '@/lib/cache';
import { NO_STORE } from '@/lib/cacheContract';

// Endpoint de purge on-demand appelé par le module PS hfmstorefront après édition BO.
// Auth : en-tête "x-hfm-revalidate-secret" == process.env.HFM_REVALIDATE_SECRET.
// Corps : { tags: string[] }. Purge Redis (index inverse) + revalidateTag (Data Cache Next).
export const dynamic = 'force-dynamic';

export async function POST(req: NextRequest) {
  const secret = process.env.HFM_REVALIDATE_SECRET;
  const provided = req.headers.get('x-hfm-revalidate-secret');
  // 403 si le secret n'est pas configuré côté front OU s'il ne correspond pas.
  if (!secret || provided !== secret) {
    return NextResponse.json({ error: 'forbidden' }, { status: 403, headers: NO_STORE });
  }

  const body = await req.json().catch(() => ({}));
  const tags: unknown = body?.tags;
  if (!Array.isArray(tags) || tags.length === 0 || !tags.every((t) => typeof t === 'string')) {
    return NextResponse.json({ error: 'missing_tags' }, { status: 400, headers: NO_STORE });
  }

  await purgeTags(tags as string[]);
  return NextResponse.json({ revalidated: true, tags }, { headers: NO_STORE });
}
