<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Geo_visit_day extends ObjectModel
{
    public $day;
    public $month;
    public $year;
    public $ip_visit;

    public static $definition = [
        'table' => 'ets_geo_visit_day',
        'primary' => 'ip_visit',
        'fields' => [
            'day' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'month' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'year' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'ip_visit' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
        ],
    ];

    public function __construct($id = null, Context $context = null)
    {
        parent::__construct($id);
        $this->day = (int) date('j');
        $this->month = (int) date('n');
        $this->year = (int) date('Y');
        unset($context);
    }

    public function deleteOtherDay()
    {
        $sql = 'SELECT `ip_visit` 
                  FROM `' . _DB_PREFIX_ . 'ets_geo_visit_day` 
                  WHERE `day` = ' . (int) $this->day . ' 
                        AND `month` = ' . (int) $this->month . ' 
                        AND `year` = ' . (int) $this->year . ' 
                  ';

        if (!Db::getInstance()->getRow($sql)) {
            return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_geo_visit_day`');
        }

        return true;
    }
}
