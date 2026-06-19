<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

class PBPAdminConfigGeneralController extends PBPControllerCore
{
    protected $sibling;

    /** @var string  */
    private $route = 'pbpadminconfiggeneralcontroller';

    public function __construct(&$sibling = null)
    {
        parent::__construct($sibling);
        if ($sibling !== null) {
            $this->sibling = &$sibling;
        }
    }

    /**
     * Render the General settings form
     */
    public function render()
    {
        $fields_form = array();
        $fields_form[0]['form'] = array(
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->sibling->l('Enabled in Quick View?', $this->route),
                    'name' => 'pbp_quickview_enabled',
                    'desc' => $this->sibling->l('Select if bundles should be displayed in the product quick view popup', $this->route),
                    'values' => array(
                        array(
                            'id' => 'pbp_quickview_enabled_on',
                            'value' => 1,
                            'label' => $this->sibling->l('Yes', $this->route),
                        ),
                        array(
                            'id' => 'pbp_quickview_enabled_off',
                            'value' => 0,
                            'label' => $this->sibling->l('No', $this->route),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->sibling->l('Display Attributes of parent product in bundle?', $this->route),
                    'name' => 'pbp_parent_attr_display',
                    'desc' => $this->sibling->l('Select if the attributes of the parent product should be displayed in the bundle', $this->route),
                    'values' => array(
                        array(
                            'id' => 'pbp_parent_attr_display_on',
                            'value' => 1,
                            'label' => $this->sibling->l('Yes', $this->route),
                        ),
                        array(
                            'id' => 'pbp_parent_attr_display_off',
                            'value' => 0,
                            'label' => $this->sibling->l('No', $this->route),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->sibling->l('Display Quantity field in bundle?', $this->route),
                    'name' => 'pbp_show_bundle_quantity',
                    'desc' => $this->sibling->l('Allows the customer to add multiple quantities of a bundle to the cart', $this->route),
                    'values' => array(
                        array(
                            'id' => 'pbp_show_bundle_quantity_on',
                            'value' => 1,
                            'label' => $this->sibling->l('Yes', $this->route),
                        ),
                        array(
                            'id' => 'pbp_show_bundle_quantity_off',
                            'value' => 0,
                            'label' => $this->sibling->l('No', $this->route),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->sibling->l('Display multiple bundles in a slider?', $this->route),
                    'name' => 'pbp_slider',
                    'desc' => $this->sibling->l('Organise multiple bundles into a slider.  Disable to display them stacked vertically.', $this->route),
                    'values' => array(
                        array(
                            'id' => 'pbp_slider_on',
                            'value' => 1,
                            'label' => $this->sibling->l('Yes', $this->route),
                        ),
                        array(
                            'id' => 'pbp_slider_off',
                            'value' => 0,
                            'label' => $this->sibling->l('No', $this->route),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->sibling->l('Bundle discounts can be combined with other vouchers?', $this->route),
                    'name' => 'pbp_bundle_discount_combinable',
                    'desc' => $this->sibling->l('Allow customer to redeem other vouchers while a bundle discount is active in their cart', $this->route),
                    'values' => array(
                        array(
                            'id' => 'pbp_bundle_discount_combinable_on',
                            'value' => 1,
                            'label' => $this->sibling->l('Yes', $this->route),
                        ),
                        array(
                            'id' => 'pbp_bundle_discount_combinable_off',
                            'value' => 0,
                            'label' => $this->sibling->l('No', $this->route),
                        ),
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->sibling->l('Layout', $this->route),
                    'name' => 'pbp_layout',
                    'desc' => $this->sibling->l('Select the layout / design for the bundles displayed', $this->route),
                    'options' => array(
                        'query' => array(
                            array(
                                'pbp_layout' => 'pbp-widget-full-width',
                                'name' => $this->sibling->l('Full Width Bundles (Default)', $this->route),
                            ),
                            array(
                                'pbp_layout' => 'pbp-widget-half-width',
                                'name' => $this->sibling->l('Half width bundles Grid', $this->route),
                            )
                        ),
                        'id' => 'pbp_layout',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->sibling->l('Display Location', $this->route),
                    'name' => 'pbp_location',
                    'desc' => $this->sibling->l('location to display the bundles on the product page', $this->route),
                    'options' => array(
                        'query' => array(
                            array(
                                'pbp_location' => 'product-footer',
                                'name' => $this->sibling->l('Product Page Footer', $this->route),
                            ),
                            array(
                                'pbp_location' => 'product-addcart',
                                'name' => $this->sibling->l('Product Add to cart Block', $this->route),
                            )
                        ),
                        'id' => 'pbp_location',
                        'name' => 'name',
                    )
                )
            ),
            'submit' => array(
                'title' => $this->sibling->l('Save', $this->route),
                'class' => 'btn btn-pbp-general-save pull-right'
            )
        );

        $helper = new HelperForm();

        $helper->fields_value['pbp_quickview_enabled'] = Configuration::get('pbp_quickview_enabled');
        $helper->fields_value['pbp_parent_attr_display'] = Configuration::get('pbp_parent_attr_display');
        $helper->fields_value['pbp_layout'] = Configuration::get('pbp_layout');
        $helper->fields_value['pbp_location'] = Configuration::get('pbp_location');
        $helper->fields_value['pbp_show_bundle_quantity'] = Configuration::get('pbp_show_bundle_quantity');
        $helper->fields_value['pbp_slider'] = Configuration::get('pbp_slider');
        $helper->fields_value['pbp_bundle_discount_combinable'] = Configuration::get('pbp_bundle_discount_combinable');

        $this->setupHelperConfigForm($helper, $this->route, 'process');

        Context::getContext()->smarty->assign(array(
            'module_config_url' => $this->module_config_url,
            'form' => $helper->generateForm($fields_form)
        ));

        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/config/general.tpl');
    }

    /**
     * Save general settings form
     */
    public function processForm()
    {
        Configuration::updateValue('pbp_quickview_enabled', Tools::getValue('pbp_quickview_enabled'));
        Configuration::updateValue('pbp_parent_attr_display', Tools::getValue('pbp_parent_attr_display'));
        Configuration::updateValue('pbp_layout', Tools::getValue('pbp_layout'));
        Configuration::updateValue('pbp_location', Tools::getValue('pbp_location'));
        Configuration::updateValue('pbp_slider', Tools::getValue('pbp_slider'));
        Configuration::updateValue('pbp_show_bundle_quantity', Tools::getValue('pbp_show_bundle_quantity'));
        Configuration::updateValue('pbp_bundle_discount_combinable', Tools::getValue('pbp_bundle_discount_combinable'));
    }

    public function route()
    {
        switch (Tools::getValue('action')) {
            case 'processform':
                die($this->processForm());

            default:
                die($this->render());
        }
    }
}
