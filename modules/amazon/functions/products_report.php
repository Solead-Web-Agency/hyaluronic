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
/* Is ajax/cron file */
require_once(dirname(__FILE__) . '/AmazonFunction.php');
require_once(dirname(__FILE__) . '/../classes/amazon.batch.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.webservice.class.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonProductReport extends AmazonFunction
{
    public static $errors = array();
    public static $warnings = array();
    public static $messages = array();

    public function doIt()
    {
        if (!$this->functionAuthorization()) {
            die('Wrong Token');
        }

        $action = (string)AmazonTools::getValue('action');

        switch ($action) {
            case 'display-statistics':
                $this->displayStatistics();
                break;
            case 'purge':
                $this->purge();
                break;
            case 'list-reports':
                $this->listReports();
                break;
        }
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if (!$lang) {
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));
        }

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }

    public function displayStatistics()
    {
        $i = 0;
        $stats = AmazonProduct::marketplaceCountProducts();

        $actives = AmazonConfiguration::get('ACTIVE');

        $statistics = array();

        $batchTypes = array(
            'session_products' => $this->l('Products'),
            'session_offers' => $this->l('Offers'),
            'session_repricing' => $this->l('Repricing'),
        );
        foreach ($batchTypes as $batch_type => $mode) {
            $batches = new AmazonBatches($batch_type);
            $batches_list = $batches->load();

            foreach ($batches_list as $batch) {
                $index = $batch->timestart . '.' . $i;

                $statistics[$index] = $batch->format();
                $statistics[$index]['records'] = $batch->created + $batch->updated + $batch->deleted;
                $statistics[$index]['mode'] = $mode;

                $i++;
            }
        }
        krsort($statistics);

        $this->smarty->assign(
            array(
                'productReportStats' => $stats,
                'productReportStatistics' => $statistics,
                'productReportText1' => $this->l('Statistics'),
                'productReportText2' => sprintf(
                    $this->l('There are %d synchronizable products and %d combinations in %d languages'),
                    $stats['products'],
                    $stats['attributes'],
                    is_array($actives) ? count($actives) : 0
                ),
                'productReportText3' => $this->l('Latest Updates'),
                'productReportText4' => $this->l('Action'),
                'productReportText5' => $this->l('Type'),
                'productReportText6' => $this->l('Data'),
                'productReportText7' => $this->l('Date/Time'),
                'productReportText8' => $this->l('Items'),
                'productReportText9' => $this->l('Duration'),
                'productReportText10' => $this->l('No recorded updates'),
            )
        );

        echo $this->display(
            $this->path . 'amazon.php',
            'views/templates/admin/functions/product_report_statistics.tpl'
        );
    }

    public function purge()
    {
        foreach (
            array(
                'batch_products_cron',
                'batch_products',
                'batch_offers_cron',
                'batch_offers',
                'batch_repricing',
                AmazonBatches::TYPE_ORDER_FULFILLMENT,
                AmazonBatches::TYPE_ORDER_ACKNOWLEDGE,
                AmazonBatches::TYPE_ORDER_CANCELLATION,
                AmazonBatches::TYPE_VAT_INVOICE,
                'session_products',
                'session_offers',
                'session_repricing',
            ) as $batch_type) {
            $batches = new AmazonBatches($batch_type);
            $batches->deleteKey();
        }
        echo $this->l('Batches entries have been deleted');
    }

    public function listReports()
    {
        $spConnector = $this->initSpConnector();
        $filter = '';
        $filterByRegion = false;
        if ($spConnector) {
            if ($spConnector instanceof AmazonSPConnectorPSMkp) {
                $filter = $spConnector->getIso();
            } elseif ($spConnector instanceof AmazonSPConnectorPSRegion) {
                $filter = $spConnector->getRegion();
                $filterByRegion = true;
            }
        }

        ob_start();
        $type = AmazonTools::getValue('type');
        $full_batch_list = $this->getBatchesType($type);

        $i = 0;
        $reports = array();
        if (count($full_batch_list)) {
            /**
             * @var int $key
             * @var AmazonBatch $batch
             */
            foreach ($full_batch_list as $batch) {
                if (!$filter || $filter == $batch->region
                    // VCS is always by marketplace. We list all of them when filter by region
                    // todo: Still has bug when called on other region, but let it go for now 
                    || $filterByRegion && $batch->group == AmazonBatches::TYPE_VAT_INVOICE) {
                    $index = sprintf('%016s.%03s', $batch->timestart, $i);

                    $reports[$index] = $batch->format();
                    $reports[$index]['records'] = $batch->created + $batch->updated + $batch->deleted;

                    $i++;
                }
            }
            krsort($reports);
        }

        $result = trim(ob_get_clean());
        if (!empty($result)) {
            self::$warnings[] = $result;
        }

        echo json_encode(array(
            'count' => count($reports),
            'reports' => $reports,
            'error' => (bool)count(self::$errors),
            'errors' => self::$errors,
            'warning' => (bool)count(self::$warnings),
            'warnings' => self::$warnings,
            'message' => count(self::$messages),
            'messages' => self::$messages
        ));
    }

    protected function getBatchesType($type)
    {
        $batch_by_type = array(
            'product' => array(
                'batch_products_cron',
                'batch_products',
                'batch_offers_cron',
                'batch_offers',
                'batch_repricing',
                AmazonBatches::TYPE_CATALOG_DELETION,
            ),
            'order' => array(
                AmazonBatches::TYPE_ORDER_FULFILLMENT,
                AmazonBatches::TYPE_ORDER_ACKNOWLEDGE,
                AmazonBatches::TYPE_ORDER_CANCELLATION,
                AmazonBatches::TYPE_VAT_INVOICE,
            ),
        );

        if (isset($batch_by_type[$type])) {
            $batch_types = $batch_by_type[$type];
        } else {
            $batch_types = array_merge($batch_by_type['product'], $batch_by_type['order']);
        }

        $full_batch_list = array();
        foreach ($batch_types as $batch_type) {
            $batches = new AmazonBatches($batch_type);
            $batches_list = $batches->batches;
            $full_batch_list = array_merge($full_batch_list, $batches_list);
        }

        return $full_batch_list;
    }
}

$apr = new AmazonProductReport();
$apr->doIt();
