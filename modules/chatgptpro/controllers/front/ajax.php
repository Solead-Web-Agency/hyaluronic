<?php
/**
* 2007-2023 Weblir
*
*  @author    weblir <hello@weblir.com>
*  @copyright 2012-2023 weblir
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*  International Registered Trademark & Property of weblir.com
*
*  You are allowed to modify this copy for your own use only. You must not redistribute it. License
*  is permitted for one Prestashop instance only but you can install it on your test instances.
*/
class ChatGPTProAjaxModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $this->page_name = 'gpt-ajax';
        parent::init();
        $this->id_lang = $this->context->language->id;
        $this->name = 'chatgptpro';
        $this->mod = 'WEBLIR_' . strtoupper($this->name);
    }

    protected function disableColumns()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        $this->display_footer = false;
        $this->display_header = false;
    }

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

    public function psversion()
    {
        $version = _PS_VERSION_;
        $ver = explode('.', $version);

        return $ver[1];
    }

    public function display()
    {
        $this->ajax = 1;
        $this->ajaxDie();
    }

    public function initContent()
    {
        parent::initContent();
        $this->disableColumns();

        if (Tools::getValue('id_language') && (int)Tools::getValue('id_language') > 0) {
            $id_lang = (int)Tools::getValue('id_language');
        } else {
            $id_lang = (int) $this->context->language->id;
        }
        
        $id_shop = (int) $this->context->shop->id;
        $currency = $this->context->currency->sign;

        $secret_code = Configuration::get($this->mod . '_TOKEN');
        if (Tools::getValue('secret_token') == $secret_code) {
            if (Tools::getValue('action') == 'initiatePrompt') {
                $prompt = urldecode(Tools::getValue('prompt'));

                if (Tools::strlen($prompt) > 0) {
                    // if category id isset then replace shortcodes
                    if (Tools::getValue('id_category') && (int)Tools::getValue('id_category')>0) {
                        
                        $init_category = new Category((int)Tools::getValue('id_category'), $id_lang);
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
                    }

                    // if cms id isset then replace shortcodes
                    if (Tools::getValue('id_cms') && (int)Tools::getValue('id_cms')>0) {
                        
                        $init_cms = new CMS((int)Tools::getValue('id_cms'), $id_lang);
                        $prompt = str_replace("{cms_title}", $init_cms->meta_title, $prompt);

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
                    }

                    // if manufacturer id isset then replace shortcodes
                    if (Tools::getValue('id_manufacturer') && (int)Tools::getValue('id_manufacturer')>0) {
                        
                        $init_manufacturer = new Manufacturer((int)Tools::getValue('id_manufacturer'), false, $id_lang);
                        $prompt = str_replace("{brand_name}", $init_manufacturer->name, $prompt);

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

                        if (strpos($prompt, "{brand_description}")) {
                            $prompt = str_replace("{category_description}", $init_manufacturer->description, $prompt);
                        }

                        if (strpos($prompt, "{brand_short_description}")) {
                            $prompt = str_replace("{category_description}", $init_manufacturer->short_description, $prompt);
                        }
                    }

                    $reply = $this->initiateChatGPT($prompt);

                    if (isset($reply['error']) && isset($reply['error']['message'])) {
                        $arr = [
                            'status' => 'error',
                            'msg' => $reply['error']['message'],
                        ];
                        echo json_encode($arr);
                    } else {
                        $returned_data = [];

                        // if (Configuration::get($this->mod . '_MODEL') == "gpt-3.5-turbo") {
                        //     $returned_data['response'] = nl2br($reply['choices'][0]['message']['content']);
                        // } elseif (strpos(Configuration::get($this->mod . '_MODEL'), 'gpt-4') !== false) {
                        //     $returned_data['response'] = nl2br($reply['choices'][0]['message']['content']);
                        // } else {
                        //     $returned_data['response'] = nl2br($reply['choices'][0]['text']);
                        // }

                        if ($this->isChatModel(Configuration::get($this->mod . '_MODEL'))) {
                            $returned_data['response'] = nl2br($reply['choices'][0]['message']['content']);
                        } else {
                            $returned_data['response'] = nl2br($reply['choices'][0]['text']);
                        }

                        $returned_data['total_tokens'] = $reply['usage']['total_tokens'];
                        $arr = [
                            'status' => 'success',
                            'msg' => $this->translateString('Data successfully retrieved.'),
                            'date' => $returned_data,
                        ];

                        $log_data = [
                            'type' => "general",
                            'method' => "initiatePrompt",
                            'prompt' => pSQL(urldecode($prompt)),
                            'reply' => pSQL($returned_data['response'])
                        ];

                        if (Tools::getValue('id_category') && (int)Tools::getValue('id_category')>0) {
                            $log_data['id_category'] = (int)Tools::getValue('id_category');
                            $log_data['type'] = 'singleCategory';
                        }

                        if (Tools::getValue('id_cms') && (int)Tools::getValue('id_cms')>0) {
                            $log_data['id_cms'] = (int)Tools::getValue('id_cms');
                            $log_data['type'] = 'singleCMS';
                        }

                        if (Tools::getValue('id_manufacturer') && (int)Tools::getValue('id_manufacturer')>0) {
                            $log_data['id_manufacturer'] = (int)Tools::getValue('id_manufacturer');
                            $log_data['type'] = 'singleBrand';
                        }

                        // add log
                        Db::getInstance()->insert(
                            $this->name.'_log',
                            $log_data
                        );

                        echo json_encode($arr);
                    }
                } else {
                    $arr = [
                        'status' => 'error',
                        'msg' => $this->translateString('Error! Prompt missing!'),
                    ];
                    echo json_encode($arr);
                }
            } elseif (Tools::getValue('action') == 'updateSettings') {
                Configuration::updateValue(
                    'WEBLIR_CHATGPTPRO_MAX_TOKENS',
                    pSQL(Tools::getValue('WEBLIR_CHATGPTPRO_MAX_TOKENS'))
                );

                Configuration::updateValue(
                    'WEBLIR_CHATGPTPRO_TEMPERATURE',
                     pSQL(Tools::getValue('WEBLIR_CHATGPTPRO_TEMPERATURE'))
                );

                Configuration::updateValue(
                    'WEBLIR_CHATGPTPRO_TOP_P',
                     pSQL(Tools::getValue('WEBLIR_CHATGPTPRO_TOP_P'))
                );

                Configuration::updateValue(
                    'WEBLIR_CHATGPTPRO_FREQ_PEN',
                     pSQL(Tools::getValue('WEBLIR_CHATGPTPRO_FREQ_PEN'))
                );

                Configuration::updateValue(
                    'WEBLIR_CHATGPTPRO_PRES_PEN',
                     pSQL(Tools::getValue('WEBLIR_CHATGPTPRO_PRES_PEN'))
                );

                $returned_data = [];
                $arr = [
                    'status' => 'success',
                    'msg' => $this->translateString('Settings successfully updated.'),
                    'date' => $returned_data,
                ];
                echo json_encode($arr);
            } elseif (Tools::getValue('action') == 'updateProduct') {
                $id_product = (int)Tools::getValue('id_product');
                $id_lang = (int)Tools::getValue('idlanguage');
                $prompt = urldecode(Tools::getValue('prompt'));
                $target = Tools::getValue('target');
                $behavior = Tools::getValue('behavior');

                if (Tools::strlen($prompt)<4) {
                    $arr = [
                        'status' => 'error',
                        'msg' => $this->translateString('Error! Prompt is missing or too short!'),
                    ];
                    echo json_encode($arr);
                    exit;
                }

                if ((int)$id_product < 1) {
                    $arr = [
                        'status' => 'error',
                        'msg' => $this->translateString('Error! Product ID is missing!'),
                    ];
                    echo json_encode($arr);
                    exit;
                }

                if (Tools::strlen($target)<1) {
                    $arr = [
                        'status' => 'error',
                        'msg' => $this->translateString('Error! Target is missing!'),
                    ];
                    echo json_encode($arr);
                    exit;
                }

                $init_product = new Product($id_product, false, $id_lang);

                if ($behavior == "keep") {
                    if ($target == "name" && Tools::strlen($init_product->name)>0) {
                        $returned_data = [
                            "product_name" => $init_product->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Product skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    } elseif ($target == "description" && Tools::strlen($init_product->description)>0) {
                        $returned_data = [
                            "product_name" => $init_product->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Product skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    } elseif ($target == "description_short" && Tools::strlen($init_product->description_short)>0) {
                        $returned_data = [
                            "product_name" => $init_product->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Product skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    } elseif ($target == "meta_title" && Tools::strlen($init_product->meta_title)>0) {
                        $returned_data = [
                            "product_name" => $init_product->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Product skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    } elseif ($target == "meta_description" && Tools::strlen($init_product->meta_description)>0) {
                        $returned_data = [
                            "product_name" => $init_product->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Product skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    } elseif ($target == "tags" && count(Tag::getProductTags((int)$id_product))>0) {
                        $returned_data = [
                            "product_name" => $init_product->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Product skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    }
                }

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

                if (strpos($prompt, "{product_category_description}")) {
                    $init_cat = new Category($init_product->id_category_default, $this->context->language->id);

                    $prompt = str_replace("{product_category_description}", $init_cat->description, $prompt);
                }

                if (strpos($prompt, "{product_brand}")) {
                    $manufacturer = Manufacturer::getNameById($init_product->id_manufacturer);

                    $prompt = str_replace("{product_brand}", $manufacturer, $prompt);
                }

                if (strpos($prompt, "{product_categories}")) {
                    $cats = Product::getProductCategoriesFull((int)$id_product, (int)$this->context->language->id);
                    $cat_list = [];
                    foreach ($cats as $key => $cat) {
                        $cat_list[] = $cat['name'];
                    }

                    $prompt = str_replace("{product_categories}", implode(",", $cat_list), $prompt);
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
                    $feature_list = Product::getFrontFeaturesStatic((int)$this->context->language->id, $id_product);
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

                // to do
                // if (strpos($prompt, "{product_image")) {
                //     $re = '/(?:\{product_image|\G(?!^))(?=[^][]*})\h+([^\s=]+)="([^\s"]+)"/m';
                //     $str = '{product_image img="5" size="large_default"}';
                //     preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
                //     print_r($matches);
                // }
                
                
                $reply = $this->initiateChatGPT($prompt);

                if (isset($reply['error']) || !isset($reply['choices'])) {
                    $details = "";
                    if (isset($reply['error']['message'])) {
                        $details = " " . $this->translateString('Details: ') . $reply['error']['message'];
                    }

                    $returned_data = [
                        "product_name" => $init_product->name
                    ];

                    $arr = [
                        'status' => 'error',
                        'msg' => $this->translateString('Error! Content retrieve has failed!') . $details,
                        'date' => $returned_data,
                    ];
                    echo json_encode($arr);
                    exit;
                }

                // if (Configuration::get($this->mod . '_MODEL') == "gpt-3.5-turbo") {
                    
                // }  elseif (strpos(Configuration::get($this->mod . '_MODEL'), 'gpt-4') !== false) {
                //     $usage = $reply['usage']['total_tokens'];
                //     $reply = $reply['choices'][0]['message']['content'];
                // } else {
                //     $usage = $reply['usage']['total_tokens'];
                //     $reply = $reply['choices'][0]['text'];
                // }

                if ($this->isChatModel(Configuration::get($this->mod . '_MODEL'))) {
                    $usage = $reply['usage']['total_tokens'];
                    $reply = $reply['choices'][0]['message']['content'];
                } else {
                    $usage = $reply['usage']['total_tokens'];
                    $reply = $reply['choices'][0]['text'];
                }

                $reply = nl2br($reply);

                $type = "general";
                $old_name = false;
                $new_name = false;
                $old_description = false;
                $new_description = false;
                $old_description_short = false;
                $new_description_short = false;
                $old_meta_title = false;
                $new_meta_title = false;
                $old_meta_description = false;
                $new_meta_description = false;
                $old_tags = false;
                $new_tags = false;



                if ($target == 3) {
                    $old_name = $init_product->name;
                    $new_name = $reply;
                } elseif ($target == 0) {
                    $old_description = $init_product->description;
                    $new_description = $reply;
                } elseif ($target == 1) {
                    $old_description_short = $init_product->description_short;
                    $new_description_short = $reply;
                } elseif ($target == 4) {
                    $old_meta_title = $init_product->meta_title;
                    $new_meta_title = $reply;
                } elseif ($target == 5) {
                    $old_meta_description = $init_product->meta_description;
                    $new_meta_description = $reply;
                } elseif ($target == 6) {
                    if (is_array(Tag::getProductTags($id_product))>0) {
                        $old_tags = implode(",", Tag::getProductTags($id_product));
                    } else {
                        $old_tags = "";
                    }

                    $new_tags = $reply;
                }




                if ($behavior == "single_product") {
                    $returned_data['response'] = $reply;
                    $returned_data['total_tokens'] = $usage;
                    $arr = [
                        'status' => 'success',
                        'msg' => $this->translateString('Data successfully retrieved.'),
                        'date' => $returned_data,
                    ];

                    $log_data = [
                        'type' => "single_product",
                        'method' => "updateProduct",
                        'prompt' => pSQL(urldecode($prompt)),
                        'reply' => pSQL($returned_data['response']),

                        'id_product' => (int)$id_product,
                        'old_name' => pSQL($old_name),
                        'new_name' => pSQL($new_name),
                        'old_description' => pSQL($old_description),
                        'new_description' => pSQL($new_description),
                        'old_description_short' => pSQL($old_description_short),
                        'new_description_short' => pSQL($new_description_short),
                        'old_meta_title' => pSQL($old_meta_title),
                        'new_meta_title' => pSQL($new_meta_title),
                        'old_meta_description' => pSQL($old_meta_description),
                        'new_meta_description' => pSQL($new_meta_description),

                        'old_tags' => pSQL($old_tags),
                        'new_tags' => pSQL($new_tags),

                    ];

                    // add log
                    Db::getInstance()->insert(
                        $this->name.'_log',
                        $log_data
                    );

                    echo json_encode($arr);
                    exit;
                }

                


                if ($target == "name") {
                    $type = "product_name";
                    $old_description = $init_product->description;

                    if ($behavior == "keep_top") {
                        $init_product->name = substr($reply . "<br><br>" . $init_product->name, 0, 128);
                    } elseif ($behavior == "keep_end") {
                        $init_product->name = substr($init_product->name . "<br><br>" . $reply, 0, 128);
                    } elseif ($behavior == "keep") {
                        $init_product->name = substr($reply, 0, 128);
                    }  elseif ($behavior == "replace") {
                        $init_product->name = substr($reply, 0, 128);
                    }

                    $new_name = $init_product->name;
                } elseif ($target == "description") {
                    $type = "product_description";
                    $old_description = $init_product->description;

                    if ($behavior == "keep_top") {
                        $init_product->description = $reply . "<br><br>" . $init_product->description;
                    } elseif ($behavior == "keep_end") {
                        $init_product->description = $init_product->description . "<br><br>" . $reply;
                    } elseif ($behavior == "keep") {
                        $init_product->description = $reply;
                    }  elseif ($behavior == "replace") {
                        $init_product->description = $reply;
                    }

                    $new_description = $init_product->description;
                } elseif ($target == "description_short") {
                    $type = "product_description_short";
                    $old_description_short = $init_product->description_short;

                    if ($behavior == "keep_top") {
                        $init_product->description_short = substr($reply . "<br><br>" . $init_product->description_short, 0, 800);
                    } elseif ($behavior == "keep_end") {
                        $init_product->description_short = substr($init_product->description_short . "<br><br>" . $reply, 0, 800);
                    } elseif ($behavior == "keep") {
                        $init_product->description_short = substr($reply, 0, 800);
                    }  elseif ($behavior == "replace") {
                        $init_product->description_short = substr($reply, 0, 800);
                    }

                    $new_description_short = $init_product->description_short;
                } elseif ($target == "meta_title") {
                    $type = "product_meta_title";
                    $old_meta_title = $init_product->meta_title;

                    $reply = str_replace("<br />", "", $reply);
                    $reply = str_replace(array("\n", "\r"), '', $reply);

                    if ($behavior == "keep_top") {
                        $init_product->meta_title = substr($reply . "<br><br>" . $init_product->meta_title, 0, 69);
                    } elseif ($behavior == "keep_end") {
                        $init_product->meta_title = substr($init_product->meta_title . "<br><br>" . $reply, 0, 69);
                    } elseif ($behavior == "keep") {
                        $init_product->meta_title = substr($reply, 0, 69);
                    }  elseif ($behavior == "replace") {
                        $init_product->meta_title = substr($reply, 0, 69);
                    }

                    $new_meta_title = $init_product->meta_title;
                } elseif ($target == "meta_description") {
                    $type = "product_meta_description";
                    $old_meta_description = $init_product->meta_description;

                    $reply = str_replace("<br />", "", $reply);
                    $reply = str_replace(array("\n", "\r"), '', $reply);

                    if ($behavior == "keep_top") {
                        $init_product->meta_description = substr($reply . "<br><br>" . $init_product->meta_description, 0, 159);
                    } elseif ($behavior == "keep_end") {
                        $init_product->meta_description = substr($init_product->meta_description . "<br><br>" . $reply, 0, 159);
                    } elseif ($behavior == "keep") {
                        $init_product->meta_description = substr($reply, 0, 159);
                    }  elseif ($behavior == "replace") {
                        $init_product->meta_description = substr($reply, 0, 159);
                    }

                    $new_meta_description = $init_product->meta_description;
                } elseif ($target == "tags") {
                    $type = "product_tags";

                    $old_tags = implode(",", Tag::getProductTags((int)$id_product));

                    $reply = str_replace("<br />", "", $reply);
                    $reply = str_replace(array("\n", "\r"), '', $reply);

                    if ($behavior == "keep_top") {
                        Tag::addTags($id_lang, (int)$id_product, $reply . "," . $old_tags, $separator = ',');
                    } elseif ($behavior == "keep_end") {
                        $init_product->meta_description = substr($old_tags . "<br><br>" . $reply, 0, 512);
                        Tag::addTags($id_lang, (int)$id_product, $old_tags . "," . $reply, $separator = ',');
                    } elseif ($behavior == "keep") {
                        Tag::addTags($id_lang, (int)$id_product, $reply, $separator = ',');
                    }  elseif ($behavior == "replace") {
                        Tag::addTags($id_lang, (int)$id_product, $reply, $separator = ',');
                    }

                    $new_tags = $init_product->meta_description;
                }

                $init_product->save();

                $log_data = [
                    'id_product' => (int)$id_product,
                    'type' => pSQL($type),
                    'method' => "updateProduct",
                    'prompt' => pSQL(urldecode($prompt)),
                    'reply' => pSQL($reply),
                    'old_name' => pSQL($old_name),
                    'new_name' => pSQL($new_name),
                    'old_description' => pSQL($old_description),
                    'new_description' => pSQL($new_description),
                    'old_description_short' => pSQL($old_description_short),
                    'new_description_short' => pSQL($new_description_short),
                    'old_meta_title' => pSQL($old_meta_title),
                    'new_meta_title' => pSQL($new_meta_title),
                    'old_meta_description' => pSQL($old_meta_description),
                    'new_meta_description' => pSQL($new_meta_description),
                    'old_tags' => pSQL($old_tags),
                    'new_tags' => pSQL($new_tags),
                ];

                // add log
                Db::getInstance()->insert(
                    $this->name.'_log',
                    $log_data
                );

                $returned_data = [
                    "product_name" => $init_product->name,
                    "generated_content" => $reply,
                ];

                $arr = [
                    'status' => 'success',
                    'msg' => $this->translateString('Content successfully generated and updated to product.'),
                    'date' => $returned_data,
                ];
                echo json_encode($arr);

            } elseif (Tools::getValue('action') == 'initiateProductRestore') {
                $id_lang = (int)Tools::getValue('id_lang');
                $id_log = (int)Tools::getValue('id_log');
                $type = Tools::getValue('type');

                $query = 'SELECT * FROM `'._DB_PREFIX_.$this->name .'_log` WHERE id_log = '. (int)$id_log;
                $row = Db::getInstance()->getRow($query);

                $init_product = new Product((int)$row['id_product'], false, $id_lang);


                if ($type == "product_name") {
                    $init_product->name = substr($row['old_name'], 0, 128);
                } elseif ($type == "product_description") {
                    $init_product->description = $row['old_description'];
                } elseif ($type == "product_description_short") {
                    $init_product->description_short = substr($row['old_description_short'], 0, 800);
                } elseif ($type == "product_meta_title") {
                    $init_product->meta_title = substr($row['old_meta_description'], 0, 69);
                } elseif ($type == "product_meta_description") {
                    $init_product->meta_description = substr($row['old_meta_description'], 0, 159);
                } elseif ($type == "product_tags") {
                    Tag::addTags($id_lang, (int)$row['id_product'], $row['old_meta_description'], $separator = ',');
                }


                $init_product->save();

                $returned_data = [
                    
                ];

                $arr = [
                    'status' => 'success',
                    'msg' => $this->translateString('Content successfully restored.') . ' ' . $this->translateString('Refresh this page to see changes.'),
                    'date' => $returned_data,
                ];

                echo json_encode($arr);
            } elseif (Tools::getValue('action') == 'updateCategory') {
                $id_category = (int)Tools::getValue('id_category');
                $id_lang = (int)Tools::getValue('id_lang');
                $prompt = urldecode(Tools::getValue('prompt'));
                $target = Tools::getValue('target');
                $behavior = Tools::getValue('behavior');

                if (Tools::strlen($prompt)<4) {
                    $arr = [
                        'status' => 'error',
                        'msg' => $this->translateString('Error! Prompt is missing or too short!'),
                    ];
                    echo json_encode($arr);
                    exit;
                }

                if ((int)$id_category < 1) {
                    $arr = [
                        'status' => 'error',
                        'msg' => $this->translateString('Error! Category ID is missing!'),
                    ];
                    echo json_encode($arr);
                    exit;
                }

                if (Tools::strlen($target)<1) {
                    $arr = [
                        'status' => 'error',
                        'msg' => $this->translateString('Error! Target is missing!'),
                    ];
                    echo json_encode($arr);
                    exit;
                }

                $init_category = new Category($id_category, $id_lang);

                if ($behavior == "keep") {
                    if ($target == "name" && Tools::strlen($init_category->name)>0) {
                        $returned_data = [
                            "category_name" => $init_category->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Category skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    } elseif ($target == "description" && Tools::strlen($init_category->description)>0) {
                        $returned_data = [
                            "category_name" => $init_category->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Category skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    } elseif ($target == "meta_title" && Tools::strlen($init_category->meta_title)>0) {
                        $returned_data = [
                            "category_name" => $init_category->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Category skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    } elseif ($target == "meta_description" && Tools::strlen($init_category->meta_description)>0) {
                        $returned_data = [
                            "category_name" => $init_category->name
                        ];

                        $arr = [
                            'status' => 'error',
                            'msg' => $this->translateString('Category skipped.'),
                            'date' => $returned_data,
                        ];
                        echo json_encode($arr);
                        exit;
                    }
                }


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
                    $prompt = str_replace("{shop_country}", Country::getNameById((int)$this->context->language->id, Configuration::get('PS_COUNTRY_DEFAULT')), $prompt);
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



                $reply = $this->initiateChatGPT($prompt);

                if (isset($reply['error']) || !isset($reply['choices'])) {
                    $details = "";
                    if (isset($reply['error']['message'])) {
                        $details = " " . $this->translateString('Details: ') . $reply['error']['message'];
                    }

                    $returned_data = [
                        "category_name" => $init_category->name
                    ];

                    $arr = [
                        'status' => 'error',
                        'msg' => $this->translateString('Error! Content retrieve has failed!') . $details,
                        'date' => $returned_data,
                    ];
                    echo json_encode($arr);
                    exit;
                }

                // if (Configuration::get($this->mod . '_MODEL') == "gpt-3.5-turbo") {
                //     $usage = $reply['usage']['total_tokens'];
                //     $reply = $reply['choices'][0]['message']['content'];
                // }  elseif (strpos(Configuration::get($this->mod . '_MODEL'), 'gpt-4') !== false) {
                //     $usage = $reply['usage']['total_tokens'];
                //     $reply = $reply['choices'][0]['message']['content'];
                // } else {
                //     $usage = $reply['usage']['total_tokens'];
                //     $reply = $reply['choices'][0]['text'];
                // }

                if ($this->isChatModel(Configuration::get($this->mod . '_MODEL'))) {
                    $usage = $reply['usage']['total_tokens'];
                    $reply = $reply['choices'][0]['message']['content'];
                } else {
                    $usage = $reply['usage']['total_tokens'];
                    $reply = $reply['choices'][0]['text'];
                }

                $reply = nl2br($reply);

                if ($behavior == "single_category") {
                    $returned_data['response'] = $reply;
                    $returned_data['total_tokens'] = $usage;
                    $arr = [
                        'status' => 'success',
                        'msg' => $this->translateString('Data successfully retrieved.'),
                        'date' => $returned_data,
                    ];

                    $log_data = [
                        'type' => "single_category",
                        'id_category' => (int)$id_category,
                        'method' => "updateCategory",
                        'prompt' => pSQL(urldecode($prompt)),
                        'reply' => pSQL($returned_data['response'])
                    ];

                    // add log
                    Db::getInstance()->insert(
                        $this->name.'_log',
                        $log_data
                    );

                    echo json_encode($arr);
                    exit;
                }

                $type = "general";
                $old_name = false;
                $new_name = false;
                $old_description = false;
                $new_description = false;
                $old_meta_title = false;
                $new_meta_title = false;
                $old_meta_description = false;
                $new_meta_description = false;


                if ($target == "name") {
                    $type = "category_name";
                    $old_description = $init_category->description;

                    if ($behavior == "keep_top") {
                        $init_category->name = substr($reply . "<br><br>" . $init_category->name, 0, 128);
                    } elseif ($behavior == "keep_end") {
                        $init_category->name = substr($init_category->name . "<br><br>" . $reply, 0, 128);
                    } elseif ($behavior == "keep") {
                        $init_category->name = substr($reply, 0, 128);
                    }  elseif ($behavior == "replace") {
                        $init_category->name = substr($reply, 0, 128);
                    }

                    $new_name = $init_category->name;
                } elseif ($target == "description") {
                    $type = "category_description";
                    $old_description = $init_category->description;

                    if ($behavior == "keep_top") {
                        $init_category->description = $reply . "<br><br>" . $init_category->description;
                    } elseif ($behavior == "keep_end") {
                        $init_category->description = $init_category->description . "<br><br>" . $reply;
                    } elseif ($behavior == "keep") {
                        $init_category->description = $reply;
                    }  elseif ($behavior == "replace") {
                        $init_category->description = $reply;
                    }

                    $new_description = $init_category->description;
                } elseif ($target == "meta_title") {
                    $type = "category_meta_title";
                    $old_meta_title = $init_category->meta_title;

                    $reply = str_replace("<br />", "", $reply);
                    $reply = str_replace(array("\n", "\r"), '', $reply);

                    if ($behavior == "keep_top") {
                        $init_category->meta_title = substr($reply . "<br><br>" . $init_category->meta_title, 0, 69);
                    } elseif ($behavior == "keep_end") {
                        $init_category->meta_title = substr($init_category->meta_title . "<br><br>" . $reply, 0, 69);
                    } elseif ($behavior == "keep") {
                        $init_category->meta_title = substr($reply, 0, 69);
                    }  elseif ($behavior == "replace") {
                        $init_category->meta_title = substr($reply, 0, 69);
                    }

                    $new_meta_title = $init_category->meta_title;
                } elseif ($target == "meta_description") {
                    $type = "category_meta_description";
                    $old_meta_description = $init_category->meta_description;

                    $reply = str_replace("<br />", "", $reply);
                    $reply = str_replace(array("\n", "\r"), '', $reply);

                    if ($behavior == "keep_top") {
                        $init_category->meta_description = substr($reply . "<br><br>" . $init_category->meta_description, 0, 159);
                    } elseif ($behavior == "keep_end") {
                        $init_category->meta_description = substr($init_category->meta_description . "<br><br>" . $reply, 0, 159);
                    } elseif ($behavior == "keep") {
                        $init_category->meta_description = substr($reply, 0, 159);
                    }  elseif ($behavior == "replace") {
                        $init_category->meta_description = substr($reply, 0, 159);
                    }

                    $new_meta_description = $init_category->meta_description;
                }

                $init_category->save();

                $log_data = [
                    'id_category' => (int)$id_category,
                    'type' => pSQL($type),
                    'method' => "updateCategory",
                    'prompt' => pSQL(urldecode($prompt)),
                    'reply' => pSQL($reply),
                    'old_name' => pSQL($old_name),
                    'new_name' => pSQL($new_name),
                    'old_description' => pSQL($old_description),
                    'new_description' => pSQL($new_description),
                    'old_meta_title' => pSQL($old_meta_title),
                    'new_meta_title' => pSQL($new_meta_title),
                    'old_meta_description' => pSQL($old_meta_description),
                    'new_meta_description' => pSQL($new_meta_description),
                ];

                // add log
                Db::getInstance()->insert(
                    $this->name.'_log',
                    $log_data
                );

                $returned_data = [
                    "category_name" => $init_category->name,
                    "generated_content" => $reply,
                ];

                $arr = [
                    'status' => 'success',
                    'msg' => $this->translateString('Content successfully generated and updated to category.'),
                    'date' => $returned_data,
                ];
                echo json_encode($arr);
            } else {
                $arr = [
                    'status' => 'error',
                    'msg' => $this->translateString('Error! Action missing!'),
                ];
                echo json_encode($arr);
            }
        } else {
            $arr = [
                'status' => 'error',
                'msg' => $this->translateString('Error! Invalid token!'),
            ];
            echo json_encode($arr);
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

    private function translateString($mystring)
    {
        $mod_name = $this->name;
        $mod = Module::getInstanceByName($mod_name);
        return $mod->l($mystring, $mod_name);
    }
}
