import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet } from '@/lib/ps';

// Proxy catalogue : transmet limit/page/id_category/id_product au bridge PS.
export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string> = {};
  for (const k of ['limit', 'page', 'id_category', 'id_manufacturer', 'q', 'id_product', 'id_lang', 'id_currency']) {
    const v = sp.get(k);
    if (v) params[k] = v;
  }
  const data = await bridgeGet('products', params);
  return NextResponse.json(data);
}
