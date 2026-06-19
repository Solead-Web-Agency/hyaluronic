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
if (!defined('_PS_VERSION_')) { exit; }
/**
 * todo: Migrate all admin configuration > Cron (Scheduler) to this class
 * todo: Translate all labels
 * todo: Use AmazonAdminBuildCron[] to build cron
 */
class AmazonAdminConfigurationCron
{
    const CRON_ORDERS_IMPORT = 'cron_orders_import';
    const CRON_ORDERS_STATUS = 'cron_orders_status';
    const CRON_PRODUCTS_SYNC = 'cron_products_sync';

    const AFN = 'AFN';
    const MFN = 'MFN';

    const FORCE = 'force';

    public static $predefined_cron_params = array(
        self::CRON_ORDERS_IMPORT => array(
            'label' => 'Orders - Import',
            'params' => array(
                'channel' => array(
                    'label' => 'Channel',
                    'options' => array(
                        self::MFN => array(
                            'label' => self::MFN,
                            'is_selected' => 1
                        ),
                        self::AFN => array(
                            'label' => self::AFN,
                            'is_selected' => 1
                        )
                    ),
                    'at_least_one' => 1
                ),
                'status' => array(
                    'label' => 'Status',
                    'options' => array(
                        AmazonConstant::OI_ORDER_STATUS_PENDING => array(
                            'label' => AmazonConstant::OI_ORDER_STATUS_PENDING,
                            'is_selected' => 1
                        ),
                        AmazonConstant::OI_ORDER_STATUS_UNSHIPPED => array(
                            'label' => AmazonConstant::OI_ORDER_STATUS_UNSHIPPED,
                            'is_selected' => 1
                        ),
                        AmazonConstant::OI_ORDER_STATUS_PARTIALLY_SHIPPED => array(
                            'label' => AmazonConstant::OI_ORDER_STATUS_PARTIALLY_SHIPPED,
                            'is_selected' => 1
                        ),
                        AmazonConstant::OI_ORDER_STATUS_SHIPPED => array(
                            'label' => AmazonConstant::OI_ORDER_STATUS_SHIPPED,
                            'is_selected' => 0
                        ),
                    ),
                    'at_least_one' => 1
                ),
            )
        ),
        self::CRON_ORDERS_STATUS => array(
            'label' => 'Orders - Status',
            'params' => array(
                'force' => array(
                    'label' => 'Option',
                    'options' => array(
                        1 => array( // key = 1 to set it in query string
                            'label' => 'Force',
                            'is_selected' => 0
                        ),
                    ),
                    'at_least_one' => 0
                ),
            ),
        ),
        self::CRON_PRODUCTS_SYNC => array(
            'label' => 'Products - Synchronization',
            'class' => 'd-flex',
            'params' => array(
                'extended-datas' => array(
                    'label' => 'Options',   // Share label for all options in flex display
                    'options' => array(
                        1 => array( // key = 1 to set it in query string
                            'label' => 'Extended data',
                            'is_selected' => 0
                        ),
                    ),
                    'at_least_one' => 0
                ),
                'no-price' => array(
                    'options' => array(
                        1 => array( // key = 1 to set it in query string
                            'label' => 'No Price',
                            'is_selected' => 0
                        ),
                    ),
                    'at_least_one' => 0
                ),
            ),
        ),
    );

    /** @var Amazon */
    protected $module;

    protected $baseUrl;
    protected $moduleCustomContextValue = '';
    protected $imgPath;
    protected $moduleFeatures;

    // Late resolved properties
    protected $cronTokens = array();
    protected $additionalParams;

    public function __construct($module)
    {
        $this->module = $module;
        $this->baseUrl = AmazonTools::getHttpHost(true, true) . __PS_BASE_URI__ .
            basename(_PS_MODULE_DIR_) . '/' . $this->module->name . '/functions/';
        $this->moduleCustomContextValue = $this->module->moduleCustomContextValue;
        $this->imgPath = $this->module->images;
        $this->moduleFeatures = $this->module->moduleFeatures;
    }

    public function setCronToken($cronTokens)
    {
        $this->cronTokens = $cronTokens;

        return $this;
    }

    public function setAdditionalParams($cronAdditionalParams)
    {
        $this->additionalParams = $cronAdditionalParams;

        return $this;
    }

    public function getCronParams()
    {
        $saved_cron_params = AmazonConfiguration::get(AmazonConstant::CONFIG_CRON_PARAMS) ?: array();
        $result = AmazonAdminConfigurationCron::$predefined_cron_params;

        foreach ($saved_cron_params as $cron_name => $param_names) {
            $cronParamsAsQuery = array();
            foreach ($param_names as $param_name => $param_options) {
                // Backward compatibility, todo: Remove in the future
                if ($cron_name === AmazonAdminConfigurationCron::CRON_ORDERS_IMPORT
                    && !in_array($param_name, array('channel', 'status'))) {
                    continue;
                }

                $cronParamsAsQueryOfAnOption = array();
                foreach ($param_options as $option_name => $is_selected_option) {
                    // for showing checkboxes
                    // override predefined cron params by saved params for selected param options
                    $result[$cron_name]['params'][$param_name]['options'][$option_name]['is_selected'] = $is_selected_option;

                    // for URLs params
                    if ($is_selected_option) {
                        $cronParamsAsQueryOfAnOption[] = $option_name;
                    }
                }

                // We build HTTP query manually to prevent `,` being encode
                if (count($cronParamsAsQueryOfAnOption)) {
                    $cronParamsAsQuery[$param_name] = $param_name . '=' . implode(',', $cronParamsAsQueryOfAnOption);
                }
            }

            // We build HTTP query manually to prevent `,` being encode
            $result[$cron_name]['additional_params'] = count($cronParamsAsQuery) ?
                implode('&', $cronParamsAsQuery) : '';
        }

        // Backward compatibility, todo: Remove in the future
        if (isset(
            $result[self::CRON_ORDERS_STATUS]['params'],
            $result[self::CRON_ORDERS_STATUS]['params']['force'],
            $result[self::CRON_ORDERS_STATUS]['params']['force']['options'],
            $result[self::CRON_ORDERS_STATUS]['params']['force']['options']['force'])) {
            // `force` becomes `1`
            unset($result[self::CRON_ORDERS_STATUS]['params']['force']['options']['force']);
        }
//        pdt(json_encode($result));

        return $result;
    }

    public function tabCronAdditionalParams($marketPlaceIds, $activeMarketplaces)
    {
        $moduleConfig = $this->module->getConfig();
        $expertMode = $this->moduleFeatures->expert_mode;

        $cronViewParams = array(
            'vidr' => array(
                'enable' => isset($moduleConfig['vidr']) && $expertMode && $moduleConfig['vidr'],
                'jobs' => array(),
            ),
            'products' => array(
                'fix' => array(),
            ),
            'fba' => array(
                'status' => array(),
            ),
        );
        if (!is_array($marketPlaceIds) || !count($marketPlaceIds)) {
            return $cronViewParams;
        }

        foreach (AmazonTools::languages() as $language) {
            $id_lang = $language['id_lang'];
            $langIso = $language['iso_code'];
            $cronTokens = $this->cronTokens;
            $cronToken = isset($cronTokens[$id_lang]) ? $cronTokens[$id_lang] : array_shift($cronTokens);
            $flag = $this->imgPath . 'geo_flags/' . $this->module->geoFlag($id_lang) . '.gif';

            // For all active marketplaces
            if (!(isset($activeMarketplaces[$id_lang]) && (int)$activeMarketplaces[$id_lang])) {
                continue;
            }

            $spMkp = $marketPlaceIds[$id_lang];
            $lang = $this->module->getConfig()['regions'][$id_lang];

            // Each marketplace should have a dependent product sync / stock fix / FBA orders status
            $cronViewParams['products']['synch'][$id_lang] = $this->cronProductsSync(
                $cronToken, $spMkp, $lang, $id_lang, $langIso, $flag
            );
            if ($this->module->getConfig()['features']['expert_mode']) {
                $cronViewParams['products']['fix'][$id_lang] = $this->cronStockFix(
                    $cronToken, $spMkp, $id_lang, $langIso, $flag
                );
            }
            if ($this->module->getConfig()['fba_multichannel']) {
                $cronViewParams['fba']['status'][$id_lang] = $this->cronFbaOrdersStatus(
                    $cronToken, $spMkp, $id_lang, $langIso, $flag
                );
            }
            if ($this->module->getConfig()['features']['fba']) {
                $fbaStockCron = $this->cronFbaStockManager($cronToken, $spMkp, $id_lang, $langIso, $flag);
                if ($fbaStockCron) {
                    $cronViewParams['fba']['stocks'][$id_lang] = $fbaStockCron;       
                }
            }

            // For all European marketplaces
            if (AmazonTools::isEuropeMarketplaceId($spMkp)) {
                // VCS all jobs in 1 script
                $cronViewParams['vidr']['jobs'][$id_lang] = $this->cronVcs(
                    $cronToken, $spMkp, $id_lang, $langIso, $flag
                );
            }
        }

        $ordersCancelJobs = isset($moduleConfig['canceled_state']) && $moduleConfig['canceled_state'] && $this->moduleFeatures->cancel_orders ?
            $this->cronOrdersCancel($marketPlaceIds) : array();

        return array_merge_recursive(
            $cronViewParams,
            $this->cronOrdersImport($marketPlaceIds),
            $this->cronOrdersFulfillment($marketPlaceIds),
            $ordersCancelJobs
        );
    }

    protected function cronOrdersImport($marketPlaceIds)
    {
        $result = array();
        $moduleCronTokens = $this->cronTokens;
        $moduleCronToken = array_shift($moduleCronTokens);

        $url_cron_params = $this->additionalParams;
        $additionalUrlQuery = isset(
            $url_cron_params[AmazonAdminConfigurationCron::CRON_ORDERS_IMPORT],
            $url_cron_params[AmazonAdminConfigurationCron::CRON_ORDERS_IMPORT]['additional_params']
        ) ? "{$url_cron_params[AmazonAdminConfigurationCron::CRON_ORDERS_IMPORT]['additional_params']}" : '';

        if (is_array($marketPlaceIds) && count($marketPlaceIds)) {
            foreach ($marketPlaceIds as $marketPlaceId) {
                if ($marketPlaceId) {
                    $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($marketPlaceId);
                    $taskUrl = $this->baseUrl . 'orders/import.php?';
                    $urlQuery = array(
                        'cron_token' => $moduleCronToken,
                        'context_key' => $this->moduleCustomContextValue,
                    );

                    if ($spMkp->belongToUnifiedEU()) {
                        $region = $spMkp->getRegion();
                        $taskId = $region;
                        $flag = $this->imgPath . "geo_flags/$region.gif";
                        $urlQuery['sp_region'] = $region;
                    } else {
                        $taskId = $marketPlaceId;
                        $flag = $this->imgPath . "geo_flags/$marketPlaceId.gif";
                        $urlQuery['sp_mkp'] = $marketPlaceId;
                    }
                    $taskUrl .= http_build_query($urlQuery) . "&$additionalUrlQuery";

                    $cronBuilder = new AmazonAdminBuildCron(
                        null, null, $flag, $taskUrl,
                        $this->module->l('Orders Import'),
                        -1, AmazonAdminConfigurationCron::CRON_ORDERS_IMPORT
                    );
                    $result['orders']['import'][$taskId] = $cronBuilder->buildCronURL();
                }
            }
        }

        return $result;
    }

    protected function cronOrdersFulfillment($marketPlaceIds)
    {
        $result = array();
        $moduleCronTokens = $this->cronTokens;
        $moduleCronToken = array_shift($moduleCronTokens);

        $url_cron_params = $this->additionalParams;
        $additionalUrlQuery = isset(
            $url_cron_params[AmazonAdminConfigurationCron::CRON_ORDERS_STATUS],
            $url_cron_params[AmazonAdminConfigurationCron::CRON_ORDERS_STATUS]['additional_params']
        ) ? "{$url_cron_params[AmazonAdminConfigurationCron::CRON_ORDERS_STATUS]['additional_params']}" : '';

        if (is_array($marketPlaceIds) && count($marketPlaceIds)) {
            foreach ($marketPlaceIds as $marketPlaceId) {
                if ($marketPlaceId) {
                    $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($marketPlaceId);
                    $taskUrl = $this->baseUrl . 'status.php?';
                    $urlQuery = array(
                        'cron_token' => $moduleCronToken,
                        'context_key' => $this->moduleCustomContextValue,
                    );

                    if ($spMkp->belongToUnifiedEU()) {
                        $region = $spMkp->getRegion();
                        $taskId = $region;
                        $flag = $this->imgPath . "geo_flags/$region.gif";
                        $urlQuery['sp_region'] = $region;
                    } else {
                        $taskId = $marketPlaceId;
                        $flag = $this->imgPath . "geo_flags/$marketPlaceId.gif";
                        $urlQuery['sp_mkp'] = $marketPlaceId;
                    }

                    $taskUrl .= http_build_query($urlQuery) . "&$additionalUrlQuery";
                    $cronBuilder = new AmazonAdminBuildCron(
                        null, null, $flag, $taskUrl,
                        $this->module->l('Orders Status'),
                        4, self::CRON_ORDERS_STATUS
                    );
                    $result['orders']['status'][$taskId] = $cronBuilder->buildCronURL();
                }
            }
        }

        return $result;
    }

    protected function cronOrdersCancel($marketPlaceIds)
    {
        $result = array();
        $moduleCronTokens = $this->cronTokens;
        $moduleCronToken = array_shift($moduleCronTokens);

        if (is_array($marketPlaceIds) && count($marketPlaceIds)) {
            foreach ($marketPlaceIds as $marketPlaceId) {
                if ($marketPlaceId) {
                    $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($marketPlaceId);
                    $taskUrl = $this->baseUrl . 'canceled.php?';
                    $urlQuery = array(
                        'cron_token' => $moduleCronToken,
                        'context_key' => $this->moduleCustomContextValue,
                    );

                    if ($spMkp->belongToUnifiedEU()) {
                        $region = $spMkp->getRegion();
                        $taskId = $region;
                        $flag = $this->imgPath . "geo_flags/$region.gif";
                        $urlQuery['sp_region'] = $region;
                    } else {
                        $taskId = $marketPlaceId;
                        $flag = $this->imgPath . "geo_flags/$marketPlaceId.gif";
                        $urlQuery['sp_mkp'] = $marketPlaceId;
                    }

                    $taskUrl .= http_build_query($urlQuery);
                    $cronBuilder = new AmazonAdminBuildCron(
                        null, null, $flag, $taskUrl,
                        $this->module->l('Canceled Orders'), 2
                    );
                    $result['orders']['canceled'][$taskId] = $cronBuilder->buildCronURL();
                }
            }
        }

        return $result;
    }

    protected function cronStockFix($cronToken, $mkpId, $id_lang, $langIso, $flag)
    {
        $fixStockFullUrl = $this->baseUrl . 'check_stock.php?' . http_build_query(array(
                'cron_token' => $cronToken,
                'context_key' => $this->moduleCustomContextValue,
                'mkp' => $mkpId,
                'fix' => 1,
            ));
        $fixStockCron = new AmazonAdminBuildCron(
            $id_lang, $langIso, $flag, $fixStockFullUrl,
            $this->module->l('Fix Stock Issues'), 0
        );

        return $fixStockCron->buildCronURL();
    }

    protected function cronVcs($cronToken, $mkpId, $id_lang, $langIso, $flag)
    {
        $vcsFullUrl = $this->baseUrl . 'vidr/vidr.php?' . http_build_query(array(
                'cron_token' => $cronToken,
                'context_key' => $this->moduleCustomContextValue,
                'sp_mkp' => $mkpId,
            ));
        $vcsCron = new AmazonAdminBuildCron(
            $id_lang, $langIso, // Use lang iso instead of our definition code. Eg 'gb' instead of 'uk'
            $flag, $vcsFullUrl,
            $this->module->l('VCS jobs'), 30
        );

        return $vcsCron->buildCronURL();
    }

    protected function cronProductsSync($cronToken, $mkpId, $lang, $id_lang, $langIso, $flag)
    {
        $url_cron_params = $this->additionalParams;
        $additionalUrlQuery = isset(
            $url_cron_params[AmazonAdminConfigurationCron::CRON_PRODUCTS_SYNC],
            $url_cron_params[AmazonAdminConfigurationCron::CRON_PRODUCTS_SYNC]['additional_params']
        ) ? "{$url_cron_params[AmazonAdminConfigurationCron::CRON_PRODUCTS_SYNC]['additional_params']}" : '';

        $productsSyncUrl = $this->baseUrl . 'products.php?' . http_build_query(array(
                'cron_token' => $cronToken,
                'context_key' => $this->moduleCustomContextValue,
                'cron' => 1,
                'action' => 'update',
                'lang' => $lang,
                'sp_mkp' => $mkpId,
            )) . "&$additionalUrlQuery";
        $productsSyncCron = new AmazonAdminBuildCron(
            $id_lang, $langIso, $flag, $productsSyncUrl,
            $this->module->l('Synchronization'), 1,
            self::CRON_PRODUCTS_SYNC
        );

        return $productsSyncCron->buildCronURL();
    }

    protected function cronFbaOrdersStatus($cronToken, $mkpId, $id_lang, $langIso, $flag)
    {
        $fbaOSUrl = $this->baseUrl . 'fbaorder.php?' . http_build_query(array(
                'cron_token' => $cronToken,
                'context_key' => $this->moduleCustomContextValue,
                'cron' => 1,
                'action' => 'status',
                'sp_mkp' => $mkpId,
            ));
        $fbaOSCron = new AmazonAdminBuildCron(
            $id_lang, $langIso, $flag, $fbaOSUrl,
            $this->module->l('FBA Orders Status'), 2
        );

        return $fbaOSCron->buildCronURL();
    }

    protected function cronFbaStockManager($cronToken, $mkpId, $id_lang, $langIso, $flag)
    {
        $behavior = $this->module->getConfig()['fba_stock_behaviour'];
        $script = '';
        if ($behavior == AmazonConstant::FBA_STOCK_BEHAVIOUR_SWITCH) {
            $script = 'fbamanager';
        } elseif ($behavior == AmazonConstant::FBA_STOCK_BEHAVIOUR_SYNC) {
            $script = 'fbastocksynch';
        }

        if (!$script) {
            return array();
        }

        $fbaStockUrl = $this->baseUrl . $script . '.php?' . http_build_query(array(
                'cron_token' => $cronToken,
                'context_key' => $this->moduleCustomContextValue,
                'cron' => 1,
                'action' => 'stocks',
                'sp_mkp' => $mkpId,
            ));
        $fbaStockCron = new AmazonAdminBuildCron(
            $id_lang, $langIso, $flag, $fbaStockUrl,
            $this->module->l('FBA Manager'), 2
        );

        return $fbaStockCron->buildCronURL();
    }
}
