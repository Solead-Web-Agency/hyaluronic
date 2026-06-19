<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

class PBPAdminConfigTabsController extends PBPControllerCore
{
    protected $sibling;

    public function __construct(&$sibling = null)
    {
        parent::__construct($sibling);
        if ($sibling !== null) {
            $this->sibling = &$sibling;
        }
    }

    /**
     * Render the General settings form
     */
    public function renderList()
    {
        $tabs_collection = (new PBPTabLangModel())->loadAll($this->id_lang_default);

        Context::getContext()->smarty->assign(array(
            'tabs' => $tabs_collection
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/config/tabs.tpl');
    }

    /**
     * Render edit tab form
     */
    public function renderEditForm()
    {
        $languages = Language::getLanguages();
        $title_localised_array = array();
        $id_tab = (int)Tools::getValue('id_tab');

        if ($id_tab > 0) {
            foreach ($languages as $language) {
                $pbp_tab_model = new PBPTabLangModel();
                $pbp_tab_model->load((int)Tools::getValue('id_tab'), $language['id_lang']);
                $title_localised_array[$language['id_lang']] = $pbp_tab_model->title;
            }
        } else {
            foreach ($languages as $language) {
                $title_localised_array[$language['id_lang']] = '';
            }
        }

        Context::getContext()->smarty->assign(array(
            'languages' => $languages,
            'id_lang_default' => Configuration::get('PS_LANG_DEFAULT', null, Context::getContext()->shop->id_shop_group, Context::getContext()->shop->id),
            'id_tab' => Tools::getValue('id_tab'),
            'tab_bundle_layout' => PBPConfigHelper::getTabBundleLayout($id_tab),
            'title_localised_array' => $title_localised_array
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/config/tab_edit.tpl');
    }

    /**
     * Save new Tab
     */
    public function processEditForm()
    {
        $languages = Language::getLanguages();
        $ajax_response = new PBPAjaxResponse();
        $layout = (int)Tools::getValue('layout');

        if (Tools::getValue('title_' . $this->id_lang_default) == '') {
            $ajax_response->addMessage('Missing tab name', '#title_' . $this->id_lang_default, true);
        }

        if ($ajax_response->hasErrors()) {
            $ajax_response->ajaxDie();
        }

        if (Tools::getValue('id_tab') == 0) {
            $id_tab = PBPTabLangModel::getNextTabId();
            foreach ($languages as $language) {
                $tab_lang_model = new PBPTabLangModel();
                $tab_lang_model->id_tab = $id_tab;
                $tab_lang_model->id_lang = $language['id_lang'];

                if (Tools::getValue('title_' . (int)$language['id_lang']) != '') {
                    $tab_lang_model->title = Tools::getValue('title_' . (int)$language['id_lang']);
                } else {
                    $tab_lang_model->title = Tools::getValue('title_' . (int)$this->id_lang_default);
                }
                $tab_lang_model->add();
            }
        } else {
            $id_tab = (int)Tools::getValue('id_tab');
            if ($id_tab == 0) {
                return false;
            }

            foreach ($languages as $language) {
                $tab_lang_model = new PBPTabLangModel();
                $tab_lang_model->id_tab = $id_tab;
                $tab_lang_model->id_lang = $language['id_lang'];

                if (Tools::getValue('title_' . (int)$language['id_lang']) != '') {
                    $tab_lang_model->title = Tools::getValue('title_' . (int)$language['id_lang']);
                } else {
                    $tab_lang_model->title = Tools::getValue('title_' . (int)$this->id_lang_default);
                }
                $tab_lang_model->updateTitle();
            }
        }
        PBPConfigHelper::setTabBundleLayout($layout, $id_tab);
        return Tools::jsonEncode(array());
    }

    /**
     * Delete a tab and all related data
     */
    public function processDelete()
    {
        if ((int)Tools::getValue('id_tab') == 0) {
            return false;
        }
        PBPTabLangModel::deleteTab(Tools::getValue('id_tab'));
    }

    /**
     * Save general settings form
     */
    public function processForm()
    {
    }

    public function route()
    {
        switch (Tools::getValue('action')) {
            case 'processform':
                die($this->processForm());

            case 'rendereditform':
                die($this->renderEditForm());

            case 'processeditform':
                die($this->processEditForm());

            case 'processdelete':
                die($this->processDelete());

            default:
                die($this->renderList());
        }
    }
}
