<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is a helper class which provides some functions used all over the module
 */
class ElegantalEasyImportTools
{

    /**
     * Serializes array to store in database
     * @param array $array
     * @return string
     */
    public static function serialize($array)
    {
        // return Tools::jsonEncode($array);
        // return serialize($array);
        // return base64_encode(serialize($array));
        return call_user_func('base64_encode', serialize($array));
    }

    /**
     * Un-serializes serialized string
     * @param string $string
     * @return array
     */
    public static function unserialize($string)
    {
        // $array = Tools::jsonDecode($string, true);
        // $array = @unserialize($string);
        // $array = @unserialize(base64_decode($string));
        $array = @unserialize(call_user_func('base64_decode', $string));
        return empty($array) ? array() : $array;
    }

    /**
     * Returns formatted file size in GB, MB, KB or bytes
     * @param int $size
     * @return string
     */
    public static function displaySize($size)
    {
        $size = (int) $size;

        if ($size < 1024) {
            $size .= " bytes";
        } elseif ($size < 1048576) {
            $size = round($size / 1024) . " KB";
        } elseif ($size < 1073741824) {
            $size = round($size / 1048576, 1) . " MB";
        } else {
            $size = round($size / 1073741824, 1) . " GB";
        }

        return $size;
    }

    public static function getModifiedPriceByFormula($price, $formula)
    {
        $is_price_negative = false;
        if ($price < 0) {
            $is_price_negative = true;
            $price *= -1;
        }

        $formula = str_replace(' ', '', $formula);
        $price_modifier_matches = null;
        if ($formula && preg_match_all("/([\*\+\-\/])([0-9]+\.{0,1}[0-9]*)/", $formula, $price_modifier_matches) && isset($price_modifier_matches[0]) && isset($price_modifier_matches[1]) && isset($price_modifier_matches[2]) && is_array($price_modifier_matches[0]) && is_array($price_modifier_matches[1]) && is_array($price_modifier_matches[2])) {
            foreach ($price_modifier_matches[1] as $arithmetic_key => $arithmetic_operator) {
                $arithmetic_value = (float) $price_modifier_matches[2][$arithmetic_key];
                switch ($arithmetic_operator) {
                    case '*':
                        $price = $price * $arithmetic_value;
                        break;
                    case '/':
                        if ($arithmetic_value > 0) {
                            $price = $price / $arithmetic_value;
                        }
                        break;
                    case '+':
                        $price = $price + $arithmetic_value;
                        break;
                    case '-':
                        $price = $price - $arithmetic_value;
                        break;
                    default:
                        break;
                }
            }
        }

        if ($is_price_negative) {
            $price *= -1;
        }

        return (float) number_format($price, 6, '.', '');
    }

    /**
     * Deletes given file
     * @param string $filename
     * @return boolean
     * @throws Exception
     */
    public static function deleteTmpFile($filename)
    {
        if (empty($filename)) {
            return true;
        }

        $targetFile = self::getTempDir() . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($targetFile) && !@unlink($targetFile)) {
            throw new Exception("File could not be deleted.");
        }

        return true;
    }

    /**
     * Returns real path of given file
     * @param string $fileName
     * @return boolean|string
     */
    public static function getRealPath($fileName)
    {
        $targetFile = self::getTempDir() . DIRECTORY_SEPARATOR . $fileName;
        if ($fileName && file_exists($targetFile)) {
            return $targetFile;
        }
        return false;
    }

    /**
     * Creates path to given filename. This path is to a non-existing file. It is used to create the file.
     * @param string $fileName
     * @return string
     */
    public static function createPath($fileName)
    {
        if (empty($fileName)) {
            throw new Exception("File name is not valid.");
        }

        $targetDir = self::getTempDir() . DIRECTORY_SEPARATOR;
        $targetFile = $targetDir . $fileName;
        $fileType = Tools::strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);

        if (file_exists($targetFile)) {
            $count = 0;
            while (file_exists($targetFile)) {
                $count++;
                $targetFile = $targetDir . $fileNameWithoutExt . '_' . $count . '.' . $fileType;
            }
        }
        return $targetFile;
    }

    public static function convertToCsv($file, $extension, $entity, $multiple_value_separator)
    {
        if (!is_file($file)) {
            throw new Exception("File does not exist: " . $file);
        }

        switch ($extension) {
            case 'xls':
            case 'xlsx':
                self::excelToCsv($file);
                break;
            case 'xml':
                self::xmlToCsv($file, $entity, $multiple_value_separator);
                break;
            case 'json':
                self::jsonToCsv($file, $entity, $multiple_value_separator);
                break;
            default:
                break;
        }

        return true;
    }

    public static function excelToCsv($file)
    {
        // Require PHPExcel library
        $lib = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendors' . DIRECTORY_SEPARATOR . 'PHPExcel-1.8' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'IOFactory.php';
        if (file_exists($lib) && is_file($lib)) {
            require_once($lib);
        } else {
            throw new Exception("PHPExcel library could not be loaded.");
        }

        // Load Excel data
        $objPHPExcel = PHPExcel_IOFactory::load($file);

        // Save as CSV
        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        $writer->setUseBOM(true);
        $writer->save($file);

        return true;
    }

    public static function xmlToCsv($file, $entity, $multiple_value_separator)
    {
        /* $f = fopen($file, 'r');
          $line = fgets($f);
          $line = trim($line);
          fclose($f);
          if ($line == 'This XML file does not appear to have any style information associated with it. The document tree is shown below.') {
          $fileArr = file($file);
          unset($fileArr[0]);
          file_put_contents($file, $fileArr);
          }
         */

        // Replace & in XML file, as it is not allowed
        $file_content = Tools::file_get_contents($file);
        $file_content = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $file_content);
        file_put_contents($file, $file_content);
        unset($file_content);

        // Load XML and convert to associative array. LIBXML_NOCDATA ignores <![CDATA[...]]>
        $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (empty($xml) || !is_object($xml)) {
            throw new Exception("XML file is not valid.");
        }

        // Make changes in xml object here if necessary
        if (isset($xml->shop) && isset($xml->shop->categories) && isset($xml->shop->offers->offer)) {
            // Get categories into array
            $shop_categories = array();
            if (isset($xml->shop->categories)) {
                foreach ($xml->shop->categories->category as $category) {
                    $shop_categories[(int) $category->attributes()->id[0]] = $category . "";
                }
            }
            foreach ($xml->shop->offers->offer as $offer) {
                // Replace category ID with category name
                if (isset($offer->categoryId) && isset($shop_categories[(int) $offer->categoryId]) && $shop_categories[(int) $offer->categoryId]) {
                    $offer->categoryId[0] = $shop_categories[(int) $offer->categoryId];
                }
                // Parse features into one column
                if (isset($offer->param)) {
                    $n = 1;
                    foreach ($offer->param as $param) {
                        $param[0] = (isset($param->attributes()->name[0]) ? $param->attributes()->name[0] . "" : "") . ":" . $param . ":" . $n . ":0";
                        $n++;
                    }
                }
            }
        }
        if (isset($xml->categories) && isset($xml->categories->category) && isset($xml->products->product)) {
            $shop_categories = array();
            // Anonymous function to get category name including its parents
            $getCategoriesWithParents = function ($xml_category) use (&$getCategoriesWithParents, $xml) {
                if (empty($xml_category->parent[0] . "")) {
                    return $xml_category->name[0] . "";
                }
                $parent_cat = null;
                foreach ($xml->categories->category as $tmp_cat) {
                    if ($tmp_cat->id[0] . "" == $xml_category->parent[0] . "") {
                        $parent_cat = $tmp_cat;
                        break;
                    }
                }
                return ($parent_cat) ? $getCategoriesWithParents($parent_cat) . "->" . $xml_category->name[0] : $xml_category->name[0] . "";
            };
            foreach ($xml->categories->category as $category) {
                $shop_categories[(int) $category->id[0]] = $getCategoriesWithParents($category);
            }
            foreach ($xml->products->product as $xml_product) {
                if (isset($xml_product->categories->category)) {
                    foreach ($xml_product->categories->category as $xml_cat) {
                        $xml_cat[0] = isset($shop_categories[(int) $xml_cat[0]]) ? $shop_categories[(int) $xml_cat[0]] : "";
                    }
                }
            }
        }
        if (isset($xml->categories) && isset($xml->categories->category) && isset($xml->product)) {
            $shop_categories = array();
            foreach ($xml->categories->category as $category) {
                $shop_categories[(int) $category->id[0]] = $category->name[0] . "";
            }
            foreach ($xml->product as $xml_product) {
                if (isset($xml_product->categories->category)) {
                    foreach ($xml_product->categories->category as $xml_cat) {
                        $xml_cat[0] = isset($shop_categories[(int) $xml_cat[0]]) ? $shop_categories[(int) $xml_cat[0]] : "";
                    }
                }
            }
        }
        if (isset($xml->attributes()->file_format) && isset($xml->attributes()->generated) && isset($xml->attributes()->version) && isset($xml->attributes()->extensions) && isset($xml->products->product)) {
            foreach ($xml->products->product as $xml_product) {
                if (isset($xml_product->description->name)) {
                    $xml_name_count = 1;
                    foreach ($xml_product->description->name as $xml_name) {
                        $xml_product->{"name_" . $xml_name_count} = $xml_name . "";
                        $xml_name_count++;
                    }
                }
                if (isset($xml_product->description->long_desc)) {
                    $xml_long_desc_count = 1;
                    foreach ($xml_product->description->long_desc as $xml_long_desc) {
                        $xml_product->{"long_desc_" . $xml_long_desc_count} = $xml_long_desc . "";
                        $xml_long_desc_count++;
                    }
                }
            }
        }
        if (isset($xml->Product) && isset($xml->Product[0]->SellingPrices)) {
            foreach ($xml->Product as $product) {
                if (isset($product->SellingPrices->SellingPrice)) {
                    $count = 1;
                    foreach ($product->SellingPrices->SellingPrice as $sellingPrice) {
                        $product->addChild("SellingPrice" . $count . "_Price", $sellingPrice->Price . "");
                        $product->addChild("SellingPrice" . $count . "_PriceIncludingVAT", $sellingPrice->PriceIncludingVAT . "");
                        $product->addChild("SellingPrice" . $count . "_MinQuantity", $sellingPrice->MinQuantity . "");
                        $count++;
                    }
                }
                foreach ($product as $key => $child) {
                    // Remove original nodes that has lang attribute and create new nodes for lang
                    if (isset($child->attributes()->lang) && $child->attributes()->lang && count($product->{$key}) > 1) {
                        $count = 0;
                        foreach ($product->{$key} as $node) {
                            if (isset($node->attributes()->lang)) {
                                $lang = Tools::strtoupper(preg_replace("/[^a-zA-Z]+/", "", $node->attributes()->lang . ""));
                                if ($lang && !$product->{$key . $lang}) {
                                    $product->addChild($key . $lang, $node . "");
                                    unset($product->{$key}[$count]);
                                }
                            }
                            $count++;
                        }
                    }
                }
            }
        }
        if ($entity == 'combination' && isset($xml->Product[0]->Product_code) && isset($xml->Product[0]->variants->variant[0]->spec)) {
            foreach ($xml->Product as $product) {
                foreach ($product->variants->variant as $variant) {
                    $attribute_names = "";
                    $attribute_values = "";
                    if (isset($variant->spec)) {
                        foreach ($variant->spec as $spec) {
                            $attribute_names .= $attribute_names ? $multiple_value_separator : "";
                            $attribute_names .= $spec->attributes()->name . "";
                            $attribute_values .= $attribute_values ? $multiple_value_separator : "";
                            $attribute_values .= $spec . "";
                        }
                    }
                    $variant->AttributeNames = $attribute_names;
                    $variant->AttributeValues = $attribute_values;
                }
            }
        }

        // Here you can remove namespaces if exist. This code is generic but you can allow it only for xml files that need it.
        if (isset($xml->channel) && isset($xml->channel->title) && isset($xml->channel->link) && isset($xml->channel->description)) {
            $namespaces = $xml->getDocNamespaces(true);
            if ($namespaces && is_array($namespaces)) {
                $file_content = Tools::file_get_contents($file);
                foreach ($namespaces as $ns => $ns_url) {
                    $file_content = str_replace(array('<' . $ns . ':', '</' . $ns . ':'), array('<', '</'), $file_content);
                }
                file_put_contents($file, $file_content);
                unset($file_content);
                $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
            }
        }

        $json = Tools::jsonEncode($xml);
        $first_array = Tools::jsonDecode($json, true);
        $array = $first_array;

        if (isset($array['@attributes'])) {
            unset($array['@attributes']);
        }
        if (isset($array['message_header'])) {
            unset($array['message_header']);
        }
        if (isset($array['products']['@attributes'])) {
            unset($array['products']['@attributes']);
        }
        if (isset($array['Header'])) {
            unset($array['Header']);
        }
        if (isset($array['Service']['Header'])) {
            unset($array['Service']['Header']);
        }
        if (isset($array['envelop'])) {
            unset($array['envelop']);
        }
        if (isset($array['channel']['title'])) {
            unset($array['channel']['title']);
        }
        if (isset($array['channel']['link'])) {
            unset($array['channel']['link']);
        }
        if (isset($array['channel']['description'])) {
            unset($array['channel']['description']);
        }
        if (isset($array['created_at'])) {
            unset($array['created_at']);
        }
        if (isset($array['EXPORT_INFO'])) {
            unset($array['EXPORT_INFO']);
        }
        if (isset($array['DATE'])) {
            unset($array['DATE']);
        }
        if (isset($array['HEAD'])) {
            unset($array['HEAD']);
        }
        if (isset($array['headerInfo'])) {
            unset($array['headerInfo']);
        }
        if (isset($array['categories'])) {
            unset($array['categories']);
        }
        if (isset($array['shop']['name'])) {
            unset($array['shop']['name']);
        }
        if (isset($array['shop']['company'])) {
            unset($array['shop']['company']);
        }
        if (isset($array['shop']['currencies'])) {
            unset($array['shop']['currencies']);
        }
        if (isset($array['shop']['categories'])) {
            unset($array['shop']['categories']);
        }
        if (isset($array['shop']['url'])) {
            unset($array['shop']['url']);
        }
        if (isset($array['estado'])) {
            unset($array['estado']);
        }
        if (isset($array['Дата'])) {
            unset($array['Дата']);
        }
        if (isset($array['DocTimeStamp'])) {
            unset($array['DocTimeStamp']);
        }
        if (isset($array['datetime'])) {
            unset($array['datetime']);
        }
        if (isset($array['title'])) {
            unset($array['title']);
        }
        if (isset($array['link'])) {
            unset($array['link']);
        }
        if (isset($array['description'])) {
            unset($array['description']);
        }
        if (isset($array['site_url'])) {
            unset($array['site_url']);
        }
        if (isset($array['created_for_customer_email'])) {
            unset($array['created_for_customer_email']);
        }
        if (isset($array['customer_key'])) {
            unset($array['customer_key']);
        }
        if (isset($array['customer_categories'])) {
            unset($array['customer_categories']);
        }
        if (isset($array['products_found_num'])) {
            unset($array['products_found_num']);
        }
        if (isset($array['time_for_creation'])) {
            unset($array['time_for_creation']);
        }
        if (isset($array['shop']['platform'])) {
            unset($array['shop']['platform']);
        }
        if (isset($array['shop']['version'])) {
            unset($array['shop']['version']);
        }
        if (isset($array['shop']['agency'])) {
            unset($array['shop']['agency']);
        }
        if (isset($array['shop']['email'])) {
            unset($array['shop']['email']);
        }
        if (isset($array['shop']['local_delivery_cost'])) {
            unset($array['shop']['local_delivery_cost']);
        }
        if (isset($array['COMP_CODE'])) {
            unset($array['COMP_CODE']);
        }
        if (isset($array['LANG'])) {
            unset($array['LANG']);
        }
        if (isset($array['COMP_CODE_BUYER'])) {
            unset($array['COMP_CODE_BUYER']);
        }
        if (isset($array['SEARCH_CODE'])) {
            unset($array['SEARCH_CODE']);
        }
        if (isset($array['MANUFACTURER_NAME'])) {
            unset($array['MANUFACTURER_NAME']);
        }
        if (isset($array['TYPE_NAME'])) {
            unset($array['TYPE_NAME']);
        }
        if (isset($array['SUPPLY_TYPE'])) {
            unset($array['SUPPLY_TYPE']);
        }
        if (isset($array['total_products'])) {
            unset($array['total_products']);
        }
        if ($entity == 'combination' && isset($array['Produs']) && is_array($array['Produs']) && isset($array['Produs'][0]) && is_array($array['Produs'][0]) && isset($array['Produs'][0]['Combinatii'])) {
            foreach ($array['Produs'] as $key => $produs) {
                $array['Produs'][$key]['Combination Attributes'] = '';
                $array['Produs'][$key]['Combination Values'] = '';
                $array['Produs'][$key]['Combination Referinta'] = '';
                $array['Produs'][$key]['Combination EAN13'] = '';
                $array['Produs'][$key]['Combination DataDisponibilitate'] = '';
                $array['Produs'][$key]['Combination StocFurnizor'] = '';
                $array['Produs'][$key]['Combination Stoc'] = '';
                if (isset($produs['Combinatii']) && is_array($produs['Combinatii']) && isset($produs['Combinatii']['Combinatie']) && is_array($produs['Combinatii']['Combinatie']) && $produs['Combinatii']['Combinatie']) {
                    if (isset($produs['Combinatii']['Combinatie'][0]) && is_array($produs['Combinatii']['Combinatie'][0]) && isset($produs['Combinatii']['Combinatie'][0]['Nume'])) {
                        foreach ($produs['Combinatii']['Combinatie'] as $combination_key => $combinatie) {
                            $combinatie_numes = explode(';', $combinatie['Nume']);
                            if ($combination_key === 0) {
                                foreach ($combinatie_numes as $combinatie_nume) {
                                    $combinatie_nume = explode(':', $combinatie_nume);
                                    $array['Produs'][$key]['Combination Attributes'] .= $array['Produs'][$key]['Combination Attributes'] ? $multiple_value_separator : '';
                                    $array['Produs'][$key]['Combination Attributes'] .= trim($combinatie_nume[0]);
                                    $array['Produs'][$key]['Combination Values'] .= $array['Produs'][$key]['Combination Values'] ? $multiple_value_separator : '';
                                    $array['Produs'][$key]['Combination Values'] .= trim($combinatie_nume[1]);
                                }
                                $array['Produs'][$key]['Combination Referinta'] = trim($combinatie['Referinta']);
                                $array['Produs'][$key]['Combination EAN13'] = trim($combinatie['EAN13']);
                                $array['Produs'][$key]['Combination DataDisponibilitate'] = trim($combinatie['DataDisponibilitate']);
                                $array['Produs'][$key]['Combination StocFurnizor'] = trim($combinatie['StocFurnizor']);
                                $array['Produs'][$key]['Combination Stoc'] = trim($combinatie['Stoc']);
                            } else {
                                $produs['Combination Attributes'] = '';
                                $produs['Combination Values'] = '';
                                foreach ($combinatie_numes as $combinatie_nume) {
                                    $combinatie_nume = explode(':', $combinatie_nume);
                                    $produs['Combination Attributes'] .= $produs['Combination Attributes'] ? $multiple_value_separator : '';
                                    $produs['Combination Attributes'] .= trim($combinatie_nume[0]);
                                    $produs['Combination Values'] .= $produs['Combination Values'] ? $multiple_value_separator : '';
                                    $produs['Combination Values'] .= trim($combinatie_nume[1]);
                                }
                                $produs['Combination Referinta'] = trim($combinatie['Referinta']);
                                $produs['Combination EAN13'] = trim($combinatie['EAN13']);
                                $produs['Combination DataDisponibilitate'] = trim($combinatie['DataDisponibilitate']);
                                $produs['Combination StocFurnizor'] = trim($combinatie['StocFurnizor']);
                                $produs['Combination Stoc'] = trim($combinatie['Stoc']);
                                $array['Produs'][] = $produs;
                            }
                        }
                    } elseif (isset($produs['Combinatii']['Combinatie']['Nume'])) {
                        $combinatie_numes = explode(';', $produs['Combinatii']['Combinatie']['Nume']);
                        foreach ($combinatie_numes as $combinatie_nume) {
                            $combinatie_nume = explode(':', $combinatie_nume);
                            $array['Produs'][$key]['Combination Attributes'] .= $array['Produs'][$key]['Combination Attributes'] ? $multiple_value_separator : '';
                            $array['Produs'][$key]['Combination Attributes'] .= trim($combinatie_nume[0]);
                            $array['Produs'][$key]['Combination Values'] .= $array['Produs'][$key]['Combination Values'] ? $multiple_value_separator : '';
                            $array['Produs'][$key]['Combination Values'] .= trim($combinatie_nume[1]);
                        }
                        $array['Produs'][$key]['Combination Referinta'] = trim($produs['Combinatii']['Combinatie']['Referinta']);
                        $array['Produs'][$key]['Combination EAN13'] = trim($produs['Combinatii']['Combinatie']['EAN13']);
                        $array['Produs'][$key]['Combination DataDisponibilitate'] = trim($produs['Combinatii']['Combinatie']['DataDisponibilitate']);
                        $array['Produs'][$key]['Combination StocFurnizor'] = trim($produs['Combinatii']['Combinatie']['StocFurnizor']);
                        $array['Produs'][$key]['Combination Stoc'] = trim($produs['Combinatii']['Combinatie']['Stoc']);
                    }
                }
            }
        }
        if (isset($array['Ladu'][0]['Rida']) && is_array($array['Ladu'][0]['Rida']) && isset($array['Ladu'][0]['Rida'][0]['s']) && isset($array['Ladu'][0]['Rida'][0]['a'])) {
            $array_new = array();
            foreach ($array['Ladu'] as $ladu) {
                $array_new = array_merge($array_new, $ladu['Rida']);
            }
            $array = $array_new;
        }
        if (isset($array['CODEBOOKS']) && isset($array['PRODUCTS'])) {
            unset($array['CODEBOOKS']);
        }

        // Find array of products. Array of products are under a node which has numeric keys.
        $key = key($array);
        while ($key !== 0 && $key && isset($array[$key])) {
            $array = $array[$key];
            if (is_array($array)) {
                $key = key($array);
            } else {
                $key = false;
            }
        }

        if (!is_array($array)) {
            $array = $first_array;
        }

        // Process products array before writing to csv
        if ($entity == 'combination' && isset($array[0]) && isset($array[0]['id']) && isset($array[0]['title']) && isset($array[0]['sku']) && isset($array[0]['categories']) && isset($array[0]['childrens'])) {
            foreach ($array as $key => $product) {
                if (isset($product['childrens']['child']) && is_array($product['childrens']['child'])) {
                    foreach ($product['childrens']['child'] as $child) {
                        $product['Combination SKU'] = $child['sku'];
                        $product['Size'] = $child['size'];
                        $product['Count'] = $child['count'];
                        unset($product['description']);
                        unset($product['childrens']);
                        $array[] = $product;
                    }
                    unset($array[$key]);
                }
            }
        } elseif ($entity == 'combination' && isset($array[0]) && isset($array[0]['model']) && isset($array[0]['category_name']) && isset($array[0]['variants'])) {
            foreach ($array as $key => $product) {
                if (isset($product['variants']['variant']) && is_array($product['variants']['variant'])) {
                    foreach ($product['variants']['variant'] as $variant) {
                        if (isset($variant['@attributes']['code']) && isset($variant['@attributes']['size'])) {
                            $product['Combination Code'] = $variant['@attributes']['code'];
                            $product['Size'] = $variant['@attributes']['size'];
                            unset($product['description']);
                            unset($product['description_long']);
                            unset($product['variants']);
                            $array[] = $product;
                        }
                    }
                    unset($array[$key]);
                }
            }
        } elseif ($entity == 'combination' && isset($array[0]) && isset($array[0]['NAME']) && isset($array[0]['ITEM_TYPE']) && isset($array[0]['VARIANTS'])) {
            foreach ($array as $key => $product) {
                if (isset($product['VARIANTS']['VARIANT']) && is_array($product['VARIANTS']['VARIANT'])) {
                    foreach ($product['VARIANTS']['VARIANT'] as $variant) {
                        if (isset($variant['PARAMETERS']['PARAMETER']) && is_array($variant['PARAMETERS']['PARAMETER'])) {
                            $comb_attributes = "";
                            $comb_values = "";
                            if (isset($variant['PARAMETERS']['PARAMETER']['NAME']) && isset($variant['PARAMETERS']['PARAMETER']['VALUE'])) {
                                $comb_attributes = $variant['PARAMETERS']['PARAMETER']['NAME'];
                                $comb_values = $variant['PARAMETERS']['PARAMETER']['VALUE'];
                            } elseif (isset($variant['PARAMETERS']['PARAMETER'][0]['NAME']) && isset($variant['PARAMETERS']['PARAMETER'][0]['VALUE'])) {
                                foreach ($variant['PARAMETERS']['PARAMETER'] as $parameter) {
                                    $comb_attributes .= $comb_attributes ? $multiple_value_separator : "";
                                    $comb_attributes .= $parameter['NAME'];
                                    $comb_values .= $comb_values ? $multiple_value_separator : "";
                                    $comb_values .= $parameter['VALUE'];
                                }
                            }
                            $product['VARIANT PARAMETER NAME'] = $comb_attributes;
                            $product['VARIANT PARAMETER VALUE'] = $comb_values;
                            $product['VARIANT CODE'] = $variant['CODE'];
                            $product['VARIANT EAN'] = $variant['EAN'];
                            $product['VARIANT CURRENCY'] = $variant['CURRENCY'];
                            $product['VARIANT VAT'] = $variant['VAT'];
                            $product['VARIANT PRICE'] = $variant['PRICE'];
                            $product['VARIANT PURCHASE_PRICE'] = $variant['PURCHASE_PRICE'];
                            $product['VARIANT STANDARD_PRICE'] = $variant['STANDARD_PRICE'];
                            $product['VARIANT PRICE_VAT'] = $variant['PRICE_VAT'];
                            $product['VARIANT AVAILABILITY'] = $variant['AVAILABILITY'];
                            unset($product['SHORT_DESCRIPTION']);
                            unset($product['DESCRIPTION']);
                            unset($product['CATEGORIES']);
                            unset($product['VARIANTS']);
                            $array[] = $product;
                        }
                    }
                    unset($array[$key]);
                }
            }
        } elseif ($entity == 'combination' && isset($array[0]['Product_code']) && isset($array[0]['variants']['variant'])) {
            foreach ($array as $key => $product) {
                if (isset($product['variants']['variant'][0]['spec'])) {
                    foreach ($product['variants']['variant'] as $variant) {
                        unset($variant['spec']);
                        $new_product = array_merge(array('Product_code' => $product['Product_code']), $variant);
                        $array[] = $new_product;
                        unset($array[$key]);
                    }
                }
            }
        } elseif (isset($array[0]) && isset($array[0]['DELIVERY']) && isset($array[0]['DELIVERY'][0]['DELIVERY_ID']) && $array[0]['DELIVERY'][0]['DELIVERY_ID'] && isset($array[0]['DELIVERY'][0]['DELIVERY_PRICE'])) {
            foreach ($array as $key => $product) {
                if (isset($product['DELIVERY']) && is_array($product['DELIVERY'])) {
                    $delivery_id = "";
                    $delivery_price = "";
                    foreach ($product['DELIVERY'] as $delivery) {
                        if (isset($delivery['DELIVERY_ID']) && isset($delivery['DELIVERY_PRICE'])) {
                            $delivery_id .= $delivery_id ? $multiple_value_separator : "";
                            $delivery_id .= $delivery['DELIVERY_ID'];
                            $delivery_price .= $delivery_price ? $multiple_value_separator : "";
                            $delivery_price .= $delivery['DELIVERY_PRICE'];
                        }
                    }
                    $array[$key]['DELIVERY_ID'] = $delivery_id;
                    $array[$key]['DELIVERY_PRICE'] = $delivery_price;
                }
            }
        } elseif (isset($array[0]) && isset($array[0]['inventory']['quantity']) && isset($array[0]['inventory']['price'])) {
            foreach ($array as $key => $product) {
                if (isset($product['inventory']) && is_array($product['inventory']) && isset($product['inventory']['quantity']) && isset($product['inventory']['price'])) {
                    $array[$key]['inventory_quantity'] = $product['inventory']['quantity'];
                    $array[$key]['inventory_price'] = $product['inventory']['price'];
                    unset($array[$key]['inventory']);
                }
            }
        }

        // Open file pointer
        $handle = fopen($file, 'w');

        // Write CSV header
        // Build header from all rows, because some rows may have columns that does not exist on other rows.
        $header = array();
        foreach ($array as $product) {
            if (!is_array($product)) {
                continue;
            }
            if (isset($product['@attributes'])) {
                // Add attributes as columns
                $attributes = $product['@attributes'];
                unset($product['@attributes']);
                $product = array_merge($attributes, $product);
            }
            foreach ($product as $attr => $value) {
                if (!in_array($attr, $header)) {
                    $header[] = $attr;
                }
            }
        }
        fputcsv($handle, $header, ';', '"');

        // Write each row to csv
        foreach ($array as $product) {
            if (!is_array($product)) {
                continue;
            }
            if (isset($product['@attributes'])) {
                // Add attributes as columns
                $attributes = $product['@attributes'];
                unset($product['@attributes']);
                $product = array_merge($attributes, $product);
            }
            // Remove unwanted data
            if (isset($product['category']['@attributes']['id'])) {
                unset($product['category']['@attributes']['id']);
            }
            if (isset($product['producer']['@attributes']['id'])) {
                unset($product['producer']['@attributes']['id']);
            }
            if (isset($product['unit']['@attributes']['id'])) {
                unset($product['unit']['@attributes']['id']);
            }
            if (isset($product['Manufacturer']['Id'])) {
                unset($product['Manufacturer']['Id']);
            }
            if (isset($product['Tax']['Id'])) {
                unset($product['Tax']['Id']);
            }
            if (isset($product['SellingPrices']['SellingPrice']['Price']) && $product['SellingPrices']['SellingPrice']['Price']) {
                $product['SellingPrices'] = $product['SellingPrices']['SellingPrice']['Price'];
            }
            if (isset($product['ProdCategories']['ProdCategory']['Id'])) {
                unset($product['ProdCategories']['ProdCategory']['Id']);
            }
            if (isset($product['ProdCategories']['ProdCategory']['Code'])) {
                unset($product['ProdCategories']['ProdCategory']['Code']);
            }
            if (isset($product['ProdCategories']['ProdCategory']['FullPathName']) && $product['ProdCategories']['ProdCategory']['FullPathName']) {
                $product['ProdCategories'] = explode(' / ', $product['ProdCategories']['ProdCategory']['FullPathName']);
                if (isset($product['MainProdCategory'])) {
                    $product['MainProdCategory'] = $product['ProdCategories'];
                }
            }
            if (isset($product['ProdCategories']['ProdCategory'][0]['FullPathName']) && $product['ProdCategories']['ProdCategory'][0]['FullPathName']) {
                if (isset($product['MainProdCategory']) && $product['MainProdCategory']) {
                    $MainProdCategory = $product['MainProdCategory'];
                    // default value
                    $product['MainProdCategory'] = explode(' / ', $product['ProdCategories']['ProdCategory'][0]['FullPathName']);
                    // find default category by id
                    foreach ($product['ProdCategories']['ProdCategory'] as $key => $ProdCategory) {
                        if ($ProdCategory['Id'] == $MainProdCategory) {
                            $product['MainProdCategory'] = explode(' / ', $ProdCategory['FullPathName']);
                            unset($product['ProdCategories']['ProdCategory'][$key]);
                            break;
                        }
                    }
                }
                $product['ProdCategories']['ProdCategory'] = reset($product['ProdCategories']['ProdCategory']);
                $product['ProdCategories'] = explode(' / ', $product['ProdCategories']['ProdCategory']['FullPathName']);
            }
            if (isset($product['Photos']['Photo']) && $product['Photos']['Photo'] && is_array($product['Photos']['Photo'])) {
                if (isset($product['Photos']['Photo'][0]) && is_array($product['Photos']['Photo'][0])) {
                    foreach ($product['Photos']['Photo'] as &$photo) {
                        if (isset($photo['RelativeFilePath'])) {
                            $photo = $photo['RelativeFilePath'];
                        }
                    }
                } elseif (isset($product['Photos']['Photo']['RelativeFilePath'])) {
                    $product['Photos']['Photo'] = $product['Photos']['Photo']['RelativeFilePath'];
                }
            }
            if (isset($first_array['SHOPITEM']) && (isset($product['ITEMGROUP_ID']) || isset($product['ITEM_GROUP_ID']))) {
                if (isset($product['PRODUCTNAME']) && isset($product['VARIANT']) && $product['VARIANT']) {
                    $product['PRODUCTNAME'] .= " - " . $product['VARIANT'];
                }
                if (isset($product['PARAM']) && $product['PARAM'] && is_array($product['PARAM'])) {
                    if (isset($product['PARAM']['PARAM_NAME']) && isset($product['PARAM']['VAL'])) {
                        $product['PARAM'] = $product['PARAM']['PARAM_NAME'] . ":" . $product['PARAM']['VAL'];
                    } elseif (isset($product['PARAM'][0]) && is_array($product['PARAM'][0])) {
                        $product_param = "";
                        foreach ($product['PARAM'] as $param) {
                            if (isset($param['PARAM_NAME']) && isset($param['VAL'])) {
                                $product_param .= $product_param ? $multiple_value_separator : "";
                                $product_param .= $param['PARAM_NAME'] . ":" . $param['VAL'];
                            }
                        }
                        $product['PARAM'] = $product_param;
                    }
                }
            }
            if (isset($product['Categoria']) && isset($product['SubCat1']) && isset($product['SubCat2'])) {
                if ($product['SubCat1']) {
                    $product['Categoria'] .= ',' . $product['SubCat1'];
                }
                if ($product['SubCat2']) {
                    $product['Categoria'] .= ',' . $product['SubCat2'];
                }
            }
            if (isset($product['IMGURL']) && isset($product['IMGURL_ALTERNATIVE']) && $product['IMGURL_ALTERNATIVE']) {
                if (is_array($product['IMGURL_ALTERNATIVE'])) {
                    $product['IMGURL'] = array_merge(array($product['IMGURL']), $product['IMGURL_ALTERNATIVE']);
                } else {
                    $product['IMGURL'] = array($product['IMGURL'], $product['IMGURL_ALTERNATIVE']);
                }
            }
            if (isset($product['stock']['inStockLocal']) && isset($product['stock']['inStockCentral'])) {
                $product['stock'] = $product['stock']['inStockLocal'] ? $product['stock']['inStockLocal'] : $product['stock']['inStockCentral'];
            }
            if (isset($product['priceLevels']['normalPricing']['price'])) {
                $product['priceLevels'] = $product['priceLevels']['normalPricing']['price'];
            }
            if (isset($product['description']) && is_array($product['description']) && isset($product['description']['name'][0]) && is_array($product['description']['name']) && isset($product['description']['long_desc'][0])) {
                $product['description'] = $product['description']['long_desc'][0];
            }
            if (isset($product['price']) && is_array($product['price']) && isset($product['price']['@attributes']['gross']) && isset($product['price']['@attributes']['net']) && isset($product['price']['@attributes']['vat'])) {
                $product['price'] = $product['price']['@attributes']['vat'];
            }
            if (isset($product['images']['icons']['icon'])) {
                unset($product['images']['icons']);
            }
            if (isset($product['images']['icons']['icon'])) {
                unset($product['images']['icons']);
            }
            if (isset($product['images']['large']['image']['@attributes']['url'])) {
                $product['images'] = $product['images']['large']['image']['@attributes']['url'];
            }
            if (isset($product['Tax']) && isset($product['Tax']['Code']) && isset($product['Tax']['PercentAmount'])) {
                $product['Tax'] = $product['Tax']['PercentAmount'] . '% VAT';
            }
            if (isset($product['arrivi']['arrivo']['qta'])) {
                $product['arrivi'] = $product['arrivi']['arrivo']['qta'];
            }
            if (isset($product['arrivi']['arrivo'][0]['qta'])) {
                $product['arrivi'] = $product['arrivi']['arrivo'][0]['qta'];
            }
            if (isset($product['Pctrs']['@attributes'])) {
                unset($product['Pctrs']['@attributes']);
            }
            if (isset($product['AttrSet']['@attributes'])) {
                unset($product['AttrSet']['@attributes']);
            }
            if (isset($product['AttrSet']['ItmAttr']) && is_array($product['AttrSet']['ItmAttr']) && $product['AttrSet']['ItmAttr']) {
                $AttrSet = "";
                foreach ($product['AttrSet']['ItmAttr'] as $key => $ItmAttr) {
                    if (isset($ItmAttr['@attributes']['No']) && isset($ItmAttr['@attributes']['Desc'])) {
                        $AttrSet .= $AttrSet ? $multiple_value_separator : "";
                        $AttrSet .= $ItmAttr['@attributes']['Desc'] . ":" . $ItmAttr['@attributes']['No'] . ":" . ($key + 1) . ":0";
                    }
                }
                $product['AttrSet'] = $AttrSet;
            }
            if (isset($product['Cats']['Cat'][0]['Sub']) && is_array($product['Cats']['Cat'][0]['Sub'])) {
                foreach ($product['Cats']['Cat'] as $key => $cat) {
                    if (is_array($cat['Sub']) && $cat['Sub']) {
                        $subCats = "";
                        foreach ($cat['Sub'] as $sub) {
                            $subCats .= $subCats ? "/" : "";
                            $subCats .= $sub;
                        }
                        $product['Cats']['Cat'][$key] = $subCats;
                    }
                }
            }
            if (isset($product['ProductCode']) && isset($product['AttrList']) && is_array($product['AttrList']) && isset($product['AttrList']['element']) && is_array($product['AttrList']['element'])) {
                $AttrListFeatures = "";
                if (isset($product['AttrList']['element']['@attributes']) && isset($product['AttrList']['element']['@attributes']['Name']) && $product['AttrList']['element']['@attributes']['Name'] && isset($product['AttrList']['element']['@attributes']['Value']) && $product['AttrList']['element']['@attributes']['Value']) {
                    $AttrListFeatures = $product['AttrList']['element']['@attributes']['Name'] . ':' . Tools::substr(strip_tags($product['AttrList']['element']['@attributes']['Value']), 0, 255);
                } else {
                    foreach ($product['AttrList']['element'] as $AttrListElement) {
                        if (isset($AttrListElement['@attributes']) && is_array($AttrListElement['@attributes']) && isset($AttrListElement['@attributes']['Name']) && $AttrListElement['@attributes']['Name'] && isset($AttrListElement['@attributes']['Value']) && $AttrListElement['@attributes']['Value']) {
                            $AttrListFeatures .= $AttrListFeatures ? $multiple_value_separator : '';
                            $AttrListFeatures .= $AttrListElement['@attributes']['Name'] . ':' . Tools::substr(strip_tags($AttrListElement['@attributes']['Value']), 0, 255);
                        }
                    }
                }
                $product['AttrList'] = $AttrListFeatures;
            }
            if ($entity == 'product' && isset($first_array['total_products']) && isset($first_array['products']['product']) && is_array($first_array['products']['product'])) {
                if (isset($product['attributes']['attribute']) && is_array($product['attributes']['attribute'])) {
                    $product_attributes_final = "";
                    if (isset($product['attributes']['attribute']['@attributes']) && is_array($product['attributes']['attribute']['@attributes'])) {
                        $product['attributes']['attribute'] = array($product['attributes']['attribute']);
                    }
                    foreach ($product['attributes']['attribute'] as $product_attributes) {
                        $product_attributes_final .= $product_attributes_final ? $multiple_value_separator : '';
                        $product_attributes_final .= $product_attributes['@attributes']['name'] . ':' . (is_array($product_attributes['values']['value']) ? implode('/', $product_attributes['values']['value']) : $product_attributes['values']['value']);
                    }
                    $product['attributes'] = $product_attributes_final;
                }
                if (isset($product['prices']['price']) && is_array($product['prices']['price']) && isset($product['prices']['price'][0]['price_sell'])) {
                    $product['prices'] = $product['prices']['price'][0]['price_sell'];
                }
            }
            if (isset($product['artnum']) && isset($product['attributes']) && is_array($product['attributes']) && isset($product['attributes']['attribute']) && is_array($product['attributes']['attribute'])) {
                if (isset($product['attributes']['attribute'][0]['attributetitle']) && isset($product['attributes']['attribute'][0]['attributevalue'])) {
                    $features = "";
                    foreach ($product['attributes']['attribute'] as $feature) {
                        $features .= $features ? $multiple_value_separator : "";
                        $features .= $feature['attributetitle'] . ":" . $feature['attributevalue'];
                    }
                    $product['attributes'] = $features;
                } elseif (isset($product['attributes']['attribute']['attributetitle']) && isset($product['attributes']['attribute']['attributevalue'])) {
                    $product['attributes'] = $product['attributes']['attribute']['attributetitle'] . ":" . $product['attributes']['attribute']['attributevalue'];
                }
            }
            if (isset($first_array['Produs']) && is_array($first_array['Produs']) && isset($product['IdProdus']) && $product['IdProdus'] && isset($product['Stoc']) && isset($product['StocFurnizor'])) {
                if ($product['StocFurnizor'] > $product['Stoc']) {
                    $product['Stoc'] = $product['StocFurnizor'];
                }
            }
            if (isset($product['TECHNICAL_ATTACHMENT']['ITEM'][0]['URL'])) {
                $tech_attachment = "";
                foreach ($product['TECHNICAL_ATTACHMENT']['ITEM'] as $tech_attach_item) {
                    $tech_attachment .= $tech_attachment ? $multiple_value_separator : "";
                    $tech_attachment .= $tech_attach_item['URL'];
                }
                $product['TECHNICAL_ATTACHMENT'] = trim($tech_attachment);
            }

            // Take care of multiple values
            foreach ($product as $attr => $value) {
                if (is_array($value) && empty($value)) {
                    $product[$attr] = "";
                    continue;
                } elseif (!is_array($value) || empty($value)) {
                    continue;
                }
                $new_value = "";
                foreach ($value as $sub_value) {
                    if (is_array($sub_value) && $sub_value) {
                        foreach ($sub_value as $sub_sub_value) {
                            if (is_array($sub_sub_value) && $sub_sub_value) {
                                foreach ($sub_sub_value as $sub_sub_sub_value) {
                                    if (is_array($sub_sub_sub_value) && $sub_sub_sub_value) {
                                        foreach ($sub_sub_sub_value as $sub_sub_sub_sub_value) {
                                            if (!is_array($sub_sub_sub_sub_value)) {
                                                $new_value .= $new_value ? $multiple_value_separator : "";
                                                $new_value .= $sub_sub_sub_sub_value;
                                            }
                                        }
                                    } elseif ($sub_sub_sub_value) {
                                        $new_value .= $new_value ? $multiple_value_separator : "";
                                        $new_value .= $sub_sub_sub_value;
                                    }
                                }
                            } elseif ($sub_sub_value) {
                                $new_value .= $new_value ? $multiple_value_separator : "";
                                $new_value .= $sub_sub_value;
                            }
                        }
                    } elseif ($sub_value) {
                        $new_value .= $new_value ? $multiple_value_separator : "";
                        $new_value .= $sub_value;
                    }
                }
                $product[$attr] = $new_value;
            }

            // Build new product array by header columns
            $product_final = array();
            foreach ($header as $column) {
                $product_final[$column] = isset($product[$column]) ? $product[$column] : "";
            }

            fputcsv($handle, $product_final, ';', '"');
        }

        // Close file pointer
        fclose($handle);

        return true;
    }

    public static function jsonToCsv($file, $entity, $multiple_value_separator)
    {
        $first_array = Tools::jsonDecode(Tools::file_get_contents($file), true);
        $array = $first_array;

        if (!$array || !is_array($array)) {
            throw new Exception("JSON file is not valid.");
        }
        if (isset($array['outListinoAnagraficaMagazzino']['@versione'])) {
            unset($array['outListinoAnagraficaMagazzino']['@versione']);
        }
        if (isset($array['outListinoAnagraficaMagazzino']['@riferimentoAnno'])) {
            unset($array['outListinoAnagraficaMagazzino']['@riferimentoAnno']);
        }
        if (isset($array['outListinoAnagraficaMagazzino']['@riferimentoMese'])) {
            unset($array['outListinoAnagraficaMagazzino']['@riferimentoMese']);
        }
        if (isset($array['outListinoAnagraficaMagazzino']['@riferimentoGiorno'])) {
            unset($array['outListinoAnagraficaMagazzino']['@riferimentoGiorno']);
        }
        if (isset($array['outListinoAnagraficaMagazzino']['@riferimentoOra'])) {
            unset($array['outListinoAnagraficaMagazzino']['@riferimentoOra']);
        }
        if (isset($array['outListinoAnagraficaMagazzino']['@identificativo'])) {
            unset($array['outListinoAnagraficaMagazzino']['@identificativo']);
        }
        if (isset($array['outListinoAnagraficaMagazzino']['@cliente'])) {
            unset($array['outListinoAnagraficaMagazzino']['@cliente']);
        }
        if (isset($array['error'])) {
            unset($array['error']);
        }
        if (isset($array['update'])) {
            unset($array['update']);
        }
        if (isset($array['service'])) {
            unset($array['service']);
        }
        if (isset($array['Success'])) {
            unset($array['Success']);
        }

        // Find array of products. Array of products are under a node which has numeric keys.
        $key = key($array);
        while ($key !== 0 && $key && isset($array[$key])) {
            $array = $array[$key];
            if (is_array($array)) {
                $key = key($array);
            } else {
                $key = false;
            }
        }

        if (!is_array($array)) {
            if (isset($first_array['products']) && is_array($first_array['products'])) {
                $array = $first_array['products'];
            } else {
                $array = $first_array;
            }
        }

        // Open file pointer
        $handle = fopen($file, 'w');

        // Write CSV header
        // Build header from all rows, because some rows may have columns that does not exist on other rows.
        $header = array();
        foreach ($array as $product) {
            if (!is_array($product)) {
                continue;
            }
            if (isset($product['product'])) {
                $product = $product['product'];
            }
            foreach ($product as $attr => $value) {
                if (!in_array($attr, $header)) {
                    $header[] = $attr;
                }
            }
        }
        fputcsv($handle, $header, ';', '"');

        // Write CSV content
        foreach ($array as $product) {
            if (!is_array($product)) {
                continue;
            }
            if (isset($product['product'])) {
                $product = $product['product'];
            }

            if (isset($product['Manufacturer']['ManufacturerId']) && isset($product['Manufacturer']['Description'])) {
                $product['Manufacturer'] = $product['Manufacturer']['Description'];
            }
            if (isset($product['Brand']['ManufacturerId']) && isset($product['Brand']['BrandId']) && isset($product['Brand']['Description'])) {
                $product['Brand'] = $product['Brand']['Description'];
            }
            if (isset($product['Category']) && is_array($product['Category'])) {
                if (isset($product['Category']['CategoryId'])) {
                    unset($product['Category']['CategoryId']);
                }
                if (isset($product['Category']['Subcategories']) && $product['Category']['Subcategories'] && is_array($product['Category']['Subcategories'])) {
                    foreach ($product['Category']['Subcategories'] as &$sub_cat1) {
                        if (isset($sub_cat1['CategoryId'])) {
                            unset($sub_cat1['CategoryId']);
                        }
                        if (isset($sub_cat1['Subcategories']) && $sub_cat1['Subcategories'] && is_array($sub_cat1['Subcategories'])) {
                            foreach ($sub_cat1['Subcategories'] as &$sub_cat2) {
                                if (isset($sub_cat2['CategoryId'])) {
                                    unset($sub_cat2['CategoryId']);
                                }
                                if (isset($sub_cat2['Subcategories']) && $sub_cat2['Subcategories'] && is_array($sub_cat2['Subcategories'])) {
                                    foreach ($sub_cat2['Subcategories'] as &$sub_cat3) {
                                        if (isset($sub_cat3['CategoryId'])) {
                                            unset($sub_cat3['CategoryId']);
                                        }
                                        if (isset($sub_cat3['Subcategories']) && $sub_cat3['Subcategories'] && is_array($sub_cat3['Subcategories'])) {
                                            foreach ($sub_cat3['Subcategories'] as &$sub_cat4) {
                                                if (isset($sub_cat4['CategoryId'])) {
                                                    unset($sub_cat4['CategoryId']);
                                                }
                                                if (isset($sub_cat4['Subcategories']) && $sub_cat4['Subcategories'] && is_array($sub_cat4['Subcategories'])) {
                                                    unset($sub_cat4['Subcategories']); // I stopped here, you can continue if needed
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (isset($product['prezzo listino no iva']) && isset($product['attributo 1'][0]['valore '])) {
                $product['prezzo listino no iva'] = preg_replace("/[^0-9,]/", "", $product['prezzo listino no iva']);
                $product['prezzo listino no iva'] = preg_replace("/,/", ".", $product['prezzo listino no iva']);
                $product['prezzo listino no iva'] *= 1 + ((float) $product['attributo 1'][0]['valore '] / 100);
                $product['prezzo listino no iva'] = preg_replace("/\./", ",", $product['prezzo listino no iva']);
            }
            if (isset($product['prezzo listino no iva']) && isset($product['attributo 2'][0]['valore '])) {
                $product['attributo 2'] = $product['attributo 2'][0]['valore '];
            }
            if (isset($product['images']) && is_array($product['images'])) {
                $product_images = "";
                foreach ($product['images'] as $key => $value) {
                    if (is_array($value) && isset($value['detailed']) && is_array($value['detailed']) && isset($value['detailed']['image_path']) && $value['detailed']['image_path']) {
                        $product_images .= $product_images ? $multiple_value_separator : "";
                        $product_images .= $value['detailed']['image_path'];
                    }
                }
                if ($product_images) {
                    $product['images'] = $product_images;
                }
            }
            if (isset($product['features']) && is_array($product['features']) && isset($product['features']['feature0']) && isset($product['features']['feature0']['name']) && isset($product['features']['feature0']['value'])) {
                $product_features = "";
                foreach ($product['features'] as $value) {
                    if (isset($value['name']) && $value['name'] && isset($value['value']) && $value['value']) {
                        $product_features .= $product_features ? $multiple_value_separator : "";
                        $product_features .= $value['name'] . ":" . $value['value'];
                    }
                }
                if ($product_features) {
                    $product['features'] = $product_features;
                }
            }
            if (isset($product['features']) && is_array($product['features'])) {
                $product_features = "";
                foreach ($product['features'] as $key => $value) {
                    if (is_array($value) && isset($value['feature_name']) && $value['feature_name'] && array_key_exists('feature_value_sel', $value)) {
                        $product_features .= $product_features ? $multiple_value_separator : "";
                        $product_features .= $value['feature_name'] . ":" . $value['feature_value_sel'];
                    }
                }
                if ($product_features) {
                    $product['features'] = $product_features;
                }
            }
            if (isset($product['categories']) && is_array($product['categories'])) {
                foreach ($product['categories'] as $key => $category) {
                    if (isset($category['category_id']) && isset($category['category_name'])) {
                        $product['categories'][$key] = $category['category_name'];
                    }
                }
            }
            if (isset($product['extra_fields']) && is_array($product['extra_fields'])) {
                $product_features = "";
                foreach ($product['extra_fields'] as $key => $extra_field) {
                    if (isset($extra_field['name']) && $extra_field['name'] && isset($extra_field['value'])) {
                        $product_features .= $product_features ? $multiple_value_separator : "";
                        $product_features .= $extra_field['name'] . ":" . $extra_field['value'];
                    }
                }
                if ($product_features) {
                    $product['extra_fields'] = $product_features;
                }
            }
            if (isset($product['extra_images']) && is_array($product['extra_images'])) {
                foreach ($product['extra_images'] as $key => $extra_image) {
                    if (isset($extra_image['image']) && $extra_image['image'] && isset($extra_image['title'])) {
                        $product['extra_images'][$key] = $extra_image['image'];
                    }
                }
            }
            if (isset($product['prodottoMediaLink']['prodottoMedia']['immagini']['immagine'][0]['immagineLink'])) {
                $immagini = "";
                foreach ($product['prodottoMediaLink']['prodottoMedia']['immagini']['immagine'] as $immagine) {
                    $immagini .= $immagini ? $multiple_value_separator : "";
                    $immagini .= reset($immagine['immagineLink']);
                }
                $product['prodottoMediaLink'] = $immagini;
            }
            if (isset($product['images'][0]['src'])) {
                $images = "";
                foreach ($product['images'] as $image) {
                    $images .= $images ? $multiple_value_separator : "";
                    $images .= $image['src'];
                }
                $product['images'] = $images;
            }
            if (isset($product['image']['id']) && isset($product['image']['src'])) {
                $product['image'] = $product['image']['src'];
            }

            // Take care of multiple values
            foreach ($product as $attr => $value) {
                if (is_array($value) && empty($value)) {
                    $product[$attr] = "";
                    continue;
                } elseif (!is_array($value) || empty($value)) {
                    continue;
                }
                $new_value = "";
                foreach ($value as $sub_value) {
                    if (is_array($sub_value) && $sub_value) {
                        foreach ($sub_value as $sub_sub_value) {
                            if (is_array($sub_sub_value) && $sub_sub_value) {
                                foreach ($sub_sub_value as $sub_sub_sub_value) {
                                    if (is_array($sub_sub_sub_value) && $sub_sub_sub_value) {
                                        foreach ($sub_sub_sub_value as $sub_sub_sub_sub_value) {
                                            if (!is_array($sub_sub_sub_sub_value)) {
                                                $new_value .= $new_value ? $multiple_value_separator : "";
                                                $new_value .= $sub_sub_sub_sub_value;
                                            }
                                        }
                                    } elseif ($sub_sub_sub_value) {
                                        $new_value .= $new_value ? $multiple_value_separator : "";
                                        $new_value .= $sub_sub_sub_value;
                                    }
                                }
                            } elseif ($sub_sub_value) {
                                $new_value .= $new_value ? $multiple_value_separator : "";
                                $new_value .= $sub_sub_value;
                            }
                        }
                    } elseif ($sub_value) {
                        $new_value .= $new_value ? $multiple_value_separator : "";
                        $new_value .= $sub_value;
                    }
                }
                $product[$attr] = $new_value;
            }

            // Build new product array by header columns
            $product_final = array();
            foreach ($header as $column) {
                $product_final[$column] = isset($product[$column]) ? $product[$column] : "";
            }
            fputcsv($handle, $product_final, ';', '"');
        }

        fclose($handle);

        return true;
    }

    public static function isValidUrl($url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return (boolean) preg_match('/^(https?:)?\/\/[$~:;!#,%&_=\(\)\[\]\.\? \+\-@\/a-zA-Z0-9]+$/', $url);
        }
        return false;
    }

    public static function copyFile($source, $destination, $stream_context = null)
    {
        if (is_null($stream_context) && !preg_match('/^https?:\/\//', $source)) {
            return @copy($source, $destination);
        }
        return @file_put_contents($destination, Tools::file_get_contents($source, false, $stream_context));
    }

    public static function downloadFileFromUrl($url_to_file, $local_file = null, $username = null, $password = null, $method = 'GET', $post_params = "")
    {
        if (self::isValidUrl($url_to_file)) {
            $url_to_file = str_replace(' ', '%20', $url_to_file);

            $parced_url = parse_url($url_to_file);
            if (isset($parced_url['host']) && Tools::strtolower($parced_url['host']) == 'www.dropbox.com' && Tools::substr($url_to_file, -5) == '?dl=0') {
                // Convert dropbox URL to downloadable by making ?dl=1
                $url_to_file = substr_replace($url_to_file, "1", -1);
            } elseif (isset($parced_url['host']) && Tools::strtolower($parced_url['host']) == 'drive.google.com' && isset($parced_url['path']) && preg_match("/^(\/file\/d\/)(.+?(?=\/))/", $parced_url['path'], $path_match)) {
                // Convert Google Drive link to direct download link
                $url_to_file = "https://drive.google.com/uc?id=" . $path_match[2] . "&export=download";
            }
        }

        // Replace accented characters with urlencode value
        $accented = array(
            'Š' => '%C5%A0', 'š' => '%C5%A1', 'Ž' => '%C5%BD', 'ž' => '%C5%BE', 'À' => '%C3%80', 'Á' => '%C3%81', 'č' => '%C4%8D',
            'Â' => '%C3%82', 'Ã' => '%C3%83', 'Ä' => '%C3%84', 'Å' => '%C3%85', 'Æ' => '%C3%86', 'Ç' => '%C3%87', 'È' => '%C3%88',
            'É' => '%C3%89', 'Ê' => '%C3%8A', 'Ë' => '%C3%8B', 'Ì' => '%C3%8C', 'Í' => '%C3%8D', 'Î' => '%C3%8E', 'Ï' => '%C3%8F',
            'Ñ' => '%C3%91', 'Ò' => '%C3%92', 'Ó' => '%C3%93', 'Ô' => '%C3%94', 'Õ' => '%C3%95', 'Ö' => '%C3%96', 'Ø' => '%C3%98',
            'Ù' => '%C3%99', 'Ú' => '%C3%9A', 'Û' => '%C3%9B', 'Ü' => '%C3%9C', 'Ý' => '%C3%9D', 'Þ' => '%C3%9E', 'ß' => '%C3%9F',
            'à' => '%C3%A0', 'á' => '%C3%A1', 'â' => '%C3%A2', 'ã' => '%C3%A3', 'ä' => '%C3%A4', 'å' => '%C3%A5', 'æ' => '%C3%A6',
            'ç' => '%C3%A7', 'è' => '%C3%A8', 'é' => '%C3%A9', 'ê' => '%C3%AA', 'ë' => '%C3%AB', 'ì' => '%C3%AC', 'í' => '%C3%AD',
            'î' => '%C3%AE', 'ï' => '%C3%AF', 'ð' => '%C3%B0', 'ñ' => '%C3%B1', 'ò' => '%C3%B2', 'ó' => '%C3%B3', 'ô' => '%C3%B4',
            'õ' => '%C3%B5', 'ö' => '%C3%B6', 'ø' => '%C3%B8', 'ù' => '%C3%B9', 'ú' => '%C3%BA', 'û' => '%C3%BB', 'ý' => '%C3%BD',
            'þ' => '%C3%BE', 'ÿ' => '%C3%BF', 'ř' => '%C5%99',
        );
        $url_to_file = strtr($url_to_file, $accented);

        $extension = pathinfo($url_to_file, PATHINFO_EXTENSION);
        $local_file = $local_file ? $local_file : self::getTempDir() . DIRECTORY_SEPARATOR . rand(1000, 1000000) . '.' . $extension;

        $context = null;
        if ($username && $password) {
            $context = stream_context_create(array(
                'http' => array(
                    'header' => "Authorization: Basic " . call_user_func('base64_encode', $username . ":" . $password)
                )
            ));
        }
        $file_contents = (Tools::strtoupper($method) == 'POST') ? false : Tools::file_get_contents($url_to_file, false, $context, 20);

        if (!$file_contents && self::isValidUrl($url_to_file)) {
            $error = error_get_last();
            if ($error && isset($error['message'])) {
                $error = $error['message'];
            }
            // Try with CURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url_to_file);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            if (Tools::strtoupper($method) == 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                if ($post_params) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
                }
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            }

            if ($username && $password) {
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM | CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
                curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            }

            $response = curl_exec($ch);

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false) {
                $error .= ". cURL error: " . curl_error($ch);
            }

            curl_close($ch);

            if ($response && $http_code == 200) {
                $file_contents = $response;
            } else {
                throw new Exception('An error occured while downloading the file.' . ' ' . $error);
            }
        }

        if (empty($file_contents) || !file_put_contents($local_file, $file_contents)) {
            throw new Exception('An error occured while downloading the file.');
        }

        return $local_file;
    }

    /**
     * Returns mime type of given file
     * @param string $file
     * @return string|boolean
     */
    public static function getMimeType($file)
    {
        if (!is_file($file)) {
            return false;
        }
        $mime = null;
        if (function_exists('finfo_file') && function_exists('finfo_open') && defined('FILEINFO_MIME_TYPE')) {
            // Use the Fileinfo PECL extension (PHP 5.3+)
            $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
        } elseif (function_exists('mime_content_type')) {
            // Deprecated in PHP 5.3
            $mime = mime_content_type($file);
        } elseif (function_exists('exif_imagetype')) {
            if (exif_imagetype($file) == IMAGETYPE_PNG) {
                $mime = 'image/png';
            } elseif (exif_imagetype($file) == IMAGETYPE_JPEG) {
                $mime = 'image/jpeg';
            }
        }

        if ($mime == 'text/plain') {
            $handle = fopen($file, 'r');
            $first_char = fread($handle, 1);
            fclose($handle);
            if ($first_char == '{' || $first_char == '[') {
                $mime = 'application/json';
            }
        }

        return $mime ? Tools::strtolower($mime) : false;
    }

    /**
     * Encodes an ISO-8859-1 string to UTF-8
     * @param string $str
     * @return string
     */
    public static function encodeUtf8($str)
    {
        $str = utf8_encode($str);

        /* Take care of some characters that do not work in UTF-8.
          https://www.php.net/manual/en/function.utf8-encode.php
          This structure encodes the difference between ISO-8859-1 and Windows-1252, as a map from the UTF-8
          encoding of some ISO-8859-1 control characters to the UTF-8 encoding of the non-control characters
          that Windows-1252 places at the equivalent code points. */
        $cp1252_map = array(
            "\xc2\x80" => "\xe2\x82\xac", // EURO SIGN
            "\xc2\x82" => "\xe2\x80\x9a", // SINGLE LOW-9 QUOTATION MARK
            "\xc2\x83" => "\xc6\x92", // LATIN SMALL LETTER F WITH HOOK
            "\xc2\x84" => "\xe2\x80\x9e", // DOUBLE LOW-9 QUOTATION MARK
            "\xc2\x85" => "\xe2\x80\xa6", // HORIZONTAL ELLIPSIS
            "\xc2\x86" => "\xe2\x80\xa0", // DAGGER
            "\xc2\x87" => "\xe2\x80\xa1", // DOUBLE DAGGER
            "\xc2\x88" => "\xcb\x86", // MODIFIER LETTER CIRCUMFLEX ACCENT
            "\xc2\x89" => "\xe2\x80\xb0", // PER MILLE SIGN
            "\xc2\x8a" => "\xc5\xa0", // LATIN CAPITAL LETTER S WITH CARON
            "\xc2\x8b" => "\xe2\x80\xb9", // SINGLE LEFT-POINTING ANGLE QUOTATION
            "\xc2\x8c" => "\xc5\x92", // LATIN CAPITAL LIGATURE OE
            "\xc2\x8e" => "\xc5\xbd", // LATIN CAPITAL LETTER Z WITH CARON
            "\xc2\x91" => "\xe2\x80\x98", // LEFT SINGLE QUOTATION MARK
            "\xc2\x92" => "\xe2\x80\x99", // RIGHT SINGLE QUOTATION MARK
            "\xc2\x93" => "\xe2\x80\x9c", // LEFT DOUBLE QUOTATION MARK
            "\xc2\x94" => "\xe2\x80\x9d", // RIGHT DOUBLE QUOTATION MARK
            "\xc2\x95" => "\xe2\x80\xa2", // BULLET
            "\xc2\x96" => "\xe2\x80\x93", // EN DASH
            "\xc2\x97" => "\xe2\x80\x94", // EM DASH
            "\xc2\x98" => "\xcb\x9c", // SMALL TILDE
            "\xc2\x99" => "\xe2\x84\xa2", // TRADE MARK SIGN
            "\xc2\x9a" => "\xc5\xa1", // LATIN SMALL LETTER S WITH CARON
            "\xc2\x9b" => "\xe2\x80\xba", // SINGLE RIGHT-POINTING ANGLE QUOTATION
            "\xc2\x9c" => "\xc5\x93", // LATIN SMALL LIGATURE OE
            "\xc2\x9e" => "\xc5\xbe", // LATIN SMALL LETTER Z WITH CARON
            "\xc2\x9f" => "\xc5\xb8" // LATIN CAPITAL LETTER Y WITH DIAERESIS
        );
        $str = strtr($str, $cp1252_map);

        return $str;
    }

    /**
     * Returns temporary directory name
     * @return string
     */
    public static function getTempDir()
    {
        $filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tmp';

        if (!is_dir($filename)) {
            mkdir($filename);
            chmod($filename, 0777);
        }

        if (is_dir($filename) && is_writable($filename)) {
            return $filename;
        } elseif (function_exists('sys_get_temp_dir')) {
            return sys_get_temp_dir();
        }

        return dirname(__FILE__);
    }
}
