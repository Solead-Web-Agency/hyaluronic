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
        $this->attachCustomer($cart);
        $action = (string) $this->in('action', 'add');

        if ($action === 'attach') {
            // Rattachement seul (après connexion) : on renvoie l'état du panier.
            $cart->update();
            return $this->cartPayload($cart);
        }
        $idProduct = (int) $this->in('id_product');
        $idAttribute = (int) $this->in('id_product_attribute', 0);
        $qty = (int) $this->in('qty', 1);

        switch ($action) {
            case 'add':
                // Refus si le produit est en rupture et que la vente hors stock n'est pas autorisée.
                if (!$this->isProductOrderable($idProduct, $idAttribute)) {
                    return ['error' => 'out_of_stock'] + $this->cartPayload($cart);
                }
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

    /** Lie le panier au client connecté (contexte alimenté par id_customer) s'il ne l'est pas déjà. */
    protected function attachCustomer(Cart $cart)
    {
        if (!Validate::isLoadedObject($this->context->customer)) {
            return;
        }
        $idCustomer = (int) $this->context->customer->id;
        if ((int) $cart->id_customer === $idCustomer) {
            return;
        }
        $cart->id_customer = $idCustomer;
        if (!$cart->id_address_delivery) {
            $cart->id_address_delivery = (int) Address::getFirstCustomerAddressId($idCustomer);
            $cart->id_address_invoice = $cart->id_address_delivery;
        }
        $cart->update();
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

    /**
     * Produit commandable ? Aligné sur l'affichage front "available" :
     * stock > 0, OU le produit autorise explicitement la vente hors stock (out_of_stock = 1).
     * (On n'utilise PAS le réglage global pour rester cohérent avec le bouton "M'alerter au retour".)
     */
    protected function isProductOrderable($idProduct, $idAttribute)
    {
        $qty = (int) StockAvailable::getQuantityAvailableByProduct((int) $idProduct, (int) $idAttribute);
        if ($qty > 0) {
            return true;
        }
        $p = new Product((int) $idProduct);
        return Validate::isLoadedObject($p) && (int) $p->out_of_stock === 1;
    }

    /** Lien image (cover) d'un produit du panier. */
    protected function cartLineImage($p)
    {
        $idImage = 0;
        if (!empty($p['id_image'])) {
            $idImage = (int) $p['id_image'];
        } else {
            $cover = Product::getCover((int) $p['id_product']);
            $idImage = $cover ? (int) $cover['id_image'] : 0;
        }
        if (!$idImage) {
            return null;
        }
        $linkRewrite = !empty($p['link_rewrite']) ? $p['link_rewrite'] : 'produit';
        return $this->context->link->getImageLink($linkRewrite, $idImage, 'home_default');
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
                'image' => $this->cartLineImage($p),
                'link_rewrite' => isset($p['link_rewrite']) ? $p['link_rewrite'] : '',
                'quantity' => (int) $p['cart_quantity'],
                'unit_price_excl_tax' => (float) Tools::ps_round($p['price'], 2),
                'unit_price_incl_tax' => (float) Tools::ps_round($p['price_wt'], 2),
                'total_excl_tax' => (float) Tools::ps_round($p['total'], 2),
                'total_incl_tax' => (float) Tools::ps_round($p['total_wt'], 2),
            ];
        }

        $deliveryAddress = null;
        if ($cart->id_address_delivery) {
            $a = new Address((int) $cart->id_address_delivery);
            if (Validate::isLoadedObject($a)) {
                $deliveryAddress = [
                    'firstname' => $a->firstname,
                    'lastname' => $a->lastname,
                    'address1' => $a->address1,
                    'postcode' => $a->postcode,
                    'city' => $a->city,
                    'country_iso' => Country::getIsoById((int) $a->id_country),
                ];
            }
        }

        return [
            'id_cart' => (int) $cart->id,
            'id_currency' => (int) $cart->id_currency,
            'id_customer' => (int) $cart->id_customer,
            'id_address_delivery' => (int) $cart->id_address_delivery,
            'delivery_address' => $deliveryAddress,
            'rpps_required' => $this->cartRequiresRpps($cart),
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
