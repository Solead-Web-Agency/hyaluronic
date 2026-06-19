<?php
/**
 * Endpoint panier headless.
 *   GET  ?id_cart=..&id_lang=..&id_currency=..[&id_customer=..]   -> contenu + totaux
 *   POST {action:add|update|remove|clear, id_cart?, id_product, id_product_attribute?, qty?, id_lang, id_currency, id_customer?}
 * Les prix/totaux passent par le calcul PrestaShop (l'exonération TVA taxexempt s'applique si le client est exonéré).
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';

class HfmstorefrontCartModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        $cart = $this->loadCart((int) $this->in('id_cart'));
        if (!Validate::isLoadedObject($cart)) {
            return ['error' => 'cart_not_found'];
        }
        return $this->cartPayload($cart);
    }

    public function handlePost()
    {
        $cart = $this->loadOrCreateCart();
        $action = (string) $this->in('action', 'add');
        $idProduct = (int) $this->in('id_product');
        $idAttribute = (int) $this->in('id_product_attribute', 0);
        $qty = (int) $this->in('qty', 1);

        switch ($action) {
            case 'add':
                $cart->updateQty(max(1, $qty), $idProduct, $idAttribute, false, 'up');
                break;
            case 'update': // qty = quantité absolue cible
                $current = $this->productQty($cart, $idProduct, $idAttribute);
                $delta = $qty - $current;
                if ($delta !== 0) {
                    $cart->updateQty(abs($delta), $idProduct, $idAttribute, false, $delta > 0 ? 'up' : 'down');
                }
                break;
            case 'remove':
                $cart->deleteProduct($idProduct, $idAttribute);
                break;
            case 'clear':
                foreach ($cart->getProducts() as $p) {
                    $cart->deleteProduct((int) $p['id_product'], (int) $p['id_product_attribute']);
                }
                break;
            default:
                return ['error' => 'unknown_action'];
        }
        $cart->update();

        return $this->cartPayload($cart);
    }

    protected function loadCart($idCart)
    {
        $cart = new Cart($idCart);
        if (Validate::isLoadedObject($cart)) {
            $this->context->cart = $cart;
        }
        return $cart;
    }

    protected function loadOrCreateCart()
    {
        $idCart = (int) $this->in('id_cart');
        if ($idCart) {
            $cart = $this->loadCart($idCart);
            if (Validate::isLoadedObject($cart)) {
                return $cart;
            }
        }
        $cart = new Cart();
        $cart->id_lang = (int) ($this->context->language->id);
        $cart->id_currency = (int) ($this->context->currency->id);
        $cart->id_shop = (int) $this->context->shop->id;
        $cart->id_shop_group = (int) $this->context->shop->id_shop_group;
        if (Validate::isLoadedObject($this->context->customer)) {
            $cart->id_customer = (int) $this->context->customer->id;
            $cart->id_address_delivery = (int) Address::getFirstCustomerAddressId($cart->id_customer);
            $cart->id_address_invoice = $cart->id_address_delivery;
        }
        $cart->add();
        $this->context->cart = $cart;

        return $cart;
    }

    protected function productQty($cart, $idProduct, $idAttribute)
    {
        foreach ($cart->getProducts() as $p) {
            if ((int) $p['id_product'] === $idProduct && (int) $p['id_product_attribute'] === $idAttribute) {
                return (int) $p['cart_quantity'];
            }
        }
        return 0;
    }

    protected function cartPayload(Cart $cart)
    {
        $products = [];
        foreach ($cart->getProducts() as $p) {
            $products[] = [
                'id_product' => (int) $p['id_product'],
                'id_product_attribute' => (int) $p['id_product_attribute'],
                'name' => $p['name'],
                'reference' => isset($p['reference']) ? $p['reference'] : '',
                'quantity' => (int) $p['cart_quantity'],
                'unit_price_excl_tax' => (float) Tools::ps_round($p['price'], 2),
                'unit_price_incl_tax' => (float) Tools::ps_round($p['price_wt'], 2),
                'total_excl_tax' => (float) Tools::ps_round($p['total'], 2),
                'total_incl_tax' => (float) Tools::ps_round($p['total_wt'], 2),
            ];
        }

        return [
            'id_cart' => (int) $cart->id,
            'id_currency' => (int) $cart->id_currency,
            'id_customer' => (int) $cart->id_customer,
            'nb_items' => (int) $cart->nbProducts(),
            'products' => $products,
            'totals' => [
                'products_excl_tax' => (float) Tools::ps_round($cart->getOrderTotal(false, Cart::ONLY_PRODUCTS), 2),
                'products_incl_tax' => (float) Tools::ps_round($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS), 2),
                'shipping_incl_tax' => (float) Tools::ps_round($cart->getOrderTotal(true, Cart::ONLY_SHIPPING), 2),
                'total_excl_tax' => (float) Tools::ps_round($cart->getOrderTotal(false, Cart::BOTH), 2),
                'total_incl_tax' => (float) Tools::ps_round($cart->getOrderTotal(true, Cart::BOTH), 2),
            ],
        ];
    }
}
