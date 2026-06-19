<?php
/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

require_once(_GMCP_PATH_LIB_XML . 'base-xml_class.php');

class BT_XmlGenerateLocal
{
    /**
     * @param array $aParams
     */
    public function __construct($aParams = array())
    {
        $this->data = new stdClass();
        $this->sContent = '';
        $this->aParams = $aParams;
        $this->bOutput = 1;
    }

    /**
     * get the XML for current data feed type
     *
     */
    public function generate()
    {
        require_once(_GMCP_PATH_LIB_XML . 'base-product-strategy_class.php');

		return BT_BaseProductStrategy::get('local', array('type' => 'local'))->generate(array('reporting' => 0));
    }
}
