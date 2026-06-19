<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*/

// AJAX reviews loading
require(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include_once('steavisgarantis.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

if (Tools::getValue('id_product') === false or Tools::getValue('currentPage') === false or Tools::getValue('id_lang') === false) {
    exit;
}

$maxReviewsPerPage = Configuration::get('steavisgarantis_maxReviewPerPage');
$idProduct = (int)(Tools::getValue('id_product'));
$currentPage = (int)(Tools::getValue('currentPage'));
$offset = ($currentPage - 1) * $maxReviewsPerPage;
$offset = (int)($offset < 0 ? 0 : $offset);
$id_lang = (int)(Tools::getValue('id_lang'));
$sql = "SELECT * FROM "._DB_PREFIX_."steavisgarantis_reviews WHERE product_id = '$idProduct' and id_lang = '$id_lang' ORDER BY date_time DESC LIMIT $maxReviewsPerPage OFFSET $offset";
$reviews = Db::getInstance()->ExecuteS($sql);

// Add potential custom answers
$reviews = STEAVISGARANTIS::addCustomAnswers($reviews);

// On formate la date des avis
foreach ($reviews as $key => $review) {
    $reviews[$key]["date_time"] = STEAVISGARANTIS::formatDate($review["date_time"], $id_lang);
    if ($reviews[$key]["order_date"] and (strtotime($reviews[$key]["order_date"])>0)) {
        $reviews[$key]["order_date"] = STEAVISGARANTIS::formatOrderDate($review["order_date"], $id_lang);
    }
    else {
        $reviews[$key]["order_date"] = false;
    }
}

if (!isset($smarty)) {
    $smarty = Context::getContext()->smarty;
}
            
$smarty->assign(array(
    'reviews' => $reviews,
    'modules_dir' => _MODULE_DIR_
));

echo $smarty->fetch(_PS_ROOT_DIR_.'/modules/steavisgarantis/views/templates/front/load-comments.tpl');
