# Audit sécurité — 2026-06-19

Réalisé après la migration en PrestaShop 9.1.4. Le site **a été compromis par le passé** (fichiers cœur altérés + présence d'outils de nettoyage), mais le cœur a été **réécrit propre par l'upgrade 9.1.4**. Aucun malware actif persistant trouvé dans le code/la base au moment de l'audit.

## ✅ Neutralisé (mis en quarantaine dans `~/hyaluronic_backups/security_quarantine_2026-06-19/`)

| Fichier | Nature | Pourquoi retiré |
|---|---|---|
| `modules/connector.php` | Connecteur **LitExtension** (migration) — copie en vrac | Endpoint token unique → **file manager + SQL + export + `exec()` conditionnel**. À supprimer post-migration (reco LitExtension). |
| `modules/le_connector/` | Connecteur **LitExtension** (dossier complet + `.htaccess` désactivant la protection) | Idem. Légitime mais dangereux laissé accessible. |
| `943be8340fd0.php` | **Cleaner© @eolia** (anti-malware communautaire) | Outil puissant, ne doit pas rester public. |
| `d6336909ae93.php` | **Cleaner© @eolia** (version antérieure) | Idem. |

> Rien dans le code n'incluait ces fichiers → suppression sans impact (boot front/admin OK après).

## 🔍 Vérifié — RAS

- **Pas de webshell obfusqué** dans le code custom (modules/thème) : les signatures détectées = code de frameworks (smarty, twig, symfony) + les connecteurs ci-dessus (retirés).
- **Pas de skimmer CB** ni de `<script>` malveillant injecté dans `ps_configuration` (seuls Hotjar + un cron de livraison légitimes).
- **Fichiers PHP modifiés en 2025** = modules standards touchés par l'upgrade, rien d'injecté.
- `file-webscript.png` = vrai PNG inoffensif.

## ⚠️ À traiter (hygiène — pas de breach actif, mais surface de risque)

1. ~~**Comptes admin** : 23 employés, beaucoup de super-admins.~~ ✅ **FAIT (2026-06-19)** : seul `ma.amzallag@gmail.com` (id 25, super-admin) reste **actif**, les 22 autres désactivés (`active=0`, réversible, non supprimés pour préserver l'historique des commandes). Login `ma.amzallag` vérifié OK.
2. **Clés webservice `id 1` & `id 2`** : accès **GET/POST/PUT/DELETE sur ~360 ressources**, **sans description**. Créées par d'anciennes intégrations (Colissimo, transporteurs). → **identifier et sinon désactiver** (`ps_webservice_account`). Restreindre les méthodes (DELETE rarement nécessaire).
3. **Scripts custom en vrac à la racine** : `changemeta.php` (maj meta), `disableproduct.php` (sync prix fournisseur via XML externe `…plesk.page`). Légitimes mais **exécutables par n'importe qui**. → les déplacer hors racine web / les protéger par token, ou les passer en commande CLI/cron.

## 🔐 Avant la mise en production — rotation des secrets
- Mot de passe **admin** (celui partagé en clair doit être changé), purger les sessions.
- **Mot de passe DB** (actuellement `Bb1940045034000.` dans `parameters.php`).
- **Clés webservice** (régénérer celles à garder, supprimer le reste).
- `cookie_key`, `cookie_iv`, `secret` de `app/config/parameters.php`.
- Secret du module headless `HFMSTOREFRONT_SECRET` (valeur de dev).
- Restaurer/auditer le `.htaccess` (plusieurs sauvegardes suspectes à la racine : `.htaccess.b4lsc…`, `.htaccess.bak-avant-block-1847`).
