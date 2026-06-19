<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'cedwish/classes/api.php';

class CedwishAuthorizationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * Initialize cart controller.
     *
     * @see FrontController::init()
     */
    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        $result = array();
        if (Tools::getIsset('code')) {
            $code =  Tools::getValue('code');
            $wishApi = new CedWishApi();
            $result = $wishApi->getToken(trim($code));
        }
        $this->context->smarty->assign(
            array(
                'code' => Tools::getValue('code'),
                'response' => $result
            )
        );

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            die(json_encode($result));
        } else {
            parent::initContent();
            $this->setTemplate("module:cedwish/views/templates/front/authorize.tpl");
        }
    }
}
