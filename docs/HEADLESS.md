# Architecture headless — Next.js + PrestaShop 9.1.4

_Mis en place le 2026-06-19. Front Next.js/React, PrestaShop 9 en back + API._

## Principe

```
Navigateur (React)
      │  (fetch, jamais de secret PS)
      ▼
Next.js  (API routes / Server Components)   ← détient les secrets
      │
      ├── Catalogue / entités  → Webservice PrestaShop (clé WS)
      └── Panier / checkout / client → Module bridge "hfmstorefront" (secret partagé)
                                              ▼
                                        PrestaShop 9.1.4 (BO + logique métier : prix, TVA, stock, transport)
```

**Règle d'or :** les secrets (clé WS, secret bridge) restent **côté serveur Next.js**. Le navigateur ne parle qu'à Next.

## 1) Catalogue — Webservice legacy

- Activé (`PS_WEBSERVICE=1`). Clé créée pour le front (voir BO > Paramètres avancés > Webservice, compte « Headless Next.js (dev) »).
- Auth : HTTP Basic, **login = la clé**, mot de passe vide.
- URL (prod, Apache) : `https://VOTRE-DOMAINE/api/{resource}` — en local (serveur PHP intégré sans rewrite) : `http://localhost:8080/webservice/dispatcher.php?url={resource}`.
- Format : ajouter `&output_format=JSON`. Filtres/limites : `&display=full&limit=20&filter[active]=1`.
- Ressources autorisées : products, categories, manufacturers, suppliers, combinations, product_options(_values), stock_availables, images, specific_prices, customers, addresses, carts, cart_rules, orders, order_details/histories/states, carriers, countries, states, zones, currencies, languages, content_management_system, tags, product_features(_values), search. (GET/HEAD ; POST/PUT sur carts/customers/addresses/orders.)

Exemple (server-side Next) :
```ts
const r = await fetch(`${PS_URL}/api/products?output_format=JSON&display=full&limit=20`, {
  headers: { Authorization: 'Basic ' + Buffer.from(`${WS_KEY}:`).toString('base64') },
});
```

> Le webservice est orienté **données** (CRUD). Il ne gère PAS proprement le tunnel d'achat → d'où le module bridge ci-dessous.

## 2) Panier / checkout / client — Module bridge `hfmstorefront`

Module maison (`modules/hfmstorefront`) exposant des endpoints **JSON**. Les prix/totaux passent par le moteur PrestaShop → **TVA, exonération B2B (`taxexempt`), stock, transport tous corrects**.

- Auth : en-tête **`X-Storefront-Token: <secret>`** (BO > module > config, clé `HFMSTOREFRONT_SECRET`). Appels **serveur-à-serveur depuis Next**.
- CORS : origines dans `HFMSTOREFRONT_CORS` (défaut `http://localhost:3000`).
- URL endpoint : `…/index.php?fc=module&module=hfmstorefront&controller={cart|customer|checkout}` (marche sans rewrite).

### ✅ Endpoint `cart` (implémenté & testé)

`GET ?id_cart=&id_lang=&id_currency=[&id_customer=]` → contenu + totaux
`POST {action, id_cart?, id_product, id_product_attribute?, qty?, id_lang, id_currency, id_customer?}`
- `action` : `add` (incrémente), `update` (qty = quantité absolue), `remove`, `clear`

Réponse :
```json
{
  "id_cart": 394403, "id_currency": 1, "id_customer": 106569, "nb_items": 2,
  "products": [{ "id_product": 6415, "name": "...", "quantity": 2,
    "unit_price_excl_tax": 29, "unit_price_incl_tax": 29,
    "total_excl_tax": 58, "total_incl_tax": 58 }],
  "totals": { "products_excl_tax": 58, "products_incl_tax": 58,
    "shipping_incl_tax": 12, "total_excl_tax": 70, "total_incl_tax": 70 }
}
```
**Vérifié** : client groupe 4 (exonéré) → TTC = HT (pas de TVA) ; client normal → TVA 20% appliquée. La logique métier PS est respectée.

### ⏳ À construire (même patron : `controllers/front/*.php` étendant `HfmStorefrontApiController`)

- **`customer`** : `POST {action:login|register|me, email, password, ...}` → valider via `Customer::getByEmail`+`checkPassword`, renvoyer l'`id_customer` (+ un JWT signé côté Next pour la session navigateur). Adresses : créer/lister via `Address`.
- **`checkout`** : 
  1. `GET shipping?id_cart=` → transporteurs dispo + prix (`Carrier::getCarriersForOrder` / `$cart->getDeliveryOptionList()`)
  2. `POST set-address {id_cart, id_address_delivery, id_address_invoice}`
  3. `POST set-carrier {id_cart, id_carrier}`
  4. `POST voucher {id_cart, code}` (`CartRule`)
  5. `POST order {id_cart, payment_module, ...}` → `validateOrder()` (création commande)
- **Paiement** : en full headless, intégrer les **SDK PSP directement dans Next** (Stripe/PayPal/Viva JS) puis créer la commande PS via `validateOrder` côté serveur après confirmation du paiement (webhook PSP → endpoint Next → bridge `order`).

## Sécurité / déploiement
- HTTPS obligatoire en prod (les secrets transitent en en-têtes).
- **Régénérer** `HFMSTOREFRONT_SECRET` et la clé WS pour la prod (les valeurs dev ne doivent pas fuiter).
- Restreindre l'IP/origine d'appel du bridge si possible (appels uniquement depuis le serveur Next).
- Garder `taxexempt`, transport, feeds côté PS (logique métier) ; ne PAS réexposer la clé WS au navigateur.
