# Migration PrestaShop 1.7.8.9 → 9.1.4 — État & suite

_Dernière mise à jour : 2026-06-19_

## ✅ État actuel

- **PrestaShop 9.1.4** (code + base), tourne en local.
- **Front + back-office authentifié vérifiés** : front (home, catégorie, produit, contact, promos) **et** BO connecté (dashboard, **commandes**, **produits**, **clients**, **modules**, infos système) — tous **HTTP 200**.
- **91 / 167 modules actifs** (68 au départ de la migration).
- ✅ **Exonération TVA B2B (`taxexempt`, 170 clients) validée par test** : groupe 4 → prix HT, sinon TTC.
- ⚠️ Module **`welcome` désactivé** : sa version (trop ancienne) générait la route supprimée `admin_product_new` via le hook `displayBackOfficeHeader` → **500 sur toutes les pages admin**. (Onboarding non essentiel ; réinstaller la version PS9 si besoin.)
- **11 overrides PS9-compatibles** redéployés et actifs.
- Thème front : bascule sur **`classic`** (le thème `warehouse` est incompatible PS9 et sera remplacé par la refonte front).

## ▶️ Lancer en local

```bash
./start-local.sh
```
- Boutique : http://localhost:8080/
- Back-office : http://localhost:8080/admin78026/
- DB : MariaDB 10.6 (Docker, `docker compose up -d`), base `admin_`, host `127.0.0.1:3306`
- Runtime : **PHP 8.1** (requis PS9). PHP 7.4 n'a servi qu'à lancer la migration depuis 1.7.8.9.

## 💾 Sauvegardes — `/Users/nathancaudeli/hyaluronic_backups/`

| Fichier | Contenu |
|---|---|
| `hfm_db_2026-06-09.sql` | Dump DB d'origine (1.7.8.9) |
| `db_8.2.7.sql.gz` | Snapshot DB au palier 8.2.7 |
| `code_pre-upgrade_1.7.8.9.tgz` | Code custom avant upgrade (thème, modules, override, mails, config) |
| `override_1.7/` | Les 42 overrides 1.7 d'origine |
| `modules_incompatibles_PS9/orderedit/` | Module sorti car il cassait le conteneur admin |

## 🧩 État des modules par catégorie

### ✅ Actifs et fonctionnels (90)
Tous les **natifs PrestaShop 9** + modules custom réactivés : `everpsminimumorder`, `blockproductsbycountry` (corrigé), `eangenerator` (corrigé), `ezaddress`, `changeproductcreationdate`, `customerpdf`, `carrieronorder`, `deleteordersfree`, `groupinc`, `xmlfeeds`, `masseditproduct`, `advancedvatmanager`, `taxexempt`, `dgridproducts`, stats, `welcome`, `ps_buybuttonlite`, `sekeywords`.

### 🔧 Corrigés pour PS9 (code modifié)
- **`blockproductsbycountry`** : retrait des appels `addJquery()` (supprimé en PS9). Override `Cart::checkProductsAccess` actif (blocage produits par pays — **important réglementairement**).
- **`eangenerator`** : syntaxe PHP 8 (`$digits{1}` → `$digits[1]`).

### ✅ Validé par test runtime
- **`taxexempt`** (exonération TVA B2B, **groupe 4 = 170 clients**) : **VALIDÉ**. Test sur produit taxé + adresse FR : client groupe 4 → **29,00 HT**, client normal → **34,80 TTC** (TVA 20% correctement évitée). L'override `Product::getPriceStatic` bascule `usetax` selon le groupe puis délègue au cœur PS9. (L'ancien override `src/.../PriceCalculator` risqué n'a pas été redéployé.) Reco : un dernier test UAT via login client groupe 4 avant prod.
- **`hfmrelance`** (relance custom HFM) : activé, n'impacte pas le BO.

### 🟡 Partiellement porté
- **`orderedit`** : **activé, page commande stable** (testée : `/sell/orders/{id}/view` → 200, aucune exception). ✅ Décoration corrigée (`TranslatorInterface` Component→Contracts, signature handler conforme PS9) ; ✅ overrides de thème de formulaire (`TwigTemplateForm`, qui utilisaient `{% spaceless %}` supprimé en Twig 3.12) **retirés** → PS9 utilise son thème natif. ❌ **Reste** : son UI d'édition (blocs `Sell/Order/.../Blocks/View`) ne s'affiche pas encore (PS9 ne charge pas ces overrides de templates admin tels quels). À finaliser : ré-appliquer les modifs d'orderedit sur les **templates de page commande actuels de PS9** (et non les copies 1.7). Module sensible → tester.

### ❌ À porter / diagnostiquer
- **`exportproducts`** : `enable` CLI échoue car le service `ModuleOverrideChecker` n'est pas dans le conteneur **console** (il existe pourtant côté web). Son override `Configuration::getGlobalValue` force `loadConfiguration()` à chaque appel (perf globale dégradée). → activer via l'**UI admin** si besoin, ou réécrire l'override. Faible priorité (outil d'export).
- **`thecheckout`** : références à l'ancienne `TranslatorInterface` dans `classes/` (5 fichiers). Module de **tunnel de commande payant** → privilégier la version PS9 éditeur ; laissé désactivé (n'impacte pas le checkout standard actuel).
- **`deliveryorderautoupdate`**, **`hscombineguests`** : erreurs à diagnostiquer.

### 💡 Limite CLI utile à retenir
`php bin/console prestashop:module enable <m>` **échoue pour les modules qui déploient un override** (`ModuleOverrideChecker` absent du conteneur console). Workaround : activer ces modules depuis l'**UI admin** (Modules > Gestionnaire), ou déployer l'override manuellement + `active=1` en base.

### 🛠️ Correctifs GLOBAUX de compatibilité (réparent plusieurs modules d'un coup — philosophie « garder + corriger »)
- **`config/defines_custom.inc.php`** : `define('_CAN_LOAD_FILES_', true)` — constante legacy supprimée en PS9 ; sans elle, les vieux modules avec `if (!defined('_CAN_LOAD_FILES_')) exit;` (ex. groupinc) font **exit** au chargement → réponse 0 octet / page blanche (notamment le gestionnaire de modules). Définit aussi `_PS_MODE_DEV_=false` (prod).
- **`override/classes/Tools.php`** : réintroduit `jsonEncode`, `jsonDecode`, `encrypt`, `decrypt`, `getValueRaw` (supprimées en PS9, encore appelées par ~50 modules).
- **`override/classes/controller/AdminController.php`** : réintroduit `l()` (protected, supprimée des contrôleurs admin PS9) — couvre tous les `ModuleAdminController` (ets_seo, etc.). Protected pour rester compatible avec les modules qui déclarent leur propre `l()`.
- **Garde `class_exists`** ajoutée automatiquement sur les classes des dossiers `backward_compatibility/` et `override*/` des vieux modules (évite « Cannot redeclare class » Context/Shop/Helper/Product…).
- Modules corrigés individuellement : `tggatos` (`get()`→`getConfVar()`), `ets_seo` (`Tools::encrypt`→`hash`), `eangenerator` (`{}`→`[]`), `blockproductsbycountry` (`addJquery`).

### 🧠 Patterns d'incompatibilité PS9 rencontrés (réutilisables pour le portage)
| Symptôme | Cause | Fix |
|---|---|---|
| `Call to undefined method ...::addJquery()` | `addJquery()` supprimé (jQuery chargé par défaut) | retirer l'appel |
| `Array/string offset access with curly braces` (Fatal compile) | `$x{$i}` interdit en PHP 8 | `$x{$i}` → `$x[$i]` |
| `non-existent service "Symfony\Component\Translation\TranslatorInterface"` | interface déplacée (Symfony 6) | `Component\Translation` → `Contracts\Translation` ; injecter `@translator` |
| `Unexpected "spaceless" tag` (Twig SyntaxError) | `{% spaceless %}` supprimé en Twig 3.12 | `{% apply spaceless %}…{% endapply %}` |
| `Attempted to call an undefined method "encrypt" of class "Tools"` (erreur sur TOUTES les pages BO) | `Tools::encrypt()` supprimé ; un module **inactif** mais avec onglets `ps_tab` est instancié au rendu du menu admin | `Tools::encrypt(` → `Tools::hash(` (fait pour `ets_seo`) ; pattern : un module inactif à onglets peut casser tout le BO |
| `Attempted to call an undefined method "l"` en cliquant un menu de module inactif (ets_seo, iqit…) | les **onglets `ps_tab` de modules désactivés** restaient visibles/cliquables → contrôleurs 1.7 (`$this->l()`, etc.) → crash | **Masqués** : `UPDATE ps_tab t JOIN ps_module m ON m.name=t.module COLLATE utf8mb4_general_ci SET t.active=0 WHERE m.active=0 AND t.module<>''` (129 onglets masqués). Réversible : réactiver le module réaffiche ses onglets. ⚠️ collation mixte `general_ci`/`unicode_ci` héritée de la migration → `COLLATE` obligatoire dans les JOIN ps_tab↔ps_module. |
| `Cannot declare class X, name already in use` | lib partagée entre modules, classe sans garde | encadrer de `if (!class_exists('X', false)) { … }` |
| Conteneur admin qui casse (500 sans erreur PHP) | `services.yml` d'un module **installé** (même inactif) chargé au boot | corriger/sortir le `services.yml` |
| `Unable to generate a URL for the named route "admin_product_new"` (500 sur **toutes** les pages BO) | un module (hook `displayBackOfficeHeader`) appelle une route admin renommée | màj/désactiver le module (route PS9 = `admin_products_create`) |

### 💳 Payants — version PS9 à récupérer chez l'éditeur (action **toi**)
`amazon`/`amazonpay`, `dhlexpress`/`dhlexpresscommerce`, `vivawalletsmartcheckout`, `payplug`, `paypal`, `steavisgarantis`, `cartsguru`, `migrationpro`, `gmerchantcenter`/`gmerchantcenterpro`, `productbundlespro`, `pscartabandonmentpro`, `ets_*`, `revsliderprestashop`, `elegantaleasyimport`, `autotranslator`, `chatgptpro`, `hotjar`, `zendesk`…
> Une licence ne suffit pas : il faut la **version PS9** du module. Les fichiers actuels sont conservés (dormants) ; réinstaller la version PS9 + licence quand dispo.

### 🗑️ Jetables (refonte front en IA)
Suite **Warehouse / `iqit*`** (~23 modules), blog `ph_*` (`ph_simpleblog`, `ph_relatedposts`, `ph_blog_column_custom`), `custompopup`, `arcontactus`.

### ☁️ Suite PrestaShop cloud (optionnel, capricieux)
`ps_accounts`, `ps_eventbus`, `ps_mbo`, `ps_checkout`, `psaddonsconnect` — nécessitent une connexion addons/cloud. Non réactivés (non critiques en local).

## 🔁 Overrides
- Système d'overrides **réactivé** (`PS_DISABLE_OVERRIDES=0`, `config/defines_custom.inc.php` remis à vide).
- 11 overrides redéployés automatiquement par les modules réactivés, tous PS9-compatibles (boot 200) : `Cart`, `Product`, `ObjectModel`, `CustomerAddressForm`, `TaxRulesTaxManager`, PDF (`HTMLTemplateInvoice`/`OrderSlip`), `CheckoutPaymentStep`, contrôleurs front (`Address`, `Order`, `Cart`).
- Les overrides 1.7 d'origine incompatibles (`Dispatcher` notamment) sont en sauvegarde, **non réintégrés**.

## 🔒 Sécurité
- Site **historiquement compromis** (cœur réécrit propre par l'upgrade 9.1.4).
- `943be8340fd0.php` / `d6336909ae93.php` = **Cleaner© @eolia** (outil anti-malware légitime, pas un webshell) — à retirer d'un environnement public.
- Avant prod : scan des modules/thème custom restants.

## 📌 Prochaines étapes conseillées
1. Valider la TVA `taxexempt` sur une commande groupe exonéré.
2. Porter `orderedit` / `thecheckout` (TranslatorInterface) et `gremarketing`/`hfmrelance`.
3. Récupérer les versions PS9 des modules payants (paiement/transport en priorité).
4. Brancher la refonte front (thème PS9) → supprimer définitivement Warehouse/`iqit*`.
5. Nettoyage sécurité + passage en prod.
