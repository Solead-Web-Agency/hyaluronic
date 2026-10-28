#!/usr/bin/env python3
"""
Contrôle prix / stock : site actuel (hyaluronicfillermarket.com, PrestaShop 1.7) contre le
nouveau back (bridge hfmstorefront de la préprod, puis de la prod après bascule).

Audit « Parcours d'achat » du 27/10/2026, B4 : 32 fiches au prix HT différent, 17 produits en
rupture affichés « En stock ». À lancer après chaque resynchronisation et la veille de l'ouverture.

  BRIDGE_URL=https://back.prodsolead.com BRIDGE_SECRET=… python3 scripts/controle_prix_stock.py sortie.csv

- Site actuel : les URL produits du sitemap FR, puis le JSON `data-product` de chaque fiche
  (id_product, price_tax_exc, quantity, allow_oosp). Prix vus depuis la France (TVA FR).
- Nouveau back : endpoint `products&ids=…` du bridge (price_excl_tax, availability).
Sortie : un CSV des écarts (prix HT > 1 centime, disponibilité différente, produit absent d'un
côté) et un résumé à l'écran. Aucune écriture nulle part.
"""
import csv
import html
import json
import os
import re
import sys
import time
import urllib.parse
import urllib.request
from concurrent.futures import ThreadPoolExecutor

LIVE = os.environ.get('LIVE_URL', 'https://hyaluronicfillermarket.com').rstrip('/')
BRIDGE = os.environ.get('BRIDGE_URL', '').rstrip('/')
SECRET = os.environ.get('BRIDGE_SECRET', '')
UA = 'Mozilla/5.0 (controle-prix-stock HFM)'


def get(url, headers=None, tries=3):
    for i in range(tries):
        try:
            req = urllib.request.Request(url, headers={'User-Agent': UA, 'Accept-Language': 'fr', **(headers or {})})
            with urllib.request.urlopen(req, timeout=40) as r:
                return r.read().decode('utf-8', 'replace')
        except Exception as e:  # réseau capricieux : on réessaie
            if i == tries - 1:
                raise
            time.sleep(2 * (i + 1))


def live_product_urls():
    xml = get(f'{LIVE}/fr/sitemap/product/1.xml')
    return re.findall(r'<loc><!\[CDATA\[(https?://[^\]]+)\]\]></loc>', xml) or re.findall(r'<loc>(https?://[^<]+)</loc>', xml)


def live_product(url):
    try:
        page = get(url)
    except Exception as e:
        return {'url': url, 'error': str(e)}
    m = re.search(r'data-product="([^"]+)"', page)
    if not m:
        return {'url': url, 'error': 'pas de data-product'}
    d = json.loads(html.unescape(m.group(1)))
    qty = int(d.get('quantity') or 0)
    allow = int(d.get('allow_oosp') or 0)
    return {
        'url': url,
        'id_product': int(d['id_product']),
        'name': d.get('name', ''),
        'price_ht': round(float(d.get('price_tax_exc') or 0), 2),
        'availability': 'in_stock' if qty > 0 else ('backorder' if allow else 'unavailable'),
    }


def bridge_products(ids):
    out = {}
    for i in range(0, len(ids), 24):
        chunk = ids[i:i + 24]
        qs = urllib.parse.urlencode({'fc': 'module', 'module': 'hfmstorefront', 'controller': 'products',
                                     'id_lang': 1, 'ids': ','.join(map(str, chunk))})
        data = json.loads(get(f'{BRIDGE}/index.php?{qs}', {'X-Storefront-Token': SECRET}))
        for p in data.get('products', []):
            out[int(p['id_product'])] = p
    return out


def main():
    if not BRIDGE or not SECRET:
        sys.exit('BRIDGE_URL et BRIDGE_SECRET sont requis (voir l\'en-tête du script).')
    out_path = sys.argv[1] if len(sys.argv) > 1 else 'ecarts-prix-stock.csv'
    urls = live_product_urls()
    print(f'{len(urls)} fiches dans le sitemap du site actuel…', flush=True)
    with ThreadPoolExecutor(max_workers=4) as pool:
        live = list(pool.map(live_product, urls))
    errors = [l for l in live if 'error' in l]
    live_ok = {l['id_product']: l for l in live if 'error' not in l}
    new = bridge_products(sorted(live_ok))

    rows = []
    for pid, l in sorted(live_ok.items()):
        n = new.get(pid)
        if not n:
            rows.append([pid, l['name'], l['url'], 'absent du nouveau site (inactif ?)', l['price_ht'], '', l['availability'], ''])
            continue
        nht = round(float(n.get('price_excl_tax') or 0), 2)
        if abs(nht - l['price_ht']) > 0.01:
            rows.append([pid, l['name'], l['url'], 'prix HT', l['price_ht'], nht, l['availability'], n.get('availability')])
        if n.get('availability') != l['availability']:
            rows.append([pid, l['name'], l['url'], 'disponibilité', l['price_ht'], nht, l['availability'], n.get('availability')])
    for l in errors:
        rows.append(['', '', l['url'], 'fiche illisible : ' + l['error'], '', '', '', ''])

    with open(out_path, 'w', newline='', encoding='utf-8') as f:
        w = csv.writer(f, delimiter=';')
        w.writerow(['id_product', 'produit', 'url site actuel', 'écart', 'HT actuel', 'HT nouveau', 'dispo actuelle', 'dispo nouvelle'])
        w.writerows(rows)

    kinds = {}
    for r in rows:
        kinds[r[3].split(' :')[0]] = kinds.get(r[3].split(' :')[0], 0) + 1
    print(f'{len(live_ok)} fiches comparées, {len(rows)} écarts -> {out_path}')
    for k, v in sorted(kinds.items(), key=lambda kv: -kv[1]):
        print(f'  {v:4d}  {k}')


if __name__ == '__main__':
    main()
