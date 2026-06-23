<?php
/**
 * Endpoint commandes headless : historique + détail d'une commande pour le client.
 * L'id_customer est imposé par la session côté Next ; on vérifie que la commande lui appartient.
 *   GET ?action=list&id_customer=..              -> historique
 *   GET ?action=detail&id_customer=..&id_order=..-> détail (lignes, adresse, transport, statut)
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';

class HfmstorefrontOrdersModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        $idCustomer = (int) $this->in('id_customer');
        if (!$idCustomer) {
            return ['error' => 'unauthenticated'];
        }
        if ((string) $this->in('action') === 'detail') {
            return $this->detail($idCustomer, (int) $this->in('id_order'));
        }
        return ['orders' => $this->listOrders($idCustomer)];
    }

    protected function listOrders($idCustomer)
    {
        $idLang = (int) $this->context->language->id;
        $rows = Order::getCustomerOrders($idCustomer);
        $out = [];
        foreach ((array) $rows as $r) {
            $order = new Order((int) $r['id_order']);
            $state = new OrderState((int) $order->current_state, $idLang);
            $out[] = [
                'id_order' => (int) $r['id_order'],
                'reference' => $order->reference,
                'date' => substr((string) $r['date_add'], 0, 10),
                'total_paid' => (float) Tools::ps_round($order->total_paid, 2),
                'nb_products' => (int) array_sum(array_map(function ($p) { return (int) $p['product_quantity']; }, $order->getProducts())),
                'state' => Validate::isLoadedObject($state) ? $state->name : '',
                'state_color' => Validate::isLoadedObject($state) ? $state->color : '#999',
                'payment' => $order->payment,
            ];
        }
        return $out;
    }

    protected function detail($idCustomer, $idOrder)
    {
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order) || (int) $order->id_customer !== $idCustomer) {
            return ['error' => 'order_not_found'];
        }
        $idLang = (int) $this->context->language->id;
        $state = new OrderState((int) $order->current_state, $idLang);

        $products = [];
        foreach ($order->getProducts() as $p) {
            $products[] = [
                'id_product' => (int) $p['product_id'],
                'name' => $p['product_name'],
                'reference' => isset($p['product_reference']) ? $p['product_reference'] : '',
                'quantity' => (int) $p['product_quantity'],
                'unit_price_incl_tax' => (float) Tools::ps_round($p['unit_price_tax_incl'], 2),
                'total_incl_tax' => (float) Tools::ps_round($p['total_price_tax_incl'], 2),
            ];
        }

        $addr = null;
        if ($order->id_address_delivery) {
            $a = new Address((int) $order->id_address_delivery);
            if (Validate::isLoadedObject($a)) {
                $addr = [
                    'firstname' => $a->firstname, 'lastname' => $a->lastname,
                    'address1' => $a->address1, 'postcode' => $a->postcode, 'city' => $a->city,
                    'country' => (new Country((int) $a->id_country, $idLang))->name, 'phone' => $a->phone,
                ];
            }
        }

        $carrier = $order->id_carrier ? new Carrier((int) $order->id_carrier, $idLang) : null;

        return ['order' => [
            'id_order' => (int) $order->id,
            'reference' => $order->reference,
            'date' => substr((string) $order->date_add, 0, 16),
            'state' => Validate::isLoadedObject($state) ? $state->name : '',
            'state_color' => Validate::isLoadedObject($state) ? $state->color : '#999',
            'payment' => $order->payment,
            'products' => $products,
            'total_products' => (float) Tools::ps_round($order->total_products_wt, 2),
            'total_shipping' => (float) Tools::ps_round($order->total_shipping, 2),
            'total_discounts' => (float) Tools::ps_round($order->total_discounts, 2),
            'total_paid' => (float) Tools::ps_round($order->total_paid, 2),
            'carrier' => $carrier && Validate::isLoadedObject($carrier) ? $carrier->name : '',
            'address' => $addr,
        ]];
    }
}
