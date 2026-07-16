import { ImageResponse } from 'next/og';

// Image de partage social par défaut, GÉNÉRÉE en 1200×630 (format recommandé Facebook/LinkedIn,
// et 1.91:1 conforme au minimum Twitter de 300×157 pour summary_large_image).
// Aucun visuel du projet n'était exploitable : le plus grand faisait 710×553, et la hero
// (299×480, portrait) passait SOUS le minimum Twitter -> carte dégradée sur toutes les pages.
//
// Servie sous /api/og et NON via la convention de fichier `opengraph-image` : cette dernière
// est exposée à la racine (/opengraph-image), que le middleware next-intl détourne en 307 vers
// /{locale}/... . Le préfixe /api est explicitement exclu du matcher du middleware.
export const runtime = 'nodejs';

export async function GET() {
  return new ImageResponse(
    (
      <div
        style={{
          width: '100%',
          height: '100%',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          background: 'linear-gradient(160deg, #202B16 0%, #26331A 55%, #2C3A1D 100%)',
          fontFamily: 'sans-serif',
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: 18 }}>
          <div style={{ width: 14, height: 74, background: '#8CC63F', borderRadius: 999 }} />
          <div style={{ display: 'flex', flexDirection: 'column' }}>
            <div style={{ fontSize: 72, color: '#FFFFFF', lineHeight: 1.1 }}>Hyaluronic Filler</div>
            <div style={{ fontSize: 72, color: '#8CC63F', lineHeight: 1.1 }}>Market</div>
          </div>
        </div>
        <div style={{ marginTop: 34, fontSize: 30, color: '#C7D2BA' }}>
          L’injectable de référence pour les professionnels
        </div>
      </div>
    ),
    {
      width: 1200,
      height: 630,
      headers: {
        // Image purement statique (aucune donnée dynamique) : on la laisse en cache longue durée.
        'Cache-Control': 'public, max-age=31536000, immutable',
      },
    },
  );
}
