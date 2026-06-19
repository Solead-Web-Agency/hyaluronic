<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Suppose to manage all Amazon module hooks
 * Class AmazonHook
 */
class AmazonHookManager
{

    /**
     * @var Amazon
     */
    public $module;

    /**
     * @var Context
     */
    public $context;

    public $marketplace_columns = array();

    public function __construct($module, $context)
    {
        $this->module = $module;
        $this->context = $context;

        // Get marketplace columns
        $marketplace_columns = AmazonConfiguration::get(AmazonConstant::CONFIG_MARKETPLACE_COLUMNS);
        if (!$marketplace_columns) {
            $this->marketplace_columns = Amazon::$marketplace_columns;
        } else {
            if (is_array($marketplace_columns) && count($marketplace_columns) > 0) {
                foreach (Amazon::$marketplace_columns as $col_key => $col_name) {
                    if (!in_array($col_key, $marketplace_columns)) {
                        $this->marketplace_columns[$col_key] = $col_name;
                    }
                }
            }
        }
    }

    /**
     * Display Available at Amazon
     * @param $params
     *
     * @return string
     */
    public function displayProductButtons($params)
    {
        $associate_ids = AmazonConfiguration::get(Amazon::CONFIG_ASSOCIATE_ID);
        if (!is_array($associate_ids) || !count(array_filter($associate_ids))) {
            return '';
        }

        // Check input params
        if (!isset($params['product'])) {
            return '';
        }
        $product = $params['product'];
        if (is_object($product) && isset($product->id)) {
            $id_product = $product->id;
        } elseif (is_array($product) && isset($product['id'])) {
            $id_product = $product['id'];
        } else {
            return '';
        }

        if (isset($params['cart'])) {
            /** @var Cart $cart */
            $cart = $params['cart'];
            $id_lang = $cart->id_lang;
        } else {
            // Get lang on front end, not $this->context
            $id_lang = Context::getContext()->language->id;
        }

        $asin = $this->getProductAsin($id_product, $id_lang);

        if (is_string($asin) && !empty($asin)) {
            $product_link = $this->buildAmazonLink($asin, $id_lang);
            if ($product_link) {
                $this->context->smarty->assign(array(
                    'images_url' => $this->module->images,
                    'product_link' => $product_link,
                    'ps17x' => $this->module->ps17x,
                    'ps16x' => $this->module->ps16x,
                    'ps15x' => $this->module->ps15x,
                    'ps14x' => $this->module->ps14x,
                ));

                return $this->context->smarty->fetch($this->module->path . 'views/templates/front/available.tpl');
            }
        }

        return '';
    }

    /**
     * Get product asin, prioritize main product
     * @param $id_product
     * @param $id_lang
     *
     * @return bool
     */
    private function getProductAsin($id_product, $id_lang)
    {
        // Get product options by id_product_attribute priority (0 is the highest, to get main product)
        $product_options = AmazonProduct::getProductOptionsV4($id_product, null, $id_lang);
        if (is_array($product_options)) {
            usort($product_options, array($this, 'sortProductOptions'));
            foreach ($product_options as $product_option) {
                if (isset($product_option['asin1'])) {
                    return $product_option['asin1'];
                }
            }
        }

        return false;
    }

    /**
     * Sort product options by id_product_attribute
     * @param $option1
     * @param $option2
     *
     * @return int
     */
    protected function sortProductOptions($option1, $option2)
    {
        if (!isset($option1['id_product_attribute'], $option2['id_product_attribute'])) {
            return 0;
        }

        $id_attribute1 = (int)$option1['id_product_attribute'];
        $id_attribute2 = (int)$option2['id_product_attribute'];
        if ($id_attribute1 == $id_attribute2) {
            return 0;
        }

        return ($id_attribute1 < $id_attribute2) ? -1 : 1;
    }

    /**
     * Try to build an Amazon product link: https://www.amazon.{platform}/gp/product/asin/?[m=merchantID]&[tag=associateID]
     * @param $asin
     * @param $id_lang
     *
     * @return string
     */
    private function buildAmazonLink($asin, $id_lang)
    {
        $regions = AmazonConfiguration::get(Amazon::CONFIG_REGION);
        if (isset($regions[$id_lang]) && !empty($regions[$id_lang])) {
            $merchant_ids = AmazonConfiguration::get(Amazon::CONFIG_MERCHANT_ID);
            $associates = AmazonConfiguration::get(Amazon::CONFIG_ASSOCIATE_ID);

            $query_params = array();
            if (isset($merchant_ids[$id_lang]) && !empty($merchant_ids[$id_lang])) {
                $query_params['m'] = $merchant_ids[$id_lang];
            }
            if (isset($associates[$id_lang]) && !empty($associates[$id_lang])) {
                $query_params['tag'] = $associates[$id_lang];
            }
            $query_string = http_build_query($query_params);

            $domain = sprintf('https://www.amazon.%s', AmazonTools::idToDomain($id_lang));
            $product_link = "$domain/gp/product/$asin/" . ($query_string ? "?$query_string" : '');

            return $product_link;
        }

        return '';
    }

    public function displayAdminOrder($params)
    {
        require_once(_PS_MODULE_DIR_ . $this->module->name . '/classes/amazon.admin_order.class.php');
        $adminOrder = new AmazonAdminOrder();

        return $adminOrder->marketplaceOrderDisplay($params);
    }

    public function displayPDFInvoice($object)
    {
        require_once(_PS_MODULE_DIR_ . $this->module->name . '/classes/amazon.admin_order.class.php');
        require_once(_PS_MODULE_DIR_ . $this->module->name . '/classes/amazon.order.class.php');
        require_once(_PS_MODULE_DIR_ . $this->module->name . '/classes/amazon.order_item.class.php');

        /** @var OrderInvoice $order_invoice */
        $order_invoice = $object['object'];
        $id_order = $order_invoice->id_order;
        $customizationsByItems = array();

        $marketplace_order = AmazonOrder::getByOrderId($id_order);
        if (!$marketplace_order) {
            return '';
        }

        // Show product customization
        $order_detail = AmazonAdminOrder::getOrderDetailList($id_order);
        if (is_array($order_detail) && count($order_detail)) {
            foreach ($order_detail as $detail) {
                $item = AmazonOrderItem::getItem($detail['id_order_detail'], $id_order, $detail['product_id'],
                    $detail['product_attribute_id']);
                if ($item && is_array($item) && isset($item['sku'], $item['customization']) && is_array($item['customization'])
                    && isset($item['customization']['type'], $item['customization']['data'])
                    && is_array($item['customization']['data']) && count($item['customization']['data'])) {

                    $customizationType = $item['customization']['type'];
                    $customizationData = $item['customization']['data'];

                    // Todo: Legacy is compatible only, remove in future
                    if ($customizationType == 'legacy') {
                        if (count($customizationData) === 1) {
                            $customizationsByItems[] = array(
                                'type' => 'legacy',
                                'item_name' => $item['sku'],
                                'data' => $customizationData[0],
                            );
                        } else {
                            $i = 1;
                            foreach ($customizationData as $customization_sku) {
                                $customizationsByItems[] = array(
                                    'type' => 'legacy',
                                    'item_name' => $item['sku'] . '[' . $i . ']',
                                    'data' => $customization_sku,
                                );
                                $i++;
                            }
                        }
                    } elseif ($customizationType == 'complete') {
                        $customizationsByItems[] = array(
                            'type' => 'complete',
                            'item_name' => $item['sku'],
                            'item_qty' => $item['quantity'],
                            'data' => $customizationData,
                        );
                    }
                }
            }
        }

        return $this->context->smarty->assign(array(
            'amazon_order_id' => $marketplace_order['mp_order_id'],
            'customization_by_items' => $customizationsByItems,
        ))->fetch($this->module->path . 'views/templates/admin/admin_order/invoice_additional_info.tpl');
    }

    /*********************************** Admin Order listing custom column ********************************************/
    // > PS1.7.7: https://devdocs.prestashop.com/1.7/development/components/grid/tutorials/modify-grid-in-module/
    /**
     * PS1.5 <= version < PS1.7.7
     * Same code for all our modules. Also modify others if change (amazon, cdiscount)
     * This hook is called 2 time for each module, 1 for filter then 1 for listing
     * @param $params
     */
    public function actionAdminOrdersListingFieldsModifier($params)
    {
        $module = $this->module->name;
        $moduleTbl = _DB_PREFIX_ . 'marketplace_orders';
        $moduleTblAlias = "cs_mp_order_alias";

        if (isset($params['fields']) && !isset($params['select']) && !isset($params['join'])) {
            // Filter injection
            $this->pd("$module filter")->pd($params['fields']);

            if (count($this->marketplace_columns) > 0) {
                foreach ($this->marketplace_columns as $col_key => $col_name) {
                    if (!isset($params['fields'][$col_key])) {
                        $params['fields'][$col_key] = array(
                            'title' => $col_name,  // Old PS shows breadcrumb: `filter by ...`
                            'filter_key' => "$moduleTblAlias!{$col_key}",    // Adjust filter key in search form
                            'cs_integrated' => false,
                        );
                    }
                }
            }

        } elseif (isset($params['fields'], $params['select'], $params['join'])) {
            // Listing injection
            $this->pd("$module listing")->pd($params['fields']);

            $searchJoin = "LEFT JOIN `$moduleTbl` AS `$moduleTblAlias`";
            if (!preg_match("/{$searchJoin}/i", $params['join'])) {
                $params['join'] .= " $searchJoin ON (a.`id_order` = $moduleTblAlias.`id_order`)";
            }

            foreach ($this->marketplace_columns as $col_key => $col_name) {
                if (!isset($params['fields'][$col_key])
                    || !isset($params['fields'][$col_key]['cs_integrated']) || !$params['fields'][$col_key]['cs_integrated']) {
                    $params['fields'][$col_key] = array(
                        'title' => $col_name,
                        'align' => 'text-center',
                        'class' => 'fixed-width-xs',
                        'filter_key' => "$moduleTblAlias!{$col_key}",    // Adjust filter key in search form
                        'cs_integrated' => true,
                    );

                    $searchSelect = "`$moduleTblAlias`.`{$col_key}` AS `{$col_key}`";
                    if (!preg_match("/{$searchSelect}/i", $params['select'])) {
                        $params['select'] .= ", $searchSelect";
                    }
                }
            }

            // Disable Shipping deadline column prevent CDiscount (other module) create new if not exists
            if (!array_key_exists('latest_ship_date', $this->marketplace_columns)) {
                $params['fields']['latest_ship_date'] = array(
                    'title' => 'Shipping deadline',
                    'align' => 'hidden',
                    'class' => 'disable-column',
                    'filter_key' => "$moduleTblAlias!latest_ship_date",
                    'cs_integrated' => true,
                );
            }
            if (!array_key_exists('mp_order_id', $this->marketplace_columns)) {
                $params['fields']['mp_order_id'] = array(
                    'title' => 'Marketplace Order ID',
                    'align' => 'hidden',
                    'class' => 'disable-column',
                    'filter_key' => "$moduleTblAlias!mp_order_id",
                    'cs_integrated' => true,
                );
            }
        }

        $this->pd("$module params after resolve:")->pd($params);
    }

    public function actionOrderGridDefinitionModifier($params)
    {
        /** @var PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinition $definition */
        $definition = $params['definition'];

        /** @var PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection */
        $columns = $definition->getColumns();

        // Not possible to sort / search custom field at this moment. Waiting for updating from PS
        /** @var PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection $filters */
        $filters = $definition->getFilters();

        foreach ($this->marketplace_columns as $col_key => $col_name) {
            $mpOrderIdColumn = new \PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn($col_key);
            $mpOrderIdColumn->setName($col_name);
            $mpOrderIdColumn->setOptions(array('field' => $col_key));
            $mpOrderIdFilter = new PrestaShop\PrestaShop\Core\Grid\Filter\Filter(
                $col_key,
                'Symfony\Component\Form\Extension\Core\Type\TextType'
            );
            $mpOrderIdFilter->setTypeOptions(array(
                'required' => false,
                'attr' => array(
                    'placeholder' => $col_name,
                )
            ))->setAssociatedColumn($col_key);

            $columns->addBefore('actions', $mpOrderIdColumn);
            $filters->add($mpOrderIdFilter);
        }
    }

    public function actionOrderGridQueryBuilderModifier($params)
    {
        /** @var Doctrine\DBAL\Query\QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        $moduleTbl = _DB_PREFIX_ . 'marketplace_orders';
        $moduleTblAlias = 'cs_tbl_alias_amazon';

        $searchQueryBuilder->leftJoin('o', $moduleTbl, $moduleTblAlias, "o.id_order = $moduleTblAlias.id_order");
        foreach ($this->marketplace_columns as $columnName => $columnLabel) {
            $searchQueryBuilder->addSelect("$moduleTblAlias.$columnName AS $columnName");
        }

        if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
            $searchCriteria = $params['search_criteria'];
            foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
                if (array_key_exists($filterName, $this->marketplace_columns)) {
                    $searchQueryBuilder->andWhere($moduleTblAlias . '.' . $filterName . ' LIKE "%' . $filterValue . '%"');
                }
            }
        }
    }

    /******************************** End: Admin Order listing custom column ******************************************/

    public function addWebServiceResources()
    {
        return array(
            'marketplace_orders' => array(
                'description' => 'Marketplace orders (Common Services)',
                'specific_management' => true,
            ),
            'marketplace_order_details' => array(
                'description' => 'Marketplace order details (Common Services)',
                'specific_management' => true,
            ),
            'marketplace_product_options' => array(
                'description' => 'Amazon marketplace products options (Common Services)',
                'class' => 'AmazonProductOption', // The classname of your Entity
                'forbidden_method' => array() // Optional, if you want to forbid some methods
            ),
        );
    }

    public function manageFBAIncomingOrder($params)
    {
        if (!isset($params['order_history'], $params['order_history']->id_order)) {
            return;
        }
        $id_order = (int)$params['order_history']->id_order;

        $order_status = new OrderState((int)$params['order_history']->id_order_state);
        if (!Validate::isLoadedObject($order_status)) {
            return;
        }
        if (!$order_status->paid || $order_status->shipped || $order_status->delivery) {
            return;
        }

        // Only if FBA MultiChannel is active
        if (!Configuration::get(AmazonConstant::CONFIG_TOGGLE_FBA_MULTICHANNEL)
            || !Configuration::get(AmazonConstant::CONFIG_TOGGLE_FBA_MULTICHANNEL_AUTO)) {
            return;
        }

        //prevent=> Uncaught Exception: Kernel Container is not available
        AmazonTools::validateKernel();
        require_once(dirname(__FILE__) . '/../classes/amazon.multichannel.class.php');
        require_once(dirname(__FILE__) . '/../classes/amazon.mail.logger.class.php');

        $debug = (bool)Configuration::get('AMAZON_DEBUG_MODE');
        $send_email = (bool)Configuration::get('AMAZON_EMAIL');
        $message = null;

        if ($debug && $send_email) {
            $message .= sprintf('%s : %s' . Amazon::LF, AmazonTools::callingFunction(), 'Testing FBA Eligibility');
        }

        if (!($order = AmazonMultiChannel::isEligible($id_order))) {
            if ($debug && $send_email) {
                $message .= sprintf('%s : %s' . Amazon::LF, AmazonTools::callingFunction(), 'Order is not eligible');
                AmazonMailLogger::message($message);
            }
            return;
        }

        if (!isset($order->id_lang) || !$order->id_lang) {
            if ($debug && $send_email) {
                $message .= sprintf('%s : %s' . Amazon::LF, AmazonTools::callingFunction(), 'Missing ID Lang');
                AmazonMailLogger::message($message);
                return;
            }
        }

        // TODO: FBA Matrix to identify the closest fullfilment center
        $id_lang = $order->id_lang;
        // Eligibility Check passed, place a new FBA Order
        //
        $amazonMultiChannelOrder = new AmazonMultiChannel($id_order);
        if (!Validate::isLoadedObject($amazonMultiChannelOrder)) {
            if ($debug && $send_email) {
                $message .= sprintf('%s : %s' . Amazon::LF, AmazonTools::callingFunction(), 'Unable to read object');
                AmazonMailLogger::message($message);
            }
            // abnormal
            return;
        }
        // Module itself
        if (Tools::strtolower($amazonMultiChannelOrder->module) === $this->module->name) {
            return;
        }
        // Already ordered, shipped or canceled
        //
        if (Tools::strlen($amazonMultiChannelOrder->marketPlaceChannelStatus)) {
            return;
        }

        $spMkp = $amazonMultiChannelOrder->amazon_order_info->marketplace_id;
        $spConnector = AmazonSPConnectorPSMkp::initFromMarketplace($spMkp);
        $logger = new AmazonLogger(AmazonLogger::CHANNEL_FBA_MCF);
        if (!($AmazonFBAOrder = $amazonMultiChannelOrder->createFulfillmentOrder($id_lang, $spConnector, $logger))) {
            if ($debug && $send_email) {
                $message .= sprintf('%s : %s' . Amazon::LF, AmazonTools::callingFunction(),
                    'CreateFulfillmentOrder Failed');
                if (AmazonMultiChannel::$errors) {
                    $message .= print_r(AmazonMultiChannel::$errors, true);
                }
                $message .= print_r($AmazonFBAOrder, true);
                AmazonMailLogger::message($message);
            } elseif ($debug) {
                echo nl2br(print_r($AmazonFBAOrder, true));
            }
            return;
        }

        if ($send_email) {
            $mailOrderedItems = '';
            $mailAmzInfo = '';
            if (is_array($AmazonFBAOrder['Items'])) {
                foreach ($AmazonFBAOrder['Items'] as $Item) {
                    $mailOrderedItems .= sprintf('%d x %s - %s' . "\n", $Item['Quantity'], $Item['SKU'],
                        $Item['DisplayableComment']);
                }
                $mailOrderedItems = nl2br($mailOrderedItems);
                $mailAmzInfo = sprintf(
                    $this->module->l('Order #%s submitted on %s Amazon response ID: %s'),
                    $AmazonFBAOrder['DisplayableOrderId'], $AmazonFBAOrder['DisplayableOrderDateTime'],
                    $AmazonFBAOrder['Response']
                );
            }

            $mailtemplate = array(
                '{order}' => nl2br(
                    sprintf('%s : %s
                        %s : %s
                        %s : %s', $this->module->l('Order ID'), $id_order, $this->module->l('Date'),
                        $AmazonFBAOrder['DisplayableOrderDateTime'], $this->module->l('Shipping'),
                        $AmazonFBAOrder['ShippingSpeedCategory'])
                ),
                '{customer_address}' => nl2br(
                    sprintf('%s
                                    %s
                                    %s
                                    %s
                                    %s %s (%s)
                                    %s',
                        $AmazonFBAOrder['DestinationAddress']['Name'], $AmazonFBAOrder['DestinationAddress']['Line1'],
                        $AmazonFBAOrder['DestinationAddress']['Line2'], $AmazonFBAOrder['DestinationAddress']['Line3'],
                        $AmazonFBAOrder['DestinationAddress']['PostalCode'],
                        $AmazonFBAOrder['DestinationAddress']['City'],
                        $AmazonFBAOrder['DestinationAddress']['CountryCode'],
                        $AmazonFBAOrder['DestinationAddress']['PhoneNumber']
                    )
                ),
                '{ordered_items}' => $mailOrderedItems,
                '{amazon_info}' => $mailAmzInfo,
            );

            $email_address = Configuration::get('PS_SHOP_EMAIL');
            if ($debug) {
                $message .= sprintf('%s : %s' . Amazon::LF, AmazonTools::callingFunction(),
                    'Amazon FBA Order Complete');
                $message .= print_r($AmazonFBAOrder, true);
                AmazonMailLogger::message($message);
            }
            Mail::Send(
                $id_lang, // id_lang
                'fba_multichannel', // template
                $this->module->l('Amazon FBA: A new multichannel order has been processed'), // subject
                $mailtemplate, // templateVars
                $email_address, // to
                null, // To Name
                null, // From
                null, // From Name
                null, // Attachment
                null, // SMTP
                $this->module->path . 'mails/'
            );
        }

        if ($debug && !$send_email) {
            echo nl2br(print_r($AmazonFBAOrder, true));
        }
    }

    /**
     * @param $debug
     * @return AmazonHookManager
     */
    private function pd($debug)
    {
        if (Amazon::$debug_mode) {
            AmazonTools::p($debug);
        }

        return $this;
    }
}
