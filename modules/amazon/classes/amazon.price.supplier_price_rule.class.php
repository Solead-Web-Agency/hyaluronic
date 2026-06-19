<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }

require_once(dirname(__FILE__) . '/../classes/amazon.price.price_rule.class.php');

class AmazonSupplierPriceRule extends AmazonPriceRule
{
    const SUPPLIER_PRICE_RULE = 'pr_supplier';

    protected $supplier = null;

    public function __construct($profile_key = null, $id_lang = null, $mkpId = null, $type = self::TYPE_PERCENT)
    {
        parent::__construct($profile_key, $id_lang, $mkpId, $type);
    }

    /**
     * Get default price rule
     * @return array
     */
    protected function getEmptyPriceRule()
    {
        $priceRule = array();
        $rule = new AmazonRule();
        $priceRule['currency_sign'] = $this->currencySign();
        $priceRule['type'] = $this->type;
        $priceRule['rule'] = $rule->getRule();
        $priceRule['supplier'] = $this->supplier;

        return $priceRule;
    }

    /**
     * @param $supplierPriceRule
     * @return void
     */
    public function updateSupplierPriceRule($supplierPriceRule)
    {
        $priceRules = AmazonConfiguration::get('PRICE_RULES');
        $supplierPriceRule['currency_sign'] = $this->currencySign();
        $priceRules[self::SUPPLIER_PRICE_RULE][$this->profileKey][$this->idLang] = $supplierPriceRule;

        AmazonConfiguration::updateValue('PRICE_RULES', $priceRules);
    }

    /**
     * Get supplier price rule values
     * @return array|mixed
     */
    public function getPriceRule()
    {
        $priceRules = AmazonConfiguration::get('PRICE_RULES');

        if ($priceRules && is_array($priceRules)
            && $priceRules[self::SUPPLIER_PRICE_RULE]
            && isset($priceRules[self::SUPPLIER_PRICE_RULE][$this->profileKey])
            && isset($priceRules[self::SUPPLIER_PRICE_RULE][$this->profileKey][$this->idLang])) {
            return $priceRules[self::SUPPLIER_PRICE_RULE][$this->profileKey][$this->idLang];
        }

        return $this->getEmptyPriceRule();
    }

    /**
     * Check have rule base on supplier id
     * @param int $supplierId
     * @return bool
     */
    public function hasSupplierPriceRule($supplierId)
    {
        $priceRules = AmazonConfiguration::get('PRICE_RULES');

        if ($priceRules && is_array($priceRules)
            && $priceRules[self::SUPPLIER_PRICE_RULE]
            && isset($priceRules[self::SUPPLIER_PRICE_RULE][$this->profileKey])
            && isset($priceRules[self::SUPPLIER_PRICE_RULE][$this->profileKey][$this->idLang])
            && $priceRules[self::SUPPLIER_PRICE_RULE][$this->profileKey][$this->idLang]['supplier'] == (int)$supplierId) {
            return true;
        }
        return false;
    }
}