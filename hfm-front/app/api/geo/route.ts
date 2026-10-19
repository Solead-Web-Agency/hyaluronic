import { NextRequest, NextResponse } from 'next/server';

// Pays du visiteur, d'après la géolocalisation IP fournie par Vercel (en-tête x-vercel-ip-country).
// Sert au pop-up « information professionnelle » (Europe). Pas de donnée personnelle conservée.
// En local (pas d'en-tête Vercel) : `?country=FR` permet de tester.
export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  const fromHeader = req.headers.get('x-vercel-ip-country');
  const forced = process.env.NODE_ENV !== 'production' ? req.nextUrl.searchParams.get('country') : null;
  const country = (forced || fromHeader || '').toUpperCase().slice(0, 2) || null;
  return NextResponse.json({ country }, { headers: { 'Cache-Control': 'private, no-store' } });
}
