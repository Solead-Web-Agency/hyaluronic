<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

include_once dirname(__FILE__).'/../../classes/ArContactUsTable.php';
include_once dirname(__FILE__).'/../../classes/ArContactUsListHelper.php';
include_once dirname(__FILE__).'/../../sdk/phpqrcode/qrlib.php';
include_once dirname(__FILE__).'/../../classes/ArContactUsTools.php';

class AdminArContactUsController extends ModuleAdminController
{
    public $max_image_size;
    public static $shopCache = array();
    
    protected $mimeTypes = array(
        'jpg' => array(
            'image/jpeg'
        ),
        'jpeg' => array(
            'image/jpeg'
        ),
        'gif' => array(
            'image/gif'
        ),
        'png' => array(
            'image/png'
        ),
        'svg' => array(
            'image/svg+xml',
            'text/plain'
        )
    );
    
    protected function isMimeTypeValid($mime, $ext)
    {
        if (isset($this->mimeTypes[$ext])) {
            if (in_array($mime, $this->mimeTypes[$ext])) {
                return true;
            }
        }
        return false;
    }
    
    public function ajaxProcessUploadCustomImage()
    {
        die(ArContactUsTools::jsonEncode($this->uploadImageFile('arcontactus_uploaded_img', null)));
    }
    
    protected function uploadImageFile($id, $storeKey)
    {
        self::$currentIndex = 'index.php?tab=AdminArContactUs';
        $fileTypes = array('jpeg', 'gif', 'png', 'jpg', 'svg');
        $uploader = $id;
        $isImage = true;
        if ($isImage) {
            $image_uploader = new HelperUploader($uploader);
        } else {
            $image_uploader = new HelperUploader($uploader);
        }
        $image_uploader->setAcceptTypes($fileTypes);
        if ($isImage) {
            $image_uploader->setMaxSize($this->max_image_size);
        } else {
            $image_uploader->setMaxSize($this->module->fileUploadMaxSize());
        }
        $files = $image_uploader->process();
        $errors = array();
        foreach ($files as &$file) {
            if (isset($file['error']) && $file['error']) {
                $errors[] = $file['error'];
                continue;
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['save_path']);
            finfo_close($finfo);
            
            if ($this->isMimeTypeValid($mime, $ext)) {
                $filename = uniqid() . '.' . $ext;
                $file['filename'] = $filename;
                $file['real_path'] = $this->module->getUploadPath() . $filename;
                copy($file['save_path'], $this->module->getUploadPath() . $filename);
                $file['url'] = $this->module->getUploadsUrl() . $filename;
            } else {
                $file['error'] = $this->module->l('File type does not match its extension');
                $errors[] = $file['error'];
            }
        }
        if ($errors) {
            return array(
                $uploader => $files
            );
        } else {
            if ($storeKey) {
                Configuration::updateValue($storeKey, $file['url']);
            }
            return array(
                $image_uploader->getName() => $files
            );
        }
        
        return false;
    }
    
    public function __construct()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();
        $this->meta_title = $this->l('Contact us');
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
        $this->max_image_size = (int)Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE');
    }
    
    public function ajaxProcessLoadModules()
    {
        $url = 'https://api.areama.net/plugins/list';
        $params = array(
            'lang' => Context::getContext()->language->iso_code,
            'pl' => 'ps',
            'partners' => 1
        );
        $url = $url . '?' . http_build_query($params);
        if ($json = Tools::file_get_contents($url)) {
            $data = ArContactUsTools::jsonDecode($json);
        }
        if ($data) {
            foreach ($data as $k => $module) {
                if (Module::isInstalled($module->name)) {
                    $data[$k]->installed = 1;
                } else {
                    $data[$k]->installed = 0;
                }
                $data[$k]->rate = str_replace('.', '', $data[$k]->avg_rate);
                if ($module->marketplace_item_id == $this->module->getAddonsId()) {
                    unset($data[$k]);
                }
            }
            die(ArContactUsTools::jsonEncode(array(
                'content' => $this->module->render('_partials/_modules.tpl', array(
                    'data' => $data
                )),
                'data' => $data
            )));
        }
        die(ArContactUsTools::jsonEncode(array(
            'content' => ''
        )));
    }
    
    public function ajaxProcessUpdateOrder()
    {
        $data = Tools::getValue('data');
        foreach ($data as $item) {
            $k = explode('_', $item);
            Db::getInstance()->update(ArContactUsTable::TABLE_NAME, array(
                'position' => (int)$k[1]
            ), 'id_contactus = ' . (int)$k[0]);
        }
        $this->module->clearCache();
        die(ArContactUsTools::jsonEncode(array()));
    }
    
    public function ajaxProcessDelete()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsTable($id);
        $model->active = 0;
        $this->module->clearCache();
        die(ArContactUsTools::jsonEncode(array(
            'success' => $model->delete()
        )));
    }
    
    public function ajaxProcessSave()
    {
        $data = Tools::getValue('data');
        $adData = array(
            'enable_qr' => 0,
            'qr_title' => array(),
            'qr_link' => null
        );
        $id = (int)Tools::getValue('id');
        $title = array();
        $subtitle = array();
        $errors = array();
        if ($id) {
            $model = new ArContactUsTable($id);
        } else {
            $model = new ArContactUsTable();
            $model->position = ArContactUsTable::getLastPosition() + 1;
        }
        
        foreach ($data as $param) {
            if (Tools::strpos($param['name'], 'title') !== false) {
                $lang = str_replace('title_', '', $param['name']);
                $title[$lang] = str_replace('\n', "\n", pSQL($param['value']));
            }
            if (Tools::strpos($param['name'], 'subtitle') !== false) {
                $lang = str_replace('subtitle_', '', $param['name']);
                $subtitle[$lang] = str_replace('\n', "\n", pSQL($param['value']));
            }
            if ($param['name'] == 'icon') {
                $model->icon = pSQL($param['value']);
            }
            if ($param['name'] == 'color') {
                $model->color = pSQL($param['value']);
            }
            if ($param['name'] == 'link') {
                $model->link = pSQL($param['value']);
            }
            if ($param['name'] == 'js') {
                $model->js = str_replace('\n', PHP_EOL, pSQL($param['value']));
            }
            if ($param['name'] == 'type') {
                $model->type = (int)$param['value'];
            }
            if ($param['name'] == 'product_page') {
                $model->product_page = (int)$param['value'];
            }
            if ($param['name'] == 'integration') {
                $model->integration = pSQL($param['value']);
            }
            if ($param['name'] == 'display') {
                $model->display = (int)$param['value'];
            }
            if ($param['name'] == 'registered_only') {
                $model->registered_only = (int)$param['value'];
            }
            if ($param['name'] == 'always') {
                $model->always = (int)$param['value'];
            }
            if ($param['name'] == 'time_from') {
                $model->time_from = $param['value'];
            }
            if ($param['name'] == 'time_to') {
                $model->time_to = $param['value'];
            }
            if (in_array($param['name'], array('d1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd7'))) {
                $field = $param['name'];
                $model->$field = (int)$param['value'];
            }
            if ($param['name'] == 'target') {
                $model->target = (int)$param['value'];
            }
            if ($param['name'] == 'id_shop') {
                $model->id_shop = (int)$param['value'];
            }
            
            if ($param['name'] == 'enable_qr') {
                $adData['enable_qr'] = (int)$param['value'];
            }
            if (Tools::strpos($param['name'], 'qr_title') !== false) {
                $lang = str_replace('qr_title_', '', $param['name']);
                $adData['qr_title'][$lang] = str_replace('\n', "\n", pSQL($param['value']));
            }
            if ($param['name'] == 'qr_link') {
                $adData['qr_link'] = pSQL($param['value']);
            }
            if ($param['name'] == 'icon_type') {
                $adData['icon_type'] = pSQL($param['value']);
            }
            if ($param['name'] == 'icon_svg') {
                $adData['icon_svg'] = preg_replace("{[\\\]+}is", '', pSQL($param['value'], true));
            }
            if ($param['name'] == 'icon_img') {
                if ($param['value']) {
                    $adData['icon_img'] = pSQL($param['value']);
                }
            }
            if ($param['name'] == 'no_container') {
                $adData['no_container'] = (int)$param['value'];
            }
            if (preg_match('{^groups\[(\d+)\]$}is', $param['name'], $matches)) {
                if ($param['value'] == 1) {
                    $adData['groups'][] = $matches[1];
                }
            }
        }
        
        $model->title = $title;
        $model->subtitle = $subtitle;
        $model->data = ArContactUsTools::jsonEncode($adData);
        
        $modelErrors = $model->validateFields(false, true);
        
        switch ($model->type) {
            case ArContactUsTable::TYPE_LINK:
                if (Tools::isEmpty($model->link)) {
                    $errors['link'] = $this->module->l('Link field is required');
                }
                break;
            case ArContactUsTable::TYPE_INTEGRATION:
                if (Tools::isEmpty($model->integration)) {
                    $errors['integration'] = $this->module->l('Integration field is required');
                }
                if ($adData['enable_qr'] && empty($adData['qr_link'])) {
                    $errors['qr_link'] = $this->module->l('Link is required for Action:integration menu item');
                }
                break;
            case ArContactUsTable::TYPE_JS:
                if (Tools::isEmpty($model->js)) {
                    $errors['js'] = $this->module->l('Custom javascript field is required');
                }
                if ($adData['enable_qr'] && empty($adData['qr_link'])) {
                    $errors['qr_link'] = $this->module->l('Link is required for Action:js menu item');
                }
                break;
            case ArContactUsTable::TYPE_CALLBACK:
                if ($adData['enable_qr'] && empty($adData['qr_link'])) {
                    $errors['qr_link'] = $this->module->l('Link is required for Action:callback menu item');
                }
                break;
        }
        
        if ($modelErrors !== true) {
            $errors = array_merge($errors, $modelErrors);
        }
        if ($errors) {
            die(ArContactUsTools::jsonEncode(array(
                'success' => 0,
                'errors' => $errors
            )));
        }
        $model->save();
        $this->module->clearCache();
        die(ArContactUsTools::jsonEncode(array(
            'success' => 1,
            'errors' => $errors,
            'model' => $model
        )));
    }
    
    public function ajaxProcessSwitch()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsTable($id);
        $model->status = !$model->status;
        $model->save();
        $this->module->clearCache();
        die(ArContactUsTools::jsonEncode(array(
            'active' => (int)$model->status
        )));
    }
    
    public function ajaxProcessSwitchProduct()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsTable($id);
        $model->product_page = !$model->product_page;
        $model->save();
        $this->module->clearCache();
        die(ArContactUsTools::jsonEncode(array(
            'active' => (int)$model->product_page
        )));
    }
    
    public function ajaxProcessReload()
    {
        die(ArContactUsTools::jsonEncode(array(
            'content' => $this->module->renderTable()
        )));
    }
    
    public function _ajaxProcessReloadCallbacks()
    {
        die(ArContactUsTools::jsonEncode(array(
            'content' => $this->module->renderCallbackTable()
        )));
    }
    
    public function ajaxProcessCallbackSwitch()
    {
        $id = Tools::getValue('id');
        $status = Tools::getValue('status');
        $model = new ArContactUsCallback($id);
        $model->status = $status;
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();
        die(ArContactUsTools::jsonEncode(array(
            'status' => (int)$model->status
        )));
    }
    
    public function ajaxProcessCallbackDelete()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsCallback($id);
        die(ArContactUsTools::jsonEncode(array(
            'success' => $model->delete()
        )));
    }
    
    public function ajaxProcessEdit()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsTable($id);
        $model->data = ArContactUsTools::jsonDecode($model->data);
        $model->js = str_replace('\\', '', $model->js);
        $obj = new stdClass();
        foreach ($model as $k => $v) {
            $obj->$k = $v;
        }
        $obj->enable_qr = isset($model->data->enable_qr)? $model->data->enable_qr : 0;
        $obj->qr_title = isset($model->data->qr_title)? $model->data->qr_title : null;
        $obj->qr_link = isset($model->data->qr_link)? $model->data->qr_link : null;
        $obj->icon_type = isset($model->data->icon_type)? $model->data->icon_type : 'builtin';
        $obj->icon_svg = isset($model->data->icon_svg)? $model->data->icon_svg : null;
        $obj->icon_img = isset($model->data->icon_img)? $model->data->icon_img : null;
        $obj->icon_img_url = isset($model->data->icon_img)? $this->module->getUploadsUrl() . $model->data->icon_img : null;
        $obj->no_container = isset($model->data->no_container)? (int)$model->data->no_container : 0;
        die(ArContactUsTools::jsonEncode($obj));
    }
    
    public function ajaxProcessTime()
    {
        die(ArContactUsTools::jsonEncode(array('time' => date('H:i:s'))));
    }
    
    public function displayViewLink($token, $id, $name)
    {
        $model = new ArContactUsCallback($id);
        $phoneLink = $this->getPhoneLink($model->phone);
        return $this->module->render('_partials/_table_button.tpl', array(
            'href' => '#',
            'onclick' => "return arCU.getQRCode('" . $phoneLink . "', 'Scan QR code on mobile device to direct call', 'phone', " . $id . ")",
            'class' => 'btn btn-default',
            'target' => '',
            'title' => $this->l('View'),
            'icon' => 'icon-search-plus'
        ));
    }
    
    public function displayDeleteLink($token, $id, $name)
    {
        return $this->module->render('_partials/_table_button.tpl', array(
            'href' => '#',
            'onclick' => "return arCU.callback.remove(" . $id . ")",
            'class' => '',
            'target' => '',
            'title' => $this->l('Delete item'),
            'icon' => 'icon-trash'
        ));
    }
    
    public function renderTable($params = array())
    {
        $pageSize = isset($params['selected_pagination'])? $params['selected_pagination'] : 20;
        
        $helper = new ArContactUsListHelper();
        $helper->title = $this->l('Callback requests');
        $helper->actions = array(
            'view',
            'delete'
        );
        $helper->list_id = $this->getListId();
        $helper->identifier = 'id_callback';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->module->name.'&id_shop='.Context::getContext()->shop->id.'&id_product=';
        $helper->setPagination(array(20, 50, 100));
        
        $totalCount = ArContactUsCallback::getCount($params);
        
        if (isset($params['page'])) {
            $totalPages = ceil($totalCount / $pageSize);
            if ($params['page'] > $totalPages) {
                $params['page'] = $totalPages;
            }
        }
        //print_r($params);die();
        $helper->listTotal = $totalCount;
        $helper->currentPage = isset($params['page'])? $params['page'] : 1;
        $helper->module = $this->module;
        $helper->no_link = true;
        $helper->setDefaultPagination($pageSize);
        $helper->filters = isset($params['filter'])? $params['filter'] : array();
        $helper->token = Tools::getAdminTokenLite('AdminArContactUs');
        
        $helper->bulk_actions = array(
            'deactivate' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
                'js_action' => 'arCU.callback.bulk.remove(); return false'
            )
        );
        
        $list = ArContactUsCallback::getAll($params);
        $columns = array(
            'id_callback' => array(
                'title' => $this->l('#'),
                'filter_key' => 'id',
                'orderby' => false,
            ),
            'phone' => array(
                'title' => $this->l('Phone'),
                'filter_key' => 'phone',
                'orderby' => false,
                'callback' => 'renderCallButton'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'filter_key' => 'name',
                'orderby' => false,
            ),
            'email' => array(
                'title' => $this->l('Email'),
                'filter_key' => 'email',
                'orderby' => false,
            ),
            'referer' => array(
                'title' => $this->l('URL'),
                'filter_key' => 'referer',
                'orderby' => false,
            ),
            'created_at' => array(
                'title' => $this->l('Created at'),
                'filter_key' => 'created_at',
                'orderby' => false,
            ),
            'updated_at' => array(
                'title' => $this->l('Updated at'),
                'filter_key' => 'updated_at',
                'orderby' => false,
            )
        );
        if (Shop::isFeatureActive()) {
            $columns['id_shop'] = array(
                'title' => $this->l('Shop'),
                'filter_key' => 'id_shop',
                'callback' => 'shopTableValue',
                'type'  => 'select',
                'orderby' => false,
                'search' => false,
                'list'  => $this->getShopList(),
            );
        }
        $columns['status'] = array(
            'title' => $this->l('Status'),
            'filter_key' => 'status',
            'type'  => 'select',
            'list' => $this->statusList(),
            'ajax' => true,
            'orderby' => false,
            'callback' => 'renderStatus'
        );
        $columns['comment'] = array(
            'title' => $this->l('Comment'),
            'filter_key' => 'comment',
            'orderby' => false,
            'callback' => 'renderComment'
        );
        return $helper->generateList($list, $columns);
    }
    
    public function renderComment($cellValue, $row)
    {
        $comment = strip_tags($row['comment']);
        $comment = nl2br($comment);
        return mb_substr($comment, 0, 120, 'utf-8');
    }
    
    public function renderCallButton($cellValue, $row)
    {
        $row['link'] = array(
            'viber' => $this->getViberLink($row['phone']),
            'whatsApp' => $this->getWhatsAppLink($row['phone']),
            'tg' => $this->getTgLink($row['phone']),
            'phone' => $this->getPhoneLink($row['phone']),
            'sms' => $this->getSMSLink($row['phone']),
        );
        return $this->module->render('_partials/_callbutton.tpl', array(
            'model' => $row
        ));
    }
    
    public function getViberLink($phone)
    {
        return 'viber://chat?number=' . $this->formatPhone($phone, '%2B');
    }
    
    public function getWhatsAppLink($phone)
    {
        return 'https://api.whatsapp.com/send?phone=' . $this->formatPhone($phone, '');
    }
    
    public function getTgLink($phone)
    {
        return 'tg://' . $this->formatPhone($phone, '+');
    }
    
    public function getPhoneLink($phone)
    {
        return 'tel:' . $this->formatPhone($phone, '+');
    }
    
    public function getSMSLink($phone)
    {
        return 'sms:' . $this->formatPhone($phone, '+');
    }
    
    public function formatPhone($phone, $prefix = '+')
    {
        $phone = preg_replace('/\D+/is', '', $phone);
        return $prefix . $phone;
    }
    
    public function ajaxProcessDeleteSelected()
    {
        $ids = Tools::getValue('ids');
        $ids = $this->filterIdList($ids);
        $res = Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . ArContactUsCallback::TABLE_NAME . '` WHERE id_callback IN (' . implode(',', $ids) . ')');
        die(ArContactUsTools::jsonEncode(array(
            'deleted' => $res
        )));
    }
    
    public function renderStatus($cellValue, $row)
    {
        return $this->module->render('_partials/_status.tpl', array(
            'model' => $row
        ));
    }
    
    public function getListId()
    {
        return 'callbacks';
    }

    public function statusList()
    {
        return array(
            '0' => $this->l('New'),
            '1' => $this->l('Done'),
            '2' => $this->l('Ignored'),
        );
    }
        
    public function yesNoList()
    {
        return array(
            '0' => $this->l('No'),
            '1' => $this->l('Yes')
        );
    }
    
    public function ajaxProcessReloadQRCode()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsCallback((int)$id);
        $data = Tools::getValue('data');
        $path = $this->module->getUploadPath();
        $filename = md5($data) . '.png';
        QRcode::png($data, $path . $filename, QR_ECLEVEL_L, 10);
        die(ArContactUsTools::jsonEncode(array(
            'qrcodeFile' => $this->module->getUploadsUrl() . $filename
        )));
    }
    
    public function ajaxProcessGetQRCode()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsCallback((int)$id);
        $data = Tools::getValue('data');
        $path = $this->module->getUploadPath();
        $filename = md5($data) . '.png';
        QRcode::png($data, $path . $filename, QR_ECLEVEL_L, 10);
        die(ArContactUsTools::jsonEncode(array(
            'content' => $this->module->render('_partials/_qr_content.tpl', array(
                'qrcodeFile' => $this->module->getUploadsUrl() . $filename,
                'links' => array(
                    'viber' => $this->getViberLink($model->phone),
                    'whatsApp' => $this->getWhatsAppLink($model->phone),
                    'tg' => $this->getTgLink($model->phone),
                    'phone' => $this->getPhoneLink($model->phone),
                    'sms' => $this->getSMSLink($model->phone),
                ),
                'channel' => Tools::getValue('channel'),
                'model' => $model
            )),
            'filename' => $filename,
            'path' => $path,
            'url' => $this->module->getUploadsUrl() . $filename,
            'model' => $model
        )));
    }
    
    public function ajaxProcessPreviewQRCode()
    {
        $id = Tools::getValue('id');
        $id_lang = Context::getContext()->language->id;
        $model = new ArContactUsTable((int)$id, $id_lang);
        $data = ArContactUsTools::jsonDecode($model->data, true);
        $link = ArContactUsTable::getLink($data['qr_link']? $data['qr_link'] : $model->link);
        $link = $this->module->composeURL($link);
        $title = $data['qr_title'][$id_lang]? $data['qr_title'][$id_lang] : $model->title;
        $path = $this->module->getUploadPath();
        $filename = md5($link) . '.png';
        QRcode::png($link, $path . $filename, QR_ECLEVEL_L, 10);
        die(ArContactUsTools::jsonEncode(array(
            'qrcodeFile' => $this->module->getUploadsUrl() . $filename,
            'data' => $data,
            'title' => $title,
            'link' => $link,
            'model' => $model
        )));
    }
    
    public function ajaxProcessSaveComment()
    {
        $id = Tools::getValue('id');
        $comment = pSQL(Tools::getValue('comment'));
        $comment = str_replace('\\n', PHP_EOL, $comment);
        $model = new ArContactUsCallback((int)$id);
        $model->comment = $comment;
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();
        die(ArContactUsTools::jsonEncode(array(
            'success' => 1
        )));
    }

    public function ajaxProcessReloadCallbacks()
    {
        $params = $this->getParams($this->getListId());
        die(ArContactUsTools::jsonEncode(array(
            'content' => $this->renderTable($params)
        )));
    }
    
    public function getParams($listId)
    {
        $data = Tools::getValue('data');
        if (empty($data)) {
            return array();
        }
        $params = array(
            'resetFilter' => 0
        );
        foreach ($data as $param) {
            $name = str_replace($listId, '', $param['name']);
            if (strpos($name, 'Filter') === 0) {
                $name = str_replace('Filter_', '', $name);
                $params['filter'][$name] = $param['value'];
            } elseif ($name == 'submit') {
                if (strpos($param['value'], 'submitReset') !== false) {
                    $params['resetFilter'] = 1;
                    return array();
                }
            } else {
                $params[$name] = $param['value'];
            }
        }
        return $params;
    }
    
    protected function filterIdList($ids)
    {
        $res = array();
        foreach ($ids as $id) {
            $res[] = (int)$id;
        }
        return $res;
    }
    
    public function getShopList()
    {
        if (empty(self::$shopCache)) {
            $shops = Shop::getShops();
            foreach ($shops as $shop) {
                self::$shopCache[$shop['id_shop']] = $shop['name'];
            }
        }
        $shops = self::$shopCache;
        $shops[0] = $this->l('[all shops]');
        ksort($shops);
        return $shops;
    }
    
    public function shopTableValue($cellValue, $row)
    {
        if (empty(self::$shopCache)) {
            $shops = Shop::getShops();
            foreach ($shops as $shop) {
                self::$shopCache[$shop['id_shop']] = $shop['name'];
            }
        }
        if ($row['id_shop'] == 0) {
            return $this->l('[all shops]');
        }
        return isset(self::$shopCache[$row['id_shop']])? self::$shopCache[$row['id_shop']] : $this->l('[deleted]');
    }
}
