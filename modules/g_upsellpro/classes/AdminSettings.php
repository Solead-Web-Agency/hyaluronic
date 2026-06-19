<?php
/**
 * The file is controller. Do not modify the file if you want to upgrade the module in future
 * 
 * @author    Globo Jsc <contact@globosoftware.net>
 * @copyright 2020 Globo., Jsc
 * @license   please read license in file license.txt
 * @link	     http://www.globosoftware.net
 */

class AdminSettings
{
    /*get Config table default module */
    public static function getUpsellSettings($id_shop_group=0,  $languages =array(), $id_shop=0)
    {
        $datas = array(
            'GSELL_MAIN_BUTTON_BACKGROUND' => Configuration::get('GSELL_MAIN_BUTTON_BACKGROUND', null, $id_shop_group, (int)$id_shop),
            'GSELL_MAIN_BUTTON_COLOR' => Configuration::get('GSELL_MAIN_BUTTON_COLOR', null, $id_shop_group, (int)$id_shop),
            'GSELL_MAIN_LABEL' => Configuration::get('GSELL_MAIN_LABEL', null, $id_shop_group, (int)$id_shop),
            'GSELL_SETTING_CUSTOM_CSS' => Configuration::get('GSELL_SETTING_CUSTOM_CSS', null, $id_shop_group, (int)$id_shop),
            'GSELL_ANALYTIC_TIME' => Configuration::get('GSELL_ANALYTIC_TIME', null, $id_shop_group, (int)$id_shop),
            'GSELL_MAIN_POPUP_DELAY' => Configuration::get('GSELL_MAIN_POPUP_DELAY', null, $id_shop_group, (int)$id_shop),
            'GSELL_MAIN_FLOATING_POSITION' => Configuration::get('GSELL_MAIN_FLOATING_POSITION', null, $id_shop_group, (int)$id_shop),
            'GSELL_MAIN_MOSTPOPOLAR_BACKGROUND' => Configuration::get('GSELL_MAIN_MOSTPOPOLAR_BACKGROUND', null, $id_shop_group, (int)$id_shop),
            'GSELL_MAIN_MOSTPOPOLAR_COLOR' => Configuration::get('GSELL_MAIN_MOSTPOPOLAR_COLOR', null, $id_shop_group, (int)$id_shop),
            'GSELL_MAIN_MOSTPOPOLAR_BACKGROUND_ITEM' => Configuration::get('GSELL_MAIN_MOSTPOPOLAR_BACKGROUND_ITEM', null, $id_shop_group, (int)$id_shop),
        );
        $datas['GSELL_SETTING_BUTTON_TOTAL'] = array();
        $datas['GSELL_SETTING_BUTTON_ADCART'] = array();
        $datas['GSELL_SETTING_BUTTON_UPGRADE'] = array();
        $datas['GSELL_SETTING_BUTTON_CHECKOUT'] = array();
        $datas['GSELL_SETTING_BUTTON_UPGRADECART'] = array();
        $datas['GSELL_SETTING_BUTTON_NOTHANKS'] = array();
        $datas['GSELL_SETTING_BUTTON_FLOATING'] = array();
        $datas['GSELL_SETTING_MOST_POPULAR'] = array();
        foreach ($languages as $lang) {
            $datas['GSELL_SETTING_BUTTON_TOTAL'][$lang['id_lang']] = Tools::getValue('GSELL_SETTING_BUTTON_TOTAL_'.(int)$lang['id_lang'],  Configuration::get('GSELL_SETTING_BUTTON_TOTAL', (int)$lang['id_lang'], $id_shop_group, $id_shop));
            $datas['GSELL_SETTING_BUTTON_ADCART'][$lang['id_lang']] = Tools::getValue('GSELL_SETTING_BUTTON_ADCART_'.(int)$lang['id_lang'],  Configuration::get('GSELL_SETTING_BUTTON_ADCART', (int)$lang['id_lang'], $id_shop_group, $id_shop));
            $datas['GSELL_SETTING_BUTTON_UPGRADE'][$lang['id_lang']] = Tools::getValue('GSELL_SETTING_BUTTON_UPGRADE_'.(int)$lang['id_lang'],  Configuration::get('GSELL_SETTING_BUTTON_UPGRADE', (int)$lang['id_lang'], $id_shop_group, $id_shop));
            $datas['GSELL_SETTING_BUTTON_CHECKOUT'][$lang['id_lang']] = Tools::getValue('GSELL_SETTING_BUTTON_CHECKOUT_'.(int)$lang['id_lang'],  Configuration::get('GSELL_SETTING_BUTTON_CHECKOUT', (int)$lang['id_lang'], $id_shop_group, $id_shop));
            $datas['GSELL_SETTING_BUTTON_UPGRADECART'][$lang['id_lang']] = Tools::getValue('GSELL_SETTING_BUTTON_UPGRADECART_'.(int)$lang['id_lang'],  Configuration::get('GSELL_SETTING_BUTTON_UPGRADECART', (int)$lang['id_lang'], $id_shop_group, $id_shop));
            $datas['GSELL_SETTING_BUTTON_NOTHANKS'][$lang['id_lang']] = Tools::getValue('GSELL_SETTING_BUTTON_NOTHANKS_'.(int)$lang['id_lang'],  Configuration::get('GSELL_SETTING_BUTTON_NOTHANKS', (int)$lang['id_lang'], $id_shop_group, $id_shop));
            $datas['GSELL_SETTING_BUTTON_FLOATING'][$lang['id_lang']] = Tools::getValue('GSELL_SETTING_BUTTON_FLOATING_'.(int)$lang['id_lang'],  Configuration::get('GSELL_SETTING_BUTTON_FLOATING', (int)$lang['id_lang'], $id_shop_group, $id_shop));
            $datas['GSELL_SETTING_MOST_POPULAR'][$lang['id_lang']] = Tools::getValue('GSELL_SETTING_MOST_POPULAR_'.(int)$lang['id_lang'],  Configuration::get('GSELL_SETTING_MOST_POPULAR', (int)$lang['id_lang'], $id_shop_group, $id_shop));
        }

        return $datas;
    }
    public static function tabsHTML($controller='',$link)
    {
        Context::getContext()->smarty->assign(
            array(
                'controller' => $controller,
                'link' => $link,
                'html' => 'tabs',
            ));
        return Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . "g_upsellpro/views/templates/admin/extrahtml.tpl");
    }
    public static function upsellanytic($id_g_upsellrule, $views=0, $addcarts=0, $transactions=0, $sales=0, $take_rate, $date='', $id_shop) 
    {
        $res = true;
        $sql = 'SELECT `id_g_upsellanytic` FROM `'._DB_PREFIX_.'g_upsellanytic`
        WHERE  `id_g_upsellrule` = '.(int)$id_g_upsellrule.' AND `id_shop` ='.(int)$id_shop.' AND `date` >= "'.pSQL($date).'"';
        $id_g_upsellanytic = Db::getInstance()->getValue($sql);
        if ((int)$id_g_upsellanytic > 0) {
            $sql = 'UPDATE`'._DB_PREFIX_.'g_upsellanytic` SET `take_rate` = '.(int)$take_rate;
            if ($views > 0) {
                $sql .= ',`views` = '.(int)$views;
            }
            if ($addcarts > 0) {
                $sql .= ',`addcarts` = '.(int)$addcarts;
            }
            if ($transactions > 0) {
                $sql .= ',`transactions` = '.(int)$transactions;
            }
            if ($sales > 0) {
                $sql .= ',`sales` = '.(float)$sales;
            }
            $sql .= ' WHERE `id_g_upsellanytic` = '.(int)$id_g_upsellanytic.' AND `id_shop` = '.(int)$id_shop;
            $res &= Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
        } else {
            $date= date('Y-m-d', time());
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'g_upsellanytic` (`id_g_upsellrule`,`views`,`addcarts`,`sales`,`take_rate`, `transactions`,`date`,`id_shop`) VALUES
            ('.(int)$id_g_upsellrule.','.(int)$views.','.(int)$addcarts.','.(float)$sales.','.(int)$take_rate.','.(int)$transactions.',"'.pSQL($date).'",'.(int)$id_shop.')';
            $res &= Db::getInstance()->execute($sql);
        }
        return $res;
    }
    public static function getFieldsanytic($field='views',$datefrom='',$dateto='',$id_shop)
    {
        if ($field == 'take_rate') {
            $sql = 'SELECT LEFT(`date`, 30) AS date,  ((`addcarts` + `transactions`) / `views`) as total FROM `'._DB_PREFIX_.'g_upsellanytic`
            WHERE  `id_shop` ='.(int)$id_shop.' AND `date` BETWEEN "'.pSQL($datefrom).'" AND "'.pSQL($dateto).'"';
        } else {
            $sql = 'SELECT LEFT(`date`, 30) AS date,  `'.$field.'` as total FROM `'._DB_PREFIX_.'g_upsellanytic`
            WHERE  `id_shop` ='.(int)$id_shop.' AND `date` BETWEEN "'.pSQL($datefrom).'" AND "'.pSQL($dateto).'"';
        }
        $datas =  Db::getInstance()->executeS($sql);

        return self::formatChartData($datas,$datefrom,$dateto,'day');
    }
    public static function getTotalFieldsanytic($field='views',$id_g_upsellrule=0, $datefrom='',$dateto='',$id_shop)
    {
        $sql = 'SELECT SUM(`'.$field.'`) as total FROM `'._DB_PREFIX_.'g_upsellanytic`
        WHERE  `id_shop` ='.(int)$id_shop.' AND `date` BETWEEN "'.pSQL($datefrom).'" AND "'.pSQL($dateto).'"';
        if ($id_g_upsellrule > 0) {
            $sql .= ' AND `id_g_upsellrule` ='.(int)$id_g_upsellrule;
        }
        $datas =  Db::getInstance()->getValue($sql);
        return $datas ;
    }

    public static function formatChartData($datas,$date_from,$date_to,$type = 'day'){
        $_view_values = array();
        foreach($datas as $view){
            $view_time = strtotime($view['date']);
            if (isset($_view_values[$view_time]) && !empty($_view_values[$view_time])) {
                $_view_values[$view_time]['y'] += (float)$view['total'];
            } else
                $_view_values[$view_time] = array(
                    'key'=>$view_time,
                    'y'=>(float)$view['total']);
        }
        $viewsdatas = array();
        if($type == 'month'){
            for ($date = strtotime($date_from); $date <= strtotime($date_to); $date = strtotime('+1 month', $date)) {
                if (isset($_view_values[$date]))
                    $viewsdatas[] = $_view_values[$date];
                else
                    $viewsdatas[] = array(
                        'key' => $date,
                        'y' => 0);
            }
        } else {
            for ($date = strtotime($date_from); $date <= strtotime($date_to); $date = strtotime('+1 day', $date)) {
                if (isset($_view_values[$date]))
                    $viewsdatas[] = $_view_values[$date];
                else
                    $viewsdatas[] = array(
                        'key' => $date,
                        'y' => 0);
            }
        }
        return $viewsdatas;
    }
    public static function upsellcartproduct($id_g_upsellrule, $id_cart, $id_product=0, $id_combin=0, $id_shop) 
    {
        $res = true;
        $sql = 'SELECT `id_g_upsellcart` FROM `'._DB_PREFIX_.'g_upsellcart`
        WHERE  `id_g_upsellrule` = '.(int)$id_g_upsellrule.' AND `id_product` ='.(int)$id_product.' AND `id_combin` ='.(int)$id_combin.' AND `id_cart` ='.(int)$id_cart.' AND `id_shop` ='.(int)$id_shop;
        $id_g_upsellcart = (int)Db::getInstance()->getValue($sql);
        if  ($id_g_upsellcart <= 0) {
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'g_upsellcart` (`id_g_upsellrule`,`id_cart`,`id_product`,`id_combin`,`id_shop`) VALUES
            ('.(int)$id_g_upsellrule.','.(int)$id_cart.','.(int)$id_product.','.(int)$id_combin.','.(int)$id_shop.')';
            $res &= Db::getInstance()->execute($sql);
        }
        return $id_g_upsellcart;
    }
    public static function upgetsellcartproduct($id_cart, $id_product=0, $id_combin=0, $id_shop) 
    {
        $id_product;$id_combin;
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'g_upsellcart`
        WHERE  `id_cart` ='.(int)$id_cart.' AND `id_shop` ='.(int)$id_shop;
        $upsellcarts = Db::getInstance()->executeS($sql);
        return $upsellcarts;
    }
}