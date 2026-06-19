<?php

class productsExportModel
{
  private $_context;

  public function __construct()
  {
    include_once(dirname(__FILE__) . '/../../config/config.inc.php');
    include_once(dirname(__FILE__) . '/../../init.php');
    $this->_context = Context::getContext();
  }

  public function searchProduct($id_shop = false, $id_lang = false, $search = false)
  {
    if ($id_shop === false) {
      $id_shop = $this->_context->shop->id;
    }
    if ($id_lang === false) {
      $id_lang = $this->_context->language->id;
    }
    $where = "";
    if ($search) {
      $where = " AND (pl.name LIKE '%" . pSQL($search) . "%' OR p.id_product LIKE '%" . pSQL($search) . "%' OR p.reference LIKE '%" . pSQL($search) . "%')";
    }
    $sql = '
			SELECT p.id_product, pl.name, p.reference
      FROM ' . _DB_PREFIX_ . 'product_lang AS pl
      LEFT JOIN ' . _DB_PREFIX_ . 'product AS p
      ON p.id_product = pl.id_product
      WHERE pl.id_lang = ' . (int)$id_lang . '
      AND pl.id_shop = ' . (int)$id_shop . '
      ' . $where . '
      ORDER BY pl.name
      LIMIT 0,50
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function searchManufacturer($search = false)
  {
    $where = "";
    if ($search) {
      $where = " AND (m.name LIKE '%" . pSQL($search) . "%' OR m.id_manufacturer LIKE '%" . pSQL($search) . "%')";
    }
    $sql = '
			SELECT m.id_manufacturer, m.name
      FROM ' . _DB_PREFIX_ . 'manufacturer AS m
      WHERE 1
      ' . $where . '
      ORDER BY m.name
      LIMIT 0,50
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function searchSupplier($search = false)
  {
    $where = "";
    if ($search) {
      $where = " AND (p.name LIKE '%" . pSQL($search) . "%' OR p.id_supplier LIKE '%" . pSQL($search) . "%')";
    }
    $sql = '
			SELECT p.id_supplier, p.name
      FROM ' . _DB_PREFIX_ . 'supplier AS p
      WHERE 1
      ' . $where . '
      ORDER BY p.name
      LIMIT 0,50
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function showCheckedProducts($id_shop = false, $id_lang = false, $products_check = false)
  {
    if ($id_shop === false) {
      $id_shop = $this->_context->shop->id;
    }
    if ($id_lang === false) {
      $id_lang = $this->_context->language->id;
    }
    $where = "";
    $limit = "  LIMIT 300 ";
    if ($products_check !== false) {
      if (!$products_check) {
        return array();
      }
      $products_check = implode(",", $products_check);
      $where = " AND p.id_product  IN (" . pSQL($products_check) . ") ";
      $limit = "";
    }
    $sql = '
			SELECT p.id_product, pl.name
      FROM ' . _DB_PREFIX_ . 'product_lang AS pl
      LEFT JOIN ' . _DB_PREFIX_ . 'product AS p
      ON p.id_product = pl.id_product
      WHERE pl.id_lang = ' . (int)$id_lang . '
      AND pl.id_shop = ' . (int)$id_shop . '
      ' . $where . '
      ORDER BY pl.name
      ' . $limit . '
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function showCheckedManufacturers($items_check = false)
  {
    $where = "";
    $limit = "  LIMIT 300 ";
    if ($items_check !== false) {
      if (!$items_check) {
        return array();
      }
      $items_check = implode(",", $items_check);
      $where = " AND m.id_manufacturer  IN (" . pSQL($items_check) . ") ";
      $limit = "";
    }
    $sql = '
			SELECT m.id_manufacturer, m.name
      FROM ' . _DB_PREFIX_ . 'manufacturer AS m
      WHERE 1
      ' . $where . '
      ORDER BY m.name
      ' . $limit . '
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function showCheckedSuppliers($items_check = false)
  {
    $where = "";
    $limit = "  LIMIT 300 ";
    if ($items_check !== false) {
      if (!$items_check) {
        return array();
      }
      $items_check = implode(",", $items_check);
      $where = " AND s.id_supplier  IN (" . pSQL($items_check) . ") ";
      $limit = "";
    }
    $sql = '
			SELECT s.id_supplier, s.name
      FROM ' . _DB_PREFIX_ . 'supplier AS s
      WHERE 1
      ' . $where . '
      ORDER BY s.name
      ' . $limit . '
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function getProductSuppliersID($productId = false)
  {
    $sql = '
			SELECT GROUP_CONCAT(DISTINCT ps.id_supplier SEPARATOR ";") AS suppliers_ids,
			GROUP_CONCAT(DISTINCT s.name SEPARATOR ";") AS suppliers_name
      FROM ' . _DB_PREFIX_ . 'product_supplier AS ps
      INNER JOIN ' . _DB_PREFIX_ . 'supplier AS s
       ON ps.id_supplier = s.id_supplier
      WHERE  ps.id_product = ' . (int)$productId . '
      AND ps.id_product_attribute = 0
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function getExportIds(
    $idShop = false,
    $idLang = false,
    $separate,
    $more_settings,
    $limit = 0,
    $limitN,
    $count = false,
    $separateAttribute = false,
    $count_images = false,
    $separatedCategories = false,
    $features = false,
    $specific_price_max_count = false
  ) {
    if (!$limit) {
      $limit = " LIMIT 0," . (int)$limitN . " ";
    } else {
      $limit = " LIMIT " . ((int)$limit * (int)$limitN) . "," . (int)$limitN . " ";
    }

    if ($idShop === false) {
      $idShop = $this->_context->shop->id;
    }

    $products_check = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_CHECKED', '',
      Context::getContext()->shop->id_shop_group, $idShop));
    $selected_manufacturers = Tools::unserialize(Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED', '',
      Context::getContext()->shop->id_shop_group, $idShop));
    $selected_suppliers = Tools::unserialize(Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED', '',
      Context::getContext()->shop->id_shop_group, $idShop));
    $selected_categories = Tools::unserialize(Configuration::get('GOMAKOIL_CATEGORIES_CHECKED', '',
      Context::getContext()->shop->id_shop_group, $idShop));
    $where = "";
    $justProducts = true;

    $price = $more_settings['price_products'];
    $quantity = $more_settings['quantity_products'];

    if ($price['price_value'] !== '' && $price['selection_type_price']) {
      if ($price['selection_type_price'] == 1) {
        $where .= ' AND (ps.price) < ' . (float)$price['price_value'];
      }
      if ($price['selection_type_price'] == 2) {
        $where .= ' AND (ps.price) > ' . (float)$price['price_value'];
      }
      if ($price['selection_type_price'] == 3) {
        $where .= ' AND (ps.price) = ' . (float)$price['price_value'];
      }
    }

    if ($quantity['quantity_value'] !== '' && $quantity['selection_type_quantity']) {
      if ($quantity['selection_type_quantity'] == 1) {
        $where .= ' AND (sa.quantity) < ' . (int)$quantity['quantity_value'];
      }
      if ($quantity['selection_type_quantity'] == 2) {
        $where .= ' AND (sa.quantity) > ' . (int)$quantity['quantity_value'];
      }
      if ($quantity['selection_type_quantity'] == 3) {
        $where .= ' AND (sa.quantity) = ' . (int)$quantity['quantity_value'];
      }
    }

    if ($more_settings['active_products']) {
      $where .= " AND ps.active = 1 ";
    }

    if ($more_settings['inactive_products']) {
      $where .= " AND ps.active = 0 ";
    }

    if ($more_settings['ean_products']) {
      $where .= " AND p.ean13 != 0 ";
    }

    if ($more_settings['specific_prices_products']) {
      $where .= " AND sp.id_specific_price != 0 ";
    }

    if ($more_settings['selection_type_visibility']) {
      $visibility = '';
      foreach ($more_settings['selection_type_visibility'] as $value) {
        if ($value == 1) {
          $visibility .= "'both'";
        } elseif ($value == 2) {
          $visibility .= "'catalog'";
        } elseif ($value == 3) {
          $visibility .= "'search'";
        } elseif ($value == 4) {
          $visibility .= "'none'";
        }
        $visibility .= ',';
      }

      $visibility = Tools::substr($visibility, 0, -1);
      $where .= " AND ps.visibility IN (" . ($visibility) . ") ";
    }

    if ($more_settings['selection_type_condition']) {
      $condition = '';
      foreach ($more_settings['selection_type_condition'] as $value) {
        if ($value == 1) {
          $condition .= "'new'";
        } elseif ($value == 2) {
          $condition .= "'used'";
        } elseif ($value == 3) {
          $condition .= "'refurbished'";
        }
        $condition .= ',';
      }

      $condition = Tools::substr($condition, 0, -1);
      $where .= " AND ps.condition IN (" . ($condition) . ") ";
    }

    if ($selected_manufacturers) {
      $justProducts = false;
      $selected_manufacturers = implode(",", $selected_manufacturers);
      $where .= " AND p.id_manufacturer IN (" . pSQL($selected_manufacturers) . ") ";
    }

    if ($selected_suppliers) {
      $justProducts = false;
      $selected_suppliers = implode(",", $selected_suppliers);
      $where .= " AND s.id_supplier IN (" . pSQL($selected_suppliers) . ") ";
    }

    if ($selected_categories) {
      $justProducts = false;
      $selected_categories = implode(",", $selected_categories);
      $where .= " AND cp.id_category IN (" . pSQL($selected_categories) . ") ";
    }
    if ($products_check) {
      $products_check = implode(",", $products_check);
      $justProducts = $justProducts ? 'AND' : 'OR';
      $where .= " $justProducts p.id_product IN (" . pSQL($products_check) . ") ";
    }


    $orderby = $more_settings['orderby'];
    $orderway = $more_settings['orderway'];
    $not_exported = $more_settings['not_exported'];

    $exported_products = '';
    $exported = '';
    if ($not_exported && isset($more_settings['automatic']) && $more_settings['automatic']) {
      $exported = ' AND ep.id_setting is null';
      $exported_products = '      LEFT JOIN ' . _DB_PREFIX_ . 'exported_products as ep
      ON ep.id_product= p.id_product  AND ep.id_setting= "' . (int)$more_settings['settings'] . '"';
    }

    if ($count_images) {
      $sql = "
      SELECT max(image_count) AS image_count
      FROM(
        SELECT  count(DISTINCT i.id_image) AS image_count
         FROM " . _DB_PREFIX_ . "product AS p
         INNER JOIN " . _DB_PREFIX_ . "product_shop AS ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product AS cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "image AS i
         ON p.id_product = i.id_product     
         LEFT JOIN " . _DB_PREFIX_ . "product_lang AS pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier AS s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available AS sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price AS sp
         ON p.id_product = sp.id_product
          " . $exported_products . "
         WHERE ps.id_shop = " . (int)$idShop . "
         AND pl.id_lang = " . (int)$idLang . "
            " . $where . "
            " . $exported . "
         GROUP BY i.id_product
      ) AS a";

      $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

      $image_count = 0;
      if (isset($res[0]['image_count']) && $res[0]['image_count']) {
        $image_count = $res[0]['image_count'];
      }

      return $image_count;
    }

    if ($specific_price_max_count) {
      $sql = "
      SELECT max(specific_price_count) AS specific_price_max_count
      FROM(
        SELECT  count(DISTINCT sp.id_specific_price) AS specific_price_count
         FROM " . _DB_PREFIX_ . "product AS p
         INNER JOIN " . _DB_PREFIX_ . "product_shop AS ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product AS cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "image AS i
         ON p.id_product = i.id_product     
         LEFT JOIN " . _DB_PREFIX_ . "product_lang AS pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier AS s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available AS sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price AS sp
         ON p.id_product = sp.id_product
          " . $exported_products . "
         WHERE ps.id_shop = " . (int)$idShop . "
         AND pl.id_lang = " . (int)$idLang . "
            " . $where . "
            " . $exported . "
         GROUP BY sp.id_product
      ) AS a";

      $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

      $specific_price_max_count = 0;
      if (isset($res[0]['specific_price_max_count']) && $res[0]['specific_price_max_count']) {
        $specific_price_max_count = $res[0]['specific_price_max_count'];
      }

      return $specific_price_max_count;
    }

    if ($separateAttribute) {
      $sql = "
        SELECT DISTINCT a.id_attribute_group
         FROM " . _DB_PREFIX_ . "product AS p
         INNER JOIN " . _DB_PREFIX_ . "product_shop AS ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product AS cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_lang AS pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier AS s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute AS pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available AS sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price AS sp
         ON p.id_product = sp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination AS pac
         ON pa.id_product_attribute = pac.id_product_attribute
         LEFT JOIN " . _DB_PREFIX_ . "attribute AS a
         ON a.id_attribute = pac.id_attribute
          " . $exported_products . "
         WHERE ps.id_shop = " . (int)$idShop . "
         AND pl.id_lang = " . (int)$idLang . "
            " . $where . "
            " . $exported . "
      ";

      $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

      return $res;
    }

    if ($separatedCategories) {
      $sql = "
        SELECT DISTINCT p.id_product
         FROM " . _DB_PREFIX_ . "product AS p
         INNER JOIN " . _DB_PREFIX_ . "product_shop AS ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product AS cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_lang AS pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier AS s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute AS pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available AS sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price AS sp
         ON p.id_product = sp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination AS pac
         ON pa.id_product_attribute = pac.id_product_attribute
         LEFT JOIN " . _DB_PREFIX_ . "attribute AS a
         ON a.id_attribute = pac.id_attribute
          " . $exported_products . "
         WHERE ps.id_shop = " . (int)$idShop . "
         AND pl.id_lang = " . (int)$idLang . "
            " . $where . "
            " . $exported . "
      ";

      $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

      return $res;
    }

    if ($features) {
      $sql = "
        SELECT DISTINCT fp.id_feature
         FROM " . _DB_PREFIX_ . "product AS p
         INNER JOIN " . _DB_PREFIX_ . "product_shop AS ps
         ON p.id_product = ps.id_product
         INNER JOIN " . _DB_PREFIX_ . "feature_product AS fp
         ON p.id_product = fp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product AS cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_lang AS pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier AS s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute AS pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available AS sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price AS sp
         ON p.id_product = sp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination AS pac
         ON pa.id_product_attribute = pac.id_product_attribute
         LEFT JOIN " . _DB_PREFIX_ . "attribute AS a
         ON a.id_attribute = pac.id_attribute
          " . $exported_products . "
         WHERE ps.id_shop = " . (int)$idShop . "
         AND pl.id_lang = " . (int)$idLang . "
            " . $where . "
            " . $exported . "
      ";

      $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

      return $res;
    }


    if (!$separate) {
      $select = ' DISTINCT p.id_product ';

      if ($count) {
        $select = ' count(DISTINCT p.id_product) as count ';
        $order = ' ORDER BY p.id_product DESC';
      } else {
        if ($orderway == 'asc') {
          $order_way = ' ASC';
        } else {
          $order_way = ' DESC';
        }
        if ($orderby == 'id') {
          $order = ' ORDER BY p.id_product' . $order_way;
        }
        if ($orderby == 'name') {
          $order = ' ORDER BY pl.name ' . $order_way . ', p.id_product ASC';
        }
        if ($orderby == 'price') {
          $order = ' ORDER BY p.price ' . $order_way . ', p.id_product ASC';
        }
        if ($orderby == 'quantity') {
          $order = ' ORDER BY sa.quantity ' . $order_way . ', p.id_product ASC';
        }
        if ($orderby == 'date_add') {
          $order = ' ORDER BY p.date_add ' . $order_way . ', p.id_product ASC';
        }
        if ($orderby == 'date_update') {
          $order = ' ORDER BY p.date_upd ' . $order_way . ', p.id_product ASC';
        }
      }

      $sql = "
        SELECT $select
         FROM " . _DB_PREFIX_ . "product as p
         INNER JOIN " . _DB_PREFIX_ . "product_shop as ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product as cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_lang as pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier as s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available as sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute as pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "specific_price as sp
         ON p.id_product = sp.id_product
          " . $exported_products . "
         WHERE ps.id_shop = " . (int)$idShop . "
          AND pl.id_lang = " . (int)$idLang . "
         " . $where . "
         " . $exported . "
         " . $order . "
         " . $limit . "

      ";


    } else {

      if ($count) {
        $sql = "
        SELECT count(*) AS count FROM (SELECT DISTINCT p.id_product , pa.id_product_attribute
         FROM " . _DB_PREFIX_ . "product AS p
         INNER JOIN " . _DB_PREFIX_ . "product_shop AS ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product AS cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier AS s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute AS pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available AS sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price AS sp
         ON p.id_product = sp.id_product
          " . $exported_products . "
         WHERE ps.id_shop = " . (int)$idShop . "
            " . $where . "
            " . $exported . "
      ) AS a
      ";
      } else {

        $order = ' ORDER BY p.id_product DESC, pa.id_product_attribute';
        if ($orderway == 'asc') {
          $order_way = ' ASC';
        } else {
          $order_way = ' DESC';
        }
        if ($orderby == 'id') {
          $order = ' ORDER BY p.id_product' . $order_way . ', pa.id_product_attribute ASC';
        }
        if ($orderby == 'name') {
          $order = ' ORDER BY pl.name ' . $order_way . ', pa.id_product_attribute ASC';
        }
        if ($orderby == 'price') {
          $order = ' ORDER BY p.price ' . $order_way . ', pa.price ' . $order_way . ', pa.id_product_attribute ASC';
        }
        if ($orderby == 'quantity') {
          $order = ' ORDER BY sa.quantity ' . $order_way . ', pa.id_product_attribute ASC';
        }
        if ($orderby == 'date_add') {
          $order = ' ORDER BY p.date_add ' . $order_way . ', pa.id_product_attribute ASC';
        }
        if ($orderby == 'date_update') {
          $order = ' ORDER BY p.date_upd ' . $order_way . ', pa.id_product_attribute ASC';
        }

        $sql = "
        SELECT DISTINCT p.id_product , pa.id_product_attribute
         FROM " . _DB_PREFIX_ . "product AS p
         INNER JOIN " . _DB_PREFIX_ . "product_shop AS ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product AS cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_lang AS pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier AS s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute AS pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available AS sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price AS sp
         ON p.id_product = sp.id_product
          " . $exported_products . "
         WHERE ps.id_shop = " . (int)$idShop . "
         AND pl.id_lang = " . (int)$idLang . "
            " . $where . "
            " . $exported . "
            " . $order . "
         " . $limit . "
      ";
      }
    }

    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if ($count) {
      return $res[0]['count'];
    }

    return $res;
  }

  public static function getProductForExportIdsAsString($more_settings, $idShop = false, $idLang = false)
  {
    if ($idShop === false) {
      $idShop = Context::getContext()->shop->id;
    }

    if (!$idLang) {
      $idLang = Context::getContext()->language->id;
    }

    $products_check = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_CHECKED', '',
      Context::getContext()->shop->id_shop_group, $idShop));
    $selected_manufacturers = Tools::unserialize(Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED', '',
      Context::getContext()->shop->id_shop_group, $idShop));
    $selected_suppliers = Tools::unserialize(Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED', '',
      Context::getContext()->shop->id_shop_group, $idShop));
    $selected_categories = Tools::unserialize(Configuration::get('GOMAKOIL_CATEGORIES_CHECKED', '',
      Context::getContext()->shop->id_shop_group, $idShop));

    $where = '';
    $justProducts = true;

    $price = $more_settings['price_products'];
    $quantity = $more_settings['quantity_products'];

    if ($price['price_value'] !== '' && $price['selection_type_price']) {
      if ($price['selection_type_price'] == 1) {
        $where .= ' AND (ps.price) < ' . (float)$price['price_value'];
      }
      if ($price['selection_type_price'] == 2) {
        $where .= ' AND (ps.price) > ' . (float)$price['price_value'];
      }
      if ($price['selection_type_price'] == 3) {
        $where .= ' AND (ps.price) = ' . (float)$price['price_value'];
      }
    }

    if ($quantity['quantity_value'] !== '' && $quantity['selection_type_quantity']) {
      if ($quantity['selection_type_quantity'] == 1) {
        $where .= ' AND (sa.quantity) < ' . (int)$quantity['quantity_value'];
      }
      if ($quantity['selection_type_quantity'] == 2) {
        $where .= ' AND (sa.quantity) > ' . (int)$quantity['quantity_value'];
      }
      if ($quantity['selection_type_quantity'] == 3) {
        $where .= ' AND (sa.quantity) = ' . (int)$quantity['quantity_value'];
      }
    }

    if ($more_settings['active_products']) {
      $where .= " AND ps.active = 1 ";
    }

    if ($more_settings['inactive_products']) {
      $where .= " AND ps.active = 0 ";
    }

    if ($more_settings['ean_products']) {
      $where .= " AND p.ean13 != 0 ";
    }

    if ($more_settings['specific_prices_products']) {
      $where .= " AND sp.id_specific_price != 0 ";
    }

    if ($more_settings['selection_type_visibility']) {
      $visibility = '';
      foreach ($more_settings['selection_type_visibility'] as $value) {
        if ($value == 1) {
          $visibility .= "'both'";
        } elseif ($value == 2) {
          $visibility .= "'catalog'";
        } elseif ($value == 3) {
          $visibility .= "'search'";
        } elseif ($value == 4) {
          $visibility .= "'none'";
        }
        $visibility .= ',';
      }

      $visibility = Tools::substr($visibility, 0, -1);
      $where .= " AND ps.visibility IN (" . ($visibility) . ") ";
    }

    if ($more_settings['selection_type_condition']) {
      $condition = '';
      foreach ($more_settings['selection_type_condition'] as $value) {
        if ($value == 1) {
          $condition .= "'new'";
        } elseif ($value == 2) {
          $condition .= "'used'";
        } elseif ($value == 3) {
          $condition .= "'refurbished'";
        }
        $condition .= ',';
      }

      $condition = Tools::substr($condition, 0, -1);
      $where .= " AND ps.condition IN (" . ($condition) . ") ";
    }

    if ($selected_manufacturers) {
      $justProducts = false;
      $selected_manufacturers = implode(",", $selected_manufacturers);
      $where .= " AND p.id_manufacturer IN (" . pSQL($selected_manufacturers) . ") ";
    }

    if ($selected_suppliers) {
      $justProducts = false;
      $selected_suppliers = implode(",", $selected_suppliers);
      $where .= " AND s.id_supplier IN (" . pSQL($selected_suppliers) . ") ";
    }

    if ($selected_categories) {
      $justProducts = false;
      $selected_categories = implode(",", $selected_categories);
      $where .= " AND cp.id_category IN (" . pSQL($selected_categories) . ") ";
    }
    if ($products_check) {
      $products_check = implode(",", $products_check);
      $justProducts = $justProducts ? 'AND' : 'OR';
      $where .= " $justProducts p.id_product IN (" . pSQL($products_check) . ") ";
    }

    $orderby = $more_settings['orderby'];
    $orderway = $more_settings['orderway'];
    $not_exported = $more_settings['not_exported'];

    $exported_products = '';
    $exported = '';
    if ($not_exported && isset($more_settings['automatic']) && $more_settings['automatic']) {
      $exported = ' AND ep.id_setting is null';
      $exported_products = '      LEFT JOIN ' . _DB_PREFIX_ . 'exported_products as ep
      ON ep.id_product= p.id_product  AND ep.id_setting= "' . (int)$more_settings['settings'] . '"';
    }

      if ($orderway == 'asc') {
        $order_way = ' ASC';
      } else {
        $order_way = ' DESC';
      }
      if ($orderby == 'id') {
        $order = ' ORDER BY p.id_product' . $order_way;
      }
      if ($orderby == 'name') {
        $order = ' ORDER BY pl.name ' . $order_way . ', p.id_product ASC';
      }
      if ($orderby == 'price') {
        $order = ' ORDER BY p.price ' . $order_way . ', p.id_product ASC';
      }
      if ($orderby == 'quantity') {
        $order = ' ORDER BY sa.quantity ' . $order_way . ', p.id_product ASC';
      }
      if ($orderby == 'date_add') {
        $order = ' ORDER BY p.date_add ' . $order_way . ', p.id_product ASC';
      }
      if ($orderby == 'date_update') {
        $order = ' ORDER BY p.date_upd ' . $order_way . ', p.id_product ASC';
      }

      $sql = "
        SELECT GROUP_CONCAT(DISTINCT p.id_product)
         FROM " . _DB_PREFIX_ . "product as p
         INNER JOIN " . _DB_PREFIX_ . "product_shop as ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product as cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_lang as pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier as s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available as sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute as pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "specific_price as sp
         ON p.id_product = sp.id_product
          " . $exported_products . "
         WHERE ps.id_shop = " . (int)$idShop . "
          AND pl.id_lang = " . (int)$idLang . "
         " . $where . "
         " . $exported . "
         " . $order . "

      ";

    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    return $res;
  }


  /**
   * Gets the name of a given product, in the given lang
   *
   * @since 1.5.0
   * @param int $id_product
   * @param int $id_product_attribute Optional
   * @param int $id_lang Optional
   * @return string
   */
  public static function getProductName($id_product, $id_product_attribute = null, $id_lang = null)
  {
    // use the lang in the context if $id_lang is not defined
    if (!$id_lang) {
      $id_lang = (int)Context::getContext()->language->id;
    }

    // creates the query object
    $query = new DbQuery();

    // selects different names, if it is a combination
    if ($id_product_attribute) {
      $query->select('IFNULL(CONCAT(pl.name COLLATE utf8_general_ci, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name');
    } else {
      $query->select('DISTINCT pl.name as name');
    }

    // adds joins & where clauses for combinations
    if ($id_product_attribute) {
      $query->from('product_attribute', 'pa');
      $query->join(Shop::addSqlAssociation('product_attribute', 'pa'));
      $query->innerJoin('product_lang', 'pl',
        'pl.id_product = pa.id_product AND pl.id_lang = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl'));
      $query->leftJoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute');
      $query->leftJoin('attribute', 'atr', 'atr.id_attribute = pac.id_attribute');
      $query->leftJoin('attribute_lang', 'al', 'al.id_attribute = atr.id_attribute AND al.id_lang = ' . (int)$id_lang);
      $query->leftJoin('attribute_group_lang', 'agl',
        'agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = ' . (int)$id_lang);
      $query->where('pa.id_product = ' . (int)$id_product . ' AND pa.id_product_attribute = ' . (int)$id_product_attribute);
    } else {
      // or just adds a 'where' clause for a simple product

      $query->from('product_lang', 'pl');
      $query->where('pl.id_product = ' . (int)$id_product);
      $query->where('pl.id_lang = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl'));
    }

    return Db::getInstance()->getValue($query);
  }
}