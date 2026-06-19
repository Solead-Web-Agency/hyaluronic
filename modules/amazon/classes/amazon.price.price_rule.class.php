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

require_once(dirname(__FILE__) . '/../classes/amazon.price.rule.class.php');

class AmazonPriceRule
{
    const DEFAULT_MKP_PRICE_RULE = 'pr_mkp';
    const DEFAULT_PROFILE_PRICE_RULE = 'pr_profile';

    const TYPE_PERCENT = 'percent';
    const TYPE_VALUE = 'value';

    /** @var string */
    protected $profileKey = null;
    /** @var string */
    protected $mkpId = null;
    /** @var int */
    protected $idLang;
    /** @var Currency */
    protected $currency_sign;
    /** @var string */
    protected $type;
    /** @var AmazonRule */
    protected $rule = array();

    protected $errors = array();

    public function __construct($profile_key = null, $id_lang = null, $mkpId = null, $type = self::TYPE_PERCENT)
    {
        if ($profile_key) {
            $this->profileKey = $profile_key;
        }
        if ($mkpId) {
            $this->mkpId = $mkpId;
        }
        $this->idLang = (Language::getLanguage($id_lang) !== false) ? $id_lang : Configuration::get('PS_LANG_DEFAULT');
        $this->type = ($type == self::TYPE_PERCENT) ? self::TYPE_PERCENT : self::TYPE_VALUE;
    }

    /**
     * @return bool|Currency|mixed|string|null
     */
    protected function currencySign()
    {
        $current_currency = Currency::getDefaultCurrency();
        $this->currency_sign = isset($current_currency->sign) ? $current_currency->sign : null;
        return $this->currency_sign;
    }

    public function type()
    {
        return $this->type;
    }

    /**
     * Get price rule values
     * @return array|mixed
     */
    public function getPriceRule()
    {
        $priceRules = AmazonConfiguration::get('PRICE_RULES');

        if ($priceRules && is_array($priceRules)) {
            if ($priceRules[self::DEFAULT_MKP_PRICE_RULE] && isset($priceRules[self::DEFAULT_MKP_PRICE_RULE][$this->mkpId])) {
                return $priceRules[self::DEFAULT_MKP_PRICE_RULE][$this->mkpId];
            }
            if ($priceRules[self::DEFAULT_PROFILE_PRICE_RULE] && isset($priceRules[self::DEFAULT_PROFILE_PRICE_RULE][$this->profileKey]) && isset($priceRules[self::DEFAULT_PROFILE_PRICE_RULE][$this->profileKey][$this->idLang])) {
                return $priceRules[self::DEFAULT_PROFILE_PRICE_RULE][$this->profileKey][$this->idLang];
            }
        }

        return $this->getEmptyPriceRule();
    }

    /**
     * Create or update price rule values
     * @return void
     */
    public function updatePriceRule()
    {
        $rules = array();
        $amzPriceRules[self::DEFAULT_MKP_PRICE_RULE] = array();
        $amzPriceRules[self::DEFAULT_PROFILE_PRICE_RULE] = array();

        if ($this->mkpId) {
            $rules[$this->mkpId]['currency_sign'] = $this->currencySign();
            $rules[$this->mkpId]['type'] = $this->type;
            $rules[$this->mkpId]['rule'] = $this->getRule();
            $amzPriceRules[self::DEFAULT_MKP_PRICE_RULE] = $rules;
        } elseif ($this->profileKey) {
            $rules[$this->profileKey][$this->idLang]['currency_sign'] = $this->currencySign();
            $rules[$this->profileKey][$this->idLang]['type'] = $this->type;
            $rules[$this->profileKey][$this->idLang]['rule'] = $this->getRule();
            $amzPriceRules[self::DEFAULT_PROFILE_PRICE_RULE] = $rules;
        }

        // Update or create price rules
        AmazonConfiguration::updateValue('PRICE_RULES', $amzPriceRules);
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

        return $priceRule;
    }

    /**
     * Get default price rule
     * @return array
     */
    protected function getRule()
    {
        $rule = new AmazonRule();
        return $rule->getRule();
    }

    public function calculate()
    {

    }
}