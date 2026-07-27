<?php
/**
 * Endpoint checkout headless.
 *   GET  ?action=shipping&id_cart=..              -> transporteurs dispo + prix
 *   POST {action:set-address, id_cart, id_address_delivery, id_address_invoice?}
 *   POST {action:set-carrier, id_cart, id_carrier}
 *   POST {action:voucher,     id_cart, code}
 *   POST {action:order,       id_cart, payment_method?}   -> crée la commande (validateOrder)
 * Les prix passent par le moteur PrestaShop (TVA / exonération incluses).
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';

class HfmstorefrontCheckoutModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        if ((string) $this->in('action') === 'shipping') {
            return $this->shipping();
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

    protected function shipping()
    {
        $cart = $this->loadCart();
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
            ];
        }
        return ['id_cart' => (int) $cart->id, 'id_zone' => $idZone, 'carriers' => $out];
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
        $cart->update();
        return ['ok' => true, 'id_cart' => (int) $cart->id];
    }

    protected function setCarrier()
    {
        $cart = $this->loadCart();
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

    protected function createOrder()
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

        // Contrôle RPPS : si le panier contient un produit réservé aux praticiens, on exige
        // un numéro au FORMAT permissif (9-13 chiffres). La validité réelle est confirmée
        // en back-office via une annotation sur la commande (cf. plus bas). Pas de blocage registre.
        $orderRpps = '';
        if ($this->cartRequiresRpps($cart)) {
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
                return ['error' => 'rpps_required', 'detail' => 'numéro RPPS (9 à 13 chiffres) ou attestation professionnelle requis'];
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
            $orderState = (int) Configuration::get('PS_OS_PREPARATION');
        }
        try {
            $paymentModule->validateOrder(
                (int) $cart->id,
                $orderState,
                $total,
                $paymentName,
                null,
                [],
                (int) $cart->id_currency,
                false,
                $customer->secure_key
            );
        } catch (\Throwable $e) {
            return ['error' => 'order_failed', 'detail' => $e->getMessage()];
        }
        $idOrder = (int) $paymentModule->currentOrder;
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
