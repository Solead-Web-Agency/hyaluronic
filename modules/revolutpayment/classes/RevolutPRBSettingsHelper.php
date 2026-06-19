<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

class RevolutPRBSettingsHelper
{
    protected $module;
    protected $context;

    public function __construct($rev_odule)
    {
        $this->module = $rev_odule;
        $this->context = Context::getContext();
    }

    /**
     * Save form data.
     */
    public function processPRBSettings()
    {
        if (((bool)Tools::isSubmit('submitPRBSettings')) !== true) {
            return false;
        }

        $form_values = $this->getPRBConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            $value = Tools::getValue($key);
            if ($key == 'REVOLUT_PRB_LOCATIONS') {
                if (empty($value)) {
                    $value = [];
                }

                $value = implode(',', $value);
                Configuration::updateValue('REVOLUT_PRB_LOCATION_VALUES', $value);
                continue;
            }
            Configuration::updateValue($key, $value);
        }
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    public function renderPRBSettingsForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = 'module';
        $helper->module = $this->module;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = 'id_module';
        $helper->submit_action = 'submitPRBSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getPRBConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Enable'),
                        'name' => 'REVOLUT_PRB_METHOD_ENABLE',
                        'is_bool' => true,
                        'desc' => $this->module->l('This controls whether or not "Revolut Payment Request Buttons (Apple Pay& Google Pay)" is enabled within Prestashop.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->module->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->module->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'desc' => $this->module->l('Select the button type you would like to show.'),
                        'name' => 'REVOLUT_PRB_ACTION',
                        'label' => $this->module->l('Payment Request Button Action'),
                        'options' => array(
                            'id' => 'id',
                            'name' => 'name',
                            'query' => array(
                                array(
                                    'id' => 'buy',
                                    'name' => 'Buy',
                                ),
                                array(
                                    'id' => 'donate',
                                    'name' => 'Donate',
                                ), array(
                                    'id' => 'pay',
                                    'name' => 'Pay',
                                ),
                            ),
                        )
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'desc' => $this->module->l('Select the button theme you would like to show.'),
                        'name' => 'REVOLUT_PRB_THEME',
                        'label' => $this->module->l('Payment Request Button Theme'),
                        'options' => array(
                            'id' => 'id',
                            'name' => 'name',
                            'query' => array(
                                array(
                                    'id' => 'dark',
                                    'name' => 'Dark',
                                ),
                                array(
                                    'id' => 'light',
                                    'name' => 'Light',
                                ), array(
                                    'id' => 'light-outlined',
                                    'name' => 'Light-Outlined',
                                ),
                            ),
                        )
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'desc' => $this->module->l('Select the button radius you would like to show.'),
                        'name' => 'REVOLUT_PRB_RADIUS',
                        'label' => $this->module->l('Payment Request Button Radius'),
                        'options' => array(
                            'id' => 'id',
                            'name' => 'name',
                            'query' => array(
                                array(
                                    'id' => 'none',
                                    'name' => 'None',
                                ),
                                array(
                                    'id' => 'small',
                                    'name' => 'Small',
                                ), array(
                                    'id' => 'large',
                                    'name' => 'Large',
                                ),
                            ),
                        )
                    ), array(
                        'col' => 3,
                        'type' => 'select',
                        'desc' => $this->module->l('Select the button Size you would like to show.'),
                        'name' => 'REVOLUT_PRB_SIZE',
                        'label' => $this->module->l('Payment Request Button Size'),
                        'options' => array(
                            'id' => 'id',
                            'name' => 'name',
                            'query' => array(
                                array(
                                    'id' => 'small',
                                    'name' => 'Small',
                                ), array(
                                    'id' => 'large',
                                    'name' => 'Large',
                                ),
                            ),
                        )
                    ),
                    array(
                        'col' => 8,
                        'type' => 'select',
                        'desc' => $this->module->l('Select where you would like Payment Request Buttons to be displayed.'),
                        'name' => 'REVOLUT_PRB_LOCATIONS',
                        'label' => $this->module->l('Payment Request Button Locations'),
                        'options' => array(
                            'id' => 'id',
                            'name' => 'name',
                            'query' => array(
                                array(
                                    'id' => 'product',
                                    'name' => 'Product',
                                ),
                                array(
                                    'id' => 'cart',
                                    'name' => 'Cart',
                                ),
                            ),
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->module->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    public function getPRBConfigFormValues()
    {
        return array(
            'REVOLUT_PRB_METHOD_ENABLE' => $this->getConfigFormValue('REVOLUT_PRB_METHOD_ENABLE', 0),
            'REVOLUT_PRB_ACTION' => $this->getConfigFormValue('REVOLUT_PRB_ACTION', 'buy'),
            'REVOLUT_PRB_THEME' => $this->getConfigFormValue('REVOLUT_PRB_THEME', 'dark'),
            'REVOLUT_PRB_RADIUS' => $this->getConfigFormValue('REVOLUT_PRB_RADIUS', 'none'),
            'REVOLUT_PRB_SIZE' => $this->getConfigFormValue('REVOLUT_PRB_SIZE', 'large'),
            'REVOLUT_PRB_LOCATIONS' => $this->getConfigFormValue('REVOLUT_PRB_LOCATIONS', ''),
        );
    }

    public function getConfigFormValue($key, $default)
    {
        $value = Configuration::get($key);

        if (!empty($value)) {
            return $value;
        }

        return $default;
    }
}
