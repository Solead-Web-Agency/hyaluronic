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
class AmazonSellerPartnerQuotaHandler implements IAmazonSellerPartnerQuotaHandler
{
    /**
     * max: max request per once
     * rsc: restore count per rst
     * rst: restore cycle time (second)
     * min: min request (compared to remain)
     */
    const THROTTLE_SETTING = array(
        AmazonSellerPartnerAPI::API_TYPE_REPORTS_CREATE                 => array('max' => 15, 'rsc' => 1, 'rst' => 60, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_REPORTS_GET                    => array('max' => 15, 'rsc' => 2, 'rst' => 1,  'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_REPORTS_DOCUMENT_GET           => array('max' => 15, 'rsc' => 1, 'rst' => 60, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_ORDERS_GET_LIST                => array('max' => 20, 'rsc' => 1, 'rst' => 60, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_ORDERS_GET_ORDER_ITEMS         => array('max' => 30, 'rsc' => 1, 'rst' => 2,  'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_SELLERS                        => array('max' => 15, 'rsc' => 1, 'rst' => 60, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_FEEDS_GET                      => array('max' => 15, 'rsc' => 2, 'rst' => 1,  'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_FEEDS_GET_BULK                 => array('max' => 10, 'rsc' => 1, 'rst' => 45, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_FEEDS_GET_BY_ID                => array('max' => 5, 'rsc' => 1, 'rst' => 60, 'min' => 5),//array('max' => 10, 'rsc' => 1, 'rst' => 45, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_FEEDS_GET_DOCUMENT             => array('max' => 10, 'rsc' => 1, 'rst' => 45, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_FEEDS_SUBMIT                   => array('max' => 15, 'rsc' => 1, 'rst' => 120, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_FBA_GET_BULK_FULFILLMENT_ORDER => array('max' => 30, 'rsc' => 2, 'rst' => 1, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_FBA_GET_FULFILLMENT_ORDER      => array('max' => 30, 'rsc' => 2, 'rst' => 1, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_FBA_CREATE_FULFILLMENT_ORDER   => array('max' => 30, 'rsc' => 2, 'rst' => 1, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_FBA_INVENTORY_GET_SUMMARIES    => array('max' => 2, 'rsc' => 2, 'rst' => 1, 'min' => 5),
        AmazonSellerPartnerAPI::API_TYPE_PRIME_CREATE_SHIPMENT          => array('max' => 1, 'rsc' => 1, 'rst' => 1, 'min' => 5),
    );

    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function hasOperation($operation)
    {
        return !empty(self::THROTTLE_SETTING[$operation]);
    }

    protected function getCache($operation)
    {
        $data = AmazonConfiguration::get('throttle_sp_' . $operation);
        if (!$data) {
            return null;
        } else {
            if (!isset($data['expires']) || !isset($data['v'])) {
                return null;
            } else {
                if (time() > $data['expires']) {
                    return null;
                }
            }
        }
        $data = explode(';', $data['v']);
        if (!is_array($data) || count($data) != 2) {
            return null;
        }
        return (object)array('last_update' => $data[0], 'remain' => $data[1]);
    }

    protected function setCache($operation, $last_update, $remain)
    {
        $throttleData = array('v' => $last_update . ';' . $remain, 'expires' => time() + (12 * 3600)); // 12 hours
        AmazonConfiguration::updateValue('throttle_sp_' . $operation, $throttleData);
    }

    protected function getThrottle($operation)
    {
        $setting = self::THROTTLE_SETTING[$operation];
        $max = $setting['max'];
        $cache = self::getCache($operation);

        if ($cache) {
            $remain = (int)$cache->remain;
            $last_update = (int)$cache->last_update;
        } else {
            $remain = $max;
            $last_update = time();
        }

        return new QuotaThrottle($operation, $setting['max'], $setting['rsc'], $setting['rst'], $setting['min'], $remain, $last_update);
    }

    protected function updateThrottle(QuotaThrottle $throttle)
    {
        $max = $throttle->max;
        $now = time();
        $remain = $throttle->remain;
        $last_update = $throttle->last_update;

        if ($remain == $max) {
            $throttle->remain = $remain;
            $throttle->last_update = $now;
        } else {
            // increase remain
            $diff = $now - $last_update;
            $add = (int)($diff / $throttle->rst) * $throttle->rsc;
            if ($add > 0) {
                $remain = min($remain + $add, $max);
                $last_update = $now;
                $throttle->remain = $remain;
            }
            $throttle->last_update = $last_update;
        }

        return $throttle;
    }

    protected function freezeTime(QuotaThrottle $throttle)
    {
        $freeze_time = 0;
        $remain = $throttle->remain;
        $min = $throttle->min;
        $rst = $throttle->rst;
        $last_update = $throttle->last_update;
        $add = 0;
        if ($remain < $min) {
            // rst : restore cycle time
            $diff = $min - $remain;
            $tmp = $last_update + $rst * $diff - time();
            $freeze_time = max(0, $tmp);
            $add = $diff;
        }

        if ($freeze_time > $rst * 5) {
            throw new Exception(sprintf('Action %s was throttled. Wait too long.', $throttle->operation));
        } else {
            $throttle->remain += $add;
            sleep($freeze_time);
        }

        return $throttle;
    }

    protected function decreaseRemainThrottle(QuotaThrottle $throttle)
    {
        $remain = $throttle->remain;

        $remain = max(0, $remain - 1);
        $throttle->remain = $remain;

        self::setCache($throttle->operation, time(), $remain);
    }

    public function handleThrottleBeforeCallApi($operation)
    {
        if ($this->hasOperation($operation)) {
            $throttle = $this->getThrottle($operation);
            $throttle = $this->updateThrottle($throttle);
            $throttle = $this->freezeTime($throttle);
            $this->decreaseRemainThrottle($throttle); // -1 for upcoming API call
        }
    }

    protected function resetThrottle($operation)
    {
        if (!$this->hasOperation($operation)) {
            return;
        }
        $this->setCache($operation, time(), 0);
    }

    protected function checkThrottle(AmazonSellerPartnerResponse $response)
    {
        return $response->hasError() && $response->getErrorCode() === 2001
            && $response->getUpstream() && $response->getUpstream()->getCode() === 429;
    }

    public function handleThrottleAfterCallApi($operation, $response)
    {
        if ($this->checkThrottle($response)) {
            $this->resetThrottle($operation);
        }
    }
}

class QuotaThrottle
{
    public $operation;
    public $max;
    public $rsc;
    public $rst;
    public $min;
    public $remain;
    public $last_update;

    public function __construct($operation, $max, $rsc, $rst, $min, $remain, $last_update)
    {
        if (!AmazonSellerPartnerQuotaHandler::getInstance()->hasOperation($operation)) {
            throw new Exception("QuotaThrottle operation '$operation' invalid");
        }
        $this->operation = $operation;
        $this->max = $max;
        $this->rsc = (int)$rsc;
        $this->rst = $rst;
        $this->min = $min;
        $this->remain = $remain;
        $this->last_update = $last_update;
    }
}
