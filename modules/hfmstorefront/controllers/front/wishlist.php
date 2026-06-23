<?php
/**
 * Endpoint favoris headless : liste de souhaits persistée côté serveur, par client.
 * Table dédiée (créée à la volée) -> les favoris suivent le client, pas le navigateur.
 *   GET  ?id_customer=..                          -> ids favoris + cartes produit
 *   POST {action:add|remove|toggle, id_customer, id_product}
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';

class HfmstorefrontWishlistModuleFrontController extends HfmStorefrontApiController
{
    protected function table()
    {
        return _DB_PREFIX_ . 'hfm_wishlist';
    }

    protected function ensureTable()
    {
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . $this->table() . '` (
                `id_customer` INT UNSIGNED NOT NULL,
                `id_product` INT UNSIGNED NOT NULL,
                `date_add` DATETIME NOT NULL,
                PRIMARY KEY (`id_customer`,`id_product`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
    }

    public function handleGet()
    {
        $this->ensureTable();
        $idCustomer = (int) $this->in('id_customer');
        if (!$idCustomer) {
            return ['error' => 'unauthenticated'];
        }
        return $this->payload($idCustomer);
    }

    public function handlePost()
    {
        $this->ensureTable();
        $idCustomer = (int) $this->in('id_customer');
        $idProduct = (int) $this->in('id_product');
        if (!$idCustomer) {
            return ['error' => 'unauthenticated'];
        }
        $action = (string) $this->in('action', 'toggle');
        $exists = (bool) Db::getInstance()->getValue(
            'SELECT 1 FROM `' . $this->table() . '` WHERE id_customer=' . $idCustomer . ' AND id_product=' . $idProduct
        );
        if ($action === 'add' || ($action === 'toggle' && !$exists)) {
            Db::getInstance()->execute(
                'INSERT IGNORE INTO `' . $this->table() . '` (id_customer,id_product,date_add)
                 VALUES (' . $idCustomer . ',' . $idProduct . ',NOW())'
            );
        } elseif ($action === 'remove' || ($action === 'toggle' && $exists)) {
            Db::getInstance()->execute(
                'DELETE FROM `' . $this->table() . '` WHERE id_customer=' . $idCustomer . ' AND id_product=' . $idProduct
            );
        }
        return $this->payload($idCustomer);
    }

    protected function payload($idCustomer)
    {
        $idLang = (int) $this->context->language->id;
        $rows = Db::getInstance()->executeS(
            'SELECT id_product FROM `' . $this->table() . '` WHERE id_customer=' . (int) $idCustomer . ' ORDER BY date_add DESC'
        );
        $ids = array_map(function ($r) { return (int) $r['id_product']; }, (array) $rows);
        $products = [];
        foreach ($ids as $id) {
            $p = new Product($id, false, $idLang);
            if (!Validate::isLoadedObject($p) || !$p->active) {
                continue;
            }
            $cover = Product::getCover($id);
            $idImage = $cover ? (int) $cover['id_image'] : 0;
            $products[] = [
                'id_product' => $id,
                'name' => $p->name,
                'reference' => $p->reference,
                'link_rewrite' => $p->link_rewrite,
                'brand' => $p->id_manufacturer ? Manufacturer::getNameById((int) $p->id_manufacturer) : null,
                'price_incl_tax' => (float) Tools::ps_round($p->getPrice(true), 2),
                'price_excl_tax' => (float) Tools::ps_round($p->getPrice(false), 2),
                'image' => $idImage ? $this->context->link->getImageLink($p->link_rewrite, $idImage, 'home_default') : null,
                'quantity' => (int) Product::getQuantity($id),
                'available' => (int) Product::getQuantity($id) > 0,
            ];
        }
        return ['ids' => $ids, 'products' => $products];
    }
}
