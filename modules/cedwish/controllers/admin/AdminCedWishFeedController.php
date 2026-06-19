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

require_once _PS_MODULE_DIR_ . 'cedwish/classes/feed.php';

class AdminCedWishFeedController extends ModuleAdminController
{
    public function __construct()
    {
        $this->id_lang = Context::getContext()->language->id;
        $this->bootstrap = true;
        $this->table = 'cedwish_feed';
        $this->className = 'CedWishFeed';
        $this->identifier = 'id_cedwish_feed';
        $this->_orderWay = 'DESC';
        $this->_orderBy = 'id_cedwish_feed';
        $this->list_no_link = true;
        $this->addRowAction('sync');
        $this->addRowAction('delete');
        $statusArray = array();
        $statusArray['PENDING'] = 'PENDING';
        $statusArray['FINISHED'] = 'FINISHED';
        $statusArray['RUNNING'] = 'RUNNING';
        parent::__construct();
        $this->fields_list = array(
            'id_cedwish_feed' => array(
                'title' => $this->l('ID'),
                'type' => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'job_id' => array(
                'title' => $this->l('Job ID'),
                'type' => 'text',
            ),
            'start_time' => array(
                'title' => $this->l('Created At'),
                'type' => 'text',
                'class' => 'fixed-width-lg'
            ),
            'success_count' => array(
                'title' => $this->l('Success Count'),
                'type' => 'int',
            ),
            'failure_count' => array(
                'title' => $this->l('Failure Count'),
                'type' => 'int',
            ),
            'processed_count' => array(
                'title' => $this->l('Processed Count'),
                'type' => 'int',
                'callback' => 'feedResponse'
            ),
            'state' => array(
                'title' => $this->l('Status'),
                'align' => 'text-center',
                'type' => 'select',
                'list' => $statusArray,
                'filter_key' => 'state'
            ),
            'uploader_id' => array(
                'title' => $this->l('Response'),
                'align' => 'text-center',
                'type' => 'text',
                'filter' => false,
                'callback' => 'updateFeed',
            )
        );
    }

    public function updateFeed($uploader_id, $data)
    {
        if (($data['state'] == "READY") && ($uploader_id == 'bulk_process')) {
            $link = new LinkCore();
            $field_data = $link->getAdminLink(
                'AdminCedWishFeed'
            ) . '&method=download&download_file=' . $data['job_id'];
            $this->context->smarty->assign(array(
                'file_path' => $field_data,
                'feedData' => $data,
                'wish_feed_token' => Tools::getAdminTokenLite('AdminCedWishFeed'),
            ));

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/feed/feed_response.tpl'
            );
        }
    }

    public function feedResponse($data, $rowData)
    {
        if (isset($rowData['uploader_id']) && ($rowData['uploader_id'] != 'bulk_process')) {
            $feedId = isset($rowData['job_id']) ? $rowData['job_id'] : '';
            $totalSkus = array();
            $successfullSkus = array();
            $failedSkus = array();
            $feedData = $feedArray = Tools::jsonDecode($data, true);
            if (isset($feedArray['totalSkus']) && !empty($feedArray['totalSkus'])) {
                $totalSkus = $feedArray['totalSkus'];
            }
            if (isset($feedArray['successfullSkus']) && !empty($feedArray['successfullSkus'])) {
                $successfullSkus = $feedArray['successfullSkus'];
            }
            if (isset($feedArray['failedSkus']) && !empty($feedArray['failedSkus'])) {
                $failedSkus = $feedArray['failedSkus'];
            }
            if (isset($feedArray['failedSkus']) && !empty($feedArray['failedSkus'])) {
                $failedSkus = $feedArray['failedSkus'];
            }

            $this->context->smarty->assign(
                array(
                    'feedResponse' => Tools::jsonDecode($data, true),
                    'feedData' => json_encode($feedData),
                    'feedId' => $feedId,
                    'wish_feed_token' => $this->token,
                    'processed_count' => $rowData['processed_count'],
                    'totalSkus' => json_encode($totalSkus),
                    'successfullSkus' => json_encode($successfullSkus),
                    'failedSkus' => json_encode($failedSkus),
                )
            );
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/feed/feed_response.tpl'
            );
        } else {
            return $data;
        }
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['clear_feed'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedWishFeed') . '&clearallfeeds=1',
                'desc' => 'Delete All Feeds',
                'icon' => 'process-icon-eraser'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function initToolbar()
    {
        $this->toolbar_btn['export'] = array(
            'href' => self::$currentIndex . '&export' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Export')
        );
    }

    public function displaySyncLink($token = null, $id = null, $name = null)
    {
        if ($token && $name) {
        }
        $tpl = $this->createTemplate('helpers/list/list_action_transferstock.tpl');
        if (!array_key_exists('Sync Feed', self::$cache_lang)) {
            self::$cache_lang['Sync Feed'] = $this->l('Sync Feed', 'Helper');
        }

        $tpl->assign(
            array(
                'href' => Context::getContext()->link
                        ->getAdminLink('AdminCedWishFeed') .
                    '&syncfeed=' . $id . '&id_feed=' . $id,
                'action' => self::$cache_lang['Sync Feed'],
                'id' => $id
            )
        );
        return $tpl->fetch();
    }

    public function postProcess()
    {
        $db = Db::getInstance();
        if (Tools::getIsset('syncfeed') && Tools::getValue('id_feed')) {
            $id = Tools::getValue('id_feed');
            $sql = "SELECT job_id FROM `" . _DB_PREFIX_ . "cedwish_feed` WHERE `id_cedwish_feed` = '" . (int)$id . "'";
            try {
                $feedId = $db->getValue($sql);
                if ($feedId) {
                    $api = new CedWishApi();
                    $response = $api->updateBulkUpdateProductJob($feedId);
                    if (isset($response['code']) && ($response['code'] == 0)) {
                        $response = $response['data'];
                        if (isset($response['id']) && $response['id']) {
                            $feed = new CedWishFeed((int)$id);
                            $feed->state = $response['status'];
                            $feed->processed_count = $response['total_count'];
                            $feed->job_id = $response['id'];
                            $feed->start_time = pSQL(str_replace("T", " ", $response['created_at']));
                            $feed->success_count = $response['processed_count'];
                            $feed->response = pSQL(json_encode($response));
                            $feed->uploader_id = 'bulk_process';
                            $feed->failure_count = 0;
                            $this->confirmations[] = $feedId . " : Feed synced successfully";
                            try {
                                $feed->update();
                            } catch (PrestaShopDatabaseException $e) {
                                $this->errors[] = $e->getMessage();
                            } catch (PrestaShopException $e) {
                                $this->errors[] = $e->getMessage();
                            }
                        } elseif (isset($response['message']) && $response['message']) {
                            $this->errors[] = $response['message'];
                        } else {
                            $this->errors[] = "No feed data found for ID : " . $id;
                        }
                    } elseif (isset($response['message']) && $response['message']) {
                        $this->errors[] = $response['message'];
                    } else {
                        $this->errors[] = "No feed data found for ID : " . $id;
                    }
                } else {
                    $this->errors[] = "No feed data found for ID : " . $id;
                }
            } catch (PrestaShopDatabaseException $e) {
                $this->errors[] = "Failed to sync feed";
            }
        }
        if (Tools::getIsset('clearallfeeds') && Tools::getValue('clearallfeeds')) {
            $res = $db->delete(
                'cedwish_feed'
            );
            if ($res) {
                $this->confirmations[] = "All Feeds deleted successfully";
            } else {
                $this->errors[] = "Failed to delete all feeds";
            }
        }
        parent::postProcess();
    }

    public function ajaxProcessGetFeedInfo()
    {
        $feedId = Tools::getValue('feedId');
        if (isset($feedId) && $feedId) {
            $errors = Db::getInstance()->getRow(
                "SELECT * FROM `" . _DB_PREFIX_ . "cedwish_feed` WHERE job_id LIKE '" . pSQL($feedId) . "'"
            );
            if (!empty($errors)) {
                if (isset($errors['response'])) {
                    $errors['response'] = Tools::getDescriptionClean($errors['response']);
                    $errors['response'] = json_decode($errors['response'], true);
                    if (isset($errors['response']['file_urls']) && !empty($errors['response']['file_urls'])) {
                        $file_urls = array_unique($errors['response']['file_urls']);
                        if (!empty($file_urls)) {
                            $response = array();
                            foreach ($file_urls as $file_url) {
                                $data = Tools::file_get_contents($file_url);
                                $data = explode("\n", $data);
                                if (!empty($data)) {
                                    $data = array_filter($data);
                                    foreach ($data as &$d) {
                                        $d = @json_decode($d, true);
                                    }
                                }
                                $response = array_merge($response, $data);
                            }
                            $errors['response'] = $response;
                        }
                    } else {
                        $errors['response'] = array();
                    }
                }
                die(json_encode($errors));
            } else {
                $response = array('Missing required parameters');
                die(json_encode($response));
            }
        } else {
            $response = array('Missing required parameters');
            die(json_encode($response));
        }
    }
}
