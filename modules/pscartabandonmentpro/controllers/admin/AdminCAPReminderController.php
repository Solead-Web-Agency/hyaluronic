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

class AdminCAPReminderController extends ModuleAdminController
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->ajax = true;
        parent::__construct();
    }

    /**
     * ajaxProcessSave
     */
    public function ajaxProcessSave()
    {
        $errors = array();
        $datasModify = new CartReminderDatasModify();

        // Transform the string into an array
        $aTargetData = $datasModify->parseStr(Tools::getValue('targetData'));
        $aDiscountData = $datasModify->parseStr(Tools::getValue('discountData'));
        $aTemplateData = $datasModify->parseStr(Tools::getValue('templateData'));

        // Step 1 - Validate data
        // validate return true if there is no errors. Return array if there is some errors
        $targetValidation = new ReminderTargetValidation();
        $discountValidation = new ReminderDiscountValidation();
        $templateValidation = new ReminderTemplateValidation();

        $targetDatasValidation = $targetValidation->validate($aTargetData);
        $discountDatasValidation = $discountValidation->validate($aDiscountData);
        $templateDatasValidation = $templateValidation->validate($aTemplateData);

        if (!empty($targetDatasValidation)) {
            $errors['target'] = $targetDatasValidation;
        }
        if (!empty($discountDatasValidation)) {
            $errors['discount'] = $discountDatasValidation;
        }
        if (!empty($templateDatasValidation['appearance']) || !empty($templateDatasValidation['datas'])) {
            $errors['template'] = $templateDatasValidation;
        }

        if (!empty($errors) && count($errors)) {
            echo json_encode(array(
                'errors' => $errors,
            ));
            return;
        }

        // Step 2 - Save or Update the reminder's datas
        $ajaxActionProcess = Tools::getValue('saveAction');

        if ($ajaxActionProcess === 'add') {

            $result = $targetValidation->save($aTargetData)
                && $discountValidation->save($aDiscountData)
                && $templateValidation->save($aTemplateData);

        } else {

            $reminderId = (int) Tools::getValue('reminder_id');

            $result = $targetValidation->update($aTargetData, $reminderId)
                && $discountValidation->update($aDiscountData, $reminderId)
                && $templateValidation->update($aTemplateData, $reminderId);
        }

        $this->ajaxDie(json_encode(array(
            'result' => $result
        )));
    }
}
