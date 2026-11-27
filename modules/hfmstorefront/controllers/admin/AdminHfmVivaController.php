<?php
/**
 * Action « Rembourser via Viva » du bloc Viva de la fiche commande (hfmstorefront.php,
 * vivaOrderCard) et « Rembourser via Amazon Pay » du bloc Amazon (amazonOrderCard, 18/11).
 * Onglet caché du back-office : n'est appelé que par ces formulaires.
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/viva_admin.php';
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/amazon_admin.php';
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/rpps_admin.php';

class AdminHfmVivaController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function postProcess()
    {
        $idOrder = (int) Tools::getValue('id_order');
        if (Tools::isSubmit('submitHfmVivaRefund') && $idOrder) {
            if (!$this->access('edit')) {
                $msg = 'Droits insuffisants pour rembourser.';
            } else {
                $order = new Order($idOrder);
                $raw = str_replace([',', ' '], ['.', ''], (string) Tools::getValue('hfm_viva_amount'));
                $r = Validate::isLoadedObject($order)
                    ? HfmVivaAdmin::refund($order, $raw === '' ? 0 : (float) $raw, (string) Tools::getValue('hfm_viva_reason'), (int) $this->context->employee->id)
                    : ['ok' => false, 'message' => 'Commande introuvable.'];
                $msg = ($r['ok'] ? '' : 'Échec : ') . $r['message'];
            }
            // Message de retour gardé en base (par employé), pas dans le cookie : le cookie du back-office est
            // déjà gros, et nginx renvoyait « 502 Bad Gateway » (en-têtes trop gros) sur la redirection (17/11).
            Configuration::updateGlobalValue('HFM_VIVA_FLASH_' . (int) $this->context->employee->id, $msg);
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], ['id_order' => $idOrder, 'vieworder' => 1]) . '#hfm-viva');
        }
        if (Tools::isSubmit('submitHfmAmazonRefund') && $idOrder) {
            if (!$this->access('edit')) {
                $msg = 'Droits insuffisants pour rembourser.';
            } else {
                $order = new Order($idOrder);
                $raw = str_replace([',', ' '], ['.', ''], (string) Tools::getValue('hfm_amz_amount'));
                $r = Validate::isLoadedObject($order)
                    ? HfmAmazonAdmin::refund($order, $raw === '' ? 0 : (float) $raw, (string) Tools::getValue('hfm_amz_reason'), (int) $this->context->employee->id)
                    : ['ok' => false, 'message' => 'Commande introuvable.'];
                $msg = ($r['ok'] ? '' : 'Échec : ') . $r['message'];
            }
            Configuration::updateGlobalValue('HFM_AMZ_FLASH_' . (int) $this->context->employee->id, $msg);
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], ['id_order' => $idOrder, 'vieworder' => 1]) . '#hfm-amazon');
        }
        // [26/11/2026] Saisie du n° RPPS par l'équipe (bloc « Déclaration professionnelle »).
        if (Tools::isSubmit('submitHfmRpps') && $idOrder) {
            if (!$this->access('edit')) {
                $msg = 'Échec : droits insuffisants pour modifier la commande.';
            } else {
                $order = new Order($idOrder);
                $r = Validate::isLoadedObject($order)
                    ? HfmRppsAdmin::saveForOrder($order, (string) Tools::getValue('hfm_rpps'), (int) $this->context->employee->id)
                    : ['ok' => false, 'message' => 'Commande introuvable.'];
                $msg = ($r['ok'] ? '' : 'Échec : ') . $r['message'];
            }
            Configuration::updateGlobalValue('HFM_RPPS_FLASH_' . (int) $this->context->employee->id, $msg);
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], ['id_order' => $idOrder, 'vieworder' => 1]) . '#hfm-pro');
        }
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
    }
}
