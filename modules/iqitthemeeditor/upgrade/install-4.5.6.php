<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_5_6($object)
{

    $customHooks = array(
        'displayAsLastProductImage',
        'displayAsFirstProductImage'
    );

    foreach($customHooks as $hookName){
        $idHook = Hook::getIdByName($hookName);
        if (!$idHook) {
            $newHook = new Hook();
            $newHook->name = pSQL($hookName);
            $newHook->title = pSQL($hookName);
            $newHook->position = 1;
            $newHook->add();
        }
    }

    Configuration::updateValue($object->cfgName . 'mm_content', 'separate');
    Configuration::updateValue($object->cfgName . 'mm_expand_trigger', 'entire-link');


    Configuration::updateValue($object->cfgName . 'mm_main_tab_typo', '{"size":"22","bold":null,"italic":null,"uppercase":null,"spacing":"0"}');
    Configuration::updateValue($object->cfgName . 'mm_legend_typo', '{"size":"12","bold":null,"italic":null,"uppercase":null,"spacing":"0"}');
    Configuration::updateValue($object->cfgName . 'mm_accordion_tab2_typo', '{"size":"18","bold":null,"italic":null,"uppercase":null,"spacing":"0"}');
    Configuration::updateValue($object->cfgName . 'mm_submenu_title_typo', '{"size":"20","bold":null,"italic":null,"uppercase":null,"spacing":"0"}');
    Configuration::updateValue($object->cfgName . 'mm_submenu_text_typo', '{"size":"16","bold":null,"italic":null,"uppercase":null,"spacing":"0"}');

    Configuration::updateValue($object->cfgName . 'mm_main_tab_border', '{"type":"none","width":"1","color":""}');
    Configuration::updateValue($object->cfgName . 'mm_accordion_tab2_border', '{"type":"none","width":"1","color":""}');
    Configuration::updateValue($object->cfgName . 'mm_hf_border', '{"type":"none","width":"1","color":""}');


    Configuration::updateValue($object->cfgName . 'mm_main_tab_color', '#595050');
    Configuration::updateValue($object->cfgName . 'mm_main_tab_padding', 20);
    Configuration::updateValue($object->cfgName . 'mm_arrow_icon_status', 1);
    Configuration::updateValue($object->cfgName . 'mm_arrow_icon_size', 22);
    Configuration::updateValue($object->cfgName . 'mm_arrow_icon_color', '#595050');

    Configuration::updateValue($object->cfgName . 'mm_legend_color', '#ffffff');
    Configuration::updateValue($object->cfgName . 'mm_legend_background', '#595050');

    Configuration::updateValue($object->cfgName . 'mm_accordion_tab2_color', '#595050');
    Configuration::updateValue($object->cfgName . 'mm_accordion_tab2_padding', 16);
    
    Configuration::updateValue($object->cfgName . 'mm_hf_color', '#595050');
    Configuration::updateValue($object->cfgName . 'mm_hf_background', '#f4f4f4');

    Configuration::updateValue($object->cfgName . 'mm_hf_footer_visibilty', 1);

    Configuration::updateValue($object->cfgName . 'mm_submenu_title_color', '#595050');
    Configuration::updateValue($object->cfgName . 'mm_submenu_text_color', '#595050');


    Configuration::updateValue($object->cfgName . 'mm_submenu_column_spacing', 40);
    Configuration::updateValue($object->cfgName . 'mm_submenu_title_spacingr', 8);
    Configuration::updateValue($object->cfgName . 'mm_submenu_link_spacing', 6);
    
    return true;

}

