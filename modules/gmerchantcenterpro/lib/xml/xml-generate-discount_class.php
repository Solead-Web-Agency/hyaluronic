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

class BT_XmlGenerateDiscount extends BT_BaseXml
{
    /**
     * @param array $aParams
     */
    public function __construct($aParams = array())
    {
        require_once(_GMCP_PATH_LIB_XML . 'xml-discount_class.php');
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
        $aAssign = array();

        $oDiscountXml = new BT_XmlDiscount();

        $aParams = array(
            'iLangId' => !empty(Tools::getValue('gmcp_lang_id'))  ? Tools::getValue('gmcp_lang_id') : Tools::getValue('id_lang'),
            'bOutput' => 1,
            'sType' => Tools::getValue('feed_type'),
        );

        //set the header
        $oDiscountXml->header($aParams);

        $oDiscountXml->buildDiscountXml($aParams);

        //set footer
        $oDiscountXml->footer($aParams);

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_GENERATE_OUTPUT,
            'assign' => $aAssign,
        );
    }
}
