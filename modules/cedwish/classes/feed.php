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

class CedWishFeed extends ObjectModel
{
    public static $definition = array(
        'table' => 'cedwish_feed',
        'primary' => 'id_cedwish_feed',
        'fields' => array(
            'id_cedwish_feed' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'success_count' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'failure_count' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'processed_count' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'job_id' => array('type' => self::TYPE_STRING),
            'state' => array('type' => self::TYPE_STRING),
            'response' => array('type' => self::TYPE_STRING),
            'start_time' => array('type' => self::TYPE_STRING),
            'uploader_id' => array('type' => self::TYPE_STRING)
        ),
    );
    public $id_cedwish_feed;
    public $success_count;
    public $failure_count;
    public $processed_count;
    public $job_id;
    public $state;
    public $response;
    public $start_time;
    public $uploader_id;

    public function getJobStatus($jobId = null)
    {
        $params = array();
        $api = new CedWishApi();
        $params['job_id'] = $jobId;
        return $api->getJobStatus($params);
    }

    public function getSuccessResponseByJobId($jobId)
    {
        $params = array();
        $api = new CedWishApi();
        $params['job_id'] = $jobId;
        $params['limit'] = 250;
        return $api->getJobSuccessSkus($params);
    }

    public function getErrorResponseByJobId($jobId)
    {
        $params = array();
        $api = new CedWishApi();
        $params['job_id'] = $jobId;
        $params['limit'] = 250;
        return $api->getJobFailureSkus($params);
    }

    public function addNewJob($response)
    {
        $this->state = $response['status'];
        $this->processed_count = $response['total_count'];
        $this->job_id = $response['id'];
        $this->start_time = pSQL(str_replace("T", " ", $response['created_at']));
        $this->success_count = $response['processed_count'];
        $this->response = pSQL(json_encode($response));
        $this->uploader_id = 'bulk_process';
        $this->failure_count = 0;
        try {
            $this->add();
        } catch (PrestaShopDatabaseException $e) {
        } catch (PrestaShopException $e) {
        }
    }

    public function updateJob($id, $response)
    {
        $this->state = $response['status'];
        $this->state = $response['status'];
        $this->processed_count = $response['total_count'];
        $this->job_id = $response['id'];
        $this->start_time = pSQL(str_replace("T", " ", $response['created_at']));
        $this->success_count = $response['processed_count'];
        $this->response = pSQL(json_encode($response));
        $this->uploader_id = 'bulk_process';
        $this->failure_count = 0;
        $this->id_cedwish_feed = $id;
        try {
            $this->update();
        } catch (PrestaShopDatabaseException $e) {
        } catch (PrestaShopException $e) {
        }
    }
}
