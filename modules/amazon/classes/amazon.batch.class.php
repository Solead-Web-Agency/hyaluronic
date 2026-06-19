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
require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.data_adjustment.class.php');

class AmazonBatch
{
    const TYPE_VAT_INVOICE_UPLOAD = 'Upload';

    public $id = null;
    public $timestart = 0;
    public $timestop  = 0;

    public $type   = null;
    public $region = null;

    public $created = 0;
    public $updated = 0;
    public $deleted = 0;

    public $hasFeed = false;

    public function __construct($timestamp = null)
    {
        $this->timestart = $timestamp ? (int)$timestamp : time();
    }

    public static function initInstance($id, $start, $stop, $type, $region, $created, $updated, $deleted, $hasFeed = true)
    {
        $instance = new self($start);
        $instance->id = $id;
        $instance->timestop = $stop;
        $instance->type = $type;
        $instance->region = $region;
        $instance->created = $created;
        $instance->updated = $updated;
        $instance->deleted = $deleted;
        $instance->hasFeed = $hasFeed;

        return $instance;
    }
    public static function initInstanceV2($arr)
    {
        $instance = new self();
        foreach ($arr as $key => $value) {
            $instance->{$key} = $value;
        }

        return $instance;
    }

    public function format()
    {
        $id = $this->id ?: '-';

        return array(
            'id' => $id,
            'hasid' => (bool)$id,
            'timestart' => $this->timestart ? AmazonTools::displayDate(date('Y-m-d H:i:s', $this->timestart), null, true) : '-',
            'timestop' => $this->timestop ? AmazonTools::displayDate(date('Y-m-d H:i:s', $this->timestop), null, true) : '-',
            'duration' => max($this->timestop - $this->timestart, 0),
            'type' => AmazonTools::ucfirst($this->type),
            'region' => $this->region,
            'created' => $this->created,
            'updated' => $this->updated,
            'deleted' => $this->deleted,
            'records' => $this->created + $this->updated + $this->deleted,
            'hasFeed' => $this->hasFeed,
        );
    }
}

class AmazonBatches
{
    const MAX_BATCHES = 100;

    const TYPE_ORDER_FULFILLMENT = 'batch_status';
    const TYPE_ORDER_ACKNOWLEDGE = 'batch_acknowledge';
    const TYPE_ORDER_CANCELLATION = 'batch_cancel';
    const TYPE_VAT_INVOICE = 'batch_vat_invoice';

    const TYPE_CATALOG_DELETION  = 'batch_catalog_deletion';
    public $key     = null;
    /**
     * @var int
     */
    public $current = 0;
    public $batches = array();

    public function __construct($key = null)
    {
        $this->key = $key;
        $this->load();
    }


    /**
     * Compare dates, callback function
     * @param $a
     * @param $b
     * @return null
     */
    private function getLastForRegionCompare($a, $b)
    {
        return isset($a->timestart) && isset($b->timestart) ? $b->timestart - $a->timestart : null;
    }


    /**
     * Get latest batch for the region
     * @param $region
     * @return bool|null|string
     */
    public function getLastForRegion($region)
    {
        $batches = AmazonConfiguration::get($this->key);
        
        if (AmazonDataAdjustment::unSerialize(($batches) !== false)) {
            $batches = AmazonDataAdjustment::unSerialize($batches);
        }
        
        if (Tools::strlen($region) && is_array($batches) && count($batches)) {
            usort($batches, array('self', 'getLastForRegionCompare'));

            foreach ($batches as $index => $batch) {
                if (is_array($batch)) {
                    $batch = json_decode(json_encode($batch));
                    $batches[$index]  = AmazonBatch::initInstanceV2($batch);
                }
                if ($batch instanceof AmazonBatch && $batch->id && $batch->timestart && $region == $batch->region) {
                    $this->current = sprintf('%s.%s', $batch->timestop, $batch->id);
                    return date('Y-m-d H:i:s', $batch->timestart);
                }
            }
        }

        return null;
    }

    /**
     * Return current batch
     * @return mixed|null
     */
    public function getCurrent()
    {
        if ($this->current) {
            return($this->batches[$this->current]);
        } else {
            return(null);
        }
    }

    /**
     * Delete configuration keu
     */
    public function deleteKey()
    {
        AmazonConfiguration::deleteKey($this->key);
    }


    /**
     * Load Batches
     * @return array
     */
    public function load()
    {
        $batches = AmazonConfiguration::get($this->key);
        if (AmazonDataAdjustment::unSerialize($batches) !== false) {
            $batches = AmazonDataAdjustment::unSerialize($batches);
        }

        if (is_array($batches) && count($batches)) {
            foreach ($batches as $index => $batch) {
                if (is_array($batch)) {
                    $batch = json_decode(json_encode($batch));
                    $batches[$index]  = AmazonBatch::initInstanceV2($batch);
                }
                $batch->group = $this->key;
            }
            $this->batches = $batches;
        }

        return $this->batches;
    }

    /**
     * Add a new batch
     * @param AmazonBatch $batch
     * @return bool
     */
    public function add(AmazonBatch $batch)
    {
        if (!(is_array($this->batches) && count($this->batches) && reset($this->batches) instanceof AmazonBatch)) {
            $this->batches = array();
        }

        $this->current = $index = sprintf('%s.%s', $batch->timestop, $batch->id);

        $this->batches[$index] = $batch;

        krsort($this->batches);

        if (Amazon::$debug_mode) {
            CommonTools::p("Batches, Add");
            CommonTools::p($batch);
            CommonTools::p(Tools::substr(print_r($this->batches, true), 0, Tools::strlen(print_r($batch, true))));
        }

        $this->batches = array_slice($this->batches, 0, self::MAX_BATCHES, true);

        return (true);
    }

    /**
     * Provides current batch
     * @return null or batch
     */
    public function current()
    {
        if (!(is_array($this->batches) && isset($this->batches[$this->current]) && $this->batches[$this->current] instanceof AmazonBatch)) {
            return (null);
        }

        return ($this->batches[$this->current]);
    }

    /**
     * Save current batch
     * @return bool
     */
    public function save()
    {
        //$serialized = AmazonDataAdjustment::serialize($this->batches);
        $serialized = $this->batches;

        if (Amazon::$debug_mode) {
            CommonTools::p("Batches, Serialized");
            CommonTools::p(Tools::substr(print_r($serialized, true), 0, 128).'...');
        }

        return (AmazonConfiguration::updateValue($this->key, $serialized));
    }
}
