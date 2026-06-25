<?php
/**
 * Endpoint catalogue headless : liste + fiche produit, en JSON propre (prix corrects via le moteur PS).
 *   GET ?id_lang=&id_currency=[&id_customer=]&limit=&page=&id_category=   -> liste de cartes produit
 *   GET ?id_product=..                                                    -> fiche produit détaillée
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';

class HfmstorefrontProductsModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        $idLang = (int) $this->context->language->id;
        $idProduct = (int) $this->in('id_product');
        if ($idProduct) {
            return $this->single($idProduct, $idLang);
        }
        return $this->listing($idLang);
    }

    protected function listing($idLang)
    {
        $limit = min(60, max(1, (int) $this->in('limit', 24)));
        $page = max(1, (int) $this->in('page', 1));
        $start = ($page - 1) * $limit;
        $idCategory = (int) $this->in('id_category');
        $idManufacturer = (int) $this->in('id_manufacturer');
        $q = trim((string) $this->in('q'));

        if ($q !== '') {
            // Recherche plein-texte sur le nom : on récupère les ids puis on pagine.
            $found = Product::searchByName($idLang, $q);
            $ids = array_map(function ($r) { return (int) $r['id_product']; }, (array) $found);
            $ids = array_slice($ids, $start, $limit);
            $rows = array_map(function ($id) { return ['id_product' => $id]; }, $ids);
        } elseif ($idManufacturer) {
            $rows = Manufacturer::getProducts($idManufacturer, $idLang, $page, $limit, 'id_product', 'DESC');
        } elseif ($idCategory) {
            $category = new Category($idCategory, $idLang);
            $rows = $category->getProducts($idLang, $page, $limit, 'id_product', 'DESC');
        } else {
            $rows = Product::getProducts($idLang, $start, $limit, 'id_product', 'DESC', false, true);
        }
        $items = [];
        foreach ((array) $rows as $r) {
            $items[] = $this->card((int) $r['id_product'], $idLang);
        }
        return ['page' => $page, 'limit' => $limit, 'count' => count($items), 'products' => array_values(array_filter($items))];
    }

    protected function card($idProduct, $idLang)
    {
        $p = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($p) || !$p->active) {
            return null;
        }
        $cover = Product::getCover($idProduct);
        $idImage = $cover ? (int) $cover['id_image'] : 0;
        $qty = (int) Product::getQuantity($idProduct);
        return [
            'id_product' => (int) $p->id,
            'name' => $p->name,
            'reference' => $p->reference,
            'link_rewrite' => $p->link_rewrite,
            'brand' => $p->id_manufacturer ? Manufacturer::getNameById((int) $p->id_manufacturer) : null,
            'price_incl_tax' => (float) Tools::ps_round($p->getPrice(true), 2),
            'price_excl_tax' => (float) Tools::ps_round($p->getPrice(false), 2),
            'image' => $idImage ? $this->context->link->getImageLink($p->link_rewrite, $idImage, 'home_default') : null,
            'quantity' => $qty,
            'available' => $qty > 0 || (int) $p->out_of_stock == 1,
            'rpps_required' => $this->productRequiresRpps($idProduct),
        ];
    }

    protected function single($idProduct, $idLang)
    {
        $p = new Product($idProduct, true, $idLang);
        if (!Validate::isLoadedObject($p)) {
            return ['error' => 'product_not_found'];
        }
        $images = [];
        foreach ($p->getImages($idLang) as $img) {
            $images[] = $this->context->link->getImageLink($p->link_rewrite, (int) $img['id_image'], 'large_default');
        }
        $features = [];
        foreach ($p->getFrontFeatures($idLang) as $f) {
            $features[] = ['name' => $f['name'], 'value' => $f['value']];
        }
        return ['product' => [
            'id_product' => (int) $p->id,
            'name' => $p->name,
            'reference' => $p->reference,
            'link_rewrite' => $p->link_rewrite,
            'description' => $p->description,
            'description_short' => $p->description_short,
            'price_incl_tax' => (float) Tools::ps_round($p->getPrice(true), 2),
            'price_excl_tax' => (float) Tools::ps_round($p->getPrice(false), 2),
            'manufacturer' => $p->id_manufacturer ? Manufacturer::getNameById((int) $p->id_manufacturer) : null,
            'quantity' => (int) Product::getQuantity($idProduct),
            'available' => (int) Product::getQuantity($idProduct) > 0 || (int) $p->out_of_stock === 1,
            'rpps_required' => $this->productRequiresRpps($idProduct),
            'images' => $images,
            'features' => $features,
        ]];
    }
}
