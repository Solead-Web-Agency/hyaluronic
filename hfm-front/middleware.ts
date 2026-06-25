import createMiddleware from 'next-intl/middleware';
import { routing } from './i18n/routing';

export default createMiddleware(routing);

export const config = {
  // On exclut /api, /_next, /_vercel et tous les fichiers statiques (avec extension).
  // Les routes API restent donc non préfixées, accessibles sur /api/*.
  matcher: ['/((?!api|_next|_vercel|.*\\..*).*)'],
};
