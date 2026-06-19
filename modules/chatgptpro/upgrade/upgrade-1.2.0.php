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

function upgrade_module_1_2_0($module)
{
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chatgptpro_log` ADD COLUMN `old_name` varchar(256)');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chatgptpro_log` ADD COLUMN `new_name` varchar(256)');

    $data = [];

    $data[] = array(
        'status' => 1,
        'title' => "Product description 1",
        'prompt' => "Generate creative HTML code description for this product: {product_name} following the next template:" . " \n\n " . "<H2>Product detailed description:</H2> \n\n <H2>Product features:</H2> \n\n <H2>Product recommandations:</H2> \n\n Within the description also add the following keywords written with html bold characters: {product_tags}. \n\n Also make sure you also include some spelling mistakes into the text, but not in the structure of the keywords, to create a more human-like text.",
    );

    $data[] = array(
        'status' => 2,
        'title' => "Product description 1",
        'prompt' => "Generate creative description for this product: {product_name} following the next template: \n\n Product detailed description: \n\n Product features: \n\n Product recommandations: \n\n",
    );

    $data[] = array(
        'status' => 3,
        'title' => "Product description 2",
        'prompt' => "Introducing our {product_name}, the ultimate solution for [specific use case]. With its {product_features}, this {product_default_category} is designed to [solve a particular problem or provide a specific benefit]. Its [material or manufacturing process] ensures durability and reliability, while its {product_weight} makes it perfect for [target audience]. \n\n",
    );

    $data[] = array(
        'status' => 4,
        'title' => "Product description 3",
        'prompt' => "Take your [activity or task] to the next level with our {product_name}. Designed with {product_features}, this {product_default_category} is ideal for [specific use case or target audience]. Crafted by {product_brand}, this [product category] is built to last, providing you with the best possible [benefit or solution]. So why wait? Order yours today from {shop_name}! \n\n",
    );

    $data[] = array(
        'status' => 4,
        'title' => "Product name",
        'prompt' => "Generate better short product name based on the existing product name: {product_name}. \n\n",
    );

    foreach ($data as $key => $prompt) {
        $insert = Db::getInstance()->insert(
            'chatgptpro_prompt',
            $prompt
        );
    }

    return true;
}
