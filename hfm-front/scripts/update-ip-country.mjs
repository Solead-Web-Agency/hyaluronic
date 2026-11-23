// Fabrique data/ip-country.bin.gz : la table « adresse IP -> pays » lue par lib/geoip.ts.
//
// Source : base gratuite « IP to Country Lite » de DB-IP (https://db-ip.com), mise à jour chaque mois,
// sous licence Creative Commons Attribution 4.0. Condition d'usage : un lien vers db-ip.com
// (« IP Geolocation by DB-IP ») sur le site — il est dans les mentions légales (app/[locale]/content).
//
//   node scripts/update-ip-country.mjs            télécharge le mois courant (sinon le précédent)
//   node scripts/update-ip-country.mjs fichier.csv.gz   convertit un fichier déjà téléchargé
//
// Le fichier produit n'est PAS dans git (donnée tierce, ~2 Mo, change tous les mois) : le script de
// déploiement le régénère quand il manque ou qu'il a plus de 35 jours. Sans lui, le front fonctionne
// quand même : le pays du visiteur est simplement inconnu (ou fourni par l'hébergeur s'il le donne).
//
// Format (petit-boutiste) : "HFMIPC1\n", version AAAA-MM sur 8 octets, n4 (u32), n6 (u32),
// n4 débuts de plage IPv4 (u32), n4 codes pays (2 octets ASCII), n6 débuts IPv6 (64 bits de poids
// fort, u64), n6 codes pays. Les plages se suivent sans trou : seul le début est stocké.
import fs from 'node:fs';
import path from 'node:path';
import zlib from 'node:zlib';
import { fileURLToPath } from 'node:url';

const OUT = path.join(path.dirname(fileURLToPath(import.meta.url)), '..', 'data', 'ip-country.bin.gz');

function ym(d) {
  return `${d.getUTCFullYear()}-${String(d.getUTCMonth() + 1).padStart(2, '0')}`;
}

async function download() {
  const now = new Date();
  const prev = new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth() - 1, 1));
  for (const release of [ym(now), ym(prev)]) {
    const url = `https://download.db-ip.com/free/dbip-country-lite-${release}.csv.gz`;
    const r = await fetch(url);
    if (r.ok) return { release, gz: Buffer.from(await r.arrayBuffer()) };
    console.error(`${url} -> HTTP ${r.status}`);
  }
  throw new Error('téléchargement DB-IP impossible');
}

function v4ToInt(s) {
  const p = s.split('.');
  return ((+p[0] << 24) | (+p[1] << 16) | (+p[2] << 8) | +p[3]) >>> 0;
}

// Adresse IPv6 -> [64 bits de poids fort, 64 bits de poids faible].
function v6Parts(s) {
  const [head, tail] = s.split('::');
  const h = head ? head.split(':') : [];
  const t = tail !== undefined && tail !== '' ? tail.split(':') : [];
  const groups = tail === undefined ? h : [...h, ...Array(8 - h.length - t.length).fill('0'), ...t];
  let hi = 0n;
  let lo = 0n;
  for (let i = 0; i < 4; i++) hi = (hi << 16n) | BigInt(parseInt(groups[i] || '0', 16));
  for (let i = 4; i < 8; i++) lo = (lo << 16n) | BigInt(parseInt(groups[i] || '0', 16));
  return [hi, lo];
}

const arg = process.argv[2];
let release;
let gz;
if (arg) {
  gz = fs.readFileSync(arg);
  release = (/(\d{4}-\d{2})/.exec(path.basename(arg)) || [])[1] || ym(new Date());
} else {
  ({ release, gz } = await download());
}

const s4 = [];
const c4 = [];
const s6 = [];
const c6 = [];
let prev4 = -1; // dernier début LU (et non dernier début gardé : les fusions en sautent)
let prev6 = -1n;
for (const line of zlib.gunzipSync(gz).toString('latin1').split('\n')) {
  if (!line) continue;
  const [start, end, cc] = line.split(',');
  if (!/^[A-Z]{2}$/.test(cc)) throw new Error(`ligne inattendue : ${line}`);
  if (start.includes(':')) {
    // La table ne garde que les 64 bits de poids fort (un visiteur reçoit au moins un /64 entier).
    // Une plage qui ne commence pas au début d'un /64 est donc comptée à partir du /64 SUIVANT ;
    // le /64 entamé reste au pays de la plage qui le commence. Si elle tient dans ce seul /64 : ignorée.
    let [v, lo] = v6Parts(start);
    if (lo !== 0n) {
      v += 1n;
      if (v > v6Parts(end)[0]) continue;
    }
    if (v <= prev6) continue;
    prev6 = v;
    if (c6.length && c6[c6.length - 1] === cc) continue; // même pays que la plage précédente : fusion
    s6.push(v);
    c6.push(cc);
  } else {
    const v = v4ToInt(start);
    if (v <= prev4) throw new Error(`IPv4 non triées : ${line}`);
    prev4 = v;
    if (c4.length && c4[c4.length - 1] === cc) continue;
    s4.push(v);
    c4.push(cc);
  }
}
if (s4.length < 50000 || s6.length < 20000 || s4[0] !== 0 || s6[0] !== 0n) {
  throw new Error(`fichier DB-IP anormal (${s4.length} plages IPv4, ${s6.length} IPv6)`);
}

const buf = Buffer.alloc(8 + 8 + 4 + 4 + s4.length * 6 + s6.length * 10);
let o = buf.write('HFMIPC1\n', 0, 'latin1');
o += buf.write(release.padEnd(8, ' ').slice(0, 8), o, 'latin1');
o = buf.writeUInt32LE(s4.length, o);
o = buf.writeUInt32LE(s6.length, o);
for (const v of s4) o = buf.writeUInt32LE(v, o);
for (const c of c4) o += buf.write(c, o, 'latin1');
for (const v of s6) o = buf.writeBigUInt64LE(v, o);
for (const c of c6) o += buf.write(c, o, 'latin1');

fs.mkdirSync(path.dirname(OUT), { recursive: true });
fs.writeFileSync(OUT, zlib.gzipSync(buf, { level: 9 }));
console.log(`${path.relative(process.cwd(), OUT)} : DB-IP ${release}, ${s4.length} plages IPv4, ${s6.length} IPv6, ${(fs.statSync(OUT).size / 1024).toFixed(0)} Ko`);
