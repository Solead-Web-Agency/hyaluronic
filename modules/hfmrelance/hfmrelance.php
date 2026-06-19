<?php
if (!defined('_PS_VERSION_')) exit;

class Hfmrelance extends Module
{
    public function __construct()
    {
        $this->name = 'hfmrelance';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
        $this->author = 'HFM';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = 'Relance Panier Abandonné';
        $this->description = 'Relances panier multicanal (remplace Carts Guru) + dashboard.';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install() && $this->installTab();
    }

    public function uninstall()
    {
        return $this->uninstallTab() && parent::uninstall();
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminHfmRelance';
        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentCustomer');
        if (!$tab->id_parent) $tab->id_parent = (int)Tab::getIdFromClassName('SELL');
        $tab->icon = 'mail_outline';
        foreach (Language::getLanguages(false) as $l) {
            $tab->name[(int)$l['id_lang']] = 'Relance Panier';
        }
        return $tab->add();
    }

    private function uninstallTab()
    {
        $id = (int)Tab::getIdFromClassName('AdminHfmRelance');
        if ($id) { $tab = new Tab($id); return $tab->delete(); }
        return true;
    }

    // raccourci : lien direct vers le contrôleur depuis la page module
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminHfmRelance'));
    }
}
