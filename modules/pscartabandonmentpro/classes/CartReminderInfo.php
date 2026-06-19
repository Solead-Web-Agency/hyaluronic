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

class CartReminderInfo
{
    /**
     * Get general informations from all tabs to get the list, ordered by time, of the cart reminders availables
     *
     * @return array
     */
    public function getReminderList($isActive = false)
    {
        $sWhere = 1;
        $sEmployeeLang = '';
        $context = Context::getContext();

        // In cron task there is no context
        if (isset($context->employee->id_lang)) {
            $sEmployeeLang = 'AND catsubject.id_lang = '.(int)$context->employee->id_lang;
        }

        if ($isActive) {
            $sWhere = 'ca.active = 1';
        }

        $aReminderList = Db::getInstance()->executeS(
            'SELECT ca.id_cart_abandonment, 
                ca.cart_frequency_number, 
                ca.cart_frequency_type, 
                ca.active, 
                IF(catsubject.email_subject IS NULL, catlanguages.email_subject, catsubject.email_subject) email_subject,
                COUNT(DISTINCT(cad.id_discount)) as discount_nb,
                GROUP_CONCAT(DISTINCT(catlanguages.lang_iso) SEPARATOR ", ") as template_langs, 
                cad.discount_value_type
            FROM `'._DB_PREFIX_.'cart_abandonment` ca 
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_discount` cad ON cad.id_cart_abandonment = ca.id_cart_abandonment 
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_template` cat ON cat.id_cart_abandonment = ca.id_cart_abandonment 
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_template_lang` catlanguages ON  catlanguages.id_template = cat.id_template 
            LEFT JOIN `'._DB_PREFIX_.'cart_abandonment_template_lang` catsubject ON (
                catsubject.id_template = cat.id_template 
                '.$sEmployeeLang.'
            )
            WHERE 1
                AND ca.deleted = 0 
                AND cad.deleted = 0 
                AND cat.deleted = 0 
                AND catlanguages.deleted = 0 
                AND ca.id_shop = '.(int)$context->shop->id.'
                AND '.$sWhere.'
            GROUP BY ca.id_cart_abandonment
            ORDER BY IF(cart_frequency_type = "hour", cart_frequency_number, cart_frequency_number*24), ca.id_cart_abandonment, catlanguages.id_lang'
        );

        return $aReminderList;
    }

    /**
     * Get Discount informations for a cart reminder by giving to the method the cart abandonment ID
     *
     * @param  int $idCartAbandonment
     *
     * @return array
     */
    public function getDiscountInfos($idCartAbandonment)
    {
        $aDiscountInfos = Db::getInstance()->executeS(
            'SELECT * 
            FROM `'._DB_PREFIX_.'cart_abandonment_discount` 
            WHERE id_cart_abandonment = '.(int)$idCartAbandonment.' AND deleted = 0'
        );
        
        return $aDiscountInfos;
    }

    /**
     * isDiscountAlreadyExist tell if the data already exist on the table or not
     *
     * @param  string $where
     *
     * @return bool
     */
    public function isDiscountAlreadyExist($where)
    {
        $bDataAlreadyExist = Db::getInstance()->executeS(
            'SELECT id_discount
            FROM `'._DB_PREFIX_.'cart_abandonment_discount`
            WHERE '. pSQL($where)
        );
        
        return (bool) $bDataAlreadyExist;
    }

    /**
     * Get Target informations for a cart reminder by giving to the method the cart abandonment ID
     *
     * @param  int $idCartAbandonment
     *
     * @return array
     */
    public function getTargetInfos($idCartAbandonment)
    {
        $context = Context::getContext();

        $aTargetInfos = Db::getInstance()->getRow(
            'SELECT * 
            FROM `'._DB_PREFIX_.'cart_abandonment` 
            WHERE 1
                AND id_cart_abandonment = '.(int)$idCartAbandonment.' 
                AND id_shop = '.(int)$context->shop->id.'
                AND deleted = 0'
        );
        
        return $aTargetInfos;
    }

    /**
     * getEmailTemplateName
     *
     * @param  int $idCartAbandonment
     *
     * @return string
     */
    public function getEmailTemplateName($idCartAbandonment)
    {
        $sEmailTemplateName = Db::getInstance()->getValue(
            'SELECT model_name
            FROM `'._DB_PREFIX_.'cart_abandonment_template` 
            WHERE id_cart_abandonment = '.(int)$idCartAbandonment.' AND deleted = 0'
        );
        
        return $sEmailTemplateName;
    }

    /**
     * getEmailTemplateId
     *
     * @param  int $idCartAbandonment
     *
     * @return int
     */
    public function getEmailTemplateId($idCartAbandonment)
    {
        $iEmailTemplateId = Db::getInstance()->getValue(
            'SELECT id_template
            FROM `'._DB_PREFIX_.'cart_abandonment_template` 
            WHERE id_cart_abandonment = '.(int)$idCartAbandonment.' AND deleted = 0'
        );
        
        return (int) $iEmailTemplateId;
    }

    /**
     * getEmailTemplateAppearance
     *
     * @param  int $idCartAbandonment
     *
     * @return array
     */
    public function getEmailTemplateAppearance($idCartAbandonment)
    {
        $aEmailTemplateAppearance = Db::getInstance()->getRow(
            'SELECT *
            FROM `'._DB_PREFIX_.'cart_abandonment_template` 
            WHERE id_cart_abandonment = '.(int)$idCartAbandonment.' AND deleted = 0'
        );
        
        return $aEmailTemplateAppearance;
    }

    /**
     * getEmailTemplateDatas
     *
     * @param  int $idCartAbandonment
     *
     * @return array
     */
    public function getEmailTemplateDatas($idCartAbandonment)
    {
        $aEmailTemplateDatasByLangKey = array();
        
        $aEmailTemplateDatas = Db::getInstance()->executeS(
            'SELECT catl.*
            FROM `'._DB_PREFIX_.'cart_abandonment_template`  cat
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_template_lang` catl ON catl.id_template = cat.id_template
            WHERE cat.id_cart_abandonment = '.(int)$idCartAbandonment.' 
                AND catl.deleted = 0 
                AND cat.deleted = 0'
        );  

        // We copy all the datas into a new array having for key 'id_lang'
        // We need to decode the html entites for email_content && email_unsubscribe
        foreach ($aEmailTemplateDatas as $key => $aTemplateDatas) {
            $aEmailTemplateDatasByLangKey[$aTemplateDatas['id_lang']] = $aTemplateDatas;
            $aEmailTemplateDatasByLangKey[$aTemplateDatas['id_lang']]['email_content'] = html_entity_decode($aTemplateDatas['email_content'], ENT_QUOTES);
            $aEmailTemplateDatasByLangKey[$aTemplateDatas['id_lang']]['email_discount'] = html_entity_decode($aTemplateDatas['email_discount'], ENT_QUOTES);
            $aEmailTemplateDatasByLangKey[$aTemplateDatas['id_lang']]['email_unsubscribe'] = html_entity_decode($aTemplateDatas['email_unsubscribe'], ENT_QUOTES);
        }

        return $aEmailTemplateDatasByLangKey;
    }

    /**
     * isEmailTemplateDataAlreadyExist tells if the data already exist on the table or not
     *
     * @param  string $where
     *
     * @return bool
     */
    public function isEmailTemplateDataAlreadyExist($where)
    {
        $bDataAlreadyExist = Db::getInstance()->executeS(
            'SELECT id_template
            FROM `'._DB_PREFIX_.'cart_abandonment_template_lang`
            WHERE '. pSQL($where)
        );
        
        return (bool) $bDataAlreadyExist;
    }

    
    /**
     * Multiple row tables delete
     *
     * @param  int $idCartAbandonment
     *
     * @return bool $bDeleteReminder
     */
    public function deleteReminderById($idCartAbandonment)
    {
        $context = Context::getContext();

        $bDeleteReminder = Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'cart_abandonment` ca
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_discount` cad ON cad.id_cart_abandonment = ca.id_cart_abandonment 
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_template` cat ON cat.id_cart_abandonment = ca.id_cart_abandonment 
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_template_lang` catl ON catl.id_template = cat.id_template 
            SET ca.deleted = 1,
                cad.deleted = 1,
                cat.deleted = 1,
                catl.deleted = 1
            WHERE 1
                AND ca.id_cart_abandonment = '.(int)$idCartAbandonment.'
                AND ca.id_shop = '.(int)$context->shop->id);
        
        return (bool) $bDeleteReminder;
    }

    /**
     * Get last reminder ID
     *
     * @param  void
     *
     * @return int
     */
    public function getLastestReminderId()
    {
        $context = Context::getContext();
        
        $idCartAbandonment = Db::getInstance()->getValue(
            'SELECT id_cart_abandonment
            FROM `'._DB_PREFIX_.'cart_abandonment` 
            WHERE id_shop = '.(int)$context->shop->id.'
            ORDER BY id_cart_abandonment DESC'
        );

        return (int) $idCartAbandonment;
    }

    /**
     * Define the maximum date at which cart reminders can be sent.
     *
     * @return string 
     */
    public function getMaxDateSending()
    {
        $context = Context::getContext();

        $moduleInstallDate = new DateTime(
            Configuration::get(
                'PSCARTABANDONMEDPRO_INSTALL_DATE', 
                false, 
                $context->shop->id_shop_group , 
                $context->shop->id
            )
        );

        $dateMax = $moduleInstallDate->sub(
            new DateInterval('P7D')
        );

        return $dateMax->format('Y-m-d');
    }
}