<?php
/**
* 2007-2021 Weblir
*
*  @author    weblir <hello@weblir.com>
*  @copyright 2012-2021 weblir
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*  International Registered Trademark & Property of weblir.com
*
*  You are allowed to modify this copy for your own use only. You must not redistribute it. License
*  is permitted for one Prestashop instance only but you can install it on your test instances.
*/

class ChatGPTProCronModuleFrontController extends ModuleFrontController
{
    /** @var bool If set to true, will be redirected to authentication page */
    public $auth = false;

    /** @var bool */
    public $mod = 'WEBLIR_CHATGPTPRO';
    public $name = 'chatgptpro';

    protected function isChatModel($model) {
        $chat_models = [
            "gpt-4",
            "gpt-4-0314",
            "gpt-4-0613",
            "gpt-4-32k",
            "gpt-4-32k-0314",

            "gpt-3.5-turbo",
            "gpt-3.5-turbo-0301",
            "gpt-3.5-turbo-16k",
            "gpt-3.5-turbo-16k-0613",
            "gpt-3.5-turbo-0613"
        ];

        return in_array($model, $chat_models);
    }

    public function __construct()
    {
        parent::__construct();

        if (Configuration::get($this->mod . '_ENABLE_CRON') == 0) {
            echo "Cron Job feature is disabled. You can enable it from the module settings.";
            exit;
        }
        
        if (Tools::getValue('secret') && (Tools::getValue('secret') == Configuration::get($this->mod . '_SECRET') || Tools::getValue('secret') == Configuration::get($this->mod . '_CAT_SECRET') )) {
            if (Tools::getValue('action') == 'generateProductContent') {
                $updatedField = Tools::getValue('updatedField');
                $productTarget = Tools::getValue('productTarget');
                $productsToUpdate = Tools::getValue('productsToUpdate');
                $languageId = (int)Tools::getValue('languageId');
                $promptTemplateId = (int)Tools::getValue('promptTemplateId');
                $debug = (bool)Tools::getValue('debug');

                $allowed_fields = ["name", "description", "description_short", "meta_title", "meta_description", "tags"];

                if (!in_array($updatedField, $allowed_fields)) {
                    echo "updatedField parameter is wrong!";
                    exit;
                }

                if (
                    Tools::strlen($updatedField)<1 ||
                    Tools::strlen($productTarget)<1 ||
                    Tools::strlen($productsToUpdate)<1 ||
                    Tools::strlen($languageId)<1 ||
                    Tools::strlen($promptTemplateId)<1
                ) {
                    echo "All must include all mandatory parameters!";
                    exit;
                }

                if ($debug) {
                    echo "<br><hr><br>";
                    echo "GET parameters:";
                    $this->doDebug($_GET);
                    echo "<br><hr><br>";
                }

                $where_strings = [];
                $left_join = "";

                if ($productTarget == "pendingOpenAI") {
                    $where_strings[] = "NOT EXISTS ( SELECT DISTINCT q.id_product FROM `" . _DB_PREFIX_.pSQL($this->name) . "_log` q where q.id_product = p.id_product)";
                } elseif ($productTarget == "emptyField") {
                    $left_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` as pl ON p.`id_product` = pl.`id_product` ';
                    $where_strings[] = "pl.`id_lang` = ".(int)$languageId;
                    $where_strings[] = "(pl.`" . pSQL($updatedField) . "` IS NULL OR  pl.`" . pSQL($updatedField) . "` = '')";
                }

                if (Tools::getValue('categories') && Tools::strlen(Tools::getValue('categories'))>0) {
                    $category_list = str_replace(" ", "", Tools::getValue('categories'));
                    $category_list = explode(",", $category_list);
                    $full_category_list = [];
                    foreach ($category_list as $key => $cat) {
                        if ((int)$cat > 0) {
                            $full_category_list[] = (int)$cat;
                        }
                    }
                    $where_strings[] = "p.`id_product` IN (SELECT id_product FROM `" . _DB_PREFIX_ . "category_product` WHERE `id_category` IN (" . implode(",", $full_category_list) . ")) OR p.id_category_default IN (" . implode(",", $full_category_list) . ")";
                }

                $where_string = "";
                if (count($where_strings) > 0) {
                    $where_string = " WHERE " . implode(" AND ", $where_strings);
                }

                $sql = '
                    SELECT p.id_product, p.reference
                    FROM ' . _DB_PREFIX_ . 'product p' .
                    $left_join .
                    $where_string .
                    " GROUP BY p.id_product
                    ORDER BY p.id_product ASC
                    LIMIT " . (int)$productsToUpdate;

                if ($debug) {
                    echo "<br><hr><br>";
                    echo "Product SQL string:";
                    $this->doDebug($sql);
                    echo "<br><hr><br>";
                }


                $selected_products = Db::getInstance()->executeS($sql);

                if ($debug) {
                    echo "<br><hr><br>";
                    echo "Product SQL result:";
                    $this->doDebug($selected_products);
                    echo "<br><hr><br>";
                }

                if ($selected_products && is_array($selected_products) && count($selected_products)>0) {
                    $prompt_data = Db::getInstance()->getValue('SELECT prompt FROM '._DB_PREFIX_.pSQL($this->name).'_prompt
                        WHERE id_prompt = '.(int)$promptTemplateId);
                    $updated_products = 0;

                    foreach ($selected_products as $key => $product) {
                        $init_product = new Product((int)$product['id_product'], false, (int)$languageId);
                        
                        $prompt = $prompt_data;
                        $prompt = str_replace("{product_name}", $init_product->name, $prompt);
                        $prompt = str_replace("{product_description}", $init_product->description, $prompt);
                        $prompt = str_replace("{product_description_short}", $init_product->description_short, $prompt);
                        $prompt = str_replace("{product_tags}", $init_product->tags, $prompt);
                        $prompt = str_replace("{product_reference}", $init_product->reference, $prompt);
                        $prompt = str_replace("{product_weight}", $init_product->weight, $prompt);

                        if (strpos($prompt, "{shop_name}")) {
                            $prompt = str_replace("{shop_name}", Configuration::get('PS_SHOP_NAME'), $prompt);
                        }

                        if (strpos($prompt, "{shop_url}")) {
                            $prompt = str_replace("{shop_url}", Configuration::get('PS_SHOP_DOMAIN'), $prompt);
                        }

                        if (strpos($prompt, "{shop_language}")) {
                            $init_lang = Language::getLanguage($this->context->language->id);
                            $prompt = str_replace("{shop_language}", $init_lang['name'], $prompt);
                        }
                        
                        if (strpos($prompt, "{shop_country}")) {
                            $prompt = str_replace("{shop_country}", Country::getNameById($this->context->language->id, Configuration::get('PS_COUNTRY_DEFAULT')), $prompt);
                        }
                        
                        if (strpos($prompt, "{shop_currency}")) {
                            $prompt = str_replace("{shop_currency}", Currency::getIsoCodeById(Configuration::get('PS_CURRENCY_DEFAULT')), $prompt);
                        }

                        if (strpos($prompt, "{product_default_category}")) {
                            $init_cat = new Category($init_product->id_category_default, $this->context->language->id);

                            $prompt = str_replace("{product_default_category}", $init_cat->name, $prompt);
                        }

                        if (strpos($prompt, "{product_categories}")) {
                            $cats = Product::getProductCategoriesFull($id_product, (int)$this->context->language->id);
                            $cat_list = [];
                            foreach ($cats as $key => $cat) {
                                $cat_list[] = $cat['name'];
                            }

                            $prompt = str_replace("{product_categories}", implode(",", $cat_list), $prompt);
                        }

                        if (strpos($prompt, "{product_category_description}")) {
                            $init_cat = new Category($init_product->id_category_default, $this->context->language->id);

                            $prompt = str_replace("{product_category_description}", $init_cat->description, $prompt);
                        }

                        if (strpos($prompt, "{product_brand}")) {
                            $manufacturer = Manufacturer::getNameById($init_product->id_manufacturer);

                            $prompt = str_replace("{product_brand}", $manufacturer, $prompt);
                        }

                        if (strpos($prompt, "{product_attributes}")) {
                            $attribute_list = Product::getAttributesInformationsByProduct($id_product);
                            $attribute_names = [];
                            if (count($attribute_list) > 0) {
                                foreach ($attribute_list as $key => $attr) {
                                    $attribute_names[] = $attr['group'] . ":" .$attr['attribute'];
                                }
                            }

                            $prompt = str_replace("{product_attributes}", implode(",", array_unique($attribute_names)), $prompt);
                        }

                        if (strpos($prompt, "{product_features}")) {
                            $feature_list = Product::getFrontFeaturesStatic((int)$this->context->language->id, $id_product);
                            $product_features = [];
                            $product_feature_values = [];
                            foreach ($feature_list as $key => $feature) {
                                $product_features[] = $feature['name'];
                                $product_feature_values[] = $feature['name'] . ':' . $feature['value'];
                            }

                            $prompt = str_replace("{product_features}", implode(",", $product_features), $prompt);
                        }
                        
                        if (strpos($prompt, "{product_feature_values}")) {
                            $feature_list = Product::getFrontFeaturesStatic((int)$this->context->language->id, (int)$id_product);
                            $product_features = [];
                            $product_feature_values = [];
                            foreach ($feature_list as $key => $feature) {
                                $product_features[] = $feature['name'];
                                $product_feature_values[] = $feature['name'] . ':' . $feature['value'];
                            }
                        
                            $prompt = str_replace("{product_feature_values}", implode(",", $product_feature_values), $prompt);
                        }

                        if (strpos($prompt, "{product_images}")) {
                            $product_image_urls = [];

                            // Load Product Object
                            $product = new Product($id_product);

                            // Validate CMS Page object
                            if (Validate::isLoadedObject($init_product)) {
                                // Get product images
                                $productImages = $init_product->getImages((int)$id_lang);

                                if ($productImages && count($productImages) > 0) {

                                    // Initialize the link object
                                    $link = new Link;

                                    foreach ($productImages AS $key => $val) {
                                        // get image id
                                        $id_image = $val['id_image'];

                                        // If required check image is cover or not
                                        $cover = $val['cover'];

                                        // Create image path using link object
                                        $imagePath = Tools::getShopProtocol() . $link->getImageLink($init_product->link_rewrite[Context::getContext()->language->id], $id_image, 'home_default');

                                        $product_image_urls[] = $imagePath;
                                    }
                                }
                            }
                        
                            $prompt = str_replace("{product_images}", implode(",", $product_image_urls), $prompt);
                        }

                        if ($debug) {
                            echo "<br><hr><br>";
                            echo "Final prompt:";
                            $this->doDebug($prompt);
                            echo "<br><hr><br>";
                        }

                        if (!$debug) {
                            $reply = $this->initiateChatGPT($prompt);

                            if (isset($reply['error']) && isset($reply['error']['message'])) {
                                echo "Error!";
                                echo $reply['error']['message'];
                                die();
                            } else {
                                if ($this->isChatModel(Configuration::get($this->mod . '_MODEL'))) {
                                    $response = nl2br($reply['choices'][0]['message']['content']);
                                } else {
                                    $response = nl2br($reply['choices'][0]['text']);
                                }
                            }

                            $log_data = [
                                'id_product' => (int)$product['id_product'],
                                'type' => pSQL("product_" . pSQL($updatedField)),
                                'method' => "cron",
                                'prompt' => pSQL(urldecode($prompt)),
                                'reply' => pSQL($response),
                                'old_' . pSQL($updatedField) => pSQL($init_product->$updatedField),
                                'new_' . pSQL($updatedField) => pSQL($response),
                            ];

                            //$response = pSQL($response);

                            if ($updatedField == 'name') {
                                $init_product->$updatedField = substr($response, 0, 128);
                            } else if ($updatedField == 'description_short') {
                                $init_product->$updatedField = substr($response, 0, 799);
                            } else if ($updatedField == 'meta_description') {
                                $new_metadesc = str_replace("<br />", "", $response);
                                $new_metadesc = str_replace(array("\n", "\r"), '', $new_metadesc);
                                $init_product->$updatedField = substr($new_metadesc, 0, 159);
                            } else if ($updatedField == 'meta_title') {
                                $new_metatitle = str_replace("<br />", "", $response);
                                $new_metatitle = str_replace(array("\n", "\r"), '', $new_metatitle);
                                $init_product->$updatedField = substr($new_metatitle, 0, 69);
                            } else if ($updatedField == 'tags') {
                                $new_tags = $response;
                                Tag::addTags($languageId, (int)$product['id_product'], $new_tags, $separator = ',')
                            } else {
                                $init_product->$updatedField = $response;
                            }

                            $init_product->save();

                            // add log
                            Db::getInstance()->insert(
                                $this->name.'_log',
                                $log_data
                            );
                        }
                            
                        $updated_products++;
                    }
                    if ($debug) {
                        echo $updated_products." pending products.";
                    } else {
                        echo $updated_products." products have been updated.";
                    }
                } else {
                    echo "No products to update.";
                    exit;
                }
            } else if (Tools::getValue('action') == 'generateCategoryContent') {
                $updatedField = Tools::getValue('updatedField');
                $categoryTarget = Tools::getValue('categoryTarget');
                $categoriesToUpdate = Tools::getValue('categoriesToUpdate');
                $languageId = (int)Tools::getValue('languageId');
                $promptTemplateId = (int)Tools::getValue('promptTemplateId');
                $debug = (bool)Tools::getValue('debug');

                $allowed_fields = ["name", "description", "meta_title", "meta_description"];

                if (!in_array($updatedField, $allowed_fields)) {
                    echo "updatedField parameter is wrong!";
                    exit;
                }

                if (
                    Tools::strlen($updatedField)<1 ||
                    Tools::strlen($categoryTarget)<1 ||
                    Tools::strlen($categoriesToUpdate)<1 ||
                    Tools::strlen($languageId)<1 ||
                    Tools::strlen($promptTemplateId)<1
                ) {
                    echo "All must include all mandatory parameters!";
                    exit;
                }

                if ($debug) {
                    echo "<br><hr><br>";
                    echo "GET parameters:";
                    $this->doDebug($_GET);
                    echo "<br><hr><br>";
                }

                $where_strings = ["c.id_category > ".(int)Configuration::get('PS_HOME_CATEGORY')];
                $left_join = "";

                if ($categoryTarget == "pendingOpenAI") {
                    $where_strings[] = "NOT EXISTS ( SELECT DISTINCT q.id_category FROM `" . _DB_PREFIX_.pSQL($this->name) . "_log` q where q.id_category = c.id_category)";
                } elseif ($categoryTarget == "emptyField") {
                    $left_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` as cl ON c.`id_category` = cl.`id_category` ';
                    $where_strings[] = "cl.`id_lang` = ".(int)$languageId;
                    $where_strings[] = "(cl.`" . pSQL($updatedField) . "` IS NULL OR  cl.`" . pSQL($updatedField) . "` = '')";
                }

                $where_string = "";
                if (count($where_strings) > 0) {
                    $where_string = " WHERE " . implode(" AND ", $where_strings);
                }

                $sql = '
                    SELECT c.id_category
                    FROM ' . _DB_PREFIX_ . 'category c' .
                    $left_join .
                    $where_string .
                    " GROUP BY c.id_category
                    ORDER BY c.id_category ASC
                    LIMIT " . (int)$categoriesToUpdate;

                if ($debug) {
                    echo "<br><hr><br>";
                    echo "Category SQL string:";
                    $this->doDebug("Category SQL string:");
                    echo "<br><hr><br>";
                }

                $selected_categories = Db::getInstance()->executeS($sql);

                if ($debug) {
                    echo "<br><hr><br>";
                    echo "Category SQL result:";
                    $this->doDebug($selected_categories);
                    echo "<br><hr><br>";
                }

                if ($selected_categories && is_array($selected_categories) && count($selected_categories)>0) {
                    $prompt_data = Db::getInstance()->getValue('SELECT prompt FROM '._DB_PREFIX_.pSQL($this->name).'_prompt
                        WHERE id_prompt = '.(int)$promptTemplateId);
                    $updated_categories = 0;

                    foreach ($selected_categories as $key => $category) {
                        $init_category = new Category((int)$category['id_category'], (int)$languageId);
                        
                        $prompt = $prompt_data;
                        $prompt = str_replace("{category_name}", $init_category->name, $prompt);

                        if (strpos($prompt, "{shop_name}")) {
                            $prompt = str_replace("{shop_name}", Configuration::get('PS_SHOP_NAME'), $prompt);
                        }

                        if (strpos($prompt, "{shop_url}")) {
                            $prompt = str_replace("{shop_url}", Configuration::get('PS_SHOP_DOMAIN'), $prompt);
                        }

                        if (strpos($prompt, "{shop_language}")) {
                            $init_lang = Language::getLanguage($this->context->language->id);
                            $prompt = str_replace("{shop_language}", $init_lang['name'], $prompt);
                        }
                        
                        if (strpos($prompt, "{shop_country}")) {
                            $prompt = str_replace("{shop_country}", Country::getNameById($this->context->language->id, Configuration::get('PS_COUNTRY_DEFAULT')), $prompt);
                        }
                        
                        if (strpos($prompt, "{shop_currency}")) {
                            $prompt = str_replace("{shop_currency}", Currency::getIsoCodeById(Configuration::get('PS_CURRENCY_DEFAULT')), $prompt);
                        }

                        if (strpos($prompt, "{category_description}")) {
                            $prompt = str_replace("{category_description}", $init_category->description, $prompt);
                        }

                        if (strpos($prompt, "{category_parent_name}")) {
                            $parent_data = new Category($init_category->id_parent, $id_lang);
                            $prompt = str_replace("{category_parent_name}", $parent_data->name, $prompt);
                        }

                        if ($debug) {
                            echo "<br><hr><br>";
                            echo "Final prompt:";
                            $this->doDebug($prompt);
                            echo "<br><hr><br>";
                        }

                        if (!$debug) {
                            $reply = $this->initiateChatGPT($prompt);

                            if (isset($reply['error']) && isset($reply['error']['message'])) {
                                echo "Error!";
                                echo $reply['error']['message'];
                                die();
                            } else {
                                if ($this->isChatModel(Configuration::get($this->mod . '_MODEL'))) {
                                    $response = nl2br($reply['choices'][0]['message']['content']);
                                } else {
                                    $response = nl2br($reply['choices'][0]['text']);
                                }
                            }

                            $log_data = [
                                'id_category' => $category['id_category'],
                                'type' => pSQL("category_" . pSQL($updatedField)),
                                'method' => "cron",
                                'prompt' => pSQL(urldecode($prompt)),
                                'reply' => pSQL($response),
                                'old_' . pSQL($updatedField) => pSQL($init_category->$updatedField),
                                'new_' . pSQL($updatedField) => pSQL($response),
                            ];

                            //$response = pSQL($response);

                            if ($updatedField == 'name') {
                                $init_category->$updatedField = substr($response, 0, 128);
                            } else if ($updatedField == 'description_short') {
                                $init_category->$updatedField = substr($response, 0, 799);
                            } else if ($updatedField == 'meta_description') {
                                $new_metadesc = str_replace("<br />", "", $response);
                                $new_metadesc = str_replace(array("\n", "\r"), '', $new_metadesc);
                                $init_category->$updatedField = substr($new_metadesc, 0, 159);
                            } else if ($updatedField == 'meta_title') {
                                $new_metatitle = str_replace("<br />", "", $response);
                                $new_metatitle = str_replace(array("\n", "\r"), '', $new_metatitle);
                                $init_category->$updatedField = substr($new_metatitle, 0, 69);
                            } else {
                                $init_category->$updatedField = $response;
                            }

                            $init_category->save();

                            // add log
                            Db::getInstance()->insert(
                                $this->name.'_log',
                                $log_data
                            );
                        }
                            
                        $updated_categories++;
                    }
                    echo $updated_categories." categories have been updated.";
                } else {
                    echo "No categories to update.";
                    exit;
                }
            } else {
                echo "Invalid action!";
                die();
            }

        } else {
            echo "The token does not exist or it's wrong!";
            die();
        }
    }

    protected function initiateChatGPT($prompt)
    {
        $dTemperature = (float)Configuration::get($this->mod . '_TEMPERATURE', '0.7');
        $iMaxTokens = (int) Configuration::get($this->mod . '_MAX_TOKENS', '256');
        $top_p = (int) Configuration::get($this->mod . '_TOP_P', '1');
        $frequency_penalty = (float) Configuration::get($this->mod . '_FREQ_PEN', '0');
        $presence_penalty = (float) Configuration::get($this->mod . '_PRES_PEN', '0');
        $OPENAI_API_KEY = Configuration::get($this->mod . '_API_KEY');
        $sModel = Configuration::get($this->mod . '_MODEL', 'text-davinci-003');
        $ch = curl_init();

        // if ($sModel == "gpt-3.5-turbo" || strpos(Configuration::get($this->mod . '_MODEL'), 'gpt-4') !== false || strpos(Configuration::get($this->mod . '_MODEL'), 'gpt-3.5-turbo-0613') !== false) {
        //     $mailURL = "https://api.openai.com/v1/chat/completions";
        // }

        $mailURL = 'https://api.openai.com/v1/completions';
        if ($this->isChatModel(Configuration::get($this->mod . '_MODEL'))) {
            $mailURL = "https://api.openai.com/v1/chat/completions";
        }

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $OPENAI_API_KEY . '',
        ];

        // if ($sModel == "gpt-3.5-turbo" || strpos(Configuration::get($this->mod . '_MODEL'), 'gpt-4') !== false || strpos(Configuration::get($this->mod . '_MODEL'), 'gpt-3.5-turbo-0613') !== false ) {
        //     $postData = [
        //         'model' => $sModel,
        //         'temperature' => $dTemperature,
        //         'max_tokens' => $iMaxTokens,
        //         'top_p' => $top_p,
        //         'frequency_penalty' => $frequency_penalty,
        //         'presence_penalty' => $presence_penalty,
        //         'stop' => '[" Human:", " AI:"]',
        //         "messages" => array(
        //             array(
        //                 "role" => "user",
        //                 "content" => str_replace('"', '', urldecode($prompt))
        //             )
        //         ),
        //     ];
        // } else {
        //     $postData = [
        //         'model' => $sModel,
        //         'prompt' => str_replace('"', '', urldecode($prompt)),
        //         'temperature' => $dTemperature,
        //         'max_tokens' => $iMaxTokens,
        //         'top_p' => $top_p,
        //         'frequency_penalty' => $frequency_penalty,
        //         'presence_penalty' => $presence_penalty,
        //         'stop' => '[" Human:", " AI:"]',
        //     ];
        // }

        if ($this->isChatModel(Configuration::get($this->mod . '_MODEL'))) {
            $postData = [
                'model' => $sModel,
                'temperature' => $dTemperature,
                'max_tokens' => $iMaxTokens,
                'top_p' => $top_p,
                'frequency_penalty' => $frequency_penalty,
                'presence_penalty' => $presence_penalty,
                'stop' => '[" Human:", " AI:"]',
                "messages" => array(
                    array(
                        "role" => "user",
                        "content" => str_replace('"', '', urldecode($prompt))
                    )
                ),
            ];
        } else {
            $postData = [
                'model' => $sModel,
                'prompt' => str_replace('"', '', urldecode($prompt)),
                'temperature' => $dTemperature,
                'max_tokens' => $iMaxTokens,
                'top_p' => $top_p,
                'frequency_penalty' => $frequency_penalty,
                'presence_penalty' => $presence_penalty,
                'stop' => '[" Human:", " AI:"]',
            ];
        }



        if (Configuration::get($this->mod . '_DEBUG_MODE') == 1) {
            dump($postData);
        }

        curl_setopt($ch, CURLOPT_URL, $mailURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $result = curl_exec($ch);
        $decoded_json = json_decode($result, true);

        if (Configuration::get($this->mod . '_DEBUG_MODE') == 1) {
            dump($decoded_json);
        }

        return $decoded_json;
    }

    public function display()
    {
        $this->ajax = 1;
        $this->ajaxDie();
    }

    public function doDebug($var, $exit = false)
    {
        if ($this->psversion() == '6') {
            echo "<br>";
            echo "<pre>";
            print_r($var);
            echo "</pre>";
            echo "<br>";

            if ($exit == true) {
                exit;
                die();
            }
        } else {
            dump($var);
        }
    }

    public function psversion()
    {
        $version = _PS_VERSION_;
        $ver = explode(".", $version);
        return $ver[1];
    }
}
