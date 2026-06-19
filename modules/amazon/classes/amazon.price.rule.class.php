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

class AmazonRule
{
    /** @var string */
    protected $from = array(0 => '');
    /** @var string */
    protected $to = array(0 => '');
    /** @var string */
    protected $percent = array(0 => '');
    /** @var string */
    protected $value = array(0 => '');

    /** @var string */
    protected $type = AmazonPriceRule::TYPE_PERCENT;

    public function __construct($value = array(0 => ''), $from = array(0 => ''), $to = array(0 => ''), $type = AmazonPriceRule::TYPE_PERCENT)
    {
        $this->type = $type;
        $this->from = $from;
        $this->to = $to;

        if ($this->type == AmazonPriceRule::TYPE_VALUE) {
            $this->value = $value;
        } else {
            $this->percent = $value;
        }
    }

    public function from()
    {
        return $this->from;
    }

    public function to()
    {
        return $this->to;
    }

    /**
     * The value of the rule is determined by the type (percent || value).
     * @return string|null
     */
    public function getValue()
    {
        return ($this->type == AmazonPriceRule::TYPE_PERCENT) ? $this->percent : $this->value;
    }

    /**
     * Rule object value
     * @return array
     */
    public function getRule()
    {
        $rule = array();
        $rule['from'] = $this->from();
        $rule['to'] = $this->to();
        $rule['percent'] = $this->percent;
        $rule['value'] = $this->value;

        return $rule;
    }
}