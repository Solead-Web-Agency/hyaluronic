<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AutoTranslator extends Module
{
    public $errors = array();

    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }
        $this->name = 'autotranslator';
        $this->tab = 'administration';
        $this->version = '3.0.3';
        $this->author = 'Amazzing';
        $this->need_instance = 0;
        $this->module_key = 'f08869de6029835933882a99d65d03d4';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Automatic translations');
        $this->description = $this->l('Automatic translations for products, categories, attributes etc...');
        $this->db = Db::getInstance();
        $this->shop_ids = Shop::getContextListShopID();
        $this->is_17 = Tools::substr(_PS_VERSION_, 0, 3) === '1.7';
    }

    public function install()
    {
        $this->defineAPI();
        return parent::install()
            && $this->repeatingTranslations('install')
            && $this->api->install()
            && $this->registerHook('displayBackOfficeHeader')
            && Configuration::updateValue('AT_LANG_FROM', $this->getDefaultLangISO());
    }

    public function uninstall()
    {
        $this->defineAPI();
        return parent::uninstall()
            && $this->repeatingTranslations('uninstall')
            && $this->api->uninstall()
            && Configuration::deleteByName('AT_LANG_FROM');
    }

    public function defineAPI()
    {
        if (!isset($this->api)) {
            require_once($this->local_path.'classes/TranslationAPI.php');
            $this->api = new TranslationAPI($this);
        }
    }

    public function getDefaultLangISO()
    {
        return Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
    }

    public function runSql($sql)
    {
        foreach ($sql as $s) {
            if (!$this->db->Execute($s)) {
                return false;
            }
        }
        return true;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if ($this->is_17 && Tools::getValue('controller') == 'AdminTranslations' &&
            Tools::getValue('type') == 'themes') {
            // this can be used in further versions
            // $this->context->controller->addJquery();
            // $this->context->controller->addJS($this->_path.'views/js/theme-autotranslate.js?v='.$this->version);
        } elseif (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->js_files[] = $this->_path.'views/js/back.js?v='.$this->version;
            $this->context->controller->css_files[$this->_path.'views/css/back.css?v='.$this->version] = 'all';
        }
    }

    public function hookDisplayBackOfficeTop()
    {
        if ($this->is_17 && Tools::getValue('controller') == 'AdminTranslations' &&
            Tools::getValue('type') == 'themes') {
            // this can be used in further versions
            // $ajax_action_url = 'index.php?controller=AdminModules&configure='.$this->name.
            // '&token='.Tools::getAdminTokenLite('AdminModules').'&ajax=1';
            // $this->context->smarty->assign(array(
            //     'ajax_action_url' => $ajax_action_url,
            //     'logo_path_png' => $this->_path.'/logo.png',
            //     'logo_path_gif' => $this->_path.'/logo.gif',
            // ));
            // return $this->display(__FILE__, 'views/templates/admin/theme-autotranslate-form.tpl');
        }
    }

    public function escapeApostrophe($string)
    {
        return str_replace("'", "\'", $string);
    }

    public function ajaxAction()
    {
        $action = Tools::getValue('ajax_action');
        $ret = array('hasError' => false);
        switch ($action) {
            case 'saveAPI':
                $credentials = $this->parseStr(Tools::getValue('credentials'));
                $ret['saved'] = $this->api->saveData(Tools::getValue('provider'), $credentials);
                $ret['api_stats_data'] = $this->api->getSimplifiedStatsData();
                break;
            case 'saveOverwriteOption':
                $ret['saved'] = Configuration::updateGlobalValue(
                    'AT_OVERWRITE_EXISTING',
                    (int)Tools::getValue('overwrite')
                );
                break;
            case 'autoTranslate':
                $time = microtime(true);
                $type = Tools::getValue('content_type');
                $id = Tools::getValue('identifier');
                $fields = array_fill_keys(Tools::getValue('fields'), 1);
                $overwrite = Tools::getValue('overwrite_existing');
                $from_iso = Tools::getValue('from');
                $to_isos = Tools::getValue('to');
                $id_lang_from = Language::getIdByIso($from_iso);
                $stats_not_supported = $warnings = array();
                foreach ($to_isos as $to_iso) {
                    if ($this->api->getSelectedProvider()->supportsModel($from_iso, $to_iso)) {
                        $id_lang_to = Language::getIdByIso($to_iso);
                        $this->translateResources($type, $id, $id_lang_from, $id_lang_to, $fields, $overwrite);
                        if ($this->api->not_processed) {
                            $not_processed = implode(', ', $this->api->not_processed);
                            $warning = sprintf($this->l('%s was not translated'), $not_processed);
                            $warnings[$warning] = 1; // handle duplicates
                        }
                    } else {
                        $stats_not_supported[$to_iso] = $to_iso;
                    }
                }
                if ($warnings) {
                    $ret['warning'] = utf8_encode(implode('; ', array_keys($warnings)));
                }
                $time = microtime(true) - $time;
                $msg = sprintf(
                    $this->l('%d characters processed in %s s'),
                    $this->api->processed_chars_num,
                    round($time, 2)
                );
                if (isset($this->additinal_response)) {
                    $msg .= $this->additinal_response;
                }
                $ret['response_msg'] = utf8_encode($msg);
                $ret['api_stats_data'] = $this->api->getSimplifiedStatsData();
                // prepare translation_stats
                $identifier = $this->getIdentifierFieldByContentType($type);
                $translation_stats = $this->getTranslationsStats($id, $type, $identifier, $from_iso, $to_isos);
                $translation_stats = isset($translation_stats[$id]) ? $translation_stats[$id] : array();
                $this->context->smarty->assign(array(
                    'stats' => $translation_stats,
                    'stats_not_supported' => $stats_not_supported
                ));
                $translation_stats_html = $this->display(__FILE__, 'views/templates/admin/translation-stats.tpl');
                $ret['translation_stats_html'] = utf8_encode($translation_stats_html);
                break;
            case 'callResourseList':
                $this->prepareListVariables();
                $ret['list_html'] = utf8_encode($this->display(__FILE__, 'views/templates/admin/list.tpl'));
                $ct = Tools::getValue('at_ct');
                if (Tools::getValue('updateFields') && $ct != 'theme' && $ct != 'module') {
                    $ret['translatable_fields'] = $this->getTranslatableFields($ct, true);
                    $ret['all_fields_label'] = utf8_encode($this->l('All fields'));
                }
                if ($update_lang_from = Tools::getValue('updateLangFrom')) {
                    Configuration::updateValue('AT_LANG_FROM', $update_lang_from);
                }
                break;
        }
        exit(Tools::jsonEncode($ret));
    }

    public function getTruncateOptions($field_name)
    {
        $allow_html = in_array($field_name, array('description', 'description_short', 'content', 'short_content'));
        return array('ellipsis' => '', 'exact' => false, 'html' => $allow_html);
    }

    public function translateResources($type, $id, $from, $to, $fields, $overwrite)
    {
        $r_params = array('id' => $id, 'type' => $type, 'l_ids' => array($from, $to)); // $r_params will be used later
        $rt = $this->repeatingTranslations('get', $r_params);
        $repeating_data = array(
            'saved' => isset($rt[$id][$from.'-'.$to]) ? array_keys($rt[$id][$from.'-'.$to]) : array(),
            'processed' => array(),
        );
        if (!$overwrite && !empty($repeating_data['saved'])) {
            foreach ($repeating_data['saved'] as $key) {
                unset($fields[$key]);
            }
        }

        switch ($type) {
            case 'product':
            case 'attribute_group':
            case 'attribute':
            case 'feature':
            case 'feature_value':
            case 'category':
            case 'cms':
            case 'cms_category':
            case 'manufacturer':
            case 'supplier':
            case 'simpleblog_post':
            case 'simpleblog_category':
            case 'ybc_blog_post':
            case 'ybc_blog_category':
            case 'ybc_blog_gallery':
            case 'ybc_blog_slide':
            case 'ybc_blog_employee':
            case 'fme_blog_posts':
            case 'fme_blog_categories':
            case 'editorial':
            case 'meta':
            case 'attachment':
                $obj = $this->createObject($type, $id);
                $translatable_fields = $this->getStandardTranslatableFields($obj);
                $to_translate = array();
                foreach (array_keys($translatable_fields) as $f_name) {
                    if (!isset($fields[$f_name]) || !trim($obj->{$f_name}[$from])) {
                        continue;
                    }
                    if ($overwrite || !$obj->{$f_name}[$to] || $obj->{$f_name}[$to] == $obj->{$f_name}[$from]) {
                        $to_translate[$f_name] = $obj->{$f_name}[$from];
                    }
                }
                if ($to_translate) {
                    $translated = $this->translate($to_translate, $from, $to);
                    foreach ($translated as $f_name => $value) {
                        if ($truncate = $translatable_fields[$f_name]) {
                            $value = Tools::truncateString($value, $truncate, $this->getTruncateOptions($f_name));
                        }
                        if ($f_name == 'meta_keywords') {
                            $value = Tools::strtolower($value);
                        }
                        $obj->{$f_name}[$to] = $value;
                        if (isset($to_translate[$f_name]) && $to_translate[$f_name] == $value) {
                            $repeating_data['processed'][] = $f_name;
                        }
                    }
                    $obj->saving_required = 1;
                }
                $this->updateLinkRewriteIfRequired($obj, $from, $to, $fields, $repeating_data);
                if (!empty($obj->saving_required)) {
                    if ($type == 'product' && empty($obj->price)) {
                        $obj->price = 0; // fix for some complex multishop scenarios
                    }
                    $this->saveObject($obj);
                }
                if ($type == 'product') {
                    if (isset($fields['tags'])) {
                        $this->translateProductTags($obj, $from, $to, $overwrite);
                    }
                    if (isset($fields['image_legends'])) {
                        $this->translateProductImages($obj, $from, $to, $overwrite, $repeating_data);
                    }
                    if (isset($fields['customization_fields'])) {
                        $this->translateProductCustomizationFields($obj, $from, $to, $overwrite, $repeating_data);
                    }
                    if (in_array($obj->visibility, array('both', 'search')) &&
                        Configuration::get('PS_SEARCH_INDEXATION')) {
                        Search::indexation(false, $obj->id);  // update search index
                    }
                }
                break;
            case 'a_blog_post':
            case 'a_blog_category':
                $fields_to_translate = array_fill_keys(array_intersect(
                    array_keys($fields),
                    $this->getTranslatableFields($type)
                ), 1);
                $identifier_name = $this->getIdentifierFieldByContentType($type);
                $table_name = _DB_PREFIX_.$type.'_lang';
                $imploded_shop_ids = implode(', ', $this->shop_ids);
                $row_from = $this->db->getRow('
                    SELECT * FROM '.pSQL($table_name).'
                    WHERE '.pSQL($identifier_name).' = '.(int)$id.'
                    AND id_lang = '.(int)$from.' AND id_shop = '.(int)$this->context->shop->id.'
                ');
                $rows_to = $this->db->executeS('
                    SELECT * FROM '.pSQL($table_name).'
                    WHERE '.pSQL($identifier_name).' = '.(int)$id.'
                    AND id_lang = '.(int)$to.' AND id_shop IN ('.pSQL($imploded_shop_ids).')
                ');
                $to_translate = array();
                foreach ($rows_to as $row) {
                    foreach ($row as $name => $value) {
                        if (!$row_from[$name] || !isset($fields_to_translate[$name]) || $name == 'link_rewrite') {
                            continue;
                        }
                        if ($overwrite || !$value || $value == $row_from[$name]) {
                            $to_translate[$name] = $row_from[$name];
                        }
                    }
                }
                if ($to_translate) {
                    $translated = $this->translate($to_translate, $from, $to);
                    $updated_rows = array();
                    foreach ($rows_to as $row) {
                        foreach ($row as $name => $value) {
                            if (isset($translated[$name])) {
                                $row[$name] = $translated[$name];
                                if ($name == 'title' && isset($fields_to_translate['link_rewrite'])) {
                                    $row['link_rewrite'] = Tools::str2url($translated[$name]);
                                }
                                if (isset($to_translate[$name]) && $to_translate[$name] == $value) {
                                    $repeating_data['processed'][] = $name;
                                }
                                $date = date('Y-m-d H:i:s');
                                if (isset($row['date_upd']) && $row['date_upd'] != $date) {
                                    $row['date_upd'] = $date;
                                }
                            }
                            $row[$name] = pSQL($row[$name], true);
                        }
                        $updated_rows[] = '(\''.implode('\', \'', $row).'\')';
                    }
                    if ($updated_rows) {
                        try {
                            $this->db->execute('
                                REPLACE INTO '.pSQL($table_name).' VALUES '.implode(', ', $updated_rows).'
                            ');
                        } catch (Exception $e) {
                            $msg = '"'.$row_from[$identifier_name].' - '.$row_from['title'].'" '.
                            $this->l('Was not updated ').':<br>'.$e->getMessage();
                            $this->throwError($msg);
                        }
                    }
                }
                break;
        }
        if ($repeating_data['processed'] || $overwrite) {
            $r_params['r_fields'] = $repeating_data['processed'];
            $r_params['overwrite_fields'] = $overwrite ? array_keys($fields) : array();
            $this->repeatingTranslations('updateRow', $r_params);
        }
    }

    public function updateLinkRewriteIfRequired(&$obj, $from, $to, $fields, &$repeating_data)
    {
        $lr_source_field = $this->getNameFieldByContentType($obj->at_content_type);
        $lr_field = $obj->at_link_rewrite_field;
        if ($lr_field && isset($fields[$lr_field]) && isset($obj->{$lr_source_field}[$to])) {
            $str2url = Tools::str2url($obj->{$lr_source_field}[$to]);
            if ($str2url && $str2url != '-') { // in some cases str2url may return empty string or '-'
                $obj->{$lr_field}[$to] = $str2url;
                $obj->saving_required = 1;
            }
            if (!$obj->{$lr_field}[$to] && $obj->{$lr_field}[$from]) {
                $obj->{$lr_field}[$to] = $obj->{$lr_field}[$from];
                $obj->saving_required = 1;
            }
            if ($obj->{$lr_field}[$to] == $obj->{$lr_field}[$from]) {
                // can be same if empty value was automatically replaced above
                // or if value is international, like Internet, SEO, etc..
                $repeating_data['processed'][] = $lr_field;
            }
        }
    }

    public function translateProductTags($product_obj, $from, $to, $overwrite)
    {
        $all_tags = Tag::getProductTags($product_obj->id);
        if (isset($all_tags[$from])) {
            // tags don't have language correlations, so we just compare qties of tags
            if ($overwrite || count($all_tags[$to]) < count($all_tags[$from])) {
                $all_tags[$to] = $this->translate($all_tags[$from], $from, $to);
                Tag::deleteTagsForProduct($product_obj->id);
                foreach ($all_tags as $id_lang => $tags) {
                    Tag::addTags($id_lang, $product_obj->id, $tags);
                }
            }
        }
    }

    public function translateProductImages($product_obj, $from, $to, $overwrite, &$repeating_data)
    {
        $repeating_img_translations = array();
        $prefix = $this->extraFields('getPrefix', array('name' => 'image_legends'));
        foreach ($repeating_data['saved'] as $key) {
            if ($this->startsWith($prefix, $key)) {
                $repeating_img_translations[(int)str_replace($prefix, '', $key)] = 1;
            }
        }
        $already_translated = array();
        foreach ($product_obj->getImages($from) as $img) {
            $id_image = $img['id_image'];
            $image = new Image($id_image);
            if (empty($image->id_product)) {
                $image->id_product = $product_obj->id; // fix for some complex multishop scenarios
            }
            $definition = ObjectModel::getDefinition($image);
            $truncate_legend = $definition['fields']['legend']['size'];
            $truncate_options = array('ellipsis' => '', 'exact' => false);
            if ($image->legend[$from] && ($overwrite || !$image->legend[$to] ||
                ($image->legend[$to] == $image->legend[$from] && !isset($repeating_img_translations[$id_image])))) {
                $original = $image->legend[$from];
                if (!isset($already_translated[$original])) { // legends are often repeating in same language
                    $legend = $this->translate($original, $from, $to);
                    $legend = Tools::truncateString($legend, $truncate_legend, $truncate_options);
                    $already_translated[$original] = $legend;
                }
                $image->legend[$to] = $already_translated[$original];
                if ($image->legend[$to] == $image->legend[$from]) {
                    $repeating_data['processed'][] = $prefix.$id_image;
                }
                $this->saveObject($image);
            }
        }
    }

    public function translateProductCustomizationFields($product_obj, $from, $to, $overwrite, &$repeating_data)
    {
        $repeating_cf_translations = array();
        $prefix = $this->extraFields('getPrefix', array('name' => 'customization_fields'));
        foreach ($repeating_data['saved'] as $key) {
            if ($this->startsWith($prefix, $key)) {
                $repeating_cf_translations[(int)str_replace($prefix, '', $key)] = 1;
            }
        }
        // $product_obj->getCustomizationFieldIds() doesn't take in consideraton id_shop
        foreach (array_column($product_obj->getCustomizationFields($from), 'id_customization_field') as $id_cf) {
            $cf_obj = new CustomizationField($id_cf);
            if (!isset($this->cf_truncate)) {
                $definition = ObjectModel::getDefinition($cf_obj);
                $this->cf_truncate = $definition['fields']['name']['size'];
            }
            if ($overwrite || !$cf_obj->name[$to] ||
                ($cf_obj->name[$to] == $cf_obj->name[$from] && !isset($repeating_cf_translations[$id_cf]))) {
                $cf_obj->name[$to] = Tools::truncateString(
                    $this->translate($cf_obj->name[$from], $from, $to),
                    $this->cf_truncate,
                    array('ellipsis' => '', 'exact' => false)
                );
                if ($cf_obj->name[$to] == $cf_obj->name[$from]) {
                    $repeating_data['processed'][] = $prefix.$id_cf;
                }
                $this->saveObject($cf_obj);
            }
        }
    }

    public function getContent()
    {
        if (!function_exists('array_column')) {
            return $this->displayWarning('PHP version is outdated. Please update it to v5.6 or newer');
        }
        $this->defineAPI();
        if (Tools::getValue('reset_api')) {
            $this->api->uninstall();
            $this->api->install();
        }
        if (Tools::getValue('ajax')) {
            $this->ajaxAction();
        }
        $this->context->smarty->assign(array(
            'providers' => $this->getProvidersData(),
            'selected_provider_info' => $this->selected_provider_info,
            'no_provider' => !$this->selected_provider_info['name'],
            'content_types' => $this->getContentTypes(),
            'special_params' => $this->getSpecialParams(),
            'sorting_options' => $this->getSortingOptions(),
            'overwrite_existing' => Configuration::get('AT_OVERWRITE_EXISTING'),
            'module_info' => array(
                'version' => $this->version,
                'changelog' => $this->_path.'Readme.md?v='.$this->version,
                'documentation' => $this->_path.'readme_en.pdf?v='.$this->version,
                'contact' => 'https://addons.prestashop.com/en/contact-us?id_product=19662',
                'modules' => 'https://addons.prestashop.com/en/2_community-developer?contributor=64815',
            ),
        ));
        $this->prepareListVariables(false);
        $ret = '<script type="text/javascript">
            $.extend(at, {txt: {
                saved: \''.$this->escapeApostrophe($this->l('Saved')).'\',
                quick_search: \''.$this->escapeApostrophe($this->l('Quick search...')).'\',
                select_all: \''.$this->escapeApostrophe($this->l('Select all')).'\',
            }});
        </script>';
        $ret .= $this->display(__FILE__, 'views/templates/admin/configure.tpl');
        return $ret;
    }

    public function getProvidersData()
    {
        $this->selected_provider_info = array('name' => '', 'stats' => array('day' => 0, 'month' => 0));
        $provider_objects = $this->api->getAvailableProviders();
        $providers = array();
        $shop_languages = array_keys($this->getAvailableLanguages());
        foreach ($provider_objects as $p_name => $p) {
            $providers[$p_name] = $p->data + $p->saved_data;
            foreach ($p->data['fields'] as $key => $f) {
                $f['label'] = $this->decodeTxt($key);
                if (isset($f['options'])) {
                    foreach ($f['options'] as $k => $opt_name) {
                        $f['options'][$k] = $this->decodeTxt($opt_name);
                    }
                }
                $f['value'] = $p->getCredentials($key);
                if (!$f['value'] && $f['type'] == 'select' && !empty($f['options'])) {
                    $f['value'] = current(array_keys($f['options']));
                }
                $providers[$p_name]['fields'][$key] = $f;
            }
            if ($p_name == 'IBMTranslate') {
                $providers[$p_name]['models_data'] = $p->getModelsData($shop_languages);
            } else {
                $providers[$p_name]['not_supported_languages'] = $p->getNotSupportedLanguages($shop_languages);
            }
            if (isset($p->free_limit)) {
                $info = array();
                if (!empty($p->free_limit['d'])) {
                    $info[] = sprintf($this->l('%s chars/day'), $p->free_limit['d']);
                }
                if (!empty($p->free_limit['m'])) {
                    $info[] = sprintf($this->l('%s chars/month'), $p->free_limit['m']);
                }
                $providers[$p_name]['additional_info'] = $this->l('Free package available').': '.implode(' | ', $info);
            } elseif (isset($p->yearly_trial)) {
                $providers[$p_name]['additional_info'] = sprintf(
                    $this->l('Free trial available: %s credit for 12 months'),
                    $p->yearly_trial
                );
            }
            // $p->saved_data['selected'] = 0; // DEBUG
            if ($p->saved_data['selected']) {
                $this->selected_provider_info = $providers[$p_name];
                $this->selected_provider_info['stats'] = $this->api->getSimplifiedStatsData($p);
            }
        }
        $this->sortProviders($providers);
        return $providers;
    }

    public function sortProviders(&$providers)
    {
        $top = array('GoogleTranslate');
        $sorted_providers = array();
        foreach ($top as $key) {
            if (isset($providers[$key])) {
                $sorted_providers[$key] = $providers[$key];
                unset($providers[$key]);
            }
        }
        $sorted_providers = array_merge($sorted_providers, $providers);
        $providers = $sorted_providers;
    }

    public function getSortingOptions()
    {
        $sorting_options = array(
            'id' => array(
                'name' => 'ID',
            ),
            'name' => array(
                'name' => $this->l('Name'),
            ),
            'date_add' => array(
                'name' => $this->l('Date added'),
                'class' => 'product category cms_category'
            ),
            'reference' => array(
                'name' => $this->l('Reference'),
                'class' => 'product'
            ),
            'active' => array(
                'name' => $this->l('Active status'),
                'class' => 'product category cms cms_category manufacturer supplier module'
            ),
        );
        return $sorting_options;
    }

    public function getSpecialParams()
    {
        $params = array(
            'product' => $this->getProductFilters(),
        );
        return $params;
    }

    public function getProductFilters()
    {
        $filters = array(
            'id_category' => $this->getCategoryOptions(),
            'id_manufacturer' => $this->getManufacturerOptions(),
        );
        return $filters;
    }

    public function getContentTypes()
    {
        $content_types = array(
            'product' => $this->l('Products'),
            'attribute_group' => $this->l('Attribute groups'),
            'attribute' => $this->l('Attributes'),
            'feature' => $this->l('Features'),
            'feature_value' => $this->l('Feature values'),
            'category' => $this->l('Categories'),
            'cms' => $this->l('CMS Pages'),
            'cms_category' => $this->l('CMS Categories'),
            'manufacturer' => $this->l('Manufacturers'),
            'supplier' => $this->l('Suppliers'),
            'meta' => $this->l('SEO and URLs'),
            'attachment' => $this->l('Attachments'),
        );
        if (Module::isInstalled('ph_simpleblog')) {
            $content_types['simpleblog_post'] = sprintf($this->l('%s posts'), 'Simple blog');
            $content_types['simpleblog_category'] = sprintf($this->l('%s categories'), 'Simple blog');
        }
        if (Module::isInstalled('amazzingblog')) {
            $content_types['a_blog_post'] = sprintf($this->l('%s posts'), 'Amazzing blog');
            $content_types['a_blog_category'] = sprintf($this->l('%s categories'), 'Amazzing blog');
        }
        if (Module::isInstalled('advanceblog')) {
            $content_types['fme_blog_posts'] = sprintf($this->l('%s posts'), 'Advance blog');
            $content_types['fme_blog_categories'] = sprintf($this->l('%s categories'), 'Advance blog');
        }
        if (Module::isInstalled('ybc_blog')) {
            $content_types['ybc_blog_post'] = sprintf($this->l('%s posts'), 'Blog').' (ETS-Soft)';
            $content_types['ybc_blog_category'] = sprintf($this->l('%s categories'), 'Blog').' (ETS-Soft)';
            $content_types['ybc_blog_gallery'] = 'Blog photo galleries (ETS-Soft)';
            $content_types['ybc_blog_slide'] = 'Blog sliders (ETS-Soft)';
            $content_types['ybc_blog_employee'] = 'Blog authors (ETS-Soft)';
        }
        if (Module::isInstalled('editorial')) {
            $content_types['editorial'] = 'Home text editor module';
        }
        return $content_types;
    }

    public function getAvailableLanguages($only_active = true, $key = 'iso_code', $value = 'name', $force_query = false)
    {
        if (!isset($this->available_languages) || $force_query) {
            $this->available_languages = array_column(Language::getLanguages($only_active), $value, $key);
        }
        return $this->available_languages;
    }

    public function prepareListVariables($include_items = true)
    {
        $languages = $this->getAvailableLanguages();
        $current_ct = Tools::getValue('at_ct', 'product');
        if (!$lang_from = Tools::getValue('at_from')) {
            if (!$lang_from = Configuration::get('AT_LANG_FROM')) {
                $lang_from = $this->getDefaultLangISO();
            }
        }
        $identifier = $this->getIdentifierFieldByContentType($current_ct);
        $fields_list = array(
            'name' => $this->l('Name'),
        );
        $order = array(
            'by' => Tools::getValue('order_by', 'id'),
            'way' => Tools::getValue('order_way', 'DESC'),
        );
        $pagination = array(
            'p' => Tools::getValue('p', 1),
            'npp' => Tools::getValue('npp', 20),
        );
        $vars = array(
            'identifier' => $identifier,
            'current_ct' => $current_ct,
            'languages' => $languages,
            'lang_from' => $lang_from,
            'fields_list' => $fields_list,
            'order' => $order,
            'pagination' => $pagination,
        );
        if ($include_items) {
            $vars['items'] = $this->getItems($lang_from, $current_ct, $pagination, $order);
            $vars['total'] = $this->getItems($lang_from, $current_ct, $pagination, $order, true);
            $vars['stats_not_supported'] = array();
            $langs_to = Tools::getValue('at_to', array());
            foreach ($langs_to as $lang_to) {
                if (!$this->api->getSelectedProvider()->supportsModel($lang_from, $lang_to)) {
                    $vars['stats_not_supported'][$lang_to] = $lang_to;
                }
            }
            $ids = array_column($vars['items'], $identifier);
            $stats = $this->getTranslationsStats($ids, $current_ct, $identifier, $lang_from, $langs_to);
            foreach ($vars['items'] as &$i) {
                $id_item = $i[$identifier];
                $i['stats'] = isset($stats[$id_item]) ? $stats[$id_item] : array();
            }
        }
        $this->context->smarty->assign($vars);
    }

    public function getCategoryOptions()
    {
        $categories = $this->db->executeS('
            SELECT c.id_category, c.id_parent, cl.name
            FROM '._DB_PREFIX_.'category c
            '.Shop::addSqlAssociation('category', 'c').'
            LEFT JOIN '._DB_PREFIX_.'category_lang cl
                ON c.id_category = cl.id_category
                AND cl.id_shop = '.(int)$this->context->shop->id.'
                AND cl.id_lang = '.(int)$this->context->language->id.'
        ');
        $structured_categories = array();
        foreach ($categories as $c) {
            $structured_categories[$c['id_parent']][$c['id_category']] = $c;
        }
        $max_digits = Tools::strlen($this->db->getValue('SELECT MAX(id_category) FROM '._DB_PREFIX_.'category'));
        $id_root = $this->context->shop->getCategory();
        $root_parent = $this->db->getValue('
            SELECT id_parent FROM '._DB_PREFIX_.'category WHERE id_category = '.(int)$id_root.'
        ');
        $options = array('0' => $this->l('Select category'));
        $options += $this->getCatLevelOptions($root_parent, $structured_categories, $max_digits);
        return $options;
    }

    public function getCatLevelOptions($id_parent, $structured_categories, $max_id_digits, $prefix = '')
    {
        $options = array();
        $id_parent = (int)$id_parent;
        if (isset($structured_categories[$id_parent])) {
            $categories = $structured_categories[$id_parent];
            $children_prefix = $prefix.'-';
            foreach ($categories as $c) {
                $id = $c['id_category'];
                $options[$id] = $prefix.' '.$c['name'];
                $options += $this->getCatLevelOptions($id, $structured_categories, $max_id_digits, $children_prefix);
            }
        }
        return $options;
    }

    public function getManufacturerOptions()
    {
        $options = array(0 => $this->l('Select manufacturer'));
        foreach ($this->db->executeS('SELECT * FROM '._DB_PREFIX_.'manufacturer') as $row) {
            $options[$row['id_manufacturer']] = $row['name'];
        }
        return $options;
    }

    public function emulateEditorialItems($return_count)
    {
        $id = $this->db->getValue('
           SELECT id_editorial FROM '._DB_PREFIX_.'editorial WHERE id_shop = '.(int)$this->context->shop->id.'
        ');
        // keep same format as for other resourses
        return $return_count ? 1 : array(array('id_editorial' => $id, 'name' => $this->l('Multilingual data')));
    }

    public function getIdentifierFieldByContentType($content_type)
    {
        $custom_identifiers = array(
            'a_blog_post' => 'id_post',
            'a_blog_category' => 'id_category',
            'ybc_blog_post' => 'id_post',
            'ybc_blog_category' => 'id_category',
            'ybc_blog_gallery' => 'id_gallery',
            'ybc_blog_slide' => 'id_slide',
            'ybc_blog_employee' => 'id_employee_post',
            'fme_blog_posts' => 'id_post',
            'fme_blog_categories' => 'id_blogcategory',
        );
        if (isset($custom_identifiers[$content_type])) {
            $identifier = $custom_identifiers[$content_type];
        } elseif (in_array($content_type, array('theme', 'module'))) {
            $identifier = 'name';
        } else {
            $identifier = 'id_'.$content_type;
        }
        return $identifier;
    }

    public function getNameFieldByContentType($content_type)
    {
        $custom_names = array(
            'cms' => 'meta_title',
            'theme' => 'directory',
            'feature_value' => 'value',
            'meta' => 'title',
            'a_blog_post' => 'title',
            'a_blog_category' => 'title',
            'simpleblog_post' => 'title',
            'ybc_blog_post' => 'title',
            'ybc_blog_category' => 'title',
            'ybc_blog_gallery' => 'title',
            'ybc_blog_slide' => 'caption',
            'fme_blog_posts' => 'post_title',
            'fme_blog_categories' => 'category_title',
        );
        return isset($custom_names[$content_type]) ? $custom_names[$content_type] : 'name';
    }

    public function getItems($lang_iso, $content_type, $pagination, $order, $return_count = false)
    {
        if ($content_type == 'editorial') {
            return $this->emulateEditorialItems($return_count);
        }
        $id_lang = Language::getIdByIso($lang_iso);
        $imploded_shop_ids = implode(', ', $this->shop_ids);
        $identifier = $this->getIdentifierFieldByContentType($content_type);
        $name_field = $this->getNameFieldByContentType($content_type);

        if ($order['by'] == 'id') {
            $order['by'] = $identifier;
        } elseif ($order['by'] == 'name') {
            $order['by'] = $name_field;
        } else {
            $select_order_by_column = 1;
        }
        if ($this->tableExists(_DB_PREFIX_.$content_type.'_shop') &&
            $this->columnExists(_DB_PREFIX_.$content_type.'_shop', $order['by'])) {
            $order['by'] = 'shop.'.$order['by'];
        }
        $query = new DbQuery();
        if ($return_count) {
            $query->select('COUNT(DISTINCT main.'.$identifier.')');
        } else {
            $p = $pagination['p'];
            $npp = $pagination['npp'];
            $offset = ($p - 1) * $npp;
            $query->select('main.'.$identifier.', '.$name_field.' AS name');
            if ($content_type == 'meta') {
                $query->select('main.page AS identifier_extension');
            }
            if ($content_type == 'feature_value') {
                $query->select('main.custom AS is_custom_value');
            }
            if (isset($select_order_by_column)) {
                $query->select($order['by']);
            }
            $query->orderBy(pSQL($order['by']).' '.pSQL($order['way']));
            $query->limit((int)$npp, (int)$offset);
            $query->groupBy('main.'.$identifier);
        }
        $query->from($content_type, 'main');
        if ($this->tableExists(_DB_PREFIX_.$content_type.'_shop')) {
            $on = 'shop.'.$identifier.' = main.'.$identifier;
            if ($content_type == 'fme_blog_categories') {
                $on = 'shop.id_category = main.'.$identifier;
            }
            $on .= ' AND shop.id_shop IN ('.pSQL($imploded_shop_ids).')';
            $query->innerJoin($content_type.'_shop', 'shop', $on);
        }
        if (!in_array($content_type, array('module', 'theme'))) {
            $query->innerJoin(
                $content_type.'_lang',
                'lang',
                'lang.'.$identifier.' = main.'.$identifier.'
                AND lang.id_lang = '.(int)$id_lang.'
                '.($this->columnExists(_DB_PREFIX_.$content_type.'_lang', 'id_shop') ?
                'AND lang.id_shop IN ('.pSQL($imploded_shop_ids).')' : '')
            );

            if ($content_type == 'product') {
                foreach (array_keys($this->getProductFilters()) as $key) {
                    if ($imploded_ids = $this->formatIDs(Tools::getValue($key), true)) {
                        if ($key == 'id_category') {
                            $query->leftJoin('category_product', 'cp', 'cp.id_product = main.id_product');
                            $query->where('cp.id_category IN ('.pSQL($imploded_ids).')');
                        } else {
                            $query->where('main.'.pSQL($key).' IN ('.pSQL($imploded_ids).')');
                        }
                    }
                }
            }
        }
        return $return_count ? $this->db->getValue($query) : $this->db->executeS($query);
    }

    public function getTranslationsStats($ids, $content_type, $identifier, $from_iso, $to_isos)
    {
        $sorted_stats = $original_data = $additional_original_data = array();
        if (!$impl_ids = $this->formatIDs($ids, true)) {
            return $sorted_stats;
        }
        $active_languages = $this->getAvailableLanguages(true, 'id_lang', 'iso_code', true);
        $active_languages_flipped = array_flip($active_languages);
        $lang_from = Language::getIdByIso($from_iso);
        $lang_ids = array($lang_from);
        foreach ($to_isos as $iso_code) {
            if (isset($active_languages_flipped[$iso_code])) {
                $lang_ids[] = $active_languages_flipped[$iso_code];
            }
        }
        $impl_langs = $this->formatIDs($lang_ids, true);
        $t_name = $content_type.'_lang';
        $query = new DbQuery();
        $query->from($t_name, 'l');
        $query->where('l.'.pSQL($identifier).' IN ('.pSQL($impl_ids).')');
        $query->where('l.id_lang IN ('.pSQL($impl_langs).')');
        if ($this->columnExists(_DB_PREFIX_.$t_name, 'id_shop')) {
            $query->where('l.id_shop IN ('.pSQL(implode(', ', $this->shop_ids)).')');
            $query->groupBy('CONCAT(l.'.pSQL($identifier).', \'-\', l.id_lang)');
            $query->orderBy('l.id_shop = '.(int)$this->context->shop->id.' DESC');
        }
        $query->orderBy('l.id_lang = '.(int)$lang_from.' DESC, l.id_lang ASC');
        $raw_lang_data = $this->db->executeS($query);
        $translatable_fields = $this->getTranslatableFields($content_type);
        if ($content_type == 'product') {
            $additional_multilang = $this->getAdditionalProductMultilingualData($impl_ids, $impl_langs);
        }

        $r_params = array('id' => $ids, 'type' => $content_type, 'l_ids' => $lang_ids);
        $repeating_translations = $this->repeatingTranslations('get', $r_params);

        foreach ($raw_lang_data as $d) {
            $id_item = $d[$identifier];
            $id_lang = $d['id_lang'];
            $lang_pair = $lang_from.'-'.$id_lang;
            if ($id_lang == $lang_from) {
                foreach ($translatable_fields as $field_name) {
                    if (!empty($d[$field_name])) { // consider only non-empty fields
                        $original_data[$id_item][$field_name] = $d[$field_name];
                    } elseif (!isset($d[$field_name]) &&
                        isset($additional_multilang[$id_item][$field_name][$id_lang])) {
                        $additional_original_data[$id_item][$field_name] =
                        $additional_multilang[$id_item][$field_name][$id_lang];
                    }
                }
            } elseif (isset($original_data[$id_item])) { // $lang_from goes first, so original data is complete
                $to_translate_num = count($original_data[$id_item]);
                if (isset($additional_original_data[$id_item])) {
                    foreach ($additional_original_data[$id_item] as $field_name => $additional_values) {
                        foreach ($additional_values as $val) {
                            $to_translate_num++;
                        }
                    }
                }
                $translated_num = 0;
                foreach ($original_data[$id_item] as $field_name => $orig_value) {
                    if ($orig_value && !empty($d[$field_name]) && ($d[$field_name] != $orig_value ||
                        isset($repeating_translations[$id_item][$lang_pair][$field_name]))) {
                        $translated_num++;
                    }
                }
                if (isset($additional_original_data[$id_item]) && isset($additional_multilang[$id_item])) {
                    foreach ($additional_original_data[$id_item] as $field_name => $additional_values_orig) {
                        if (!isset($additional_multilang[$id_item][$field_name][$id_lang])) {
                            continue;
                        }
                        if ($field_name == 'tags') {
                            // tags don't have exact language correlation, so percentage is defined by number of tags
                            $tags_num = count($additional_multilang[$id_item][$field_name][$id_lang]);
                            $tags_num_orig = count($additional_values_orig);
                            $translated_num += $tags_num < $tags_num_orig ? $tags_num : $tags_num_orig;
                        } elseif (in_array($field_name, array('image_legends', 'customization_fields'))) {
                            foreach ($additional_values_orig as $key => $val) {
                                $prefix = $this->extraFields('getPrefix', array('name' => $field_name));
                                if (!empty($additional_multilang[$id_item][$field_name][$id_lang][$key]) &&
                                    ($additional_multilang[$id_item][$field_name][$id_lang][$key] != $val ||
                                    isset($repeating_translations[$id_item][$lang_pair][$prefix.$key]))) {
                                    $translated_num++;
                                }
                            }
                        }
                    }
                }
                // if ($id_item == 20) {
                //     d([
                //        $additional_original_data[$id_item],
                //        array(
                //            'tags' => $additional_multilang[$id_item]['tags'][$id_lang],
                //            'image_legends' => $additional_multilang[$id_item]['image_legends'][$id_lang],
                //        ),
                //         $original_data[$id_item],
                //         $d,
                //         $to_translate_num,
                //         $translated_num,
                //         $repeating_translations,
                //     ]);
                // }
                $percentage = !$to_translate_num ? 100 : round(($translated_num/$to_translate_num) * 100);
                $sorted_stats[$id_item][$active_languages[$id_lang]] = $percentage;
            }
        }
        return $sorted_stats;
    }

    public function extraFields($action, $data = array())
    {
        $ret = '';
        switch ($action) {
            case 'getAll':
                $ret = array(
                    'tags' => 'tags',
                    'image_legends' => 'image_',
                    'customization_fields' => 'customization_field_',
                );
                break;
            case 'getPrefix':
                $all = $this->extraFields('getAll');
                if (isset($all[$data['name']])) {
                    $ret = $all[$data['name']];
                }
                break;
        }
        return $ret;
    }

    public function repeatingTranslations($action, $params = array())
    {
        $ret = array();
        $t_name = 'at_repeating_translations';
        switch ($action) {
            case 'get':
                foreach ($this->repeatingTranslations('getRows', $params) as $r) {
                    $ret[$r['id']][$r['lang_1'].'-'.$r['lang_2']] = $ret[$r['id']][$r['lang_2'].'-'.$r['lang_1']] =
                    array_fill_keys(Tools::jsonDecode($r['r_fields'], true), 1);
                }
                break;
            case 'updateRow':
                $row = $this->repeatingTranslations('getSavedRow', $params);
                $row['r_fields'] = Tools::jsonDecode($row['r_fields'], true);
                if (!empty($params['overwrite_fields'])) {
                    // remove saved repeating fields if they were overriden
                    // if they are still repeating after overriding, they will be included again
                    $row['r_fields'] = array_diff($row['r_fields'], $params['overwrite_fields']);
                    if ($row['r_fields'] && $params['type'] == 'product') {
                        foreach ($this->extraFields('getAll') as $key => $prefix) {
                            if (in_array($key, $params['overwrite_fields'])) {
                                foreach ($row['r_fields'] as $k => $f_name) {
                                    if ($this->startsWith($prefix, $f_name)) {
                                        unset($row['r_fields'][$k]);
                                    }
                                }
                            }
                        }
                    }
                }
                $ret = true;
                if ($row['r_fields'] = array_unique(array_merge($params['r_fields'], $row['r_fields']))) {
                    $row['r_fields'] = Tools::jsonEncode($row['r_fields']);
                    $ret = $this->db->execute('
                        REPLACE INTO '._DB_PREFIX_.pSQL($t_name).'
                        VALUES (\''.implode('\', \'', array_map('pSQL', $row)).'\')
                    ');
                } elseif ($row['auto_id']) {
                    $ret = $this->db->execute('
                        DELETE FROM '._DB_PREFIX_.pSQL($t_name).'
                        WHERE auto_id = '.(int)$row['auto_id'].'
                    ');
                }
                break;
            case 'getRows':
                $query = new DbQuery();
                $query->select('*')->from($t_name);
                if (isset($params['type'])) {
                    $query->where('type = \''.pSQL($params['type']).'\'');
                }
                if (isset($params['id'])) {
                    $query->where('id IN ('.pSQL($this->formatIDs($params['id'], true)).')');
                }
                if (isset($params['l_ids'])) {
                    $l_ids = $this->formatIDs($params['l_ids'], true);
                    $query->where('lang_1 IN ('.$l_ids.') AND lang_2 IN ('.$l_ids.')');
                }
                $ret = $this->db->executeS($query);
                break;
            case 'getSavedRow':
                if (!$ret = current($this->repeatingTranslations('getRows', $params))) {
                    $ret = array(
                        'auto_id' => '',
                        'type' => $params['type'],
                        'id' => $params['id'],
                        'lang_1' => $params['l_ids'][0],
                        'lang_2' => $params['l_ids'][1],
                        'r_fields' => '[]'
                    );
                }
                break;
            case 'install':
                $ret = $this->db->execute('
                    CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.pSQL($t_name).' (
                    auto_id int(10) unsigned NOT NULL AUTO_INCREMENT,
                    type varchar(128) NOT NULL,
                    id int(10) unsigned NOT NULL,
                    lang_1 int(10) unsigned NOT NULL,
                    lang_2 int(10) unsigned NOT NULL,
                    r_fields text NOT NULL,
                    PRIMARY KEY (auto_id),
                    KEY type (type), KEY id (id), KEY lang_1 (lang_1), KEY lang_2 (lang_2)
                    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8
                ');
                break;
            case 'uninstall':
                $ret = $this->db->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.pSQL($t_name));
                break;
        }
        return $ret;
    }

    public function getAdditionalProductMultilingualData($impl_product_ids, $impl_lang_ids)
    {
        $data = array();
        $tags = $this->db->executeS('
            SELECT pt.id_product, t.id_lang, t.name
            FROM '._DB_PREFIX_.'tag t
            INNER JOIN '._DB_PREFIX_.'product_tag pt
                ON t.id_tag = pt.id_tag AND pt.id_product IN ('.pSQL($impl_product_ids).')
            WHERE t.id_lang IN ('.pSQL($impl_lang_ids).')
        ');
        foreach ($tags as $t) {
            // keys can be 0,1,2 because stats will be defined by comparing numbers of tags
            $data[$t['id_product']]['tags'][$t['id_lang']][] = $t['name'];
        }
        $images = $this->db->executeS('
            SELECT i.id_image, i.id_product, il.id_lang, legend
            FROM '._DB_PREFIX_.'image i
            INNER JOIN '._DB_PREFIX_.'image_lang il
                ON il.id_image = i.id_image AND il.id_lang IN ('.pSQL($impl_lang_ids).')
                AND il.legend <> \'\'
            WHERE i.id_product IN ('.pSQL($impl_product_ids).')
        ');
        foreach ($images as $i) {
            $data[$i['id_product']]['image_legends'][$i['id_lang']][$i['id_image']] = $i['legend'];
        }
        $customization_fields = $this->db->executeS('
            SELECT cf.id_customization_field, cf.id_product, cfl.id_lang, cfl.name
            FROM '._DB_PREFIX_.'customization_field cf
            INNER JOIN '._DB_PREFIX_.'customization_field_lang cfl
                ON cfl.id_customization_field = cf.id_customization_field
                AND cfl.id_lang IN ('.pSQL($impl_lang_ids).') AND cfl.name <> \'\'
            WHERE cf.id_product IN ('.pSQL($impl_product_ids).')
        ');
        foreach ($customization_fields as $f) {
            $data[$f['id_product']]['customization_fields'][$f['id_lang']][$f['id_customization_field']] = $f['name'];
        }
        return $data;
    }

    public function getTranslatableFields($content_type, $sort = false)
    {
        $translatable_fields = array();
        if (Tools::substr($content_type, 0, 6) == 'a_blog') {
            $translatable_fields = $this->getAmazzingBlogTranslatableFields($content_type);
        } elseif ($obj = $this->createObject($content_type)) {
            $translatable_fields = array_keys($this->getStandardTranslatableFields($obj));
        }
        if ($sort) {
            sort($translatable_fields);
        }
        if ($content_type == 'product') {
            $translatable_fields = array_merge($translatable_fields, array_keys($this->extraFields('getAll')));
        }
        return $translatable_fields;
    }

    public function formatIDs($ids, $return_string = false)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $ids = array_map('intval', $ids);
        $ids = array_combine($ids, $ids);
        unset($ids[0]);
        return $return_string ? implode(',', $ids) : $ids;
    }


    public function columnExists($table_name, $column_name, $prefix_included = true)
    {
        $table_name = $prefix_included ? $table_name : _DB_PREFIX_.$table_name;
        return (bool)$this->db->executeS('
            SHOW COLUMNS FROM '.pSQL($table_name).' LIKE \''.pSQL($column_name).'\'
        ');
    }

    public function tableExists($table_name, $prefix_included = true)
    {
        $table_name = $prefix_included ? $table_name : _DB_PREFIX_.$table_name;
        return (bool)$this->db->executeS('SHOW TABLES LIKE \''.pSQL($table_name).'\'');
    }

    public function createObject($content_type, $identifier = 0)
    {
        $class_name = Tools::ucfirst($content_type);
        $special_names = array(
            'attribute_group' => 'AttributeGroup',
            'feature_value' => 'FeatureValue',
            'cms' => 'CMS',
            'cms_category' => 'CMSCategory',
            'simpleblog_post' => 'SimpleBlogPost',
            'simpleblog_category' => 'SimpleBlogCategory',
            'ybc_blog_post' => 'Ybc_blog_post_class',
            'ybc_blog_category' => 'Ybc_blog_category_class',
            'ybc_blog_gallery' => 'Ybc_blog_gallery_class',
            'ybc_blog_slide' => 'Ybc_blog_slide_class',
            'ybc_blog_employee' => 'Ybc_blog_post_employee_class',
            'fme_blog_posts' => 'BlogPosts',
            'fme_blog_categories' => 'BlogCategories',
        );
        if (!empty($special_names[$content_type])) {
            $class_name = $special_names[$content_type];
        }
        if ($content_type == 'editorial' && file_exists(_PS_MODULE_DIR_.'editorial/EditorialClass.php')) {
            require_once(_PS_MODULE_DIR_.'editorial/EditorialClass.php');
            $class_name = 'EditorialClass';
        }
        if (!class_exists($class_name)) {
            $this->throwError(sprintf($this->l('Class %s is not available'), $class_name));
        }
        $obj = new $class_name($identifier);
        if ($content_type == 'category' && empty($obj->groupBox) && method_exists($obj, 'getGroups')) {
            $obj->groupBox = $obj->getGroups();
        }
        if ($identifier && !Validate::isLoadedObject($obj)) {
            $this->throwError(sprintf($this->l('%s could not be loaded'), $class_name));
        }
        $obj->at_content_type = $content_type;
        $obj->at_link_rewrite_field = $this->getLinkRewriteField($obj);
        return $obj;
    }

    public function getLinkRewriteField($obj)
    {
        $link_rewrite_field = '';
        $possible_fields = array(
            'link_rewrite',
            'url_rewrite',
            'url_alias', // ybc_blog
            'link_rewrite_cat' // advanceblog
        );
        foreach ($possible_fields as $f_name) {
            if (isset($obj->$f_name)) {
                $link_rewrite_field = $f_name;
                break;
            }
        }
        return $link_rewrite_field;
    }

    public function getStandardTranslatableFields($obj, $exclude_link_rewrite = true)
    {
        $definition = ObjectModel::getDefinition($obj);
        $fields = array();
        $skip = array_fill_keys(array(
            'video_code', // simple_blog
            'external_url', // simple_blog
            'image', // ybc_blog
            'thumb', // ybc_blog
            'url', // ybc_blog sliders
        ), 1);
        if ($exclude_link_rewrite) {
            $skip[$obj->at_link_rewrite_field] = 1;
        }
        foreach ($definition['fields'] as $field_name => $data) {
            if (!empty($data['lang']) && !isset($skip[$field_name])) {
                $fields[$field_name] = !empty($data['size']) ? $data['size'] : 0;
            }
        }
        return $fields;
    }

    public function saveObject($obj)
    {
        try {
            $obj->save();
        } catch (Exception $e) {
            if (get_class($obj) == 'Image' && !empty($obj->id_product)) {
                $identifier = '[ID='.$obj->id_product.'], image '.$obj->id.':';
            } else {
                $identifier = '[ID='.$obj->id.']';
            }
            $msg = $identifier.' '.$e->getMessage();
            $this->throwError($msg);
        }
        return true;
    }

    public function translate($content, $from, $to)
    {
        return $this->api->translate($content, Language::getIsoById($from), Language::getIsoById($to));
    }

    /*
    * retro-compatibility
    */
    public function displayWarning($msg)
    {
        if (!is_array($msg)) {
            $msg = array($msg);
        }
        $html = '<div class="alert alert-warning">';
        $html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        $html .= '<ul class="list-unstyled">';
        foreach ($msg as $m) {
            $html .= '<li>'.$m.'</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        return $html;
    }

    public function parseStr($str)
    {
        $params = array();
        parse_str(str_replace('&amp;', '&', $str), $params);
        return $params;
    }

    public function getAmazzingBlogTranslatableFields($type)
    {
        $fields = array();
        $table_name = _DB_PREFIX_.$type.'_lang';
        if ($this->db->executeS('SHOW TABLES LIKE \''.pSQL($table_name).'\'')) {
            foreach ($this->db->executeS('SHOW COLUMNS FROM '.pSQL($table_name)) as $c) {
                $c_name = $c['Field'];
                if (Tools::substr($c_name, 0, 3) != 'id_' && Tools::substr($c_name, 0, 5) != 'date_') {
                    $fields[] = $c_name;
                }
            };
        }
        return $fields;
    }

    public function decodeTxt($code)
    {
        $codes = array(
            'api_key' => $this->l('API key'),
            'url' => $this->l('API URL'),
            'plan' => $this->l('Subscription plan'),
            'deepl_free' => $this->l('DeepL API Free (max. 500 000 chars/month)'),
            'deepl_pro' => $this->l('DeepL API Pro (Usage-based pricing)'),
            'no_curl' => $this->l('cURL extension is required for automatic translations'),
            'error' => $this->l('Error'),
            'unknown_error' => $this->l('Unknown error'),
        );
        return isset($codes[$code]) ? $codes[$code] : $code;
    }

    public function startsWith($prefix, $text)
    {
        return Tools::substr($text, 0, Tools::strlen($prefix)) == $prefix;
    }

    public function throwError($errors)
    {
        if (!is_array($errors)) {
            $errors = array($errors);
        }
        foreach ($errors as &$error_txt) {
            $error_txt = $this->decodeTxt($error_txt);
        }
        $error_html = $this->displayError(implode('<br>', $errors));
        $ret = array(
            'hasError' => true,
            'errors' => utf8_encode($error_html),
        );
        exit(Tools::jsonEncode($ret));
    }
}
