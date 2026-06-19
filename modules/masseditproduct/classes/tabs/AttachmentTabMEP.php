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

class AttachmentTabMEP extends BaseTabMEP
{
    public $attachments;
    public $old_attachment;

    public function __construct()
    {
        parent::__construct();
        $this->attachments = $this->getIntvalArrayRequest('attachments');
        $this->old_attachment = (int)Tools::getValue('old_attachment');
    }

    public function applyChangeBoth($products, $combinations)
    {
    }

    public function applyChangeForProducts($products)
    {
        foreach ($products as $id_product) {
            MassEditTools::attachToProduct(
                $id_product,
                $this->attachments,
                $this->old_attachment
            );
        }

        return array();
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function checkBeforeChange()
    {
        if (!is_array($this->attachments) || !count($this->attachments)) {
            LoggerMEP::getInstance()->error($this->l('No attachments'));
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function getTitle()
    {
        return $this->l('Attachments');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $variables['attachments'] = Attachment::getAttachments(
            $this->context->language->id,
            0,
            false
        );
        return $variables;
    }
}
