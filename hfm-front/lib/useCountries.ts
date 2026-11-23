'use client';

/**
 * Pays ouverts à la livraison, NOMMÉS DANS LA LANGUE DE LA PAGE (demande client du 23/11/2026).
 *
 * Avant, le formulaire d'adresse demandait la liste sans préciser la langue : le back répondait en
 * français quelle que soit la langue du site, et un client étranger ne retrouvait pas son pays
 * (« Deutschland » rangé à « Allemagne »). La liste est maintenant demandée avec la langue de la
 * page et triée par ordre alphabétique DANS cette langue. Une seule lecture par langue, partagée
 * par le formulaire d'adresse, le tunnel de commande et le compte client.
 */
import { useEffect, useMemo, useState } from 'react';
import { useLocale } from 'next-intl';
import { idLangFor } from './i18n-config';

export type CountryState = { id_state: number; name: string; iso_code: string };
// Règles d'adresse par pays, servies par le bridge (taxonomy action=countries).
export type CountryInfo = {
  id_country: number;
  name: string;
  iso_code: string;
  contains_states?: boolean;
  states?: CountryState[];
  need_zip_code?: boolean;
  zip_code_format?: string;
  need_identification_number?: boolean;
  vat_applicable?: boolean;
};

const TTL_MS = 5 * 60 * 1000;
const cache = new Map<number, { at: number; promise: Promise<CountryInfo[]> }>();

function load(idLang: number): Promise<CountryInfo[]> {
  const hit = cache.get(idLang);
  if (hit && Date.now() - hit.at < TTL_MS) return hit.promise;
  const promise = fetch(`/api/taxonomy?action=countries&id_lang=${idLang}`)
    .then((r) => (r.ok ? r.json() : { countries: [] }))
    .then((d) => ((d as { countries?: CountryInfo[] }).countries ?? []))
    .catch(() => {
      cache.delete(idLang); // panne : on retentera à la prochaine demande
      return [] as CountryInfo[];
    });
  cache.set(idLang, { at: Date.now(), promise });
  return promise;
}

function sortFor(locale: string, list: CountryInfo[]): CountryInfo[] {
  let compare: (a: string, b: string) => number;
  try {
    compare = new Intl.Collator(locale, { sensitivity: 'base' }).compare;
  } catch {
    compare = (a, b) => a.localeCompare(b);
  }
  return [...list].sort((a, b) => compare((a.name || '').trim(), (b.name || '').trim()));
}

/** Liste des pays dans la langue de la page, triée dans cette langue (vide tant qu'elle charge). */
export function useCountries(): CountryInfo[] {
  const locale = useLocale();
  const [list, setList] = useState<CountryInfo[]>([]);
  useEffect(() => {
    let alive = true;
    load(idLangFor(locale)).then((l) => {
      if (alive) setList(sortFor(locale, l));
    });
    return () => {
      alive = false;
    };
  }, [locale]);
  return list;
}

/** id_country -> nom du pays dans la langue de la page (pour afficher une adresse enregistrée). */
export function useCountryNames(): Record<number, string> {
  const list = useCountries();
  return useMemo(() => {
    const m: Record<number, string> = {};
    for (const c of list) m[c.id_country] = c.name;
    return m;
  }, [list]);
}
