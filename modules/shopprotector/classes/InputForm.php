<?php
/**
 * CREA4YOU CONFIDENTIAL
 * _____________________
 *
 * [2011] - [2019]
 * @author Youness EL GHAZI <contact@@crea4you.fr>
 *
 * All Rights Reserved.
 *
 * NOTICE:  All information contained herein is, and remains
 * the property of Crea4You - Youness EL GHAZI and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Crea4You Youness EL GHAZI.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Crea4You Youness EL GHAZI.
 */

/**
 * Classe de gestion des champs
 *
 * @author Youness EL GHAZI<youness.e@crea4you.fr>
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class C4ySpInputForm
{
    public static function getForm($module)
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $module->l('Settings', 'InputForm'),
                'icon' => 'icon-cogs',
                ),
                'input' => self::getFormField($module),
                'submit' => array(
                    'title' => $module->l('Save', 'InputForm'),
                ),
                'tabs' => array(
                    'mouse' => $module->l("Mouse", 'InputForm'),
                    'keyboard' => $module->l("Keyboard", 'InputForm'),
                    'alert' => $module->l("Alert", 'InputForm'),
                ),
            ),
        );
    }

    public static function getFormField($module)
    {
        $fields = array(
            // Mouse configuration
            array(
                'type' => 'switch',
                'label' => $module->l('Prohibit Right Click', 'InputForm'),
                'name' => 'SHOPPROTECTOR_MOUSE_DISABLE_RIGHT_CLICK',
                'tab' => 'mouse',
                'is_bool' => true,
                'desc' => $module->l('Prevent right click on your shop', 'InputForm'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $module->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $module->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'text',
                'label' => $module->l('IP allowed to Right Click', 'InputForm'),
                'name' => 'SHOPPROTECTOR_MOUSE_ALLOW_IP_ADDRESS',
                'tab' => 'mouse',
                'desc' => $module->l('The ip addresses you specify will be allowed to right click on your store (front). Separate the ip addresses with commas. Example: 127.0.0.1,195.124.11.21,210.141.154.1', 'InputForm'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $module->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $module->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'switch',
                'label' => $module->l('Prohibit only on picture', 'InputForm'),
                'name' => 'SHOPPROTECTOR_MOUSE_DISABLE_ONLY_PICTURE',
                'tab' => 'mouse',
                'is_bool' => true,
                'desc' => $module->l('Prevent right click only on picture, to make sure pictures only will not be stolen', 'InputForm'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $module->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $module->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'switch',
                'label' => $module->l('Prohibit text selection', 'InputForm'),
                'name' => 'SHOPPROTECTOR_MOUSE_DISABLE_SELECTION',
                'tab' => 'mouse',
                'is_bool' => true,
                'desc' => $module->l('Prevent mouse selection for protect your texts', 'InputForm'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $module->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $module->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'switch',
                'label' => $module->l('Prohibit Drag&Drop function', 'InputForm'),
                'name' => 'SHOPPROTECTOR_MOUSE_DISABLE_DRAG_DROP',
                'tab' => 'mouse',
                'is_bool' => true,
                'desc' => $module->l('Prevent drag&drop on your store', 'InputForm'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $module->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $module->l('Disabled')
                    )
                ),
            ),
            // Modal alert
            array(
                'type' => 'switch',
                'label' => $module->l('Enable alert', 'InputForm', 'InputForm'),
                'name' => 'SHOPPROTECTOR_ALERT_ENABLE',
                'tab' => 'alert',
                'is_bool' => true,
                'desc' => $module->l('An modal window will be opened when a customer try to make prohibited action', 'InputForm'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $module->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $module->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'text',
                'label' => $module->l('Title', 'InputForm'),
                'name' => 'SHOPPROTECTOR_MODAL_TITLE',
                'desc' => $module->l('You can customize the title of the alert window', 'InputForm'),
                'tab' => 'alert',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $module->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $module->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'text',
                'label' => $module->l('Description', 'InputForm'),
                'name' => 'SHOPPROTECTOR_MODAL_MESSAGE',
                'desc' => $module->l('You can customize the message of the alert window', 'InputForm'),
                'tab' => 'alert',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $module->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $module->l('Disabled')
                    )
                ),
            )
        );

        // Keyboard protection
        foreach (ShopProtector::SHORTCUT_KEYS as $sk) {
            $key = Tools::strtoupper($sk);
            $fields[] = array(
                'type' => 'switch',
                'label' => $module->l('Prohibit shortcut', 'InputForm') . " Ctrl+". $key,
                'name' => 'SHOPPROTECTOR_SHORTCUT_KEY_'. $key,
                'is_bool' => true,
                'desc' => $module->l('Prevent the usage of shortcut ', 'InputForm') . " Ctrl+". $key,
                'tab' => 'keyboard',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $module->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $module->l('Disabled')
                    )
                ),
            );
        }

        return $fields;
    }

    /**
     *  Valeurs par défaut des champs (vierge ou récupérés d'une précedente validation échoué)
     * @return type
     */
    public static function getConfigFormValues($module)
    {
        $settings = unserialize(Configuration::get('SHOPPROTECTOR_SETTINGS'));
        if ($settings === false) {
            $settings = self::defaultSettingsValues($module);
        }

        $conf = array(
            // Mouse protection
            'SHOPPROTECTOR_MOUSE_ALLOW_IP_ADDRESS'      => $settings['SHOPPROTECTOR_MOUSE_ALLOW_IP_ADDRESS'],
            'SHOPPROTECTOR_MOUSE_DISABLE_RIGHT_CLICK'   => $settings['SHOPPROTECTOR_MOUSE_DISABLE_RIGHT_CLICK'],
            'SHOPPROTECTOR_MOUSE_DISABLE_ONLY_PICTURE'  => $settings['SHOPPROTECTOR_MOUSE_DISABLE_ONLY_PICTURE'],
            'SHOPPROTECTOR_MOUSE_DISABLE_SELECTION'     => $settings['SHOPPROTECTOR_MOUSE_DISABLE_SELECTION'],
            'SHOPPROTECTOR_MOUSE_DISABLE_DRAG_DROP'     => $settings['SHOPPROTECTOR_MOUSE_DISABLE_DRAG_DROP'],
            // Modal Alert
            'SHOPPROTECTOR_ALERT_ENABLE'            => $settings['SHOPPROTECTOR_ALERT_ENABLE'],
            'SHOPPROTECTOR_MODAL_TITLE'             => $settings['SHOPPROTECTOR_MODAL_TITLE'],
            'SHOPPROTECTOR_MODAL_MESSAGE'           => $settings['SHOPPROTECTOR_MODAL_MESSAGE'],
        );
        // Keyboard protection
        foreach (ShopProtector::SHORTCUT_KEYS as $sk) {
            $key = Tools::strtoupper($sk);
            $conf['SHOPPROTECTOR_SHORTCUT_KEY_'.$key] = $settings['SHOPPROTECTOR_SHORTCUT_KEY_'.$key];
        }

        return $conf;
    }

    public static function defaultSettingsValues($module)
    {
        $default_conf = array(
            'SHOPPROTECTOR_MOUSE_ALLOW_IP_ADDRESS'      => '',
            'SHOPPROTECTOR_MOUSE_DISABLE_RIGHT_CLICK'   => false,
            'SHOPPROTECTOR_MOUSE_DISABLE_ONLY_PICTURE'  => false,
            'SHOPPROTECTOR_MOUSE_DISABLE_SELECTION'     => false,
            'SHOPPROTECTOR_MOUSE_DISABLE_DRAG_DROP'     => false,
            // Modal Alert
            'SHOPPROTECTOR_ALERT_ENABLE'            => false,
            'SHOPPROTECTOR_MODAL_TITLE'             => $module->l('Action Prohibited !'),
            'SHOPPROTECTOR_MODAL_MESSAGE'           => $module->l('This action was blocked'),
        );

        // Keyboard protection
        foreach (ShopProtector::SHORTCUT_KEYS as $sk) {
            $key = Tools::strtoupper($sk);
            $default_conf['SHOPPROTECTOR_SHORTCUT_KEY_'.$key] = false;
        }

        return $default_conf;
    }
}
