<?php
/**
 * 2007-2021 ETS-Soft
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
 * @copyright  2007-2021 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class ProductController extends ProductControllerCore
{
    public function getTemplateVarPage(): array
    {
        $page =  parent::getTemplateVarPage();
        if (isset($page['meta']['title'])){
            $moduleSeo = Module::getInstanceByName('ets_seo');
            if ($moduleSeo){
                $price = $this->getPriceProduct();
                $discount_price = $this->getPriceProduct(true);
                $brand = $this->getBrandName();
                $desc = $this->product->description_short;
                $pageTitle = $moduleSeo->formatSeoMeta($this->product->name, array('post_title' => '', 'is_title' => true, 'category' => $this->category->name, 'price' => $price, 'description' => $desc, 'brand' => $brand, 'discount_price' => $discount_price), 'product');
                $page['meta']['title'] = $moduleSeo->formatSeoMeta($page['meta']['title'], array('post_title' => $pageTitle, 'is_title' => true, 'category' => $this->category->name, 'price' => $price, 'description' => $desc, 'brand' => $brand, 'discount_price' => $discount_price), 'product');
            }
        }
        return $page;
    }

    public function getPriceProduct($getDiscount = false)
    {
        $id_customer = ($this->context->customer->id) ? (int)($this->context->customer->id) : 0;
        $id_group = null;
        if ($id_customer) {
            $id_group = Customer::getDefaultGroupId((int)$id_customer);
        }
        if (!$id_group) {
            $id_group = (int)Group::getCurrent()->id;
        }
        $group = new Group($id_group);
        if ($group->price_display_method)
            $tax = false;
        else
            $tax = true;
        if ($getDiscount)
            return Tools::displayPrice($this->product->getPrice($tax));
        return Tools::displayPrice($this->product->getPriceWithoutReduct(!$tax));
    }

    public function getBrandName()
    {
        if($this->product->id_manufacturer && ($manuf = new Manufacturer($this->product->id_manufacturer, $this->context->language->id)) && $manuf->id){
            return $manuf->name;
        }
        return '';
    }
}