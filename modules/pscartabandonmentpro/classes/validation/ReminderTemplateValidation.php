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

class ReminderTemplateValidation implements ReminderStepsValidation
{
    const ONE_AT_LEAST = 'You must fill the datas for at least one language';
    const MODEL_NAME_ERROR = 'Model name is invalid';
    const PRIMARY_COLOR_ERROR = 'Primary color is invalid';
    const SECONDARY_COLOR_ERROR = 'Secondary color is invalid';
    const EMAIL_SUBJECT_ERROR = 'The subject is invalid';
    const EMAIL_MAIN_CONTENT_ERROR = 'The main content is invalid';
    const EMAIL_MAIN_DISCOUNT_ERROR = 'The discount content is invalid';
    const EMAIL_LINK_FACEBOOK_ERROR = 'The facebook link is invalid';
    const EMAIL_LINK_TWITTER_ERROR = 'The twitter link is invalid';
    const EMAIL_LINK_INSTAGRAM_ERROR = 'The instagram link is invalid';
    const EMAIL_REASSURANCE_TXT_1_ERROR = 'The first reassurance text is invalid';
    const EMAIL_REASSURANCE_TXT_2_ERROR = 'The second reassurance text is invalid';
    const EMAIL_REASSURANCE_TXT_3_ERROR = 'The third reassurance text is invalid';
    const EMAIL_REASSURANCE_IMG_1_ERROR = 'The first reassurance image is invalid';
    const EMAIL_REASSURANCE_IMG_2_ERROR = 'The second reassurance image is invalid';
    const EMAIL_REASSURANCE_IMG_3_ERROR = 'The third reassurance image is invalid';
    const EMAIL_CTA_ERROR = 'The CTA text is invalid';
    const EMAIL_UNSUBSCRIBE_ERROR = 'The unsubscribe content is invalid';
    const EMAIL_UNSUBSCRIBE_TEXT_ERROR = 'The unsubscribe text is invalid';

    /**
     * Validates the template step
     * 
     * @param array $data
     * 
     * @return array Error lists, empty if ok
     */
    public function validate($data)
    {
        $messagesError = array();

        if (!isset($data[0])) {
            $data[0] = array();
        }
        
        $validateTemplateApparance = $this->validateAppearanceDatas($data[0]);

        if (!isset($data[1])) {
            $data[1] = array();
        }

        $validateContentTemplateDatas = $this->validateContentDatas($data[1]);

        if (is_array($validateTemplateApparance)) {
            $messagesError['appearance'] = $validateTemplateApparance;
        }

        if (is_array($validateContentTemplateDatas)) {
            $messagesError['datas'] = $validateContentTemplateDatas;
        }
        
        return $messagesError;
    }

    /**
     * validateAppearanceDatas
     *
     * @param  array $data
     *
     * @return array Error lists, empty if ok
     */
    private function validateAppearanceDatas($data) 
    {
        $messagesError = array();
        
        if (empty($data)) {
            array_push(
                $messagesError,
                self::MODEL_NAME_ERROR,
                self::PRIMARY_COLOR_ERROR,
                self::SECONDARY_COLOR_ERROR
            );
            return $messagesError;  
        }

        /**
        * Is mandatory
        * Check if data 'model_name' exist
        * Data must be 'sendy' or 'boxy' or 'puffy'
        */
        if (isset($data['model_name'])) {
            if (!in_array($data['model_name'], array('sendy', 'boxy', 'puffy'))) {
                $messagesError[] = self::MODEL_NAME_ERROR;
            }
        } else {
            $messagesError[] = self::MODEL_NAME_ERROR;
        }

        /**
        * Is mandatory
        * Check if data 'primary_color' exist
        * Data must be an hexadecimal code
        */
        if (isset($data['primary_color'])) {

            $isColorHexadecimal = preg_match('/#([a-f0-9]{3}){1,2}\b/i', $data['primary_color']);

            if (!$isColorHexadecimal) {
                $messagesError[] = self::PRIMARY_COLOR_ERROR;
            }
        } else {
            $messagesError[] = self::PRIMARY_COLOR_ERROR;
        }

        /**
        * Is mandatory
        * Check if data 'secondary_color' exist
        * Data must be an hexadecimal code
        */
        if (isset($data['secondary_color'])) {

            $isColorHexadecimal = preg_match('/#([a-f0-9]{3}){1,2}\b/i', $data['secondary_color']);

            if (!$isColorHexadecimal) {
                $messagesError[] = self::SECONDARY_COLOR_ERROR;
            }
        } else {
            $messagesError[] = self::SECONDARY_COLOR_ERROR;
        }

        return $messagesError;
    }

    /**
     * validateContentDatas
     *
     * @param  array $data
     *
     * @return array Error lists, empty if ok
     */
    private function validateContentDatas($data) 
    {
        $messagesError = array();
        
        if (empty($data)) {
            array_push(
                $messagesError,
                self::ONE_AT_LEAST
            );
            return $messagesError;  
        }

        $atLeastOneLanguageContentIsFilled = 0;

        foreach ($data as $key => $value) {

            $iLangId = $value['id_lang'];
            /*
            * If email_subject and email_content not empty, all the other datas must be setted
            * else we dont take those datas
            */
            if (!empty($value['email_subject']) && !empty($value['email_content_'.$iLangId])) {

                // At least one language content is now filled
                $atLeastOneLanguageContentIsFilled++;

                /**
                * Data must be < 256 (BDD limit)
                */
                if (strlen($value['email_subject']) > 255) {
                    $messagesError[$key][] = self::EMAIL_SUBJECT_ERROR;
                }

                /**
                * Data must be < 2501 (BDD limit)
                */
                if (strlen($value['email_content_'.$iLangId]) > 2500) {
                    $messagesError[$key][] = self::EMAIL_MAIN_CONTENT_ERROR;
                }

                /**
                * Data must be < 2501 (BDD limit)
                */
                if (strlen($value['email_discount_'.$iLangId]) > 2500) {
                    $messagesError[$key][] = self::EMAIL_MAIN_DISCOUNT_ERROR;
                }

                /**
                * Check if data 'email_link_facebook' is not empty
                * Data must be a valid url
                */
                if (!empty($value['email_link_facebook'])) {

                    $isUrlValide = filter_var($value['email_link_facebook'], FILTER_VALIDATE_URL);

                    if (!$isUrlValide) {
                        $messagesError[$key][] = self::EMAIL_LINK_FACEBOOK_ERROR;
                    }
                }

                /**
                * Check if data 'email_link_twitter' is not empty
                * Data must be a valid url
                */
                if (!empty($value['email_link_twitter'])) {

                    $isUrlValide = filter_var($value['email_link_twitter'], FILTER_VALIDATE_URL);

                    if (!$isUrlValide) {
                        $messagesError[$key][] = self::EMAIL_LINK_TWITTER_ERROR;
                    }
                }

                /**
                * Check if data 'email_link_instagram' is not empty
                * Data must be a valid url
                */
                if (!empty($value['email_link_instagram'])) {

                    $isUrlValide = filter_var($value['email_link_instagram'], FILTER_VALIDATE_URL);

                    if (!$isUrlValide) {
                        $messagesError[$key][] = self::EMAIL_LINK_INSTAGRAM_ERROR;
                    }
                }

                /**
                * Check if data 'email_reassurance_text1' is not empty
                * Data must be < 101 (BDD limit)
                * email_reassurance_img must exist
                */
                if (!empty($value['email_reassurance_text1'])) {
                    if (strlen($value['email_reassurance_text1']) > 100) {
                        $messagesError[$key][] = self::EMAIL_REASSURANCE_TXT_1_ERROR;
                    }

                    if (empty($value['email_reassurance_img1'])) {
                        $messagesError[$key][] = self::EMAIL_REASSURANCE_IMG_1_ERROR;
                    }
                }

                /**
                * Check if data 'email_reassurance_text2' is not empty
                * Data must be < 101 (BDD limit)
                * email_reassurance_img must exist
                */
                if (!empty($value['email_reassurance_text2'])) {
                    if (strlen($value['email_reassurance_text2']) > 100) {
                        $messagesError[$key][] = self::EMAIL_REASSURANCE_TXT_2_ERROR;
                    }

                    if (empty($value['email_reassurance_img2'])) {
                        $messagesError[$key][] = self::EMAIL_REASSURANCE_IMG_2_ERROR;
                    }
                }

                /**
                * Check if data 'email_reassurance_text3' is not empty
                * Data must be < 101 (BDD limit)
                * email_reassurance_img must exist
                */
                if (!empty($value['email_reassurance_text3'])) {
                    if (strlen($value['email_reassurance_text3']) > 100) {
                        $messagesError[$key][] = self::EMAIL_REASSURANCE_TXT_3_ERROR;
                    }

                    if (empty($value['email_reassurance_img3'])) {
                        $messagesError[$key][] = self::EMAIL_REASSURANCE_IMG_3_ERROR;
                    }
                }

                /**
                * Check if data 'email_cta' is not empty
                * Data must be < 26 (BDD limit)
                */
                if (!empty($value['email_cta'])) {
                    if (strlen($value['email_cta']) > 25) {
                        $messagesError[$key][] = self::EMAIL_CTA_ERROR;
                    }
                }

                /**
                * Data must be not empty and < 2501 (BDD limit)
                */
                if (!empty($value['email_unsubscribe_'.$iLangId]) && strlen($value['email_unsubscribe_'.$iLangId]) > 2500) {
                    $messagesError[$key][] = self::EMAIL_UNSUBSCRIBE_ERROR;
                }

                /**
                * Check if data 'email_unsubscribe_text' is not empty
                * AND
                * Data must be < 101 (BDD limit)
                */
                if (!empty($value['email_unsubscribe_text']) && strlen($value['email_reassurance_text3']) > 100) {
                    $messagesError[$key][] = self::EMAIL_UNSUBSCRIBE_TEXT_ERROR;
                }
                
            }
        }

        if (!$atLeastOneLanguageContentIsFilled) {
            $messagesError[0][] = self::ONE_AT_LEAST;
        }
        
        return $messagesError;
    }

    /**
     * Save the template step
     * 
     * @param array $data
     * 
     * @return bool
     */
    public function save($data)
    {
        $oReminderInfos = new CartReminderInfo;

        // get the lastest reminder id 
        $iCartReminderId = $oReminderInfos->getLastestReminderId();

        // Step 1 : We save the template appearance configurations datas
        $dataAppearance = array_map("pSQL", $data[0]);
        $iTemplateId = $this->saveAppearanceConf($iCartReminderId, $dataAppearance);

        if (!$iTemplateId) {
            $oReminderInfos->deleteReminderById($iCartReminderId);
            return false;
        }
        
        // Step 2 : We save the template datas
        $aPreparedDatasForQueries = $this->prepareContentDatas($iTemplateId, $data[1]);
        $bSaveAllTemplateContentDatas = $this->saveContentConf($aPreparedDatasForQueries);

        if (!$bSaveAllTemplateContentDatas) {
            $oReminderInfos->deleteReminderById($iCartReminderId);
            return false;
        }

        return true;
    }

    /**
     * Save the Appearance datas
     *
     * @return mixed int|boolean
     */
    private function saveAppearanceConf($iCartReminderId, $data)
    {
        $data['id_cart_abandonment'] = $iCartReminderId;

        if (!Db::getInstance()->insert('cart_abandonment_template', $data)) {
            return false;
        }

        return (int) Db::getInstance()->Insert_ID();
    }
    
    /**
     * Save the Content datas
     *
     * @return bool
     */
    private function saveContentConf($data)
    {
        foreach ($data as $key => $aData) {
            if (!Db::getInstance()->insert('cart_abandonment_template_lang', $aData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * prepareContentDatas
     *
     * @param  int $iTemplateId
     * @param  array $data
     *
     * @return array $data 
     */
    private function prepareContentDatas($iTemplateId, $data)
    {
        foreach ($data as $key => $aData) {
            $iLangId = (int) $aData['id_lang'];
            
            // we save the data only if email_content and subject aren't empty
            if (!empty($aData['email_content_'.$iLangId]) && !empty($aData['email_subject'])) {

                // We escape all the datas
                $data[$key] = array_map("pSQL", $data[$key]);

                // remove all  "_idlang" from the data key (we must do that because of ckeditor instances names)
                // These following datas must keep their HTML tags
                $data[$key]['email_content'] = htmlentities($aData['email_content_'.$iLangId], ENT_QUOTES);
                $data[$key]['email_discount'] = htmlentities($aData['email_discount_'.$iLangId], ENT_QUOTES);
                $data[$key]['email_unsubscribe'] = htmlentities($aData['email_unsubscribe_'.$iLangId], ENT_QUOTES);
                unset($data[$key]['email_content_'.$iLangId]);
                unset($data[$key]['email_discount_'.$iLangId]);
                unset($data[$key]['email_unsubscribe_'.$iLangId]);

                // we add id_template and lang_iso values to the array (very important)
                $data[$key]['id_template'] = $iTemplateId;
                $data[$key]['lang_iso'] = Language::getIsoById($iLangId);
                
            } else {
                // We won't save or update this array
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Update the Template step
     *
     * @param  array $data
     *
     * @return bool
     */
    public function update($data, $reminderId)
    {
        // Step 1 : We save the template appearance configurations datas
        $dataAppearance = array_map("pSQL", $data[0]);
        $bUpdateAppearanceConf = $this->updateAppearanceConf($reminderId, $dataAppearance);
        
        if (!$bUpdateAppearanceConf) {
            return false;
        }
        
        // Step 2 : We save the template datas
        $oCartReminderInfo = new CartReminderInfo;
        $iTemplateId = $oCartReminderInfo->getEmailTemplateId($reminderId);

        $aPreparedDatasForQueries = $this->prepareContentDatas($iTemplateId, $data[1]);
        $bUpdateAllTemplateContentDatas = $this->updateContentConf($aPreparedDatasForQueries, $iTemplateId);

        if (!$bUpdateAllTemplateContentDatas) {
            return false;
        }

        return true;
    }

    /**
     * update the Appearance datas
     *
     * @return mixed int|boolean
     */
    private function updateAppearanceConf($reminderId, $data)
    {
        $where = 'id_cart_abandonment = '.$reminderId;

        if (!Db::getInstance()->update('cart_abandonment_template', $data, $where)) {
            return false;
        }

        return true;
    }

    /**
     * update the Content datas
     *
     * @return bool
     */
    private function updateContentConf($data, $iTemplateId)
    {
        $oReminderInfos = new CartReminderInfo;

        foreach ($data as $key => $aData) {
            $id_lang = (int) $aData['id_lang'];
            $where = 'id_template = '.$iTemplateId.' AND id_lang = '.$id_lang;

            $bDataRowAlreadyExist = $oReminderInfos->isEmailTemplateDataAlreadyExist($where);
            // if $bDataRowAlreadyExist === true we can update the existing data
            // else we can insert the new data
            if ($bDataRowAlreadyExist) {
                if (!Db::getInstance()->update('cart_abandonment_template_lang', $aData, $where)) {
                    return false;
                }
            } else {
                if (!Db::getInstance()->insert('cart_abandonment_template_lang', $aData)) {
                    return false;
                }
            }
        }

        return true;
    }
}
