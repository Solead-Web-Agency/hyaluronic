<?php
/**
 * 2007-2021 PrestaShop
 * NOTICE OF LICENSE
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
if (!defined('_PS_VERSION_')) {
    exit;
}
class Carrieronorder extends Module
{
    protected $config_form = false;
    public function __construct()
    {
        $this->name = 'carrieronorder';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'h2p-rsi';
        $this->need_instance = 0;
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Display Carrier on Order list');
        $this->description = $this->l('Show carriers on order list');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }
    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('CARRIERONORDER_LIVE_MODE', false);
        return parent::install() && $this->registerHook('header') && $this->registerHook('backOfficeHeader') && $this->registerHook('actionOrderGridQueryBuilderModifier') && $this->registerHook('actionOrderFormBuilderModifier') && $this->registerHook('actionOrderGridDefinitionModifier');
    }
    public function uninstall()
    {
        Configuration::deleteByName('CARRIERONORDER_LIVE_MODE');
        return parent::uninstall();
    }
    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitCarrieronorderModule')) == true) {
            $this->postProcess();
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        return $this->context->smarty->fetch($this->local_path.'views/templates/hook/infos.tpl');
        //return $output.$this->renderForm();
    }
    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCarrieronorderModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array('fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(), 'id_language' => $this->context->language->id,);
        return $helper->generateForm(array($this->getConfigForm()));
    }
    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array('form' => array('legend' => array('title' => $this->l('Settings'), 'icon' => 'icon-cogs',), 'input' => array(array('type' => 'switch', 'label' => $this->l('Live mode'), 'name' => 'CARRIERONORDER_LIVE_MODE', 'is_bool' => true, 'desc' => $this->l('Use this module in live mode'), 'values' => array(array('id' => 'active_on', 'value' => true, 'label' => $this->l('Enabled')), array('id' => 'active_off', 'value' => false, 'label' => $this->l('Disabled'))),), array('col' => 3, 'type' => 'text', 'prefix' => '<i class="icon icon-envelope"></i>', 'desc' => $this->l('Enter a valid email address'), 'name' => 'CARRIERONORDER_ACCOUNT_EMAIL', 'label' => $this->l('Email'),), array('type' => 'password', 'name' => 'CARRIERONORDER_ACCOUNT_PASSWORD', 'label' => $this->l('Password'),),), 'submit' => array('title' => $this->l('Save'),),),);
    }
    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array('CARRIERONORDER_LIVE_MODE' => Configuration::get('CARRIERONORDER_LIVE_MODE', true), 'CARRIERONORDER_ACCOUNT_EMAIL' => Configuration::get('CARRIERONORDER_ACCOUNT_EMAIL', 'contact@prestashop.com'), 'CARRIERONORDER_ACCOUNT_PASSWORD' => Configuration::get('CARRIERONORDER_ACCOUNT_PASSWORD', null),);
    }
    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }
    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
    public function hookActionOrderGridQueryBuilderModifier(array $params)
    {
        /*CARRIER*/
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];
        /** @var CustomerFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];
        $searchQueryBuilder->addSelect(
            'IF(wcm.`name` IS NULL,0,wcm.`name`) AS `cname`'
        );
        $searchQueryBuilder->leftJoin(
            'o', '`'.pSQL(_DB_PREFIX_).'carrier`', 'wcm', 'wcm.`id_carrier` = o.`id_carrier`'
        );
        if ('cname' === $searchCriteria->getOrderBy()) {
            $searchQueryBuilder->orderBy('wcm.`name`', $searchCriteria->getOrderWay());
        }
        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if($filterValue != null || $filterValue != '') {
            if ('cname' === $filterName) {
                $searchQueryBuilder->andWhere('wcm.`name` = :cname');
                $searchQueryBuilder->setParameter('cname', $filterValue);
                if (!$filterValue) {
                    $searchQueryBuilder->orWhere('wcm.`name` IS NULL');
                }
            }
        }
        }
        /*EMAIL
        $searchQueryBuilder = $params['search_query_builder'];
     
        $searchCriteria = $params['search_criteria'];
        $searchQueryBuilder->addSelect(
            'IF(ecm.`email` IS NULL,0,ecm.`email`) AS `email`'
        );

        $searchQueryBuilder->leftJoin(
            'o',
            '`' . pSQL(_DB_PREFIX_) . 'customer`',
            'ecm',
            'ecm.`id_customer` = o.`id_customer`'
        );

        if ('email' === $searchCriteria->getOrderBy()) {
            $searchQueryBuilder->orderBy('ecm.`email`', $searchCriteria->getOrderWay());
        }

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('email' === $filterName) {
                $searchQueryBuilder->andWhere('ecm.`email` = :email');
                $searchQueryBuilder->setParameter('email', $filterValue);

                if (!$filterValue) {
                    $searchQueryBuilder->orWhere('ecm.`email` IS NULL');
                }
            }
        }*/
    }
    public static function getCarrier()
    {
        $optionscon = null;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)
                    ->Executes(
                        '
							SELECT name
							FROM `'._DB_PREFIX_.'carrier` group by `name`
							'
                    );
        $carr = array();
        $carr[''] = '';
        foreach (Carrier::getCarriers(Configuration::get('PS_LANG_DEFAULT'), null, null, null, null, 5) as $carrier) {
            $carr[$carrier['name']] = $carrier['name'];
            $carr[$carrier['name']] = $carrier['name'];
        }
        return $carr;
    }
    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        $definition = $params['definition'];
        $translator = $this->getTranslator();
        /*carrier*/
        $definition->getColumns()
                   ->addAfter(
                       'reference', (new DataColumn('cname'))->setName($translator->trans('Carrier', [], 'Modules.Carrieronorder'))
                                                             ->setOptions(
                                                                 ['field' => 'cname',]
                                                             )
                   );
        // For search filter
        $definition->getFilters()
                   ->add(
                       (new Filter('cname', ChoiceType::class))->setAssociatedColumn('cname')
                                                               ->setTypeOptions(
                                                                   ['required' => false,
                                                                       'choices' => $this->getCarrier(),]
                                                               )
                   );
        /*email

        $definition
        ->getColumns()
        ->addAfter(
            'customer',
            (new DataColumn('email'))
                ->setName($translator->trans('Email', [], 'Modules.Carrieronorder'))
                ->setOptions([
                    'field' => 'email',

                ])
        )
        ;

        $definition->getFilters()->add(
        (new Filter('email', TextType::class))
        ->setAssociatedColumn('email')
        );       */
    }
}
