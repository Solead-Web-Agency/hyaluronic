import { NextRequest, NextResponse } from 'next/server';
import { visitorCountryFrom } from '@/lib/geoip';

// Pays du visiteur : en-tête de l'hébergeur quand il existe (Vercel : x-vercel-ip-country), sinon
// recherche par adresse IP dans la base DB-IP embarquée (lib/geoip.ts).
// Sert au pop-up « information professionnelle » (Europe). Pas de donnée personnelle conservée.
// `source` (host | ip | null) dit d'où vient la réponse : pratique pour vérifier un déploiement.
// En local (ni en-tête ni adresse publique) : `?country=FR` permet de tester.
export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  const found = visitorCountryFrom(req.headers);
  const forced = process.env.NODE_ENV !== 'production' ? req.nextUrl.searchParams.get('country') : null;
  const country = (forced || found.country || '').toUpperCase().slice(0, 2) || null;
  return NextResponse.json(
    { country, source: forced ? 'forced' : found.source },
    { headers: { 'Cache-Control': 'private, no-store' } },
  );
}
