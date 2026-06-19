{*
* Do not edit the file if you want to upgrade the module in future.
* 
* @author    Globo Jsc <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @link	     http://www.globosoftware.net/
* @license   please read license in file license.txt
*/
*}

{if $fields_value}
        .gupsellpro-box .gupsellpro_page-product-heading
        {ldelim}
            text-align: {$fields_value['GSELL_MAIN_LABEL']|escape:'html':'UTF-8'}!important;
        {rdelim}
        .upsell-description 
        {ldelim}
            text-align: {$fields_value['GSELL_MAIN_LABEL']|escape:'html':'UTF-8'}!important;
        {rdelim}
        .btn.gupsellbtn-default
        {ldelim}
            background: {$fields_value['GSELL_MAIN_BUTTON_BACKGROUND']|escape:'html':'UTF-8'}!important;
            color: {$fields_value['GSELL_MAIN_BUTTON_COLOR']|escape:'html':'UTF-8'}!important;
        {rdelim}
        {$fields_value['GSELL_SETTING_CUSTOM_CSS']|escape:'html':'UTF-8'}
        .floating-product-box 
        {ldelim}
            {if $fields_value['GSELL_MAIN_FLOATING_POSITION'] == 'left'}
                left: 20px !important;
                right: unset !important;
            {/if}
        {rdelim}
        .floating-product-box-button 
        {ldelim}
            {if $fields_value['GSELL_MAIN_FLOATING_POSITION'] == 'left'}
                align-self: flex-start;
            {/if}
        {rdelim}

        .upsellpro-most-popular .upsellpro-most-popular-label 
        {ldelim}
            color: {$fields_value['GSELL_MAIN_MOSTPOPOLAR_COLOR']|escape:'html':'UTF-8'}!important;
            background: {$fields_value['GSELL_MAIN_MOSTPOPOLAR_BACKGROUND']|escape:'html':'UTF-8'}!important;
        {rdelim}
        .item_popular {
            background: {$fields_value['GSELL_MAIN_MOSTPOPOLAR_BACKGROUND_ITEM']|escape:'html':'UTF-8'}!important;
        }
{/if}