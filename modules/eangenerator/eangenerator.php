<?php
/**
* 2016 Johann Corbel Consulting
*
* NOTICE OF LICENSE
*
*  @author    Johann Corbel Consulting <contact@johanncorbelconsulting.fr>
*  @copyright 2017 Johann Corbel Consulting
*  @license   http://www.johanncorbelconsulting.fr/content/6-licence-utilisation
*
*  International Registered Trademark & Property of Johann Corbel Consulting
*/

class EanGenerator extends Module
{
    private $ean;
    private $upc;
    private $prefix;
    private $deleteOnlyInvalid;
    
    private $html = '';
    
    public function __construct()
    {
        $this->name = 'eangenerator';
        $this->tab = 'shipping_logistics';
        $this->version = '2.1.0';
        $this->author = 'Johann Corbel Consulting';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7');
        $this->bootstrap = true;
        $this->module_key = '1285cc74a9d543cf52903856de2ff452';

        parent::__construct();

        $this->displayName = $this->l('EAN / UPC codes generator');
        $this->description = $this->l('Generates EAN / UPC codes');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        
        if (!Configuration::get('EANGENERATOR_EAN') &&
            !Configuration::get('EANGENERATOR_UPC') &&
            !Configuration::get('EANGENERATOR_PREFIX') &&
            !Configuration::get('EANGENERATOR_DELETE_ONLY_INVALID')) {
                $this->warning = $this->l('You have not yet set your EAN / UPC codes parameters');
        }
    }

    public function install()
    {
        if (!parent::install() ||
                $this->registerHook('addproduct') == false ||
                $this->registerHook('updateproduct') == false) {
            return false;
        }

        Configuration::updateValue('EANGENERATOR_EAN', 0);
        Configuration::updateValue('EANGENERATOR_UPC', 0);
        Configuration::updateValue('EANGENERATOR_PREFIX', '');
        Configuration::updateValue('EANGENERATOR_DELETE_ONLY_INVALID', 1);
        
        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('EANGENERATOR_EAN') ||
            !Configuration::deleteByName('EANGENERATOR_UPC') ||
            !Configuration::deleteByName('EANGENERATOR_PREFIX') ||
            !Configuration::deleteByName('EANGENERATOR_DELETE_ONLY_INVALID') ||
            !parent::uninstall()) {
                return false;
        }

        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitJC')) == true) {
            $errors = $this->postProcess();
            if (count($errors) > 0) {
                $output.= $this->displayError(implode('<br />', $errors));
            } else {
                $output.= $this->displayConfirmation($this->l('Settings updated successfully'));
            }
        }

        if (((bool)Tools::isSubmit('submitGenerate')) == true) {
            $this->refreshProperties();

            if ($this->ean == 1) {
                $this->traitement("update", "ean", "all");
                $output .= $this->displayConfirmation($this->l('EAN codes generated'));
            }
                
            if ($this->upc == 1) {
                $this->traitement("update", "upc", "all");
                $output .= $this->displayConfirmation($this->l('UPC codes generated'));
            }
        }

        if (((bool)Tools::isSubmit('submitDelete')) == true) {
            $this->refreshProperties();
        
            if ($this->ean == 1) {
                if ($this->deleteOnlyInvalid == 0) {
                    Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'product SET ean13 = ""');
                    Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'product_attribute SET ean13 = ""');
                    $output .= $this->displayConfirmation($this->l('EAN codes deleted'));
                } else {
                    $this->traitement("delete", "ean", "all");
                    $output .= $this->displayConfirmation($this->l('Invalid EAN codes deleted'));
                }
            }

            if ($this->upc == 1) {
                if ($this->deleteOnlyInvalid == 0) {
                    Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'product SET upc = ""');
                    Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'product_attribute SET upc = ""');
                    $output .= $this->displayConfirmation($this->l('UPC codes deleted'));
                } else {
                    $this->traitement("delete", "upc", "all");
                    $output .= $this->displayConfirmation($this->l('Invalid UPC codes deleted'));
                }
            }
        }

        $msg1 = $this->l('Are you sure you want to generate missing codes ?');
        $msg2 = $this->l('Are you sure you want to delete existing codes ?');
        $output .= '
        <script type="text/javascript">
        $(document).ready(function(){
            $("#module_form_submit_btn_1").click(function() {
                return confirm(\''.addslashes(html_entity_decode($msg1)).'\');
            });
            $("#module_form_submit_btn_2").click(function() {
                return confirm(\''.addslashes(html_entity_decode($msg2)).'\');
            });
        });
        </script>';
        
        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->displayForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function displayForm()
    {
        $helper = new HelperForm();

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;

        // Language
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
//         $helper->submit_action = 'submitJC';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(true),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $label1 = $this->l('EAN code');
        $desc1 = $this->l('Generate an EAN code after each product creation');
        $label2 = $this->l('UPC code');
        $desc2 = $this->l('Generate an UPC code after each product creation');
        $label3 = $this->l('Keep valid codes');
        $desc3 = $this->l('When deleting codes, delete only invalid codes');
        $label4 = $this->l('Code prefix');
        $desc4 = $this->l('Enter your code prefix, if you have one');
        
        $form1 = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 9,
                        'type' => 'text',
                        'label' => $label4,
                        'name' => 'EANGENERATOR_PREFIX',
                        'desc' => $desc4,
                        'size' => 20,
                    ),
                    array(
                        'col' => 9,
                        'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                        'label' => $label1,
                        'name' => 'EANGENERATOR_EAN',
                        'is_bool' => true,
                        'desc' => $desc1,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('on')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('off')
                            )
                        ),
                    ),
                    array(
                            'col' => 9,
                            'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                            'label' => $label2,
                            'name' => 'EANGENERATOR_UPC',
                            'is_bool' => true,
                            'desc' => $desc2,
                            'values' => array(
                                    array(
                                            'id' => 'active_on',
                                            'value' => 1,
                                            'label' => $this->l('on')
                                    ),
                                    array(
                                            'id' => 'active_off',
                                            'value' => 0,
                                            'label' => $this->l('off')
                                    )
                            ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitJC',
                ),
            ),
        );
        
        $form2 = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Generate missing codes'),
                'icon' => 'icon-cogs',
                ),
                'submit' => array(
                    'title' => $this->l('Generate missing codes'),
                    'name' => 'submitGenerate',
                ),
            ),
        );

        $form3 = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Delete existing codes'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 9,
                        'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                        'label' => $label3,
                        'name' => 'EANGENERATOR_DELETE_ONLY_INVALID',
                        'is_bool' => true,
                        'desc' => $desc3,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('on')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('off')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Delete existing codes'),
                    'name' => 'submitDelete',
                ),
            ),
        );
        
        return array($form1, $form2, $form3);
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $this->refreshProperties();
        
        return array(
            'EANGENERATOR_EAN' => $this->ean,
            'EANGENERATOR_UPC' => $this->upc,
            'EANGENERATOR_PREFIX' => $this->prefix,
            'EANGENERATOR_DELETE_ONLY_INVALID' => $this->deleteOnlyInvalid,
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $errors = array();
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            if (!Configuration::updateValue($key, Tools::getValue($key))) {
                $errors[] = $this->l('Cannot update settings');
            }
        }
        
        return $errors;
    }
    

    private function refreshProperties()
    {
        $this->ean = (int) Configuration::get('EANGENERATOR_EAN');
        $this->upc = (int) Configuration::get('EANGENERATOR_UPC');
        $this->prefix = Configuration::get('EANGENERATOR_PREFIX');
        $this->deleteOnlyInvalid = (int) Configuration::get('EANGENERATOR_DELETE_ONLY_INVALID');
    }

    public function hookAddProduct($params)
    {
        $this->refreshProperties();
    
        $id = (int)$params['product']->id;

        if ($this->ean) {
            Db::getInstance()->Execute(
                'UPDATE '._DB_PREFIX_.'product 
                SET ean13 = "'.pSQL($this->generateEAN($id)).'" 
                WHERE (ean13 IS NULL OR ean13 = "") AND id_product = \''.$id.'\''
            );
        }

        if ($this->upc == 1) {
            Db::getInstance()->Execute(
                'UPDATE '._DB_PREFIX_.'product 
                SET upc = "'.pSQL($this->generateUPC($id)).'" 
                WHERE (upc IS NULL OR upc = "") AND id_product = \''.$id.'\''
            );
        }
    }

    public function hookUpdateProduct($params)
    {
        $this->refreshProperties();

        $id = (int)$params['product']->id;

        if ($this->ean == 1) {
            $this->traitement("update", "ean", $id);
        }
        
        if ($this->upc == 1) {
            $this->traitement("update", "upc", $id);
        }
    }
    
    private function generateEAN($id_product, $id_product_attribute = '')
    {
        $digits = '000000000000'.$id_product.($id_product_attribute != '' ? '0'.$id_product_attribute : '');
        $digits = Tools::substr($digits, -12);
        $digits = $this->prefix.Tools::substr($digits, -12+Tools::strlen($this->prefix));
    
        //first change digits to a string so that we can access individual numbers
        $digits = (string)$digits;
        // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
        $even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9] + $digits[11];
        // 2. Multiply this result by 3.
        $even_sum_three = $even_sum * 3;
        // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
        $odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];
        // 4. Sum the results of steps 2 and 3.
        $total_sum = $even_sum_three + $odd_sum;
        // 5. The check character is the smallest number which,
        // when added to the result in step 4,  produces a multiple of 10.
        $next_ten = (ceil($total_sum/10))*10;
        $check_digit = $next_ten - $total_sum;
    
        return $digits . $check_digit;
    }
    
    private function generateUPC($id_product, $id_product_attribute = '')
    {
        $digits = '00000000000'.$id_product.($id_product_attribute != '' ? '0'.$id_product_attribute : '');
        $digits = Tools::substr($digits, -11);
        $digits = $this->prefix.Tools::substr($digits, -11+Tools::strlen($this->prefix));

        $upc = (string)$digits;

        $odd_sum = $even_sum = 0;

        for ($i = 0; $i < 11; ++$i) {
            if ($i % 2) {
                $even_sum += $upc[$i];
            } else {
                $odd_sum += $upc[$i];
            }
        }

        $total_sum = $even_sum + $odd_sum * 3;
        $modulo10 = $total_sum % 10;
        $check_digit = 10 - $modulo10;
        if ($check_digit == 10) {
            $check_digit = 0;
        }
    
        return $digits.$check_digit;
    }
    
    private function checkEAN($code)
    {
        if (!preg_match("/^[0-9]{13}$/", $code)) {
            return false;
        }
    
        $digits = $code;
    
        // 1. Add the values of the digits in the
        // even-numbered positions: 2, 4, 6, etc.
        $even_sum = $digits[1] + $digits[3] + $digits[5] +
        $digits[7] + $digits[9] + $digits[11];
    
        // 2. Multiply this result by 3.
        $even_sum_three = $even_sum * 3;
    
        // 3. Add the values of the digits in the
        // odd-numbered positions: 1, 3, 5, etc.
        $odd_sum = $digits[0] + $digits[2] + $digits[4] +
        $digits[6] + $digits[8] + $digits[10];
    
        // 4. Sum the results of steps 2 and 3.
        $total_sum = $even_sum_three + $odd_sum;
    
        // 5. The check character is the smallest number which,
        // when added to the result in step 4, produces a multiple of 10.
        $next_ten = (ceil($total_sum / 10)) * 10;
        $check_digit = $next_ten - $total_sum;
    
        // if the check digit and the last digit of the
        // code are OK return true;
        if ($check_digit == $digits[12]) {
            return true;
        }
    
        return false;
    }
    
    private function checkUPC($code)
    {
        $upc = (string)$code;
    
        if (!isset($upc[11])) {
            return false;
        }
    
        $odd_sum = $even_sum = 0;
    
        for ($i = 0; $i < 11; ++$i) {
            if ($i % 2) {
                $even_sum += $upc[$i];
            } else {
                $odd_sum += $upc[$i];
            }
        }
    
        $total_sum = $even_sum + $odd_sum * 3;
        $modulo10 = $total_sum % 10;
        $check_digit = 10 - $modulo10;
        if ($check_digit == 10) {
            $check_digit = 0;
        }
    
        return $upc[11] == $check_digit;
    }
    
    private function traitement($mode, $typeCode, $id = 'all')
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        // produits
        $result = $db->ExecuteS(
            'SELECT * FROM '._DB_PREFIX_.'product p'.($id != 'all' ? ' WHERE p.id_product='.(int)$id : '')
        );
        foreach ($result as $row) {
            $ean = $row['ean13'];
            $upc = $row['upc'];

            // suppression des codes
            if ($mode == 'delete') {
                if ($typeCode == 'ean') {
                    if ($this->deleteOnlyInvalid == 0 || !$this->checkEAN($ean)) {
                        $db->Execute('UPDATE '._DB_PREFIX_.'product p 
                            set ean13="" WHERE p.id_product='.(int)$row['id_product']);
                    }
                }
                if ($typeCode == 'upc') {
                    if ($this->deleteOnlyInvalid == 0 || !$this->checkUPC($upc)) {
                        $db->Execute('UPDATE '._DB_PREFIX_.'product p 
                            set upc="" WHERE p.id_product='.(int)$row['id_product']);
                    }
                }
            }

            // regénération des codes
            if ($mode == 'update') {
                if ($typeCode == 'ean' && !$this->checkEAN($ean)) {
                    $db->Execute('UPDATE '._DB_PREFIX_.'product p 
                        set ean13="'.pSQL($this->generateEAN($row['id_product'])).'" 
                        WHERE p.id_product='.(int)$row['id_product']);
                }
                if ($typeCode == 'upc' && !$this->checkUPC($upc)) {
                    $db->Execute('UPDATE '._DB_PREFIX_.'product p 
                        set upc="'.pSQL($this->generateUPC($row['id_product'])).'" 
                        WHERE p.id_product='.(int)$row['id_product']);
                }
            }
        }

        // produits avec déclinaisons
        $result = $db->ExecuteS(
            'SELECT * FROM '._DB_PREFIX_.'product_attribute pa'.($id != 'all' ? ' WHERE pa.id_product='.(int)$id : '')
        );
        foreach ($result as $row) {
            $ean = $row['ean13'];
            $upc = $row['upc'];
                
            // suppression des codes
            if ($mode == 'delete') {
                if ($typeCode == 'ean') {
                    if ($this->deleteOnlyInvalid == 0 || !$this->checkEAN($ean)) {
                        $db->Execute('UPDATE '._DB_PREFIX_.'product_attribute pa 
                            set ean13="" WHERE pa.id_product='.(int)$row['id_product'].' 
                            AND pa.id_product_attribute='.(int)$row['id_product_attribute']);
                    }
                }
                if ($typeCode == 'upc') {
                    if ($this->deleteOnlyInvalid == 0 || !$this->checkUPC($upc)) {
                        $db->Execute('UPDATE '._DB_PREFIX_.'product_attribute pa 
                            set upc="" WHERE pa.id_product='.(int)$row['id_product'].' 
                            AND pa.id_product_attribute='.(int)$row['id_product_attribute']);
                    }
                }
            }

            // regénération des codes
            if ($mode == 'update') {
                if ($typeCode == 'ean' && !$this->checkEAN($ean)) {
                    $db->Execute('UPDATE '._DB_PREFIX_.'product_attribute pa 
                        set ean13="'.pSQL($this->generateEAN($row['id_product'], $row['id_product_attribute'])).'" 
                        WHERE pa.id_product='.(int)$row['id_product'].' 
                        AND pa.id_product_attribute='.(int)$row['id_product_attribute']);
                }
                if ($typeCode == 'upc' && !$this->checkUPC($upc)) {
                    $db->Execute('UPDATE '._DB_PREFIX_.'product_attribute pa 
                        set upc="'.pSQL($this->generateUPC($row['id_product'], $row['id_product_attribute'])).'" 
                        WHERE pa.id_product='.(int)$row['id_product'].' 
                        AND pa.id_product_attribute='.(int)$row['id_product_attribute']);
                }
            }
        }
    }
}
