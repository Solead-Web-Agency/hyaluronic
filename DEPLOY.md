# Déploiement — Hyaluronic Filler Market (headless)

Cible choisie : **front Next.js sur Vercel** + **PrestaShop 9 sur hébergeur PHP/MySQL**,
mise en ligne **staging-first** (sous-domaine de test), puis bascule du domaine principal
qui héberge aujourd'hui l'ancien **PrestaShop 1.7.8.9**.

## Architecture cible

```
  Navigateur
     │  https://hyaluronicfillermarket.com         (prod)  / new.…  (staging)
     ▼
  Front Next.js (Vercel)  ──server-to-server (HFM_BRIDGE_SECRET)──►  PrestaShop 9
     │                                                                  back.hyaluronicfillermarket.com
     └─ images produit en <img> direct ──────────────────────────────► back.…/img/**
```

- Le front (Vercel) est le **site public**. PrestaShop est **headless** : il ne sert que
  le bridge `hfmstorefront` + le back-office. Il vit sur un **sous-domaine** `back.`.
- Les appels front→PS sont **serveur-à-serveur** avec le secret (pas de CORS navigateur
  sur le chemin principal), mais on configure quand même le CORS du module par sécurité.

---

## Phase 0 — Pré-requis

**Hébergeur PrestaShop 9** (sous-domaine `back.`) :
- PHP **8.1**, extensions PS standard (gd, intl, mbstring, pdo_mysql, zip, curl, soap…).
- MySQL 5.7+/MariaDB 10.x. Place disque : code + **images catalogue** (plusieurs Go).
- SSL (Let's Encrypt). Cron pour les tâches (RPPS sync, etc.).
- Options : o2switch / OVH Perf / petit VPS infogéré.

**DNS** : créer `back.hyaluronicfillermarket.com` (→ hébergeur PS) et
`new.hyaluronicfillermarket.com` (→ Vercel). Le domaine apex reste sur l'ancien PS
jusqu'à la bascule finale.

**Comptes** : Vercel (relié au repo GitHub), accès au dashboard de chaque PSP
(Viva, PayPal, Amazon Pay) pour les clés sandbox puis live.

---

## Phase 1 — Back PrestaShop 9 (staging)

1. Déposer le code PS sur l'hébergeur (le repo SANS `hfm-front/` ni `node_modules`).
2. Importer la base : `mysqldump` de la base locale `admin_` → MySQL de l'hébergeur.
   (En staging c'est une copie de travail ; la réconciliation avec la prod live se fait
   en Phase 4, pas maintenant.)
3. Renseigner les accès DB dans `app/config/parameters.php` (host, db, user, pass, prefix `ps_`).
4. Mettre l'URL boutique = `back.hyaluronicfillermarket.com` :
   `ps_shop_url` (domain + domain_ssl), `ps_configuration` PS_SHOP_DOMAIN / PS_SHOP_DOMAIN_SSL.
5. CORS du module : `HFMSTOREFRONT_CORS` = `https://new.hyaluronicfillermarket.com`
   (puis ajouter le domaine prod à la bascule).
6. Vider le cache (`var/cache/*`), régénérer `.htaccess`, SSL ON, forcer HTTPS.
7. **Paiements PS en SANDBOX** + boutique en mode catalogue/maintenance le temps de la recette
   (zéro encaissement réel).
8. Vérifier que le bridge répond :
   `curl -H "X-Storefront-Token: <secret>" "https://back.…/index.php?fc=module&module=hfmstorefront&controller=taxonomy&action=menu&id_lang=1"`

## Phase 2 — Front Next.js sur Vercel (staging)

1. Importer le repo dans Vercel. **Root Directory = `hfm-front`**. Framework: Next.js (auto).
   Build `next build` (validé localement, exit 0). Node 20+.
2. Variables d'env (cf. `hfm-front/.env.production.example`) en scope **Preview** :
   - `PS_URL=https://back.hyaluronicfillermarket.com`
   - `PUBLIC_BASE_URL=https://new.hyaluronicfillermarket.com`
   - `HFM_BRIDGE_SECRET` (= secret côté PS), `HFM_SESSION_SECRET` (nouveau, fort)
   - Toutes les clés PSP en **SANDBOX**, ANS RPPS.
3. Domaine custom Vercel = `new.hyaluronicfillermarket.com`.
4. Webhooks / return URLs des PSP pointés vers `https://new.…` :
   - Viva webhook → `/api/payment/webhook`
   - Amazon Pay : déclarer `new.…` dans Seller Central (return URLs).
5. `next.config.ts` : `images.remotePatterns` → ajouter le host `back.…` (les images sont en
   `<img>` simple aujourd'hui, donc non bloquant, mais propre si on passe à next/image).

## Phase 3 — Recette (sur new.…)

Parcourir : méga-menu (MARQUES/Catalogue/Par zone/Par effet/Promos), fiches produit, panier
(+/- quantité, suppression), tunnel invité + compte, **RPPS**, **TVA VIES** (adresse UE → exonération),
**franco 400 € HT**, les **22 langues**, **une commande test de bout en bout par PSP en sandbox**,
emails transactionnels.

## Phase 4 — Bascule (go-live) — étape critique

> La base locale a divergé : la prod live encaisse encore. On NE pousse PAS la base locale en prod.

1. Choisir un créneau creux ; passer l'ancien PS 1.7.8.9 en **maintenance** (fige les commandes).
2. **Dump frais** de la prod live 1.7.8.9.
3. **Rejouer** sur ce dump : la migration 1.7.8.9 → 9.1.4 + **réappliquer nos changements**
   (catégories éditoriales 301-359 + groupes + ntree, hook franco + `HFM_FREE_SHIPPING_HT`,
   TVA, tables RPPS, langues des mails…). → à **scripter** pour être rejouable/fiable.
4. Importer la base migrée sur le back de prod.
5. Vercel : passer le projet en **Production** avec les clés PSP en **LIVE** et
   `PUBLIC_BASE_URL=https://hyaluronicfillermarket.com`. Ajouter le domaine apex à Vercel.
6. `HFMSTOREFRONT_CORS` (PS) + return URLs PSP → domaine prod.
7. **DNS** : `hyaluronicfillermarket.com` → Vercel (front) ; `back.` reste sur l'hébergeur PS.
8. Sortir de maintenance. Surveillance : 1ʳᵉ commande réelle, logs, emails, webhooks.

## Rollback

DNS pointant encore (TTL court conseillé avant bascule) → revenir à l'ancien PS en cas de
problème. Garder l'ancien 1.7.8.9 intact jusqu'à validation complète de la prod headless.

---

## Points de vigilance

- **Couplage seuil franco** : si le seuil change, MAJ `HFM_FREE_SHIPPING_HT` (PS) **et** le `400`
  codé dans `CheckoutClient.tsx` + `CartDrawer.tsx` + `header.topbarFreeShipping` (22 langues).
- **Secrets** : jamais committés. `HFM_BRIDGE_SECRET` identique des deux côtés ; `HFM_SESSION_SECRET`
  propre à chaque env.
- **Traductions légales/mails** : qualité machine, à relire avant prod.
- **Clés paiement LIVE = vrai argent** : ne les mettre qu'à l'étape 5 de la Phase 4.
