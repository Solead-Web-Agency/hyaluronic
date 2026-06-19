<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

class AdminCAPStatisticsController extends ModuleAdminController
{
    /**
     * ajaxProcessLoadTemplate
     *
     * @return void
     */
    public function ajaxProcessLoadTemplate()
    {
        $aDates = $this->getDatesFromToForQueries();
        $sDateFrom = $aDates['dateFrom'];
        $sDateTo = $aDates['dateTo'];

        $oReminderInfos = new CartReminderStatistics;

        $this->context->smarty->assign(array(
            'datepicker_script' => $this->module->js_path.'admin/tabs/statistics/datepicker.js',
            'abandonedCartsByPrestashop' => $oReminderInfos->getAbandonedCartsByPrestashop($sDateFrom, $sDateTo),
            'finalizedCarts' => $oReminderInfos->getFinalizedCarts($sDateFrom, $sDateTo),
            'turnoverGenerated' => $this->getTurnoverFinalStats($sDateFrom, $sDateTo),
            'unsubscribedDatas' => $oReminderInfos->getUnsubscribedDatas($sDateFrom, $sDateTo),
            'generalStatsList' => $oReminderInfos->getReminderGeneralStatistics($sDateFrom, $sDateTo),
            'currency' => $this->context->currency->sign,
        ));

        $sTemplateName = $this->getTemplateNameToDisplay();
        
        exit($this->context->smarty->fetch(_PS_MODULE_DIR_.'/'.$this->module->name.'/views/templates/admin/tabs/statistics/'.$sTemplateName));
    }

    /**
     * Merge 2 datas array to get a final Stats array
     *
     * @param  string $dateFrom (yyyy-mm-dd hh:ii:ss)
     * @param  string $dateTo (yyyy-mm-dd hh:ii:ss)
     *
     * @return array
     */
    private function getTurnoverFinalStats($sDateFrom, $sDateTo)
    {
        $oReminderInfos = new CartReminderStatistics;

        $aAllCartsStats = $oReminderInfos->getTurnoverThatCouldBeGenerated($sDateFrom, $sDateTo);
        $aAbandonedCartStats = $oReminderInfos->getTurnoverGenerated($sDateFrom, $sDateTo);

        $aStats = array_merge($aAllCartsStats, $aAbandonedCartStats);

        // To prevent division by 0
        if ($aStats['all_price'] == 0) {
            $aStats['percent'] = 0;
            return $aStats;
        }

        // Get the percentage of abandoned carts
        $percent = ($aStats['abandoned_price']/$aStats['all_price']) * 100;
        $aStats['percent'] = round($percent, 2);

        return $aStats;
    }

    /**
     * Get dates FROM / TO and return an array with these two values
     *
     * @return array
     */
    private function getDatesFromToForQueries()
    {
        // Set Date From. If there is a POST "dateFrom", initiliaze with post data
        if (false !== Tools::getValue('dateFrom')) {
            $sDateFrom = pSQL(Tools::getValue('dateFrom')).' 00:00:01';
        } else {
            $sDateFrom = "1970-01-01 00:00:01";
        }

        // Set Date From. If there is a POST "dateFrom", initiliaze with post data
        if (false !== Tools::getValue('dateTo')) {
            $sDateTo = pSQL(Tools::getValue('dateTo')).' 23:59:59';
        } else {
            $sDateTo = date('Y-m-d G:i:s');
        }

        return array(
            'dateFrom' => $sDateFrom,
            'dateTo' => $sDateTo
        );
    }

    /**
     * getTemplateNameToDisplay
     *
     * @return string
     */
    private function getTemplateNameToDisplay()
    {
        if (Tools::getValue('dateFrom') || Tools::getValue('dateTo')) {
            return 'datas.tpl';
        }

        return 'statistics.tpl';
    }
    
} 
