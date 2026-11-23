// Pays du visiteur quand l'hébergeur ne le fournit pas (serveur Node du Plesk : pas d'en-tête
// x-vercel-ip-country). On le déduit de son adresse IP avec la base gratuite « IP to Country Lite »
// de DB-IP (https://db-ip.com, CC BY 4.0 — lien d'attribution dans les mentions légales), convertie
// par scripts/update-ip-country.mjs en data/ip-country.bin.gz.
//
// Aucune donnée personnelle conservée : l'adresse sert au calcul puis est oubliée. Fichier absent ou
// illisible -> pays inconnu (null), jamais d'erreur : le site applique alors les règles par défaut
// et celles du pays de livraison, comme avant.
import fs from 'node:fs';
import net from 'node:net';
import path from 'node:path';
import zlib from 'node:zlib';

/** Ce qu'on lit d'un jeu d'en-têtes (Headers du Web comme ReadonlyHeaders de Next). */
type HeaderBag = { get(name: string): string | null };

type Db = { release: string; s4: Uint32Array; c4: Buffer; s6: BigUint64Array; c6: Buffer };

const FILE = path.join(process.cwd(), 'data', 'ip-country.bin.gz');
const MAGIC = 'HFMIPC1\n';

// undefined = pas encore tenté ; null = indisponible (on ne réessaie pas à chaque requête).
let db: Db | null | undefined;

function load(): Db | null {
  if (db !== undefined) return db;
  try {
    const buf = zlib.gunzipSync(fs.readFileSync(FILE));
    if (buf.toString('latin1', 0, 8) !== MAGIC) throw new Error('format inconnu');
    const release = buf.toString('latin1', 8, 16).trim();
    const n4 = buf.readUInt32LE(16);
    const n6 = buf.readUInt32LE(20);
    let o = 24;
    // Copies (slice d'ArrayBuffer) : les vues typées exigent un décalage aligné sur 4 / 8 octets.
    const part = (len: number) => {
      const a = buf.buffer.slice(buf.byteOffset + o, buf.byteOffset + o + len);
      o += len;
      return a;
    };
    const s4 = new Uint32Array(part(n4 * 4));
    const c4 = Buffer.from(part(n4 * 2));
    const s6 = new BigUint64Array(part(n6 * 8));
    const c6 = Buffer.from(part(n6 * 2));
    if (o !== buf.length || !n4 || !n6) throw new Error('fichier tronqué');
    db = { release, s4, c4, s6, c6 };
  } catch (err) {
    db = null;
    const missing = (err as NodeJS.ErrnoException)?.code === 'ENOENT';
    console.warn(`[geoip] ${missing ? 'data/ip-country.bin.gz absent' : 'table illisible'} : pays du visiteur inconnu hors en-tête de l'hébergeur.`, missing ? '' : err);
  }
  return db;
}

// Dernier index i tel que starts[i] <= v (les plages se suivent sans trou, la première commence à 0).
function find<T extends number | bigint>(starts: ArrayLike<T>, v: T): number {
  let lo = 0;
  let hi = starts.length - 1;
  while (lo < hi) {
    const mid = (lo + hi + 1) >>> 1;
    if (starts[mid] <= v) lo = mid;
    else hi = mid - 1;
  }
  return lo;
}

function v4ToInt(ip: string): number {
  const p = ip.split('.');
  return ((+p[0] << 24) | (+p[1] << 16) | (+p[2] << 8) | +p[3]) >>> 0;
}

function v6High(ip: string): bigint {
  const [head, tail] = ip.split('::');
  const h = head ? head.split(':') : [];
  const t = tail !== undefined && tail !== '' ? tail.split(':') : [];
  const groups = tail === undefined ? h : [...h, ...Array(Math.max(0, 8 - h.length - t.length)).fill('0'), ...t];
  // BigInt(…) et non 0n/16n : la cible TypeScript du projet (ES2017) refuse les littéraux BigInt.
  let v = BigInt(0);
  const shift = BigInt(16);
  for (let i = 0; i < 4; i++) v = (v << shift) | BigInt(parseInt(groups[i] || '0', 16) || 0);
  return v;
}

/** Adresse brute d'un en-tête -> adresse IP nue (sans port, crochets ni zone), ou '' si invalide. */
function cleanIp(raw: string | null | undefined): string {
  let ip = (raw || '').trim();
  if (!ip) return '';
  const bracket = /^\[([^\]]+)\](?::\d+)?$/.exec(ip); // [2001:db8::1]:443
  if (bracket) ip = bracket[1];
  else if (/^\d{1,3}(?:\.\d{1,3}){3}:\d+$/.test(ip)) ip = ip.replace(/:\d+$/, ''); // 1.2.3.4:5678
  ip = ip.replace(/%.*$/, ''); // zone IPv6
  const mapped = /^::ffff:(\d{1,3}(?:\.\d{1,3}){3})$/i.exec(ip); // IPv4 présentée en IPv6
  if (mapped) ip = mapped[1];
  return net.isIP(ip) ? ip : '';
}

/** Code pays ISO (2 lettres majuscules) d'une adresse IP, ou null (adresse privée, inconnue, table absente). */
export function countryOfIp(raw: string | null | undefined): string | null {
  const ip = cleanIp(raw);
  if (!ip) return null;
  const d = load();
  if (!d) return null;
  let cc: string;
  if (net.isIPv4(ip)) {
    const i = find(d.s4, v4ToInt(ip));
    cc = d.c4.toString('latin1', i * 2, i * 2 + 2);
  } else {
    const i = find(d.s6, v6High(ip));
    cc = d.c6.toString('latin1', i * 2, i * 2 + 2);
  }
  return /^[A-Z]{2}$/.test(cc) && cc !== 'ZZ' ? cc : null;
}

/** Version (AAAA-MM) de la table chargée, ou null si elle est absente. */
export function geoipRelease(): string | null {
  return load()?.release ?? null;
}

// Vercel pose lui-même ses en-têtes (et écrase ceux du client) ; il définit aussi VERCEL=1.
// Partout ailleurs, un en-tête « x-vercel-… » ou la tête de x-forwarded-for viennent du visiteur.
const ON_VERCEL = !!process.env.VERCEL;

/**
 * Adresse du visiteur, d'après ce que le serveur placé DEVANT l'application a lui-même écrit.
 * - Sur Vercel : première adresse de x-forwarded-for (réécrit par la plateforme).
 * - Derrière notre serveur web (Plesk) : x-real-ip, sinon la DERNIÈRE adresse de x-forwarded-for
 *   (celle que le serveur a ajoutée). La première est fournie par le client : la lire permettrait
 *   de changer de pays ou d'échapper aux limites de tentatives avec un simple en-tête.
 * Renvoie '' si aucune adresse valide.
 */
export function visitorIp(h: HeaderBag): string {
  const chain = (h.get('x-forwarded-for') || '').split(',');
  if (ON_VERCEL) return cleanIp(chain[0]) || cleanIp(h.get('x-real-ip'));
  return cleanIp(h.get('x-real-ip')) || cleanIp(chain[chain.length - 1]);
}

/**
 * Pays du visiteur : en-tête de l'hébergeur sur Vercel, sinon recherche par adresse IP.
 * `source` sert au diagnostic (/api/geo).
 */
export function visitorCountryFrom(h: HeaderBag): { country: string | null; source: 'host' | 'ip' | null } {
  if (ON_VERCEL) {
    const fromHost = (h.get('x-vercel-ip-country') || '').trim().toUpperCase();
    if (/^[A-Z]{2}$/.test(fromHost) && fromHost !== 'XX') return { country: fromHost, source: 'host' };
  }
  const c = countryOfIp(visitorIp(h));
  return { country: c, source: c ? 'ip' : null };
}
