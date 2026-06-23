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
            default:
                return ['error' => 'unknown_action'];
        }
    }

    protected function loadCart()
    {
        $cart = new Cart((int) $this->in('id_cart'));
        if (!Validate::isLoadedObject($cart)) {
            throw new Exception('cart_not_found');
        }
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
        $out = [];
        foreach ($carriers as $c) {
            $out[] = [
                'id_carrier' => (int) $c['id_carrier'],
                'name' => $c['name'],
                'delay' => isset($c['delay']) ? $c['delay'] : '',
                'price_incl_tax' => (float) Tools::ps_round($c['price'], 2),
                'price_excl_tax' => isset($c['price_tax_exc']) ? (float) Tools::ps_round($c['price_tax_exc'], 2) : null,
                'logo' => isset($c['logo']) ? $c['logo'] : null,
            ];
        }
        return ['id_cart' => (int) $cart->id, 'id_zone' => $idZone, 'carriers' => $out];
    }

    protected function setAddress()
    {
        $cart = $this->loadCart();
        $idDelivery = (int) $this->in('id_address_delivery');
        $idInvoice = (int) $this->in('id_address_invoice', $idDelivery);
        if (!Address::addressExists($idDelivery)) {
            return ['error' => 'invalid_address'];
        }
        $cart->id_address_delivery = $idDelivery;
        $cart->id_address_invoice = $idInvoice ?: $idDelivery;
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
        $cart = $this->loadCart();
        if (!$cart->id_customer || !$cart->id_address_delivery || !$cart->id_carrier) {
            return ['error' => 'cart_incomplete', 'detail' => 'client, adresse et transporteur requis'];
        }
        // Idempotence : si une commande existe déjà pour ce panier (ex. retour + webhook), on la renvoie.
        $existingId = (int) Order::getIdByCartId((int) $cart->id);
        if ($existingId) {
            $existing = new Order($existingId);
            return ['ok' => true, 'id_order' => $existingId, 'reference' => $existing->reference, 'total_paid' => (float) $existing->total_paid, 'already' => true];
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
        // Paiement encaissé (CB/PayPlug) => "Paiement accepté" ; sinon (virement/chèque) "en préparation".
        $orderState = ((int) $this->in('paid') === 1)
            ? (int) Configuration::get('PS_OS_PAYMENT')
            : (int) Configuration::get('PS_OS_PREPARATION');
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
        return [
            'ok' => true,
            'id_order' => $idOrder,
            'reference' => $order->reference,
            'total_paid' => (float) $order->total_paid,
        ];
    }
}
