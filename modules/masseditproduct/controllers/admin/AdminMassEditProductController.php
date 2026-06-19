<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    SeoSA <885588@bk.ru>
 * @copyright 2012-2019 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once(dirname(__FILE__) . '/../../classes/tools/config.php');

class AdminMassEditProductController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->table = 'configuration';
        $this->identifier = 'id_configuration';
        $this->className = 'Configuration';
        $this->bootstrap = true;
        $this->display = 'edit';
        parent::__construct();
        SmartyMEP::registerSmartyFunctions();
    }

    public function setMedia($isNewTheme = false)
    {
        $ps_ver = str_replace(".", "", _PS_VERSION_);
        $ps_ver = Tools::substr($ps_ver, 0, 3);
        if ($ps_ver >= 174) {
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/theme_old_for_new.css');
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/new-theme.css');
            $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery-1.11.0.min.js');
            $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery-migrate-1.2.1.min.js');

            $this->context->controller->addJqueryUI('ui.widget');
            $this->context->controller->addJqueryUI('ui.datepicker');
            $this->context->controller->addJqueryUI('ui.mouse');
            $this->context->controller->addJqueryUI('ui.slider');

            $this->context->controller->addJqueryPlugin('tagify');
            $this->context->controller->addJqueryPlugin('fancybox');
            $this->context->controller->addJqueryPlugin('autosize');

            $this->context->controller->addCSS($this->module->getPathUri() .'views/css/jquery-ui-timepicker-addon.css');
            $this->context->controller->addJS($this->module->getPathUri() . 'views/js/jquery-ui-timepicker-addon.js');
            $this->context->controller->addJS(_PS_JS_DIR_ . 'admin.js');

            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/autoload/admin-theme.css');
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/autoload/font-awesome.css');
            $this->context->controller->addCSS($this->module->getPathUri().'views/css/autoload/selector_container.css');
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/autoload/message_viewer.css');
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/autoload/modulePreloader.css');
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/autoload/redactor.css');
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/autoload/search_products.css');
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/autoload/admin.css');

            $this->context->controller->addJS(array(
                $this->module->getPathUri() . 'views/js/jquery.insertAtCaret.js',
                $this->module->getPathUri() . 'views/js/redactor/redactor.js',
                $this->module->getPathUri() . 'views/js/redactor/plugins/table.js',
                $this->module->getPathUri() . 'views/js/redactor/plugins/video.js',
                $this->module->getPathUri() . 'views/js/tree_custom.js',
                $this->module->getPathUri() . 'views/js/jquery.finderSelect.js',
                $this->module->getPathUri() . 'views/js/search_product.js',
                $this->module->getPathUri() . 'views/js/selector_container.js',
                $this->module->getPathUri() . 'views/js/vendor/select2.min.js',
                $this->module->getPathUri() . 'views/js/langField.jquery.js',
                $this->module->getPathUri() . 'views/js/tabsMEP.js',
                $this->module->getPathUri() . 'views/js/Translator.js',
                $this->module->getPathUri() . 'views/js/modulePreloader.js',
                $this->module->getPathUri() . 'views/js/jquery.fn.js',
                $this->module->getPathUri() . 'views/js/tabContainer.js',
                $this->module->getPathUri() . 'views/js/popupForm.js',
                $this->module->getPathUri() . 'views/js/jquery.liTranslit.js',
                $this->module->getPathUri() . 'views/js/jquery-confirm.js',
                $this->module->getPathUri() . 'views/js/admin.js',
                $this->module->getPathUri() . 'views/js/bootstrap-dropdown.js',
                'https://seosaps.com/ru/module/seosamanager/manager?ajax=1&action=script&iso_code='
                . Context::getContext()->language->iso_code
            ));
        } elseif (_PS_VERSION_ >= 1.6) {
            parent::setMedia();
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/new-theme-for_16.css');
            ToolsModuleMEP::autoloadCSS($this->module->getPathUri() . 'views/css/autoload/');
            $this->context->controller->addJqueryUI('ui.widget');
            $this->context->controller->addJqueryPlugin('tagify');

            if (_PS_VERSION_ < 1.6) {
                $this->context->controller->addJqueryUI('ui.slider');
                $this->context->controller->addJqueryUI('ui.datepicker');
                $this->context->controller->addCSS($this->module->getPathUri() .
                    'views/css/jquery-ui-timepicker-addon.css');
                $this->context->controller->addJS($this->module->getPathUri() .
                    'views/js/jquery-ui-timepicker-addon.js');
            } else {
                $this->context->controller->addJqueryPlugin('timepicker');
            }

            $this->context->controller->addJS(array(
                $this->module->getPathUri() . 'views/js/jquery.insertAtCaret.js',
                $this->module->getPathUri() . 'views/js/redactor/redactor.js',
                $this->module->getPathUri() . 'views/js/redactor/plugins/table.js',
                $this->module->getPathUri() . 'views/js/redactor/plugins/video.js',
                $this->module->getPathUri() . 'views/js/tree_custom.js',
                $this->module->getPathUri() . 'views/js/jquery.finderSelect.js',
                $this->module->getPathUri() . 'views/js/search_product.js',
                $this->module->getPathUri() . 'views/js/selector_container.js',
                $this->module->getPathUri() . 'views/js/vendor/select2.min.js',
                $this->module->getPathUri() . 'views/js/langField.jquery.js',
                $this->module->getPathUri() . 'views/js/tabsMEP.js',
                $this->module->getPathUri() . 'views/js/Translator.js',
                $this->module->getPathUri() . 'views/js/modulePreloader.js',
                $this->module->getPathUri() . 'views/js/jquery.fn.js',
                $this->module->getPathUri() . 'views/js/tabContainer.js',
                $this->module->getPathUri() . 'views/js/popupForm.js',
                $this->module->getPathUri() . 'views/js/jquery.liTranslit.js',
                $this->module->getPathUri() . 'views/js/jquery-confirm.js',
                $this->module->getPathUri() . 'views/js/admin.js',
                $this->module->getPathUri() . 'views/js/bootstrap-dropdown.js',
                'https://seosaps.com/ru/module/seosamanager/manager?ajax=1&action=script&iso_code='
                . Context::getContext()->language->iso_code
            ));
        } else {
            parent::setMedia();
            $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/new-theme-for_16.css');
            ToolsModuleMEP::autoloadCSS($this->module->getPathUri() . 'views/css/autoload/');
            $this->context->controller->addJqueryUI('ui.widget');
            $this->context->controller->addJqueryPlugin('tagify');

            if (_PS_VERSION_ < 1.6) {
                $this->context->controller->addJqueryUI('ui.slider');
                $this->context->controller->addJqueryUI('ui.datepicker');
                $this->context->controller->addCSS($this->module->getPathUri() .
                    'views/css/jquery-ui-timepicker-addon.css');
                $this->context->controller->addJS($this->module->getPathUri() .
                    'views/js/jquery-ui-timepicker-addon.js');
            } else {
                $this->context->controller->addJqueryPlugin('timepicker');
            }

            $this->context->controller->addJS(array(
                $this->module->getPathUri() . 'views/js/jquery.insertAtCaret.js',
                $this->module->getPathUri() . 'views/js/redactor/redactor.js',
                $this->module->getPathUri() . 'views/js/redactor/plugins/table.js',
                $this->module->getPathUri() . 'views/js/redactor/plugins/video.js',
                $this->module->getPathUri() . 'views/js/tree_custom.js',
                $this->module->getPathUri() . 'views/js/jquery.finderSelect.js',
                $this->module->getPathUri() . 'views/js/search_product.js',
                $this->module->getPathUri() . 'views/js/selector_container.js',
                $this->module->getPathUri() . 'views/js/vendor/select2.min.js',
                $this->module->getPathUri() . 'views/js/langField.jquery.js',
                $this->module->getPathUri() . 'views/js/tabsMEP.js',
                $this->module->getPathUri() . 'views/js/Translator.js',
                $this->module->getPathUri() . 'views/js/modulePreloader.js',
                $this->module->getPathUri() . 'views/js/jquery.fn.js',
                $this->module->getPathUri() . 'views/js/tabContainer.js',
                $this->module->getPathUri() . 'views/js/popupForm.js',
                $this->module->getPathUri() . 'views/js/jquery.liTranslit.js',
                $this->module->getPathUri() . 'views/js/jquery-confirm.js',
                $this->module->getPathUri() . 'views/js/admin.js',
                $this->module->getPathUri() . 'views/js/bootstrap-dropdown.js',
                'https://seosaps.com/ru/module/seosamanager/manager?ajax=1&action=script&iso_code='
                . Context::getContext()->language->iso_code
            ));
        }
    }

    public function renderForm()
    {
        $features = MassEditTools::getFeatures($this->context->language->id, true, 1, true);
        $input_product_name_type_search = array(
            'name' => 'product_name_type_search',
            'values' => array(
                array(
                    'id' => 'exact_match',
                    'text' => $this->l('Exact match'),
                ),
                array(
                    'id' => 'occurrence',
                    'text' => $this->l('Search for occurrence'),
                ),
            ),
            'default_id' => 'exact_match',
        );

        $attribute_groups = AttributeGroup::getAttributesGroups($this->context->language->id);
        if (is_array($attribute_groups) && count($attribute_groups)) {
            foreach ($attribute_groups as &$attribute_group) {
                $attribute_group['attributes'] = AttributeGroup::getAttributes(
                    $this->context->language->id,
                    (int)$attribute_group['id_attribute_group']
                );
            }
        }

        $tpl_vars = array(
            'categories' => Category::getCategories($this->context->language->id, false),
            'manufacturers' => Manufacturer::getManufacturers(
                false,
                0,
                true,
                false,
                false,
                false,
                true
            ),
            'suppliers' => Supplier::getSuppliers(
                false,
                0,
                false,
                false,
                false,
                false
            ),
            'carriers' => Carrier::getCarriers(
                false,
                0,
                false,
                false,
                false,
                false
            ),
            'features' => $features,
            'languages' => ToolsModuleMEP::getLanguages(false),
            'default_form_language' => $this->context->language->id,
            'input_product_name_type_search' => $input_product_name_type_search,
            'upload_file_dir' => _MODULE_DIR_ . $this->module->name . '/lib/redactor/file_upload.php',
            'upload_image_dir' => _MODULE_DIR_ . $this->module->name . '/lib/redactor/image_upload.php',
            'link_on_tab_module' => HelperModuleMEP::getModuleTabAdminLink(),
            'templates_products' => Db::getInstance()->executeS(TemplateProductsMEP::getAllQuery()->build()),
            'tabs' => $this->getTabs(),
            'attribures_groups' => $attribute_groups
        );

        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $tpl_vars);
        $this->fields_form = array(
            'legend' => array(
                'title' => 'tree_custom.tpl',
            ),
        );

        $this->context->controller->addCSS($this->module->getPathURI() . 'views/css/jquery-confirm.css');

        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $this->context->controller->addCSS($this->module->getPathURI() . 'views/css/admin-theme.css');
        }

        if (_PS_VERSION_ > 1.6) {
            $this->context->controller->addCSS($this->module->getPathURI() . 'views/css/admin-theme1_7.css');
        }

        return parent::renderForm();
    }

    public function getTabs($parentId = 0, $level = 0)
    {
        $files = glob(_PS_MODULE_DIR_ . $this->module->name . '/classes/tabs/*TabMEP.php');
        $tabs = array();
        if (is_array($files) && count($files)) {
            foreach ($files as $file) {
                $class = str_replace('.php', '', basename($file));
                $tab = new $class();
                $tabs[] = $tab;
            }
        }
        usort($tabs, array($this, 'sortTabsByPosition'));

        return $tabs;
    }

    /**
     * @param BaseTabMEP $a
     * @param BaseTabMEP $b
     * @return int
     */
    public function sortTabsByPosition($a, $b)
    {
        if ($a->getPosition() == $b->getPosition()) {
            return 0;
        }
        return ($a->getPosition() > $b->getPosition() ? 1 : -1);
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::getValue('action') == 'getMaxPositionForImageCaption') {
            $ids = Tools::getValue('products');
            $ids = (is_array($ids) ? array_map('intval', $ids) : array());

            $count = count($ids) ? (int)Db::getInstance()->getValue(
                'SELECT MAX(position) FROM `' . _DB_PREFIX_ . 'image`
                 WHERE `id_product` IN(' . pSQL(implode(',', $ids)) . ')'
            ) : 0;

            $string = $this->l('Position');

            $option = '';
            for ($i = 1; $i <= $count; $i++) {
                $option .= '<option value="' . $i . '">' . $string . ' ' . $i . '</option>';
            }

            die(Tools::jsonEncode(array('option' => $option)));
        }
    }

    public function ajaxProcessSearchProducts()
    {
        $products = ProductFinderMEP::getInstance()->findProducts();
        $nb_products = ProductFinderMEP::getInstance()->getTotal();
        $hash = ProductFinderMEP::getInstance()->getHash();
        $pages_nb = ceil($nb_products / ProductFinderMEP::getInstance()->getRequestParam('how_many_show'));
        $page = ProductFinderMEP::getInstance()->getRequestParam('page');
        $range = 5;
        $start = ($page - $range);
        if ($start < 1) {
            $start = 1;
        }
        $stop = ($page + $range);
        if ($stop > $pages_nb) {
            $stop = (int)$pages_nb;
        }

        $currency = Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $currency = $currency['id_currency'];
        $attribures_groups = MassEditTools::getAttributeGroups();

        die(
            Tools::jsonEncode(
                array(
                    'products' => ToolsModuleMEP::fetchTemplate(
                        'admin/mass_edit_product/helpers/form/products.tpl',
                        array(
                            'currency' => $currency,
                            'products' => $products,
                            'link' => $this->context->link,
                            'nb_products' => $nb_products,
                            'products_per_page' => $pages_nb,
                            'pages_nb' => $pages_nb,
                            'p' => $page,
                            'n' => $pages_nb,
                            'range' => $range,
                            'start' => $start,
                            'stop' => $stop,
                            'attribures_groups' => $attribures_groups,
                        )
                    ),
                    'hash' => implode('&', $hash),
                    'count_result' => $nb_products,
                )
            )
        );
    }

    public function ajaxProcessSetAllProduct()
    {
        $tab = Tools::getValue('tab_name');
        $class_name = ToolsModuleMEP::toCamelCase($tab, true) . 'TabMEP';
        if (class_exists($class_name)) {
            /**
             * @var BaseTabMEP $object
             */
            $object = new $class_name();
            return $object->apply();
        } else {
            LoggerMEP::getInstance()->error(sprintf($this->l('Class Tab %s not exists'), $class_name));
            return array();
        }
    }

    public function ajaxProcessApi()
    {
        ErrorHandlerMEP::setErrorHandler();
        HelperModuleMEP::createAjaxApiCall($this);
    }

    public function ajaxProcessCopyFieldDescription()
    {
        $id_product = Tools::getValue('id_product');
        $id_lang = Tools::getValue('id_lang');
        $iso_code = Language::getIsoById($id_lang);
        if (!$iso_code) {
            $id_lang = $this->context->language->id;
        }

        $product = new Product($id_product, false, $id_lang);
        $description = false;
        if (Validate::isLoadedObject($product)) {
            $description = $product->description;
        }
        die(
            Tools::jsonEncode(
                array(
                    'response' => $description,
                )
            )
        );
    }

    public function ajaxProcessCopyFieldDescriptionShort()
    {
        $id_product = Tools::getValue('id_product');
        $id_lang = Tools::getValue('id_lang');
        $iso_code = Language::getIsoById($id_lang);
        if (!$iso_code) {
            $id_lang = $this->context->language->id;
        }

        $product = new Product($id_product, false, $id_lang);
        $description = false;
        if (Validate::isLoadedObject($product)) {
            $description = $product->description_short;
        }
        die(Tools::jsonEncode(array('response' => $description)));
    }

    public function ajaxProcessRowCopySearchProduct()
    {
        $query = Tools::getValue('query');
        $rows = Db::getInstance()->executeS(
            'SELECT p.`id_product`, CONCAT(p.`id_product`, " - ", pl.`name`) as name
        FROM ' . _DB_PREFIX_ . 'product p
		LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.`id_product` = pl.`id_product`
		 AND pl.`id_lang` = ' . (int)$this->context->language->id . '
		WHERE ' .
            MassEditTools::buildSQLSearchWhereFromQuery(
                $query,
                false,
                'CONCAT(p.`id_product`, " - ", pl.`name`)'
            )
        );
        die(Tools::jsonEncode((is_array($rows) && count($rows) ? $rows : array())));
    }

    public function ajaxProcessDownloadAttachment()
    {
        if ($this->tabAccess['edit'] === '0') {
            //Return if no access
            return die(Tools::jsonEncode(array('error' => $this->l('You do not have the right permission'))));
        }

        $filename = array();
        $description = array();
        foreach (ToolsModuleMEP::getLanguages(false) as $lang) {
            $filename_lang = Tools::getValue('filename_' . $lang['id_lang']);
            $description_lang = Tools::getValue('description_' . $lang['id_lang']);
            $filename[$lang['id_lang']] = ($filename_lang ? $filename_lang : Tools::getValue(
                'filename_' . $this->context->language->id
            ));
            $description[$lang['id_lang']] = ($description_lang ? $description_lang : Tools::getValue(
                'description_' . $this->context->language->id
            ));
        }

        $file = $_FILES['file'];

        if (isset($file)) {
            if ((int)$file['error'] === 1) {
                $file['error'] = array();

                $max_upload = (int)ini_get('upload_max_filesize');
                $max_post = (int)ini_get('post_max_size');
                $upload_mb = min($max_upload, $max_post);
                $file['error'][] = sprintf(
                    $this->l('File %1$s exceeds the size allowed by the server. The limit is set to %2$d MB.'),
                    '<b>' . $file['name'] . '</b> ',
                    '<b>' . $upload_mb . '</b>'
                );
            }

            $file['error'] = array();

            $is_attachment_name_valid = false;

            if (array_key_exists($this->context->language->id, $filename) && $filename[$this->context->language->id]) {
                $is_attachment_name_valid = true;
            }

            if (!$is_attachment_name_valid) {
                $file['error'][] = $this->l('An attachment name is required.');
            }

            if (empty($file['error'])) {
                if (is_uploaded_file($file['tmp_name'])) {
                    if ($file['size'] > (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024)) {
                        $file['error'][] = sprintf(
                            $this->l(
                                'The file is too large. Maximum size allowed is: %1$d kB. 
                                The file you are trying to upload is %2$d kB.'
                            ),
                            (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024),
                            number_format(($file['size'] / 1024), 2, '.', '')
                        );
                    } else {
                        do {
                            $uniqid = sha1(microtime());
                        } while (file_exists(_PS_DOWNLOAD_DIR_ . $uniqid));
                        if (!copy($file['tmp_name'], _PS_DOWNLOAD_DIR_ . $uniqid)) {
                            $file['error'][] = $this->l('File copy failed');
                        }

                        @unlink($file['tmp_name']);
                    }
                } else {
                    $file['error'][] = $this->l('The file is missing.');
                }

                if (empty($file['error']) && isset($uniqid)) {
                    $attachment = new Attachment();

                    $attachment->name = $filename;
                    $attachment->description = $description;

                    $attachment->file = $uniqid;
                    $attachment->mime = $file['type'];
                    $attachment->file_name = $file['name'];

                    if (empty($attachment->mime) || Tools::strlen($attachment->mime) > 128) {
                        $file['error'][] = $this->l('Invalid file extension');
                    }

                    if (!Validate::isGenericName($attachment->file_name)) {
                        $file['error'][] = $this->l('Invalid file name');
                    }

                    if (Tools::strlen($attachment->file_name) > 128) {
                        $file['error'][] = $this->l('The file name is too long.');
                    }

                    if (empty($this->errors)) {
                        $res = $attachment->add();
                        if (!$res) {
                            $file['error'][] = $this->l('This attachment was unable to be loaded into the database.');
                        } else {
                            $file['id_attachment'] = $attachment->id;
                            $file['filename'] = $attachment->name[$this->context->employee->id_lang];
                            if (!$res) {
                                $file['error'][] = $this->l(
                                    'We were unable to associate this attachment to a product.'
                                );
                            }
                        }
                    } else {
                        $file['error'][] = $this->l('Invalid file');
                    }
                }
            }

            die(Tools::jsonEncode($file));
        }
    }

    public function ajaxProcessLoadFeatures()
    {
        $p = (int)Tools::getValue('p', 1);

        $features = MassEditTools::getFeatures($this->context->language->id, true, $p);

        foreach ($features as &$feature) {
            $feature['values'] = FeatureValue::getFeatureValuesWithLang(
                $this->context->language->id,
                $feature['id_feature']
            );
        }
        $features_list = '';

        foreach ($features as $f) {
            $this->context->smarty->assign(
                array(
                    'languages' => ToolsModuleMEP::getLanguages(false),
                    'feature' => $f,
                )
            );
            $features_list .= ToolsModuleMEP::fetchTemplate(
                'admin/mass_edit_product/helpers/form/row_feature.tpl'
            );
        }

        die(
            Tools::jsonEncode(
                array(
                    'hasError' => false,
                    'features_list' => $features_list,
                )
            )
        );
    }

    public function ajaxProcessUploadImages()
    {
        MassEditTools::clearTmpFolder();
        $images = MassEditTools::getImages('image');
        $response_images = array();
        if (is_array($images) && count($images)) {
            foreach ($images as $key => $image) {
                if (MassEditTools::checkImage('image', $key)) {
                    $response_images[$key] = array();
                    $this->uploadImageProduct($image, MassEditTools::getPath() . $key . '_original.jpg');
                    $response_images[$key]['original'] = $key . '_original.jpg';
                    $types = ImageType::getImagesTypes('products');
                    foreach ($types as $type) {
                        $this->uploadImageProduct(
                            $image,
                            MassEditTools::getPath() . $key . '_original_' . $type['name'] . '.jpg',
                            $type['width'],
                            $type['height']
                        );
                        $response_images[$key][$type['name']] = $key . '_original_' . $type['name'] . '.jpg';
                    }
                }
            }
        }
        die(
            Tools::jsonEncode(
                array(
                    'responseImages' => $response_images,
                )
            )
        );
    }

    public function ajaxProcessGetProducts()
    {
        $query = Tools::getValue('query');
        $select_products = Tools::getValue('select_products');
        $search_by = Tools::getValue('search_by');
        if (!is_array($select_products) || !count($select_products)) {
            $select_products = array();
        }

        $search_by_query = 'pl.`name`';
        if ($search_by == 0) {
            $search_by_query = 'ps.`reference`';
        }

        $result = Db::getInstance()->executeS(
            'SELECT pl.`id_product`, CONCAT(pl.`id_product`, " - ", pl.`name`) as `name` 
             FROM ' . _DB_PREFIX_ . 'product_shop p
		     LEFT JOIN ' . _DB_PREFIX_ . 'product ps ON ps.`id_product` = p.`id_product`
		     LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.`id_product` = pl.`id_product` 
		     AND pl.`id_lang` = ' . (int)$this->context->language->id .
            ' WHERE ' . $search_by_query . ' LIKE "%' . pSQL($query) . '%" 
            AND p.`id_shop` = ' . (int)$this->context->shop->id
            . ' AND pl.`id_shop` = ' . (int)$this->context->shop->id .
            (count($select_products) ?
                ' AND p.id_product NOT 
                IN(' . pSQL(implode(',', array_map('intval', $select_products))) . ') '
                : '')
        );

        if (!$result) {
            $result = array();
        }
        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessGetCombinationsByAttributes()
    {
        $attributes = array();
        foreach ((array)Tools::getValue('data') as $data) {
            $attributes[] = $data['value'];
        }
        $attributes = array_unique($attributes);

        $combinations = Db::getInstance()->executeS(
            'SELECT `id_product_attribute` FROM `' . _DB_PREFIX_ . 'product_attribute_combination`
		WHERE `id_attribute` IN (' . (count($attributes) ? implode($attributes, ', ') : '0') . ') 
		GROUP BY 1 HAVING COUNT(*) = '.(int)count($attributes)
        );

        die(
            Tools::jsonEncode(
                array(
                    'hasError' => $combinations ? false : true,
                    'error' => $this->module->l('No combinations with these attributes'),
                    'data' => $combinations,
                )
            )
        );
    }

    public function ajaxProcessGetAttributesByGroup()
    {
        $attributes = MassEditTools::getAttributes($this->context->language->id);

        die(Tools::jsonEncode(
            array(
                'hasError' => $attributes ? false : true,
                'error' => $this->module->l('No combinations with these attributes'),
                'data' => $attributes,
            )
        ));
    }

    public function ajaxProcessRenderFeatureValues()
    {
        $res = array();
        foreach (Tools::getValue('ids_feature', array()) as $id_feature) {
            $res[] = array(
                'id_feature' => $id_feature,
                'html' => ToolsModuleMEP::fetchTemplate(
                    'admin/feature_values.tpl',
                    array(
                        'values' => FeatureValue::getFeatureValuesWithLang(
                            $this->context->language->id,
                            (int)$id_feature
                        ),
                        'id_feature' => $id_feature,
                    )
                )
            );
        }

        die(
            Tools::jsonEncode(
                array(
                    'return' => $res
                )
            )
        );
    }

    public function ajaxProcessRenderAttributeValues()
    {
        $res = array();

        foreach (Tools::getValue('ids_attribute', array()) as $id_attribute) {
            $res[] = array(
                'id_attribute' => $id_attribute,
                'html' => ToolsModuleMEP::fetchTemplate(
                    'admin/attribute_values.tpl',
                    array(
                        'values' =>  AttributeGroup::getAttributes(
                            $this->context->language->id,
                            (int)$id_attribute
                        ),
                        'id_attribute' => $id_attribute,
                    )
                )
            );
        }

        die(
            Tools::jsonEncode(
                array(
                'return' => $res
                )
            )
        );
    }

    public function ajaxProcessLoadCombinations()
    {
        $id_product = (int)Tools::getValue('id_product');
        die(
            Tools::jsonEncode(
                array(
                    'combinations' => MassEditTools::renderCombinationsProduct($id_product)
                )
            )
        );
    }

    /**
     * instead ajaxProcessLoadCombinations() for one ajax request
     */
    public function ajaxProcessLoadCombinationsOneRequest()
    {
        $ids_product = Tools::getValue('ids_product');
        die(Tools::jsonEncode(MassEditTools::renderCombinationsProduct($ids_product)));
    }

    public function ajaxProcessAddCustomizationField()
    {
        $type = Tools::getValue('type');
        $counter = Tools::getValue('counter');
        $languages = ToolsModuleMEP::getLanguages(false);

        die(
            Tools::jsonEncode(
                array(
                    'html' => ToolsModuleMEP::fetchTemplate(
                        'admin/mass_edit_product/helpers/form/customization_field.tpl',
                        array(
                            'type' => $type,
                            'counter' => $counter,
                            'languages' => $languages,
                        )
                    ),
                )
            )
        );
    }

    public function ajaxProcessSaveTemplateProduct()
    {
        $products = Tools::getValue('products');
        $name = Tools::getValue('name');

        if (!is_array($products) || !count($products)) {
            $this->errors[] = $this->l('Not products!');
        }
        if (!$name) {
            $this->errors[] = $this->l('Name empty');
        }

        if (!count($this->errors)) {
            $template_products = new TemplateProductsMEP();
            $template_products->name = $name;
            foreach ($products as $product) {
                $template_products->products[] = array(
                    'id_product' => $product['id'],
                );
            }

            if (!$template_products->save()) {
                $this->errors[] = $this->l('Template save successfuly!');
            }
        }

        die(
            Tools::jsonEncode(
                array(
                    'hasError' => (count($this->errors) ? true : false),
                    'errors' => $this->errors,
                    'templates_products' => TemplateProductsMEP::getAll(true),
                )
            )
        );
    }

    public function ajaxProcessDeleteTemplateProduct()
    {
        $id = Tools::getValue('id');
        $template_products = new TemplateProductsMEP($id);

        if (Validate::isLoadedObject($template_products)) {
            $template_products->delete();
        }

        die(Tools::jsonEncode(array()));
    }

    public function ajaxProcessGetTemplateProduct()
    {
        $id = Tools::getValue('id');
        $template_products = new TemplateProductsMEP($id);

        $currency = Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $currency = $currency['id_currency'];

        $popup_list = '';
        $list = '';
        $products = array();
        foreach ($template_products->products as $product) {
            $popup_list .= ToolsModuleMEP::fetchTemplate(
                'admin/mass_edit_product/helpers/form/popup_product_line.tpl',
                array(
                    'product' => $product,
                )
            );
            $list .= ToolsModuleMEP::fetchTemplate(
                'admin/mass_edit_product/helpers/form/product_line.tpl',
                array(
                    'product' => $product,
                    'currency' => $currency,
                )
            );
            $products[$product['id_product']] = array(
                'id' => $product['id_product'],
                'name' => $product['name'],
            );
        }

        die(
            Tools::jsonEncode(
                array(
                    'popup_list' => $popup_list,
                    'list' => $list,
                    'products' => $products,
                )
            )
        );
    }

    public function ajaxProcessLoadTab()
    {
        $tab_name = Tools::getValue('tab_name');
        $class_name = ToolsModuleMEP::toCamelCase($tab_name, true) . 'TabMEP';
        if (class_exists($class_name)) {
            ob_start();
            /**
             * @var BaseTabMEP $object
             */
            $object = new $class_name();

            echo $object->renderTabForm();
            $form = ob_get_contents();
            ob_clean();

            die(Tools::jsonEncode(array(
                'hasError' => false,
                'html' => $form
            )));
        } else {
            die(Tools::jsonEncode(array(
                'hasError' => true,
                'error' => sprintf($this->l('Class tab: %s not exists'), $class_name)
            )));
        }
    }

    public function uploadImageProduct($tmp_image, $image_to, $width = null, $height = null)
    {
        ImageManager::resize($tmp_image, $image_to, $width, $height);
    }
}
