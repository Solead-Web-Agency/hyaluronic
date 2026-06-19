<?php
/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

abstract class BT_BaseProductXml
{
    /**
     * @var bool $bProductProcess : define if the product has well added
     */
    protected $bProductProcess = false;

    /**
     * @var array $aParams : array of params
     */
    protected $aParams = array();

    /**
     * @var obj $data : store currency / shipping / zone / carrier / product data into this obj as properties
     */
    protected $data = null;


    /**
     * @param array $aParams
     */
    protected function __construct(array $aParams = null)
    {
        $this->aParams = $aParams;
        $this->data = new stdClass();
    }

    /**
     * load products combination
     *
     * @param int $iProductId
     * @param bool $bExcludedProduct
     * @return array
     */
    abstract public function hasCombination($iProductId, $bExcludedProduct = false);


    /**
     * build product XML tags
     *
     * @return array
     */
    abstract public function buildDetailProductXml();


    /**
     * get images of one product or one combination
     *
     * @param obj $oProduct
     * @param int $iProdAttributeId
     * @return array
     */
    abstract public function getImages(Product $oProduct, $iProdAttributeId = null);


    /**
     * get supplier reference
     *
     * @param int $iProdId
     * @param int $iSupplierId
     * @param string $sSupplierRef
     * @param string $sProductRef
     * @param int $iProdAttributeId
     * @param string $sCombiSupplierRef
     * @param string $sCombiRef
     * @return string
     */
    abstract public function getSupplierReference($iProdId, $iSupplierId, $sSupplierRef = null, $sProductRef = null, $iProdAttributeId = null, $sCombiSupplierRef = null, $sCombiRef = null);


    /**
     * format the product name
     *
     * @param int $iAdvancedProdName
     * @param int $iAdvancedProdTitle
     * @param string $sProdName
     * @param string $sCatName
     * @param string $sManufacturerName
     * @param int $iLength
     * @param int $iProdAttrId
     * @param int $iLangId
     * @return string
     */
    abstract public function formatProductName($iAdvancedProdName, $iAdvancedProdTitle, $sProdName, $sCatName, $sManufacturerName, $iLength, $iProdAttrId = null, $iLangId = null, $sPrefix = null, $sSuffix = null);


    /**
     * store into the matching object the product and combination
     *
     * @param obj $oData
     * @param obj $oProduct
     * @param array $aCombination
     * @return array
     */
    public function setProductData(&$oData, $oProduct, $aCombination)
    {
        $this->data = $oData;
        $this->data->p = $oProduct;
        $this->data->c = $aCombination;
    }


    /**
     * define if the current product has been processed or refused for some not requirements matching
     *
     * @return bool
     */
    public function hasProductProcessed()
    {
        return $this->bProductProcess;
    }


    /**
     * build common product XML tags
     *
     * @param obj $oProduct
     * @param array $aCombination
     * @return true
     */
    public function buildProductXml()
    {
        require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');

        // reset the current step data obj
        $this->data->step = new stdClass();

        // define the product Id for reporting
        $this->data->step->attrId = !empty($this->data->c['id_product_attribute']) ? $this->data->c['id_product_attribute'] : 0;
        $this->data->step->id_reporting = $this->data->p->id . '_' . (!empty($this->data->c['id_product_attribute']) ? $this->data->c['id_product_attribute'] : 0);

        if (
            !isset($this->data->p->available_for_order)
            || (isset($this->data->p->available_for_order) && $this->data->p->available_for_order == 1)
        ) {

            //Use case to build the product name with the new option
            $sName = '';
            if (GMerchantCenterPro::$conf['GMCP_P_TITLE'] == 'meta' && !empty($this->data->p->meta_title)) {
                $sName = $this->data->p->meta_title;
            } elseif (GMerchantCenterPro::$conf['GMCP_P_TITLE'] == 'title') {
                $sName = BT_GmcProModuleTools::sanitizeProductProperty($this->data->p->name, $this->aParams['iLangId']);
            }

            // check qty , export type and the product name
            if (!empty($sName)) {

                $bExport = true;

                if (
                    $this->data->p->quantity <= 0
                    && GMerchantCenterPro::$conf['GMCP_EXPORT_OOS'] == 0
                ) {
                    $bExport = false;
                }
                // use case - out of stock product and we authorize to export but only products authorized for orders
                if (
                    $this->data->p->quantity <= 0
                    && GMerchantCenterPro::$conf['GMCP_EXPORT_OOS'] == 1
                    && GMerchantCenterPro::$conf['GMCP_EXPORT_PROD_OOS_ORDER'] == 1
                    && isset($this->data->p->out_of_stock)
                    && $this->data->p->out_of_stock != 1
                ) {
                    $bExport = false;
                }

                if ($bExport) {
                    // get  the product category object
                    $this->data->step->category = new Category((int) ($this->data->p->id_category_default), (int) $this->aParams['iLangId']);

                    // set the product ID
                    $this->data->step->id = $this->data->p->id;

                    // format product name
                    $this->data->step->name = $this->formatProductName(
                        GMerchantCenterPro::$conf['GMCP_ADV_PRODUCT_NAME'],
                        GMerchantCenterPro::$conf['GMCP_ADV_PROD_TITLE'],
                        $sName,
                        $this->data->step->category->name,
                        $this->data->p->manufacturer_name,
                        _GMCP_FEED_TITLE_LENGTH,
                        (!empty($this->data->c['id_product_attribute']) ? $this->data->c['id_product_attribute'] : null),
                        $this->aParams['iLangId'],
                        GMerchantCenterPro::$conf['GMCP_ADV_PROD_NAME_PREFIX'],
                        GMerchantCenterPro::$conf['GMCP_ADV_PROD_NAME_SUFFIX']
                    );

                    // use case export title with brands in suffix
                    if (
                        GMerchantCenterPro::$conf['GMCP_ADV_PRODUCT_NAME'] != 0
                        && Tools::strlen($sName) >= _GMCP_FEED_TITLE_LENGTH
                    ) {
                        BT_GmcProReporting::create()->set('title_length', array('productId' => $this->data->step->id_reporting));
                    }

                    $this->data->p->description_short = BT_GmcProModuleTools::sanitizeProductProperty($this->data->p->description_short, $this->aParams['iLangId']);
                    $this->data->p->description = BT_GmcProModuleTools::sanitizeProductProperty($this->data->p->description, $this->aParams['iLangId']);
                    $this->data->p->meta_description = BT_GmcProModuleTools::sanitizeProductProperty($this->data->p->meta_description, $this->aParams['iLangId']);

                    // set product description
                    $this->data->step->desc = $this->getProductDesc($this->data->p->description_short, $this->data->p->description, $this->data->p->meta_description);

                    // use case - reporting if product has no description as the merchant selected as type option
                    if (empty($this->data->step->desc)) {
                        BT_GmcProReporting::create()->set('description', array('productId' => $this->data->step->id_reporting));
                        return false;
                    }

                    // set product URL
                    $this->data->step->url = BT_GmcProModuleTools::getProductLink($this->data->p, $this->aParams['iLangId'], $this->data->step->category->link_rewrite);

                    // use case - reporting if product has no valid URL
                    if (empty($this->data->step->url)) {
                        BT_GmcProReporting::create()->set('link', array('productId' => $this->data->step->id_reporting));
                        return false;
                    }

                    $this->data->step->url_default = $this->data->step->url;

                    // format the current URL with currency or Google campaign parameters
                    if (!empty(GMerchantCenterPro::$conf['GMCP_ADD_CURRENCY'])) {
                        $this->data->step->url .= (strpos($this->data->step->url, '?') !== false) ? '&SubmitCurrency=1&id_currency=' . (int) $this->data->currencyId : '?SubmitCurrency=1&id_currency=' . (int) $this->data->currencyId;
                    }
                    if (!empty(GMerchantCenterPro::$conf['GMCP_UTM_CAMPAIGN'])) {
                        $this->data->step->url .= (strpos($this->data->step->url, '?') !== false) ? '&utm_campaign=' . GMerchantCenterPro::$conf['GMCP_UTM_CAMPAIGN'] : '?utm_campaign=' . GMerchantCenterPro::$conf['GMCP_UTM_CAMPAIGN'];
                    }
                    if (!empty(GMerchantCenterPro::$conf['GMCP_UTM_SOURCE'])) {
                        $this->data->step->url .= (strpos($this->data->step->url, '?') !== false) ? '&utm_source=' . GMerchantCenterPro::$conf['GMCP_UTM_SOURCE'] : '?utm_source=' . GMerchantCenterPro::$conf['GMCP_UTM_SOURCE'];
                    }
                    if (!empty(GMerchantCenterPro::$conf['GMCP_UTM_MEDIUM'])) {
                        $this->data->step->url .= (strpos($this->data->step->url, '?') !== false) ? '&utm_medium=' . GMerchantCenterPro::$conf['GMCP_UTM_MEDIUM'] : '?utm_medium=' . GMerchantCenterPro::$conf['GMCP_UTM_MEDIUM'];
                    }

                    // set the product path
                    $this->data->step->path = $this->getProductPath($this->data->p->id_category_default, $this->aParams['iLangId']);

                    // get the condition
                    $this->data->step->condition = BT_GmcProModuleTools::getProductCondition((!empty($this->data->p->condition) ? $this->data->p->condition : null));

                    // execute the detail part
                    if ($this->buildDetailProductXml()) {
                        // get the default image
                        $this->data->step->image_link = BT_GmcProModuleTools::getProductImage($this->data->p, (!empty(GMerchantCenterPro::$conf['GMCP_IMG_SIZE']) ? GMerchantCenterPro::$conf['GMCP_IMG_SIZE'] : null), $this->data->step->images['image'], GMerchantCenterPro::$conf['GMCP_LINK']);

                        // use case - reporting if product has no cover image
                        if (empty($this->data->step->image_link)) {
                            BT_GmcProReporting::create()->set('image_link', array('productId' => $this->data->step->id_reporting));
                            return false;
                        }

                        if (!empty(GMerchantCenterPro::$conf['GMCP_ADD_IMAGES'])) {
                            // get additional images
                            if (!empty($this->data->step->images['others']) && is_array($this->data->step->images['others'])) {
                                $this->data->step->additional_images = array();

                                foreach ($this->data->step->images['others'] as $aImage) {
                                    $sExtraImgLink = BT_GmcProModuleTools::getProductImage($this->data->p, (!empty(GMerchantCenterPro::$conf['GMCP_IMG_SIZE']) ? GMerchantCenterPro::$conf['GMCP_IMG_SIZE'] : null), $aImage, GMerchantCenterPro::$conf['GMCP_LINK']);
                                    if (!empty($sExtraImgLink)) {
                                        $this->data->step->additional_images[] = $sExtraImgLink;
                                    }
                                }
                            }
                        }

                        // get Google Categories
                        $this->data->step->google_cat = BT_GmcProModuleDao::getGoogleCategories($this->aParams['iShopId'], $this->data->p->id_category_default, $GLOBALS['GMCP_AVAILABLE_COUNTRIES'][$this->aParams['sLangIso']][$this->aParams['sCountryIso']]['taxonomy']);

                        //get all product categories
                        $oProduct = new Product($this->data->p->id);

                        $aProductCategories = $oProduct->getCategories($this->data->p->id);

                        // get google adwords tags
                        $this->data->step->google_tags = BT_GmcProCustomLabelDao::getTagsForXml($this->data->p->id, $aProductCategories, $this->data->p->id_manufacturer, $this->data->p->id_supplier, (int) $this->aParams['iLangId']);

                        // get features by category
                        $this->data->step->features = BT_GmcProModuleDao::getFeaturesByCategory($this->data->p->id_category_default, GMerchantCenterPro::$iShopId);

                        // get color options
                        $this->data->step->colors = $this->getColorOptions($this->data->p->id, (int) $this->aParams['iLangId'], (!empty($this->data->c['id_product_attribute']) ? $this->data->c['id_product_attribute'] : 0));

                        // get size options
                        $this->data->step->sizes = $this->getSizeOptions($this->data->p->id, (int) $this->aParams['iLangId'], (!empty($this->data->c['id_product_attribute']) ? $this->data->c['id_product_attribute'] : 0));

                        // get material options
                        if (
                            !empty(GMerchantCenterPro::$conf['GMCP_INC_MATER'])
                            && !empty($this->data->step->features['material'])
                            && $this->data->step->features['material'] <= _GMCP_MATERIAL_LENGTH
                        ) {
                            $this->data->step->material = $this->getFeaturesOptions($this->data->p->id, $this->data->step->features['material'], (int) $this->aParams['iLangId']);
                        }

                        // get pattern options
                        if (
                            !empty(GMerchantCenterPro::$conf['GMCP_INC_PATT'])
                            && !empty($this->data->step->features['pattern'])
                        ) {
                            $this->data->step->pattern = $this->getFeaturesOptions($this->data->p->id, $this->data->step->features['pattern'], (int) $this->aParams['iLangId']);
                        }

                        //get energy class
                        if (
                            !empty(GMerchantCenterPro::$conf['GMCP_INC_ENERGY'])
                            // The 3 data have to be added
                            && !empty($this->data->step->features['energy'])
                            && !empty($this->data->step->features['energy_min'])
                            && !empty($this->data->step->features['energy_max'])
                        ) {
                            $this->data->step->energy = $this->getFeaturesOptions($this->data->p->id, $this->data->step->features['energy'], (int) $this->aParams['iLangId']);

                            $this->data->step->energy_min = $this->getFeaturesOptions($this->data->p->id, $this->data->step->features['energy_min'], (int) $this->aParams['iLangId']);

                            $this->data->step->energy_max = $this->getFeaturesOptions($this->data->p->id, $this->data->step->features['energy_max'], (int) $this->aParams['iLangId']);
                        }

                        // get shipping label options
                        if (
                            !empty(GMerchantCenterPro::$conf['GMCP_INC_SHIPPING_LABEL'])
                            && !empty($this->data->step->features['shipping_label'])
                        ) {
                            $this->data->step->shipping_label = $this->getFeaturesOptions($this->data->p->id, $this->data->step->features['shipping_label'], (int) $this->aParams['iLangId']);
                        }

                        // get unit pricing measure
                        if (
                            !empty(GMerchantCenterPro::$conf['GMCP_INC_UNIT_PRICING'])
                            && !empty($this->data->step->features['unit_pricing_measure'])
                        ) {
                            $this->data->step->unit_pricing_measure = $this->getFeaturesOptions($this->data->p->id, $this->data->step->features['unit_pricing_measure'], (int) $this->aParams['iLangId']);
                        }

                        // get unit pricing measure
                        if (
                            !empty(GMerchantCenterPro::$conf['GMCP_INC_B_UNIT_PRICING'])
                            && !empty($this->data->step->features['base_unit_pricing_measure'])
                        ) {
                            $this->data->step->base_unit_pricing_measure = $this->getFeaturesOptions($this->data->p->id, $this->data->step->features['base_unit_pricing_measure'], (int) $this->aParams['iLangId']);
                        }

                        return true;
                    }
                } // use case - reporting if product was excluded due to no_stock
                else {
                    BT_GmcProReporting::create()->set('_no_export_no_stock', array('productId' => $this->data->step->id_reporting));
                }
            } // use case - reporting if product was excluded due to the empty name
            else {
                BT_GmcProReporting::create()->set('_no_product_name', array('productId' => $this->data->step->id_reporting));
            }
        } else {
            BT_GmcProReporting::create()->set('_no_available_for_order', array('productId' => $this->data->step->id_reporting));
        }
        return false;
    }

    /**
     * build XML tags from the current stored data
     *
     * @return true
     */
    public function buildXmlTags()
    {
        // set vars
        $sContent = '';
        $aReporting = array();

        $this->bProductProcess = false;

        $iAllowOrderOutOfStock = StockAvailable::outOfStock($this->data->p->id);

        // check if data are ok - 4 data are mandatory to fill the product out
        if (
            !empty($this->data->step)
            && !empty($this->data->step->name)
            && !empty($this->data->step->desc)
            && !empty($this->data->step->url)
            && !empty($this->data->step->image_link)
            && $this->data->step->visibility != 'none'
        ) {

            $sContent .= "\t" . '<item>' . "\n";

            if (empty(GMerchantCenterPro::$conf['GMCP_SIMPLE_PROD_ID'])) {
                $sContent .= "\t\t" . '<g:id>' . Tools::strtoupper(GMerchantCenterPro::$conf['GMCP_ID_PREFIX']) . $this->aParams['sCountryIso'] . $this->data->step->id . '</g:id>' . "\n";
            } else {
                $sContent .= "\t\t" . '<g:id>' . $this->data->step->id . '</g:id>' . "\n";
            }

            // ****** PRODUCT NAME ******
            if (!empty($this->data->step->name)) {
                $sContent .= "\t\t" . '<title><![CDATA[' . BT_GmcProModuleTools::cleanUp($this->data->step->name) . ']]></title>' . "\n";
            } else {
                $aReporting[] = 'title';
            }

            // ****** DESCRIPTION ******
            if (!empty($this->data->step->desc)) {
                $sContent .= "\t\t" . '<description><![CDATA[' . $this->data->step->desc . ']]></description>' . "\n";
            } else {
                $aReporting[] = 'description';
            }

            // ****** PRODUCT LINK ******
            if (!empty($this->data->step->url)) {
                $sContent .= "\t\t" . '<link><![CDATA[' . $this->data->step->url . ']]></link>' . "\n";
            } else {
                $aReporting[] = 'link';
            }

            // ****** ADS REDIRECT to handle utm_content={campaignid ******
            if (!empty(GMerchantCenterPro::$conf['GMCP_UTM_CONTENT'])) {
                $sCampaignIdUrl = $this->data->step->url .= (strpos($this->data->step->url, '?') !== false) ? '&utm_content={campaignid}' : '?utm_content={campaignid}';
                $sContent .= "\t\t" . '<g:ads_redirect><![CDATA[' .  $sCampaignIdUrl . ']]></g:ads_redirect>' . "\n";
            }

            // ****** IMAGE LINK ******
            if (!empty($this->data->step->image_link)) {
                $sContent .= "\t\t" . '<g:image_link><![CDATA[' . $this->data->step->image_link . ']]></g:image_link>' . "\n";
            } else {
                $aReporting[] = 'image_link';
            }

            // ****** PRODUCT CONDITION ******
            $sContent .= "\t\t" . '<g:condition>' . $this->data->step->condition . '</g:condition>' . "\n";

            // ****** ADDITIONAL IMAGES ******
            if (!empty($this->data->step->additional_images)) {
                foreach ($this->data->step->additional_images as $sImgLink) {
                    $sContent .= "\t\t" . '<g:additional_image_link><![CDATA[' . $sImgLink . ']]></g:additional_image_link>' . "\n";
                }
            }

            // ****** PRODUCT TYPE ******
            if (!empty($this->data->step->path)) {
                $sContent .= "\t\t" . '<g:product_type><![CDATA[' . $this->data->step->path . ']]></g:product_type>' . "\n";
            } else {
                $aReporting[] = 'product_type';
            }

            // ****** GOOGLE MATCHING CATEGORY ******
            if (!empty($this->data->step->google_cat['txt_taxonomy'])) {
                $sContent .= "\t\t" . '<g:google_product_category><![CDATA[' . $this->data->step->google_cat['txt_taxonomy'] . ']]></g:google_product_category>' . "\n";
            } else {
                $aReporting[] = 'google_product_category';
            }

            // ****** GOOGLE CUSTOM LABELS ******
            if (!empty($this->data->step->google_tags['custom_label'])) {
                $iCounter = 0;
                foreach ($this->data->step->google_tags['custom_label'] as $sLabel) {
                    if ($iCounter < _GMCP_CUSTOM_LABEL_LIMIT) {
                        $sContent .= "\t\t" . '<g:custom_label_' . $iCounter . '><![CDATA[' . $sLabel . ']]></g:custom_label_' . $iCounter . '>' . "\n";
                        $iCounter++;
                    }
                }
            }

            // Override the stock only if GSA is activated / percentage of stock is set and under 100 %
            if (!empty(Configuration::get('GMCP_OAUTH')) && !empty(Configuration::get('GMCP_GSA_STOCK')) && Configuration::get('GMCP_GSA_STOCK') < 100) {
                $this->data->step->quantity = $this->getStockPercent($this->data->step->quantity);
            }

            // ****** PRODUCT AVAILABILITY ******
            if (GMerchantCenterPro::$conf['GMCP_INC_STOCK'] == 2) {
                if (empty($this->data->step->availabilty_date)) {
                    if ($this->data->step->quantity > 0) {
                        $sContent .= "\t\t" . '<g:sell_on_google_quantity>' . (int) $this->data->step->quantity . '</g:sell_on_google_quantity>' . "\n";
                        $sContent .= "\t\t" . '<g:availability>in stock</g:availability>' . "\n"; 
                    } else {
                        $sContent .= "\t\t" . '<g:availability>in stock</g:availability>' . "\n"; 
                    }
                } else {
                    if ($this->data->step->quantity > 0) {
                        $sContent .= "\t\t" . '<g:sell_on_google_quantity>' . (int) $this->data->step->quantity . '</g:sell_on_google_quantity>' . "\n";
                        $sContent .= "\t\t" . '<g:availability>in stock</g:availability>' . "\n";
                    } else {
                        if ($this->data->p->out_of_stock == 0 || Configuration::get('PS_ORDER_OUT_OF_STOCK') == 0) {
                            $sContent .= "\t\t" . '<g:availability>preorder</g:availability>' . "\n";
                        } else {
                            $sContent .= "\t\t" . '<g:availability>backorder</g:availability>' . "\n";
                        }
                      
                        $sContent .= "\t\t" . '<g:availability_date>'  . BT_GmcProModuleTools::formatDateISO8601($this->data->step->availabilty_date) . '</g:availability_date>' . "\n";
                    }
                }
            } else {
                if (empty($this->data->step->availabilty_date)) {
                    if ($this->data->step->quantity > 0) {
                        $sContent .= "\t\t" . '<g:sell_on_google_quantity>' . (int) $this->data->step->quantity . '</g:sell_on_google_quantity>' . "\n";
                        $sContent .= "\t\t" . '<g:availability>in stock</g:availability>' . "\n";
                    } else {
                        $sContent .= "\t\t" . '<g:availability>out of stock</g:availability>' . "\n";
                    }
                } else {
                    if ($this->data->step->quantity > 0) {
                        $sContent .= "\t\t" . '<g:sell_on_google_quantity>' . (int) $this->data->step->quantity . '</g:sell_on_google_quantity>' . "\n";
                        $sContent .= "\t\t" . '<g:availability>in stock</g:availability>' . "\n";
                    } else {
                        if ($this->data->p->out_of_stock == 0 || Configuration::get('PS_ORDER_OUT_OF_STOCK') == 0) {
                            $sContent .= "\t\t" . '<g:availability>preorder</g:availability>' . "\n";
                        } else {
                            $sContent .= "\t\t" . '<g:availability>backorder</g:availability>' . "\n";
                        }
                        $sContent .= "\t\t" . '<g:availability_date>'  . BT_GmcProModuleTools::formatDateISO8601($this->data->step->availabilty_date) . '</g:availability_date>' . "\n";
                    }
                }
            }

            // ****** PRODUCT PRICES ******
            if ($this->data->step->price_raw < $this->data->step->price_raw_no_discount) {
                $sContent .= "\t\t" . '<g:price>' . $this->data->step->price_no_discount . '</g:price>' . "\n"
                    . "\t\t" . '<g:sale_price>' . $this->data->step->price . '</g:sale_price>' . "\n";
                if (
                    $this->data->step->specificPriceFrom != '0000-00-00 00:00:00'
                    && ($this->data->step->specificPriceTo) != '0000-00-00 00:00:00'
                ) {
                    $sContent .= "\t\t" . '<g:sale_price_effective_date>' . BT_GmcProModuleTools::formatDateISO8601($this->data->step->specificPriceFrom) . '/' . BT_GmcProModuleTools::formatDateISO8601($this->data->step->specificPriceTo) . '</g:sale_price_effective_date>' . "\n";
                }
            } else {
                $sContent .= "\t\t" . '<g:price>' . $this->data->step->price . '</g:price>' . "\n";
            }

            if (!empty($this->data->step->cost_price) && !empty(GMerchantCenterPro::$conf['GMCP_INC_COST'])) {
                $sContent .= "\t\t" . '<g:cost_of_goods_sold>' . $this->data->step->cost_price . '</g:cost_of_goods_sold>' . "\n";
            }

            if (!empty($this->data->multipack)) {
                $sContent .= "\t\t" . '<g:multipack>' . $this->data->multipack . '</g:multipack>' . "\n";
            }

            // ****** UNIQUE PRODUCT IDENTIFIERS ******
            // ****** GTIN - EAN13 AND UPC ******
            if (!empty($this->data->step->gtin)) {
                $sContent .= "\t\t" . '<g:gtin>' . $this->data->step->gtin . '</g:gtin>' . "\n";
            } else {
                $aReporting[] = 'gtin';
            }

            // ****** MANUFACTURER ******
            if (!empty($this->data->p->manufacturer_name)) {
                $sContent .= "\t\t" . '<g:brand><![CDATA[' . BT_GmcProModuleTools::cleanUp($this->data->p->manufacturer_name) . ']]></g:brand>' . "\n";
            } else {
                $aReporting[] = 'brand';
            }

            // ****** MPN ******
            if (!empty($this->data->step->mpn)) {
                $sContent .= "\t\t" . '<g:mpn><![CDATA[' . $this->data->step->mpn . ']]></g:mpn>' . "\n";
            } elseif (empty(GMerchantCenterPro::$conf['GMCP_INC_ID_EXISTS'])) {
                $aReporting[] = 'mpn';
            }

            // ****** IDENTIFIER EXISTS ******
            if (
                empty($this->data->step->gtin)
                && (empty($this->data->step->mpn)
                    || empty($this->data->p->manufacturer_name) || !empty(GMerchantCenterPro::$conf['GMCP_FORCE_IDENTIFIER']))
            ) {
                if (
                    empty($this->data->p->cache_is_pack)
                    || (BT_GmcProModuleTools::isInstalled('pm_advancedpack')
                        && AdvancedPack::isValidPack($this->data->p->id))
                ) {
                    $sContent .= "\t\t" . '<g:identifier_exists>TRUE</g:identifier_exists>' . "\n";
                } else {
                    $sContent .= "\t\t" . '<g:identifier_exists>FALSE</g:identifier_exists>' . "\n";
                }
              
            }

            // ****** APPAREL PRODUCTS ******
            // ****** TAG ADULT ******
            if (
                !empty($this->data->step->features['adult'])
                && !empty(GMerchantCenterPro::$conf['GMCP_INC_TAG_ADULT'])
            ) {
                $sContent .= "\t\t" . '<g:adult><![CDATA[' . Tools::stripslashes(Tools::strtoupper($this->data->step->features['adult'])) . ']]></g:adult>' . "\n";
            }

            // ****** TAG GENDER ******
            if (
                !empty($this->data->step->features['gender'])
                && !empty(GMerchantCenterPro::$conf['GMCP_INC_GEND'])
            ) {
                $sContent .= "\t\t" . '<g:gender><![CDATA[' . Tools::stripslashes($this->data->step->features['gender']) . ']]></g:gender>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_GEND'])) {
                $aReporting[] = 'gender';
            }

            // ****** TAG AGE GROUP ******
            if (
                !empty($this->data->step->features['agegroup'])
                && !empty(GMerchantCenterPro::$conf['GMCP_INC_AGE'])
            ) {
                $sContent .= "\t\t" . '<g:age_group><![CDATA[' . Tools::stripslashes($this->data->step->features['agegroup']) . ']]></g:age_group>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_AGE'])) {
                $aReporting[] = 'age_group';
            }

            // ****** TAG SIZE TYPE ******
            if (
                !empty($this->data->step->features['sizeType'])
                && !empty(GMerchantCenterPro::$conf['GMCP_SIZE_TYPE'])
            ) {
                $sContent .= "\t\t" . '<g:size_type><![CDATA[' . Tools::stripslashes($this->data->step->features['sizeType']) . ']]></g:size_type>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_SIZE_TYPE'])) {
                $aReporting[] = 'sizeType';
            }

            // ****** TAG SIZE TYPE ******
            if (
                !empty($this->data->step->features['sizeSystem'])
                && !empty(GMerchantCenterPro::$conf['GMCP_SIZE_SYSTEM'])
            ) {
                $sContent .= "\t\t" . '<g:size_system><![CDATA[' . Tools::stripslashes($this->data->step->features['sizeSystem']) . ']]></g:size_system>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_SIZE_SYSTEM'])) {
                $aReporting[] = 'sizeSystem';
            }

            // ****** TAG COLOR ******
            if (
                !empty($this->data->step->colors)
                && is_array($this->data->step->colors)
            ) {
                foreach ($this->data->step->colors as $aColor) {
                    $sContent .= "\t\t" . '<g:color><![CDATA[' . Tools::stripslashes($aColor['name']) . ']]></g:color>' . "\n";
                }
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_COLOR'])) {
                $aReporting[] = 'color';
            }

            // ****** TAG SIZE ******
            if (
                !empty($this->data->step->sizes)
                && is_array($this->data->step->sizes)
            ) {
                foreach ($this->data->step->sizes as $aSize) {
                    $sContent .= "\t\t" . '<g:size><![CDATA[' . Tools::stripslashes($aSize['name']) . ']]></g:size>' . "\n";
                }
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_SIZE'])) {
                $aReporting[] = 'size';
            }

            // ****** VARIANTS PRODUCTS ******
            // ****** TAG MATERIAL ******
            if (!empty($this->data->step->material)) {
                $sContent .= "\t\t" . '<g:material><![CDATA[' . $this->data->step->material . ']]></g:material>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_MATER'])) {
                $aReporting[] = 'material';
            }

            // ****** TAG PATTERN ******
            if (!empty($this->data->step->pattern)) {
                $sContent .= "\t\t" . '<g:pattern><![CDATA[' . $this->data->step->pattern . ']]></g:pattern>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_PATT'])) {
                $aReporting[] = 'pattern';
            }

            // ****** TAG ENERGY ******
            if (
                !empty($this->data->step->energy)
                && !empty($this->data->step->energy_min)
                && !empty($this->data->step->energy_max)
            ) {
                $sContent .= "\t\t" . '<g:energy_efficiency_class><![CDATA[' . $this->data->step->energy . ']]></g:energy_efficiency_class>' . "\n";
                $sContent .= "\t\t" . '<g:min_energy_efficiency_class><![CDATA[' . $this->data->step->energy_min . ']]></g:min_energy_efficiency_class>' . "\n";
                $sContent .= "\t\t" . '<g:max_energy_efficiency_class><![CDATA[' . $this->data->step->energy_max . ']]></g:max_energy_efficiency_class>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_ENERGY'])) {
                $aReporting[] = 'energy';
            }

            // ****** TAG SHIPPING LABEL ******
            if (!empty($this->data->step->shipping_label)) {
                $sContent .= "\t\t" . '<g:shipping_label><![CDATA[' . $this->data->step->shipping_label . ']]></g:shipping_label>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_SHIPPING_LABEL'])) {
                $aReporting[] = 'shipping_label';
            }

            // ****** TAG UNIT PRICE MEASURE LABEL ******
            if (!empty($this->data->step->unit_pricing_measure)) {
                $sContent .= "\t\t" . '<g:unit_pricing_measure><![CDATA[' . $this->data->step->unit_pricing_measure . ']]></g:unit_pricing_measure>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_SHIPPING_LABEL'])) {
                $aReporting[] = 'unit_pricing_measure';
            }

            // ****** TAG BASE UNIT PRICE MEASURE LABEL ******
            if (
                !empty($this->data->step->base_unit_pricing_measure)
                && !empty($this->data->step->unit_pricing_measure)
            ) {
                $sContent .= "\t\t" . '<g:unit_pricing_base_measure><![CDATA[' . $this->data->step->base_unit_pricing_measure . ']]></g:unit_pricing_base_measure>' . "\n";
            } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INC_SHIPPING_LABEL'])) {
                $aReporting[] = 'unit_pricing_base_measure';
            }

            // Use case for the excluded destination value
            if (
                !empty($this->data->step->features['excluded_destination'])
                && !empty(GMerchantCenterPro::$conf['GMCP_EXCLUDED_DEST'])
            ) {
                // Transform excluded destination to an array
                $aExcludedDest = explode(' ', $this->data->step->features['excluded_destination']);

                // Use case if is array we can handle the tag
                if (is_array($aExcludedDest)) {
                    // For each exclusion destination we set the tag
                    foreach ($aExcludedDest as $sDestination) {
                        $sContent .= "\t\t" . '<g:excluded_destination><![CDATA[' . Tools::stripslashes($GLOBALS['GMCP_EXCLUDED_DEST_VALUE'][$sDestination]) . ']]></g:excluded_destination>' . "\n";
                    }
                }
            }

            //Use case for the excluded country value
            if (
                !empty($this->data->step->features['excluded_country'])
                && !empty(GMerchantCenterPro::$conf['GMCP_EXCLUDED_COUNTRY'])
            ) {
                // Transform excluded country to an array
                $aExcludedCountry = explode(' ', $this->data->step->features['excluded_country']);

                // Use case if is array we can handle the tag
                if (is_array($aExcludedCountry)) {
                    // For each exclusion country we set the tag
                    foreach ($aExcludedCountry as $sCountry) {
                        $sContent .= "\t\t" . '<g:shopping_ads_excluded_country><![CDATA[' . Tools::stripslashes($sCountry) . ']]></g:shopping_ads_excluded_country>' . "\n";
                    }
                }
            }

            // handle the default pack from PS
            if (
                !empty($this->data->p->cache_is_pack)
                || (GMerchantCenterPro::$bAdvancedPack
                    && AdvancedPack::isValidPack($this->data->p->id))
            ) {
                $sContent .= "\t\t" . '<g:is_bundle>TRUE</g:is_bundle>' . "\n";
            }

            // ****** ITEM GROUP ID ******
            if (!empty($this->data->step->id_no_combo)) {
                if (empty(GMerchantCenterPro::$conf['GMCP_SIMPLE_PROD_ID'])) {
                    $sContent .= "\t\t" . '<g:item_group_id>' . Tools::strtoupper(GMerchantCenterPro::$conf['GMCP_ID_PREFIX']) . $this->aParams['sCountryIso'] . '-' . $this->data->step->id_no_combo . '</g:item_group_id>' . "\n";
                } else {
                    $sContent .= "\t\t" . '<g:item_group_id>' . $this->data->step->id_no_combo . '</g:item_group_id>' . "\n";
                }
            }

            // ****** TAX AND SHIPPING ******
            $sWeightUnit = Configuration::get('PS_WEIGHT_UNIT');
            if (!empty($this->data->step->weight) && !empty($sWeightUnit)) {
                if (in_array(Tools::strtolower($sWeightUnit), $GLOBALS['GMCP_WEIGHT_UNITS'])) {
                    $sContent .= "\t\t" . '<g:shipping_weight>' . number_format($this->data->step->weight, 2, '.', '') . ' ' . Tools::strtolower($sWeightUnit) . '</g:shipping_weight>' . "\n";
                } else {
                    $aReporting[] = 'shipping_weight';
                }
            }

            // Handle the dimension tag
            if (!empty(GMerchantCenterPro::$conf['GMCP_DIMENSION'])) {
                    if (!empty( $this->data->step->shipping_width ) && !empty( $this->data->step->shipping_height ) && !empty( $this->data->step->shipping_length )) {
                        $sContent .= "\t\t" . '<g:shipping_width><![CDATA[' . $this->data->step->shipping_width . ']]></g:shipping_width>' . "\n";
                        $sContent .= "\t\t" . '<g:shipping_height><![CDATA[' . $this->data->step->shipping_height . ']]></g:shipping_height>' . "\n";
                        $sContent .= "\t\t" . '<g:shipping_length><![CDATA[' . $this->data->step->shipping_length . ']]></g:shipping_length>' . "\n";
                    }
                }

            if (!empty(GMerchantCenterPro::$conf['GMCP_SHIPPING_USE'])) {
                $sContent .= "\t\t" . '<g:shipping>' . "\n"
                    . "\t\t\t" . '<g:country>' . $this->aParams['sCountryIso'] . '</g:country>' . "\n"
                    . "\t\t\t" . '<g:price>' . $this->data->step->shipping_fees . '</g:price>' . "\n"
                    . "\t\t" . '</g:shipping>' . "\n";
            }

            /** Promotion ID **/
            require_once(_GMCP_PATH_LIB_DAO . 'cart-rules-dao_class.php');
            $aPromotionIds = BT_GmcProCartRulesDao::getAssocCartRules($this->data->step->id);

            // set a counter to manage only the 10 first promotion_id
            $iCounter = 0;
            if (!empty($aPromotionIds)) {
                $sFormatIdsForXml = null;
                foreach ($aPromotionIds as $aCurrentIds) {
                    if ($iCounter < (int) _GMCP_PROMOTION_ID_NUMBER && Tools::strlen($sFormatIdsForXml) < _GMCP_PROMO_ID_LENGTH) {
                        empty($sFormatIdsForXml) ? $sFormatIdsForXml .= GMerchantCenterPro::$conf['GMCP_ID_PREFIX'] . $aCurrentIds['id_discount'] : $sFormatIdsForXml .= ',' . GMerchantCenterPro::$conf['GMCP_ID_PREFIX'] . $aCurrentIds['id_discount'];
                        $iCounter++;
                    }
                }
                $sContent .= "\t\t" . '<g:promotion_id>' . $sFormatIdsForXml . '</g:promotion_id>' . "\n";
            }

            /** MIN and MAX handling time **/
            // Only add this tag if the merchant activate the GSA serivce and set min and max handling time
            // if (!empty(Configuration::get('GMCP_OAUTH')) && !empty(Configuration::get('GMCP_MIN_HANDLING_TIME')) && !empty(Configuration::get('GMCP_MIN_HANDLING_TIME'))) {
            //     $sContent .= "\t\t" . '<g:min_handling_time>' . Configuration::get('GMCP_MIN_HANDLING_TIME') . '</g:min_handling_time>' . "\n";
            //     $sContent .= "\t\t" . '<g:max_handling_time>' . Configuration::get('GMCP_MAX_HANDLING_TIME') . '</g:max_handling_time>' . "\n";
            // }

            /** google_funded_promotion_eligibility for GSA **/
            if (!empty(Configuration::get('GMCP_OAUTH'))) {
                $sContent .= "\t\t" . '<g:google_funded_promotion_eligibility>' . Configuration::get('GMCP_FUNDED_PROMO') . '</g:google_funded_promotion_eligibility>' . "\n";
            }

            $sContent .= "\t" . '</item>' . "\n";

            $this->bProductProcess = true;
        } else {
            $aReporting[] = '_no_required_data';
        }

        // execute the reporting
        if (!empty($aReporting)) {
            foreach ($aReporting as $sLabel) {
                BT_GmcProReporting::create()->set($sLabel, array('productId' => $this->data->step->id_reporting));
            }
        }

        return $sContent;
    }


    /**
     * build XML tags from the current stored data
     *
     * @return true
     */
    public function buildXmlStockTags()
    {
        $sContent = '';

        $sContent .= "\t" . '<item>' . "\n"
            . "\t\t" . '<g:id>' . Tools::strtoupper(GMerchantCenterPro::$conf['GMCP_ID_PREFIX']) . $this->aParams['sCountryIso'] . $this->data->step->id . '</g:id>' . "\n";

        // ****** PRODUCT PRICES ******
        if ($this->data->step->price_raw < $this->data->step->price_raw_no_discount && GMerchantCenterPro::$conf['GMCP_INV_SALE_PRICE']) {
            $sContent .= "\t\t" . '<g:price>' . $this->data->step->price_no_discount . '</g:price>' . "\n"
                . "\t\t" . '<g:sale_price>' . $this->data->step->price . '</g:sale_price>' . "\n";
        } elseif (!empty(GMerchantCenterPro::$conf['GMCP_INV_PRICE'])) {
            $sContent .= "\t\t" . '<g:price>' . $this->data->step->price . '</g:price>' . "\n";
        }

        if (!empty(GMerchantCenterPro::$conf['GMCP_INV_SALE_PRICE'])) {
            if (GMerchantCenterPro::$conf['GMCP_INC_STOCK'] == 2) {

                if ($this->data->step->quantity > 0) {
                    if (empty($this->data->step->availabilty_date)) {
                        $sContent .= "\t\t" . '<g:availability>in stock</g:availability>' . "\n";
                    } else {
                        $sContent .= "\t\t" . '<g:availability>preorder</g:availability>' . "\n";
                        $sContent .= "\t\t" . '<g:availability_date>'  . BT_GmcProModuleTools::formatDateISO8601($this->data->step->availabilty_date) . '</g:availability_date>' . "\n";
                    }
                } else {
                    $sContent .= "\t\t" . '<g:quantity_to_sell_on_facebook>1</g:quantity_to_sell_on_facebook>' . "\n";

                    if (empty($this->data->step->availabilty_date)) {
                        $sContent .= "\t\t" . '<g:availability>in stock</g:availability>' . "\n";
                    } else {
                        $sContent .= "\t\t" . '<g:availability>preorder</g:availability>' . "\n";
                        $sContent .= "\t\t" . '<g:availability_date>'  . BT_GmcProModuleTools::formatDateISO8601($this->data->step->availabilty_date) . '</g:availability_date>' . "\n";
                    }
                }
            } elseif ($this->data->step->quantity > 0) {

                if (empty($this->data->step->availabilty_date)) {
                    $sContent .= "\t\t" . '<g:availability>in stock</g:availability>' . "\n";
                } else {
                    $sContent .= "\t\t" . '<g:availability>preorder</g:availability>' . "\n";
                    $sContent .= "\t\t" . '<g:availability_date>'  . BT_GmcProModuleTools::formatDateISO8601($this->data->step->availabilty_date) . '</g:availability_date>' . "\n";
                }
            } else {

                if (empty($this->data->step->availabilty_date)) {
                    $sContent .= "\t\t" . '<g:availability>out of stock</g:availability>' . "\n";
                } else {
                    $sContent .= "\t\t" . '<g:availability>preorder</g:availability>' . "\n";
                    $sContent .= "\t\t" . '<g:availability_date>'  . BT_GmcProModuleTools::formatDateISO8601($this->data->step->availabilty_date) . '</g:availability_date>' . "\n";
                }
            }
        }

        $sContent .= "\t" . '</item>' . "\n";

        return $sContent;
    }

    /**
     * returns the product path according to the category ID
     *
     * @param int $iProdCatId
     * @param int $iLangId
     * @return string
     */
    public function getProductPath($iProdCatId, $iLangId)
    {
        if (is_string(GMerchantCenterPro::$conf['GMCP_HOME_CAT'])) {
            GMerchantCenterPro::$conf['GMCP_HOME_CAT'] = unserialize(GMerchantCenterPro::$conf['GMCP_HOME_CAT']);
        }

        if (
            $iProdCatId == GMerchantCenterPro::$conf['GMCP_HOME_CAT_ID']
            && !empty(GMerchantCenterPro::$conf['GMCP_HOME_CAT'][$iLangId])
        ) {
            $sPath = Tools::stripslashes(GMerchantCenterPro::$conf['GMCP_HOME_CAT'][$iLangId]);
        } else {
            $sPath = BT_GmcProModuleTools::getProductPath((int) $iProdCatId, (int) $iLangId, '', false);
        }

        return $sPath;
    }

    /**
     * load products from DAO
     *
     * @param float $fProductPrice
     * @return float
     */
    public function getProductShippingFees($fProductPrice)
    {
        // set vars
        $fShippingFees = (float) 0;
        $bProcess = true;

        // Free shipping on price ?
        if (((float) $this->data->shippingConfig['PS_SHIPPING_FREE_PRICE'] > 0)
            && ((float) $fProductPrice >= (float) $this->data->shippingConfig['PS_SHIPPING_FREE_PRICE'])
        ) {
            $bProcess = false;
        }
        // Free shipping on weight ?
        if (((float) $this->data->shippingConfig['PS_SHIPPING_FREE_WEIGHT'] > 0)
            && ((float) $this->data->step->weight >= (float) $this->data->shippingConfig['PS_SHIPPING_FREE_WEIGHT'])
        ) {
            $bProcess = false;
        }
        // only in case of not free shipping weight or price
        if ($bProcess && is_a($this->data->currentCarrier, 'Carrier')) {
            // Get shipping method - Version 1.4 / 1.5
            $sShippingMethod = ($this->data->currentCarrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT) ? 'weight' : 'price';

            // Get main shipping fee
            if ($sShippingMethod == 'weight') {
                $fShippingFees += $this->data->currentCarrier->getDeliveryPriceByWeight($this->data->step->weight, $this->data->currentZone->id);
            } else {
                $fShippingFees += $this->data->currentCarrier->getDeliveryPriceByPrice($fProductPrice, $this->data->currentZone->id);
            }

            // Add product specific shipping fee
            if (empty($this->data->currentCarrier->is_free)) {
                $fShippingFees += (float) BT_GmcProModuleDao::getAdditionalShippingCost($this->data->p->id, $this->aParams['iShopId']);
            }

            // Add handling fees if applicable
            if (
                !empty($this->data->shippingConfig['PS_SHIPPING_HANDLING'])
                && !empty($this->data->currentCarrier->shipping_handling)
            ) {
                $fShippingFees += (float) $this->data->shippingConfig['PS_SHIPPING_HANDLING'];
            }

            $fCarrierTax = Tax::getCarrierTaxRate((int) $this->data->currentCarrier->id);
            $fShippingFees *= (1 + ($fCarrierTax / 100));

            // Covert to correct currency and format
            $fShippingFees = Tools::convertPrice($fShippingFees, $this->data->currency);
            $fShippingFees = number_format((float) ($fShippingFees), 2, '.', '') . $this->data->currency->iso_code;
        }

        return $fShippingFees;
    }

    /**
     * a cleaned desc string
     *
     * @param int $iProdCatId
     * @param int $iLangId
     * @return string
     */
    public function getProductDesc($sShortDesc, $sLongDesc, $sMetaDesc)
    {
        // set product description
        switch (GMerchantCenterPro::$conf['GMCP_P_DESCR_TYPE']) {
            case 1:
                $sDesc = !empty($sShortDesc) ? $sShortDesc : '';
                break;
            case 2:
                $sDesc = !empty($sLongDesc) ? $sLongDesc : '';
                break;
            case 3:
                $sDesc = '';
                if (!empty($sShortDesc)) {
                    $sDesc = $sShortDesc;
                }
                if (!empty($sLongDesc)) {
                    $sDesc .= (!empty($sDesc) ? ' ' : '') . $sLongDesc;
                }
                break;
            case 4:
                $sDesc = !empty($sMetaDesc) ? $sMetaDesc : '';
                break;
            default:
                $sDesc = !empty($sLongDesc) ? $sLongDesc : '';
                break;
        }

        if (!empty($sDesc)) {
            $sDesc = Tools::substr(BT_GmcProModuleTools::cleanUp($sDesc), 0, 4999);
            strlen($sDesc) == 1 ? $sDesc = '' : '';
        }
        return $sDesc;
    }


    /**
     * returns attributes and features
     *
     * @param int $iProdId
     * @param int $iLangId
     * @param int $iProdAttrId
     * @return array
     */
    public function getColorOptions($iProdId, $iLangId, $iProdAttrId = 0)
    {
        // set
        $aColors = array();

        if (!empty(GMerchantCenterPro::$conf['GMCP_INC_COLOR'])) {

            if (!empty(GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['attribute'])) {
                $sAttributes = implode(',', GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['attribute']);
            }

            if (!empty(GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['feature'])) {
                $iFeature = implode(',', GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['feature']);
            }

            if (!empty($sAttributes)) {
                $aColors = BT_GmcProModuleDao::getProductAttribute((int) $this->data->p->id, $sAttributes, (int) $iLangId, (int) $iProdAttrId);
            }

            // use case - feature selected and not empty
            if (!empty($iFeature)) {
                $sFeature = BT_GmcProModuleDao::getProductFeature((int) $this->data->p->id, (int) $iFeature, (int) $iLangId);

                if (!empty($sFeature)) {
                    $aColors[] = array('name' => $sFeature);
                }
            }
        }
        return $aColors;
    }

    /**
     * returns attributes and features
     *
     * @param int $iProdId
     * @param int $iLangId
     * @param int $iProdAttrId
     * @return array
     */
    public function getSizeOptions($iProdId, $iLangId, $iProdAttrId = 0)
    {
        // set
        $aSize = array();

        if (!empty(GMerchantCenterPro::$conf['GMCP_SIZE_OPT'])) {

            if (!empty(GMerchantCenterPro::$conf['GMCP_SIZE_OPT']['attribute'])) {
                $sAttributes = implode(',', GMerchantCenterPro::$conf['GMCP_SIZE_OPT']['attribute']);
            }

            if (!empty(GMerchantCenterPro::$conf['GMCP_SIZE_OPT']['feature'])) {
                $iFeature = implode(',', GMerchantCenterPro::$conf['GMCP_SIZE_OPT']['feature']);
            }

            if (!empty($sAttributes)) {
                $aSize = BT_GmcProModuleDao::getProductAttribute((int) $this->data->p->id, $sAttributes, (int) $iLangId, (int) $iProdAttrId);
            }

            // use case - feature selected and not empty
            if (!empty($iFeature)) {
                $sFeature = BT_GmcProModuleDao::getProductFeature((int) $this->data->p->id, (int) $iFeature, (int) $iLangId);

                if (!empty($sFeature)) {
                    $aSize[] = array('name' => $sFeature);
                }
            }
        }
        return $aSize;
    }

    /**
     * features for material or pattern
     *
     * @param int $iProdId
     * @param int $iFeatureId
     * @param int $iLangId
     * @return string
     */
    public function getFeaturesOptions($iProdId, $iFeatureId, $iLangId)
    {
        // set
        $sFeatureVal = '';

        $aFeatureProduct = Product::getFeaturesStatic($iProdId);

        if (!empty($aFeatureProduct) && is_array($aFeatureProduct)) {
            foreach ($aFeatureProduct as $aFeature) {
                if ($aFeature['id_feature'] == $iFeatureId) {
                    $aFeatureValues = FeatureValue::getFeatureValueLang((int) $aFeature['id_feature_value']);

                    foreach ($aFeatureValues as $aFeatureVal) {
                        if ($aFeatureVal['id_lang'] == $iLangId) {
                            //Use case for ps 1.7.3.0
                            if (empty(GMerchantCenterPro::$bCompare1730)) {
                                $sFeatureVal = $aFeatureVal['value'];
                            } else {
                                $sFeatureVal .= $aFeatureVal['value'] . ' ';
                            }
                        }
                    }
                }
            }
        }

        return $sFeatureVal;
    }

    /**
     * calculate the stock according to the GSA option
     *
     * @param int $iQty
     * @return int
     */
    public function getStockPercent($iQty)
    {
        if ($iQty > 0) {
            $iStockExport = ($iQty * (int)GMerchantCenterPro::$conf['GMCP_GSA_STOCK']) / 100;
        } else {
            $iStockExport = $iQty;
        }

        return (int)$iStockExport;
    }
}
