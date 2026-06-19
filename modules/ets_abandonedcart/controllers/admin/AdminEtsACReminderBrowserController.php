<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

if (!class_exists('AdminEtsACReminderEmailController'))
    require_once(dirname(__FILE__) . '/AdminEtsACReminderEmailController.php');

class AdminEtsACReminderBrowserController extends AdminEtsACReminderEmailController
{
    public $keep_old_image = null;

    public function __construct()
    {
        $this->type = 'browser';
        parent::__construct();
    }

    public function getFieldsForm()
    {
        $fields = [
            'hidden_reminder_id' => [
                'name' => 'hidden_reminder_id',
                'type' => 'hidden',
                'label' => $this->l('Reminder', 'AdminEtsACReminderBrowserController'),
                'default_value' => (int)Tools::getValue($this->identifier),
            ],
            'title' => array(
                'name' => 'title',
                'label' => $this->l('Title', 'AdminEtsACReminderBrowserController'),
                'type' => 'text',
                'lang' => true,
                'required' => true,
                'validate' => 'isCleanHtml',
                'form_group_class' => 'abancart form_message isCleanHtml required'
            ),
            'icon_notify' => array(
                'name' => 'icon_notify',
                'label' => $this->l('Icon', 'AdminEtsACReminderBrowserController'),
                'type' => 'file',
                'display_image' => true,
                'col' => 6,
                'validate' => 'isFileName',
                'form_group_class' => 'abancart form_message form-group-file isFileName'
            ),
            'content' => array(
                'name' => 'content',
                'label' => $this->l('Content', 'AdminEtsACReminderBrowserController'),
                'type' => 'text',
                'lang' => true,
                'required' => true,
                'desc_type' => $this->type,
                'validate' => 'isCleanHtml',
                'form_group_class' => 'abancart content form_message isCleanHtml required'
            ),
            'default_content' => array(
                'name' => 'default_content',
                'label' => '',
                'type' => 'default_content',
                'has_discount' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_wpn_discount.tpl'),
                'no_discount' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_wpn_nodiscount.tpl'),
                'title_no_discount' => 'Did you forget something?',
                'title_has_discount' => 'Your Shopping Cart Misses You!',
                'no_product_in_cart' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_wpn_no_product_in_cart.tpl'),
                'title_no_product_in_cart' => 'Your web push notification title',
            )
        ];
        if (isset($this->campaign->object) && $this->campaign->object->id > 0) {
            $fields['has_shopping_cart'] = [
                'name' => 'has_shopping_cart',
                'type' => 'hidden',
                'label' => $this->l('Has shopping cart', 'AdminEtsACReminderBrowserController'),
                'default_value' => $this->campaign->object->has_product_in_cart == EtsAbancartCampaign::HAS_SHOPPING_CART_YES ? 1 : 0,
            ];
        }

        return $fields;
    }

    public function renderForm()
    {
        if (!$this->loadObject(true)) {
            //return;
        }

        if ($this->object->id && $this->object->icon_notify != '' && @file_exists(($image = _PS_IMG_DIR_ . Ets_abandonedcart::ETS_ABC_UPLOAD_IMG_DIR . $this->object->icon_notify))) {
            $imageType = Tools::strtolower(Tools::substr(strrchr($this->object->icon_notify, '.'), 1));
            $image_url = ImageManager::thumbnail(
                $image,
                $this->object->icon_notify,
                220,
                $imageType,
                true,
                true
            );

            $image_size = file_exists($image) ? filesize($image) / 1000 : false;
            $this->fields_form['input']['icon_notify']['image'] = $image_url;
            $this->fields_form['input']['icon_notify']['size'] = $image_size;
            $this->fields_form['input']['icon_notify']['delete_url'] = self::$currentIndex . '&field=icon_notify' . ($this->object->id ? '&' . $this->identifier . '=' . $this->object->id : '') . '&token=' . $this->token;
        }

        return parent::renderForm();
    }

    protected function copyFromPost(&$object, $table)
    {
        // Before keep old image:
        if ($object->id && $object->icon_notify)
            $this->keep_old_image = $object->icon_notify;

        parent::copyFromPost($object, $table);

        // Process upload image:
        $this->uploadFiles('icon_notify', $object);

        // Binding image:
        if (!trim($object->icon_notify) && trim($this->keep_old_image) != '')
            $object->icon_notify = $this->keep_old_image;
    }

    public function afterUpdate($object)
    {
        if ($object->id && @file_exists(_PS_IMG_DIR_ . Ets_abandonedcart::ETS_ABC_UPLOAD_IMG_DIR . $this->keep_old_image) && trim($this->keep_old_image) != trim($object->icon_notify)) {
            @unlink(_PS_IMG_DIR_ . Ets_abandonedcart::ETS_ABC_UPLOAD_IMG_DIR . $this->keep_old_image);
            $this->keep_old_image = null;
        }

        return true;
    }

    public function ajaxProcessRenderForm()
    {
        if ($this->access('edit')) {
            $menus = EtsAbancartReminderForm::getInstance()->getReminderSteps();
            unset($menus['select_template']);
            $this->tpl_form_vars = array(
                'image_url' => $this->context->shop->getBaseURL(true) . 'img/' . $this->module->name . '/img/',
                'menus' => $menus,
                'lead_forms' => EtsAbancartForm::getAllForms(false, true),
                'maxSizeUpload' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'baseUri' => __PS_BASE_URI__,
                'field_types' => EtsAbancartField::getInstance()->getFieldType(),
                'module_dir' => _PS_MODULE_DIR_ . $this->module->name,
                'is17Ac' => $this->module->is17,
                'short_codes' => EtsAbancartDefines::getInstance()->getFields('short_codes'),
            );
            $this->toJson(array(
                'html' => $this->renderForm(),
            ));
        }
    }

    public function ajaxProcessUploadedImage()
    {
        if ($this->access('edit')) {
            $this->loadObject(true);

            // Defines.
            $key = trim(Tools::getValue('key'));
            if (!$key ||
                !Validate::isCleanHtml($key) ||
                !property_exists($this->object, $key)
            ) {
                $this->errors[] = sprintf($this->l('Property "%s" does not exist.', 'AdminEtsACReminderBrowserController'), $key);
            }

            if (!count($this->errors)) {
                $file_dest = $this->module->getLocalPath() . 'views/img/upload/';
                $json = array();
                // Process Upload Image.
                if (isset($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['name']) && $_FILES[$key]['name']) {
                    $salt = Tools::substr(sha1(microtime()), 0, 10);
                    $type = Tools::strtolower(Tools::substr(strrchr($_FILES[$key]['name'], '.'), 1));
                    $image = @file_exists($file_dest . Tools::strtolower($_FILES[$key]['name'])) || Tools::strtolower($_FILES[$key]['name']) == $this->object->$key ? $salt . '-' . Tools::strtolower($_FILES[$key]['name']) : Tools::strtolower($_FILES[$key]['name']);
                    $file_name = $file_dest . $image;

                    if (@file_exists($file_name)) {
                        $this->errors[] = $this->l('File name already exists. Try to rename the file and upload again', 'AdminEtsACReminderBrowserController');
                    } else {
                        $image_size = @getimagesize($_FILES[$key]['tmp_name']);
                        if (!$this->errors && isset($_FILES[$key]) && !empty($_FILES[$key]['tmp_name']) && !empty($image_size) && in_array($type, array('jpg', 'gif', 'jpeg', 'png'))) {
                            $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                            if ($errors = ImageManager::validateUpload($_FILES[$key]))
                                $this->errors[] = $errors;
                            elseif (!$temp_name || !move_uploaded_file($_FILES[$key]['tmp_name'], $temp_name))
                                $this->errors[] = $this->l('Cannot upload file', 'AdminEtsACReminderBrowserController');
                            elseif (!ImageManager::resize($temp_name, $file_name, null, null, $type))
                                $this->errors[] = $this->l('An error occurred during the image upload process.', 'AdminEtsACReminderBrowserController');
                            if (isset($temp_name))
                                @unlink($temp_name);
                        } else
                            $this->errors[] = $this->l('The uploaded image format is not valid. Please try again', 'AdminEtsACReminderBrowserController');
                    }

                    if (!count($this->errors)) {
                        $json['image'] = $image;
                        // Tpl_vars.
                        $this->context->smarty->assign(array(
                            'upf_link' => $this->mPath . 'views/img/upload/' . $image,
                            'upf_name' => $key,
                        ));
                        $json['html'] = $this->createTemplate('upload-img.tpl')->fetch();
                    }
                } else
                    $this->errors[] = $this->l('File does not exist', 'AdminEtsACReminderBrowserController');
            }
            $json['errors'] = count($this->errors) > 0 ? $this->module->displayError($this->errors) : '';
            //Remove old image.
            if (!$json['errors'] && $this->object->id && $this->object->$key != '') {
                $old_image = $this->object->$key;
                $this->object->$key = $image;
                if ($this->object->update())
                    unlink($file_dest . $old_image);
            }
            // Return json.
            $this->toJson($json);
        }
    }

    public function ajaxProcessDeleteImage()
    {
        if ($this->access('edit')) {
            $this->loadObject(true);

            $field = trim(Tools::getValue('field'));
            if (!$field ||
                !Validate::isCleanHtml($field) ||
                !property_exists($this->object, $field)
            ) {
                $this->errors[] = sprintf($this->l('Property "%s" does not exist.', 'AdminEtsACReminderBrowserController'), $field);
            } else {
                if (@file_exists(_PS_IMG_DIR_ . Ets_abandonedcart::ETS_ABC_UPLOAD_IMG_DIR . $this->object->$field)) {
                    @unlink(_PS_IMG_DIR_ . Ets_abandonedcart::ETS_ABC_UPLOAD_IMG_DIR . $this->object->$field);
                    $this->object->$field = '';
                    if (!$this->object->update(true))
                        $this->errors[] = $this->l('Cannot delete image.', 'AdminEtsACReminderBrowserController');
                } else
                    $this->errors[] = $this->l('Image does not exist.', 'AdminEtsACReminderBrowserController');
            }

            $hasError = count($this->errors) > 0 ? true : false;
            $this->toJson(array(
                'errors' => $hasError,
                'msg' => !$hasError ? $this->l('Delete image successfully', 'AdminEtsACReminderBrowserController') : $this->l('Delete image failed.', 'AdminEtsACReminderBrowserController'),
            ));
        }
    }
}