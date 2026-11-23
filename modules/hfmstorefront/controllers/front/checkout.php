<?php
/**
 * Endpoint checkout headless.
 *   GET  ?action=shipping&id_cart=..              -> transporteurs dispo + prix
 *   POST {action:set-address, id_cart, id_address_delivery, id_address_invoice?}
 *   POST {action:set-carrier, id_cart, id_carrier}
 *   POST {action:voucher,     id_cart, code}
 *   GET  ?action=gls-relays&id_cart=..[&postcode=..&city=..] -> points relais GLS proches
 *   POST {action:gls-relay, id_cart, relay_id}            -> enregistre le relais choisi
 *   POST {action:pro-declaration, id_cart, profession?, pro_number?, tax_number?, double_validation:1}
 *   POST {action:order,       id_cart, payment_method?}   -> crée la commande (validateOrder)
 * Les prix passent par le moteur PrestaShop (TVA / exonération incluses).
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/gls_relay.php';

class HfmstorefrontCheckoutModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        if ((string) $this->in('action') === 'shipping') {
            return $this->shipping();
        }
        if ((string) $this->in('action') === 'gls-relays') {
            return $this->glsRelays();
        }
        return ['error' => 'unknown_action'];
    }

    public function handlePost()
    {
        switch ((string) $this->in('action')) {
            case 'set-address':
                return $this->setAddress();
            case 'set-carrier':
                return $this->setCarrier();
            case 'voucher':
                return $this->voucher();
            case 'pro-declaration':
                return $this->proDeclaration();
            case 'pro-consent':
                return $this->proConsent();
            case 'gls-relay':
                return $this->glsSelectRelay();
            case 'order':
                return $this->createOrder();
            case 'refund-order':
                return $this->refundOrder();
            default:
                return ['error' => 'unknown_action'];
        }
    }

    protected function loadCart($enforceOwnership = true)
    {
        $cart = new Cart((int) $this->in('id_cart'));
        if (!Validate::isLoadedObject($cart)) {
            throw new Exception('cart_not_found');
        }
        // Anti-IDOR : l'appartenance est vérifiée AVANT toute opération (adresse, commande) ET
        // AVANT de lier l'identité au contexte — même invariant que l'endpoint cart. Sans ce
        // contrôle, un client authentifié pouvait piloter le panier/commande d'autrui, et la
        // ligne ci-dessous écrasait l'id_customer de session par celui du panier.
        // SEULE exception : la création de commande DÉJÀ ENCAISSÉE (retour/webhook PSP vérifié
        // côté serveur), qui n'a ni id_customer ni cart_token — cf. createOrder(). Ce chemin est
        // inatteignable par le tunnel client (paid/transaction_id y sont filtrés).
        if ($enforceOwnership && !$this->cartAccessAllowed($cart)) {
            $this->respond(['error' => 'forbidden'], 403);
        }
        // Panier rattaché à un client : c'est bien celui de la session (vérifié ci-dessus).
        if ($cart->id_customer) {
            $this->context->customer = new Customer((int) $cart->id_customer);
        }
        $this->context->cart = $cart;
        return $cart;
    }

    /**
     * Pays de livraison fermé en back-office (Pays -> désactivé) ? Le formulaire d'adresse ne le
     * propose plus, mais un client peut avoir une adresse ENREGISTRÉE dans ce pays (demande du 18/11) :
     * on ne livre alors plus (aucun transporteur) et la commande est refusée.
     */
    protected function deliveryCountryClosed(Cart $cart)
    {
        if (!$cart->id_address_delivery) {
            return false;
        }
        $idCountry = (int) (new Address((int) $cart->id_address_delivery))->id_country;
        return $idCountry > 0 && !(bool) Db::getInstance()->getValue(
            'SELECT active FROM `' . _DB_PREFIX_ . 'country` WHERE id_country = ' . $idCountry,
            false
        );
    }

    protected function shipping()
    {
        $cart = $this->loadCart();
        if ($this->deliveryCountryClosed($cart)) {
            return ['id_cart' => (int) $cart->id, 'id_zone' => 0, 'carriers' => [], 'gls_relay' => null, 'country_closed' => true];
        }
        $idZone = 0;
        if ($cart->id_address_delivery) {
            $idZone = (int) Address::getZoneById((int) $cart->id_address_delivery);
        }
        $groups = $cart->id_customer ? (new Customer((int) $cart->id_customer))->getGroups() : [(int) Configuration::get('PS_UNIDENTIFIED_GROUP')];
        $carriers = Carrier::getCarriersForOrder($idZone, $groups, $cart);
        $shopBase = rtrim(Tools::getShopDomainSsl(true) . __PS_BASE_URI__, '/');
        $out = [];
        foreach ($carriers as $c) {
            $idC = (int) $c['id_carrier'];
            // Logo transporteur PrestaShop (img/s/{id}.jpg), si présent.
            $logo = file_exists(_PS_IMG_DIR_ . 's/' . $idC . '.jpg') ? $shopBase . '/img/s/' . $idC . '.jpg' : null;
            $out[] = [
                'id_carrier' => $idC,
                'name' => $c['name'],
                'delay' => isset($c['delay']) ? $c['delay'] : '',
                'price_incl_tax' => (float) Tools::ps_round($c['price'], 2),
                'price_excl_tax' => isset($c['price_tax_exc']) ? (float) Tools::ps_round($c['price_tax_exc'], 2) : null,
                'logo' => $logo,
                // Transporteur « Relais GLS » : le client doit choisir un point relais (glsRelays).
                'gls_relay' => HfmGlsRelay::isRelayCarrier($idC),
            ];
        }
        return ['id_cart' => (int) $cart->id, 'id_zone' => $idZone, 'carriers' => $out, 'gls_relay' => HfmGlsRelay::selected($cart)];
    }

    protected function setAddress()
    {
        $cart = $this->loadCart();
        $idDelivery = (int) $this->in('id_address_delivery');
        $idInvoice = (int) $this->in('id_address_invoice', $idDelivery);
        $idInvoice = $idInvoice ?: $idDelivery;
        // Anti-IDOR / anti-oracle : l'adresse posée sur le panier DOIT appartenir au client du panier
        // (id_customer déjà vérifié par cartAccessAllowed via loadCart). Sans ce contrôle, un attaquant
        // posait l'adresse d'un tiers sur SON panier puis la relisait via cart.php (delivery_address)
        // -> fuite PII. On exige l'appartenance pour la livraison ET la facturation, sur le même
        // invariant que customer.php::updateAddress()/deleteAddress().
        $idCustomer = (int) $cart->id_customer;
        // Un panier INVITÉ (id_customer=0) n'a aucune adresse enregistrée légitime : il en crée une
        // fraîche, et il est rattaché à un client (>0) avant toute commande (createOrder l'exige).
        // Sans ce garde-fou, le test d'appartenance ci-dessous devient "=== 0" et matcherait TOUTES
        // les adresses id_customer=0 de la base -> oracle de fuite PII via un simple panier invité.
        if ($idCustomer <= 0) {
            return ['error' => 'invalid_address'];
        }
        $delivery = new Address($idDelivery);
        if (!Validate::isLoadedObject($delivery) || (int) $delivery->id_customer !== $idCustomer) {
            return ['error' => 'invalid_address'];
        }
        $invoice = new Address($idInvoice);
        if (!Validate::isLoadedObject($invoice) || (int) $invoice->id_customer !== $idCustomer) {
            return ['error' => 'invalid_address'];
        }
        $cart->id_address_delivery = $idDelivery;
        $cart->id_address_invoice = $idInvoice;
        // Pays fermé : on retire le transporteur éventuellement choisi pour une autre adresse,
        // sinon un paiement pourrait partir sur ce panier.
        if ($this->deliveryCountryClosed($cart)) {
            $cart->id_carrier = 0;
            $cart->delivery_option = '';
        }
        $cart->update();
        return ['ok' => true, 'id_cart' => (int) $cart->id];
    }

    protected function setCarrier()
    {
        $cart = $this->loadCart();
        if ($this->deliveryCountryClosed($cart)) {
            return ['error' => 'country_closed'];
        }
        $idCarrier = (int) $this->in('id_carrier');
        $cart->setDeliveryOption([(int) $cart->id_address_delivery => $idCarrier . ',']);
        $cart->id_carrier = $idCarrier;
        $cart->update();
        return [
            'ok' => true,
            'id_carrier' => $idCarrier,
            'shipping_incl_tax' => (float) Tools::ps_round($cart->getOrderTotal(true, Cart::ONLY_SHIPPING), 2),
            'total_incl_tax' => (float) Tools::ps_round($cart->getOrderTotal(true, Cart::BOTH), 2),
        ];
    }

    protected function voucher()
    {
        $cart = $this->loadCart();
        $code = (string) $this->in('code');
        $cartRule = new CartRule(CartRule::getIdByCode($code));
        if (!Validate::isLoadedObject($cartRule)) {
            return ['error' => 'invalid_voucher'];
        }
        $valid = $cartRule->checkValidity($this->context, false, true);
        if ($valid !== false && $valid !== '' && $valid !== null) {
            return ['error' => 'voucher_not_applicable', 'detail' => $valid];
        }
        $cart->addCartRule((int) $cartRule->id);
        return [
            'ok' => true,
            'total_incl_tax' => (float) Tools::ps_round($cart->getOrderTotal(true, Cart::BOTH), 2),
            'discount' => (float) Tools::ps_round($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS), 2),
        ];
    }

    /**
     * Preuve d'acceptation (IP + langue) posée AVANT le départ vers le prestataire de paiement (Viva,
     * Amazon Pay, PayPal) par les routes serveur du front, le client étant présent. Si la commande est
     * ensuite créée par la notification serveur (client parti avant de revenir sur le site), elle
     * garde ainsi l'IP et la langue du client (demande du 18/11). Seulement pour les règles à numéro
     * RPPS / attestation : la déclaration sur l'honneur enregistre déjà les siennes.
     */
    protected function proConsent()
    {
        $cart = $this->loadCart();
        $rule = HfmProRules::proProducts($cart) ? HfmProRules::ruleForCart($cart) : HfmProRules::RULE_NONE;
        if ($rule === HfmProRules::RULE_NONE || $rule === HfmProRules::RULE_DECLARATION || $rule === HfmProRules::RULE_FORBIDDEN) {
            return ['ok' => true, 'skipped' => $rule];
        }
        HfmProRules::saveConsent($cart, $rule, (string) $this->in('client_ip'), (string) $this->in('locale'));
        return ['ok' => true];
    }

    /**
     * Déclaration sur l'honneur (pays à règle « declaration ») : enregistrée sur le panier après la
     * double validation du tunnel (case cochée + « Je confirme »), puis rattachée à la commande.
     * L'IP est posée par la route serveur du front (jamais fournie par le navigateur).
     */
    protected function proDeclaration()
    {
        $cart = $this->loadCart();
        if (HfmProRules::ruleForCart($cart) !== HfmProRules::RULE_DECLARATION || !HfmProRules::proProducts($cart)) {
            return ['error' => 'declaration_not_applicable'];
        }
        if ((int) $this->in('double_validation') !== 1) {
            return ['error' => 'double_validation_required'];
        }
        HfmProRules::saveDeclaration($cart, [
            'profession' => $this->in('profession'),
            'pro_number' => $this->in('pro_number'),
            'tax_number' => $this->in('tax_number'),
            'ip' => $this->in('client_ip'),
            'lang' => $this->in('locale'),
            'text' => $this->in('declaration_text'),
        ]);
        return ['ok' => true];
    }

    /** Points relais GLS proches de l'adresse de livraison (ou du code postal / de la ville saisis). */
    protected function glsRelays()
    {
        $cart = $this->loadCart();
        if (!HfmGlsRelay::module()) {
            return ['error' => 'gls_unavailable'];
        }
        $res = HfmGlsRelay::search($cart, (string) $this->in('postcode', ''), (string) $this->in('city', ''));
        $res['selected'] = HfmGlsRelay::selected($cart);
        return $res;
    }

    protected function glsSelectRelay()
    {
        $cart = $this->loadCart();
        if (!HfmGlsRelay::module()) {
            return ['error' => 'gls_unavailable'];
        }
        return HfmGlsRelay::select($cart, (string) $this->in('relay_id'));
    }

    /**
     * Sérialise la création de commande PAR PANIER. Le retour navigateur et le webhook du PSP arrivent
     * souvent dans la même seconde : sans verrou, les deux passent le contrôle d'idempotence
     * (Order::getIdByCartId) avant que l'un ait créé la commande -> DEUX commandes pour un seul
     * paiement (constaté le 19/11 : 51631 + 51632, panier 407823, même transaction Viva).
     * GET_LOCK MySQL : le second appel attend la fin du premier puis trouve la commande existante.
     */
    protected function createOrder()
    {
        $idCart = (int) $this->in('id_cart');
        $lockName = 'hfm_order_cart_' . $idCart;
        $db = Db::getInstance();
        $locked = false;
        if ($idCart > 0) {
            try {
                $locked = (int) $db->getValue('SELECT GET_LOCK(\'' . pSQL($lockName) . '\', 30)', false) === 1;
            } catch (\Throwable $e) {
                $locked = false;
            }
            if (!$locked) {
                PrestaShopLogger::addLog('HFM checkout: verrou commande non obtenu, cart=' . $idCart, 2);
            }
        }
        try {
            return $this->createOrderLocked();
        } finally {
            if ($locked) {
                try {
                    $db->getValue('SELECT RELEASE_LOCK(\'' . pSQL($lockName) . '\')', false);
                } catch (\Throwable $e) {
                    // verrou libéré de toute façon à la fin de la connexion
                }
            }
        }
    }

    protected function createOrderLocked()
    {
        // Chemin PSP ENCAISSÉ (retours/webhook Viva/PayPal/Amazon) : identifié par `paid==1` ET un
        // `transaction_id` non vide. Ce couple ne peut PROVENIR que d'une route serveur qui a déjà
        // revérifié l'encaissement chez le PSP (bridgePost direct), JAMAIS du tunnel client :
        // app/api/checkout/route.ts retire `paid` et `transaction_id` du corps. On n'exige donc pas
        // l'appartenance du panier pour ce chemin — sinon le webhook (sans id_customer ni cart_token)
        // ne pourrait plus créer la commande -> risque « client débité, aucune commande ».
        // Le tunnel client (paid absent) conserve, lui, le gate anti-IDOR complet.
        $isPaidPsp = ((int) $this->in('paid') === 1 && trim((string) $this->in('transaction_id')) !== '');
        $cart = $this->loadCart(!$isPaidPsp);
        if (!$cart->id_customer || !$cart->id_address_delivery || !$cart->id_carrier) {
            return ['error' => 'cart_incomplete', 'detail' => 'client, adresse et transporteur requis'];
        }
        // Produits interdits à la vente dans le pays de livraison (blockproductsbycountry).
        // Pas sur le chemin PSP déjà encaissé : on ne refuse jamais une commande payée (traitée en BO).
        if (!$isPaidPsp) {
            $deliveryCountry = (int) (new Address((int) $cart->id_address_delivery))->id_country;
            foreach ($cart->getProducts() as $p) {
                if (HfmCountryRules::isBlocked((int) $p['id_product'], $deliveryCountry)) {
                    return ['error' => 'blocked_country', 'detail' => (string) $p['name']];
                }
            }
        }

        // Pays de livraison fermé en back-office : plus de commande (une commande DÉJÀ PAYÉE n'est
        // jamais refusée : elle est créée et tracée pour traitement en BO).
        if ($this->deliveryCountryClosed($cart)) {
            if (!$isPaidPsp) {
                return ['error' => 'country_closed'];
            }
            PrestaShopLogger::addLog('HFM checkout: commande payée vers un pays fermé, cart=' . (int) $cart->id, 2);
        }

        // Relais GLS : un point relais doit être choisi (le module GLS en fait l'adresse de livraison).
        if (!$isPaidPsp && HfmGlsRelay::isRelayCarrier((int) $cart->id_carrier) && !HfmGlsRelay::selected($cart)) {
            return ['error' => 'gls_relay_required'];
        }

        // Produits réservés aux pros : règle du pays de livraison (lib/pro_rules.php).
        $proRule = HfmProRules::proProducts($cart) ? HfmProRules::ruleForCart($cart) : HfmProRules::RULE_NONE;
        if ($proRule === HfmProRules::RULE_FORBIDDEN || $proRule === HfmProRules::RULE_DECLARATION) {
            $missing = $proRule === HfmProRules::RULE_FORBIDDEN
                ? 'pro_forbidden'
                : (HfmProRules::declarationForCart((int) $cart->id) === null ? 'pro_declaration_required' : '');
            if ($missing !== '') {
                // Jamais de refus sur une commande déjà encaissée : tracée pour contrôle en BO.
                if (!$isPaidPsp) {
                    return ['error' => $missing];
                }
                PrestaShopLogger::addLog('HFM checkout: commande encaissée sans ' . $missing . ', cart=' . (int) $cart->id, 2);
            }
        }

        // Contrôle RPPS (pays à règle « rpps ») : si le panier contient un produit réservé aux praticiens,
        // on exige un numéro au FORMAT permissif (9-13 chiffres). La validité réelle est confirmée
        // en back-office via une annotation sur la commande (cf. plus bas). Pas de blocage registre.
        $orderRpps = '';
        if ($proRule === HfmProRules::RULE_RPPS) {
            $idCust = (int) $cart->id_customer;
            $orderRpps = trim((string) $this->in('rpps'));
            if ($orderRpps !== '' && $this->isValidRpps($orderRpps)) {
                $this->setCustomerRpps($idCust, $orderRpps);
            } else {
                $orderRpps = $this->getCustomerRpps($idCust);
            }
            // Alternative au numéro : attestation "professionnel de santé" (réduit la friction).
            if ((int) $this->in('pro_attestation') === 1) {
                $this->setCustomerProAttestation($idCust, 1);
            }
            // Exigence satisfaite par un RPPS valide OU une attestation pro (la validité réelle
            // est confirmée en back-office via la bannière/annotation de commande).
            if (!$this->proRequirementMet($idCust)) {
                return ['error' => 'rpps_required', 'detail' => 'numéro RPPS (11 chiffres) ou ADELI (9 chiffres) ou attestation professionnelle requis'];
            }
        }
        // Idempotence : si une commande existe déjà pour ce panier (ex. retour + webhook), on la renvoie.
        $existingId = (int) Order::getIdByCartId((int) $cart->id);
        if ($existingId) {
            $existing = new Order($existingId);
            return [
                'ok' => true,
                'id_order' => $existingId,
                'reference' => $existing->reference,
                'total_paid' => (float) $existing->total_paid,
                'paid' => $this->orderIsPaid($existing),
                'already' => true,
                'payment_instructions' => $this->orderIsPaid($existing) ? null : $this->paymentInstructions(),
            ];
        }
        // Un paiement PSP ne valide qu'UNE commande : un identifiant de transaction déjà rattaché à la
        // commande d'un AUTRE panier (retour PSP rejoué sur un autre panier) est refusé. Le même panier
        // est déjà couvert par l'idempotence ci-dessus (retour + webhook simultanés).
        if ($isPaidPsp) {
            $usedBy = (int) Db::getInstance()->getValue(
                'SELECT o.id_order FROM `' . _DB_PREFIX_ . 'order_payment` op
                 INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON o.reference = op.order_reference
                 WHERE op.transaction_id = \'' . pSQL(trim((string) $this->in('transaction_id'))) . '\'
                   AND o.id_cart <> ' . (int) $cart->id
            );
            if ($usedBy) {
                PrestaShopLogger::addLog('HFM checkout: transaction PSP déjà utilisée par la commande ' . $usedBy . ', refusée pour cart=' . (int) $cart->id, 3);
                return ['error' => 'transaction_already_used'];
            }
        }

        $customer = new Customer((int) $cart->id_customer);
        // Passerelle de paiement headless = ce module lui-même (commande rattachée à "hfmstorefront").
        $paymentModule = Module::getInstanceByName('hfmstorefront');
        if (!$paymentModule || !($paymentModule instanceof PaymentModule)) {
            // Repli si le module n'est pas (encore) une passerelle de paiement valide.
            $paymentModule = Module::getInstanceByName('ps_wirepayment');
            if (!$paymentModule || !($paymentModule instanceof PaymentModule)) {
                $paymentModule = Module::getInstanceByName('ps_checkpayment');
            }
        }
        if (!$paymentModule || !($paymentModule instanceof PaymentModule)) {
            return ['error' => 'no_payment_module', 'detail' => 'passerelle de paiement indisponible'];
        }
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $paymentName = (string) $this->in('payment_method', 'Headless (' . $paymentModule->name . ')');
        // "Paiement accepté" UNIQUEMENT si l'encaissement a été confirmé côté serveur (flag `paid`
        // + `transaction_id` PSP, posés par les retours vérifiés qui appellent le bridge en direct ;
        // le tunnel client filtre ces deux champs -> commande "en préparation"). ET garde-fou anti
        // « payer 50 recevoir 500 » : le montant réellement encaissé (`amount_paid`, EN EUROS,
        // transmis par le retour PSP) DOIT couvrir le total du panier. Sinon la commande est créée
        // en « Erreur de paiement » (traçable/remboursable) au lieu de « Paiement accepté ».
        // `amount_paid` absent (retour pas encore mis à jour) -> comportement antérieur conservé +
        // journalisation : on ne casse rien au déploiement, et seules des routes serveur de
        // confiance peuvent atteindre ce chemin (le tunnel client ne peut pas envoyer `paid`).
        if ($isPaidPsp) {
            $amountPaidRaw = $this->in('amount_paid');
            if ($amountPaidRaw === null || $amountPaidRaw === '') {
                $orderState = (int) Configuration::get('PS_OS_PAYMENT');
                PrestaShopLogger::addLog('HFM checkout: paiement PSP sans amount_paid (garde-fou montant inactif), cart=' . (int) $cart->id, 2);
            } elseif ((float) $amountPaidRaw + 0.01 >= $total) {
                $orderState = (int) Configuration::get('PS_OS_PAYMENT');
            } else {
                $orderState = (int) Configuration::get('PS_OS_ERROR');
                PrestaShopLogger::addLog('HFM checkout: montant encaisse ' . (float) $amountPaidRaw . ' EUR < total panier ' . $total . ' EUR -> Erreur de paiement, cart=' . (int) $cart->id, 3);
            }
        } else {
            // Paiement HORS LIGNE (virement / chèque) : rien n'est encaissé. On utilise les états
            // natifs « En attente de virement » / « En attente du paiement par chèque » (paid=0,
            // invoice=0) comme l'ancien site, au lieu de « En cours de préparation » (paid=1,
            // invoice=1) qui émettait une facture et un OrderPayment du total sans aucun encaissement.
            // Ces états envoient aussi le mail natif bankwire/cheque avec les coordonnées de paiement.
            $offline = $this->offlineOrderState($paymentName);
            $orderState = $offline['state'];
            $mailVars = $offline['mail_vars'];
        }
        try {
            $paymentModule->validateOrder(
                (int) $cart->id,
                $orderState,
                $total,
                $paymentName,
                null,
                isset($mailVars) ? $mailVars : [],
                (int) $cart->id_currency,
                false,
                $customer->secure_key
            );
        } catch (\Throwable $e) {
            // Un module greffé sur la validation (hook actionValidateOrder…) peut planter APRÈS la
            // création de la commande : elle existe alors bel et bien. On ne renvoie pas d'erreur au
            // client dans ce cas (il repaierait / recommanderait), on journalise.
            if (!(int) Order::getIdByCartId((int) $cart->id)) {
                return ['error' => 'order_failed', 'detail' => $e->getMessage()];
            }
            PrestaShopLogger::addLog('HFM checkout: erreur après création de la commande (cart=' . (int) $cart->id . ') : ' . $e->getMessage(), 3);
        }
        $idOrder = (int) $paymentModule->currentOrder ?: (int) Order::getIdByCartId((int) $cart->id);
        $order = new Order($idOrder);

        // Trace l'identifiant de transaction PSP (Viva) sur le paiement de la commande
        // -> visible en BO et nécessaire pour les remboursements.
        $transactionId = (string) $this->in('transaction_id');
        if ($transactionId !== '') {
            foreach (OrderPayment::getByOrderReference($order->reference) as $payment) {
                $payment->transaction_id = $transactionId;
                $payment->update();
            }
        }

        // Paiement PSP : PrestaShop enregistre parfois DEUX fois l'encaissement (validateOrder ajoute un
        // paiement, puis le passage à l'état payé en ajoute un second pour le « reste à payer » de la
        // facture). Constaté le 17/11 sur la 1re commande Viva réelle : 2 x 11,67 €, « payé » 23,34 €.
        if ($isPaidPsp && Validate::isLoadedObject($order)) {
            $this->keepSinglePspPayment($order, $paymentName);
        }

        // Journal « déclaration professionnelle » de la commande (affiché en BO).
        if ($proRule !== HfmProRules::RULE_NONE) {
            HfmProRules::recordForOrder($cart, $idOrder, $proRule, $orderRpps, ['ip' => $this->in('client_ip'), 'lang' => $this->in('locale')]);
        }

        // Rattachement "pro" (RPPS / attestation / justificatif) :
        //  - CLIENT CONNECTÉ : reste sur son compte (réutilisable d'une commande à l'autre) ;
        //  - INVITÉ (compte éphémère) : on DÉPLACE les données sur la COMMANDE.
        if ($customer->is_guest) {
            // Résolution compte + mémorisation par email (l'invité a pu valider lors d'une commande précédente).
            $d = $this->resolveProData((int) $customer->id, $customer->email);
            if ($d['attestation'] || $d['pro_doc'] !== '' || $d['rpps'] !== '') {
                $this->setOrderProDoc($idOrder, $d['rpps'], (int) $d['attestation'], $d['pro_doc']);
                $this->deleteCustomerProRow((int) $customer->id);
                // (La mémorisation PAR EMAIL est conservée : prochaine commande même email = sans friction.)
            }
        }

        return [
            'ok' => true,
            'id_order' => $idOrder,
            'reference' => $order->reference,
            'total_paid' => (float) $order->total_paid,
            'paid' => $this->orderIsPaid($order),
            'transaction_id' => $transactionId,
            'rpps' => $orderRpps,
            // Coordonnées de paiement hors-ligne (virement / chèque) : renvoyées UNIQUEMENT pour une
            // commande NON encaissée (le client doit encore régler). Le front les affiche sur l'écran
            // de confirmation puisque, la commande étant rattachée à hfmstorefront, l'email natif
            // « coordonnées bancaires » de ps_wirepayment ne se déclenche pas.
            'payment_instructions' => $this->orderIsPaid($order) ? null : $this->paymentInstructions(),
        ];
    }

    /**
     * Coordonnées de paiement HORS-LIGNE, LUES AU RUNTIME depuis la configuration PrestaShop
     * (modules natifs ps_wirepayment / ps_checkpayment) — JAMAIS codées en dur, pour survivre à
     * un ré-import de la boutique. Le front choisit le bloc à afficher selon le moyen retenu.
     */
    protected function paymentInstructions()
    {
        return [
            'wire' => [
                'owner' => (string) Configuration::get('BANK_WIRE_OWNER'),
                'details' => (string) Configuration::get('BANK_WIRE_DETAILS'),
                'address' => (string) Configuration::get('BANK_WIRE_ADDRESS'),
                'reservation_days' => (int) Configuration::get('BANK_WIRE_RESERVATION_DAYS'),
            ],
            'cheque' => [
                'payee' => (string) Configuration::get('CHEQUE_NAME'),
                'address' => (string) Configuration::get('CHEQUE_ADDRESS'),
            ],
        ];
    }

    /**
     * La commande est-elle réellement ENCAISSÉE (argent capté par le PSP) ?
     *
     * Sert au front à ne déclencher la conversion Google Ads que sur du CA réel — parité avec
     * l'ancien module `gadwordstracking` qui ne taguait que si `$oOrder->valid` (virement/chèque
     * = état d'attente non logable = jamais de conversion). ATTENTION : ici on ne peut PAS se fier
     * à `$order->valid` ni au flag `paid` de l'état : ce contrôleur place les commandes non
     * encaissées en PS_OS_PREPARATION, qui est logable ET paid=1 dans PrestaShop. On compare donc
     * explicitement à l'état « Paiement accepté ».
     */
    /**
     * Ne garde qu'UN paiement quand les paiements enregistrés dépassent le total de la commande
     * (doublon créé par PrestaShop pendant la validation). Garde celui au libellé du moyen de paiement
     * (« Carte bancaire (Viva Wallet) »), le rattache aux factures et remet le montant encaissé au bon
     * chiffre. Ne touche à rien si les montants sont cohérents.
     */
    protected function keepSinglePspPayment(Order $order, $paymentName)
    {
        $payments = iterator_to_array(OrderPayment::getByOrderReference($order->reference));
        if (count($payments) < 2) {
            return;
        }
        $sum = 0.0;
        foreach ($payments as $p) {
            $sum += (float) $p->amount;
        }
        if ($sum <= (float) $order->total_paid_tax_incl + 0.01) {
            return;
        }
        $keep = null;
        foreach ($payments as $p) {
            if ((string) $p->payment_method === (string) $paymentName) {
                $keep = $p;
                break;
            }
        }
        $keep = $keep ?: reset($payments);
        $db = Db::getInstance();
        foreach ($payments as $p) {
            if ((int) $p->id === (int) $keep->id) {
                continue;
            }
            $db->delete('order_invoice_payment', 'id_order_payment = ' . (int) $p->id);
            $p->delete();
        }
        foreach ($order->getInvoicesCollection() as $invoice) {
            $linked = (int) $db->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'order_invoice_payment`
                WHERE id_order_invoice = ' . (int) $invoice->id . ' AND id_order_payment = ' . (int) $keep->id);
            if (!$linked) {
                $db->insert('order_invoice_payment', [
                    'id_order_invoice' => (int) $invoice->id,
                    'id_order_payment' => (int) $keep->id,
                    'id_order' => (int) $order->id,
                ]);
            }
        }
        Cache::clean('order_invoice_paid_*');
        $order->total_paid_real = (float) $keep->amount;
        $order->update();
        PrestaShopLogger::addLog('HFM checkout: paiement en double retiré (commande ' . (int) $order->id . ', ' . (count($payments) - 1) . ' ligne(s))', 1, null, 'Order', (int) $order->id, true);
    }

    /**
     * État initial + variables d'email d'une commande payée HORS LIGNE.
     * `payment_method` vient du front (valeurs internes FR « Virement bancaire » / « Chèque »,
     * cf. CheckoutClient PAYMENTS). Tout autre libellé hors ligne est traité comme un virement
     * (jamais comme « payé »). Repli sur PS_OS_PREPARATION seulement si l'état natif n'existe pas.
     */
    protected function offlineOrderState($paymentName)
    {
        $isCheque = in_array(Tools::strtolower(trim((string) $paymentName)), ['chèque', 'cheque'], true);
        if ($isCheque) {
            $state = (int) Configuration::get('PS_OS_CHEQUE');
            $address = (string) Configuration::get('CHEQUE_ADDRESS');
            $vars = [
                '{check_name}' => (string) Configuration::get('CHEQUE_NAME'),
                '{check_address}' => $address,
                '{check_address_html}' => str_replace("\n", '<br />', $address),
            ];
        } else {
            $state = (int) Configuration::get('PS_OS_BANKWIRE');
            $vars = [
                '{bankwire_owner}' => (string) Configuration::get('BANK_WIRE_OWNER'),
                '{bankwire_details}' => nl2br((string) Configuration::get('BANK_WIRE_DETAILS')),
                '{bankwire_address}' => nl2br((string) Configuration::get('BANK_WIRE_ADDRESS')),
            ];
        }
        if ($state <= 0) {
            $state = (int) Configuration::get('PS_OS_PREPARATION');
        }
        return ['state' => $state, 'mail_vars' => $vars];
    }

    protected function orderIsPaid(Order $order)
    {
        $paidState = (int) Configuration::get('PS_OS_PAYMENT');
        return $paidState > 0 && (int) $order->getCurrentState() === $paidState;
    }

    /**
     * Remboursement côté PrestaShop : passe la commande en état remboursé.
     * (Le remboursement réel chez Viva est fait par Next via l'API acquiring ; ici on
     *  reflète l'état dans PS.) id_order imposé/contrôlé par l'appelant serveur.
     */
    protected function refundOrder()
    {
        $idOrder = (int) $this->in('id_order');
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return ['error' => 'order_not_found'];
        }
        $state = (int) (Configuration::get('PS_OS_REFUND') ?: 0);
        if ($state) {
            $history = new OrderHistory();
            $history->id_order = $idOrder;
            $history->changeIdOrderState($state, $idOrder);
            $history->addWithemail();
        }
        return ['ok' => true, 'id_order' => $idOrder, 'state' => $state];
    }
}
