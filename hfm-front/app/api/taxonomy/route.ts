import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet } from '@/lib/ps';

// Proxy taxonomie : ?action=categories|manufacturers|countries[&id_parent=]
export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string> = {};
  for (const k of ['action', 'id_parent', 'id_lang']) {
    const v = sp.get(k);
    if (v) params[k] = v;
  }
  return NextResponse.json(await bridgeGet('taxonomy', params));
}
