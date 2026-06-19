{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="cap_appearance_conf" class="cap_content_appearance_conf col-lg-12 selected">
    <div class="inner-template-section form-control-label" data-selected='1'>
        {l s='Appearance' mod='pscartabandonmentpro'}
        <i class="material-icons">keyboard_arrow_up</i>
    </div>

    {* Model appearance *}
    <div class="cap_content_conf_elems">
        <div class="row clear">
            <div class="col-lg-12 col-xs-12">
                <label>{l s='Email template' mod='pscartabandonmentpro'}</label>
            </div>
            <div class="col-lg-12 col-xs-12 customradios">
                <div class="col-lg-4 col-xs-4">
                    <label for="model_name_sendy" class="col-lg-12 col-xs-12 customradiodesign btn-radio {if (isset($template_appearance['model_name']) && $template_appearance['model_name'] == 'sendy') || !isset($template_appearance['model_name'])}selected{/if}">
                        <input type="radio" name="model_name" id="model_name_sendy" value="sendy" 
                            {if (isset($template_appearance['model_name']) && $template_appearance['model_name'] == 'sendy') || !isset($template_appearance['model_name'])}checked{/if}
                        />
                        <label for="model_name_sendy">{l s='Sendy' mod='pscartabandonmentpro'}</label>
                    </label>
                </div>
                <div class="col-lg-4 col-xs-4">
                    <label for="model_name_boxy" class="col-lg-12 col-xs-12 customradiodesign btn-radio  {if isset($template_appearance['model_name']) && $template_appearance['model_name'] == 'boxy'}selected{/if}">
                        <input type="radio" name="model_name" id="model_name_boxy" value="boxy" 
                            {if isset($template_appearance['model_name']) && $template_appearance['model_name'] == 'boxy'}checked{/if}
                        />
                        <label for="model_name_boxy">{l s='Boxy' mod='pscartabandonmentpro'}</label>
                    </label>
                </div>
                <div class="col-lg-4 col-xs-4">
                    <label for="model_name_puffy" class="col-lg-12 col-xs-12 customradiodesign btn-radio  {if isset($template_appearance['model_name']) && $template_appearance['model_name'] == 'puffy'}selected{/if}">
                        <input type="radio" name="model_name" id="model_name_puffy" value="puffy" 
                            {if isset($template_appearance['model_name']) && $template_appearance['model_name'] == 'puffy'}checked{/if}
                        />
                        <label for="model_name_puffy">{l s='Puffy' mod='pscartabandonmentpro'}</label>
                    </label>
                </div>
            </div>
        </div>

        {* 2x Color pickers *}
        <div class="row clear">
            <div id="primary_color" class="col-lg-4 col-xs-4">
                <div class="col-lg-12 col-xs-12">
                    <label>{l s='Primary color' mod='pscartabandonmentpro'}</label>
                </div>
                <div class="col-lg-12 col-xs-12">
                    <div class="ps_colorpicker1"></div>
                </div>
                <input type="hidden" class="data_input" name="primary_color"
                    value="{if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}#00b9dc{/if}"
                />
            </div>
            <div id="secondary_color" class="col-lg-4 col-xs-4">
                <div class="col-lg-12 col-xs-12">
                    <label>{l s='Secondary color' mod='pscartabandonmentpro'}</label>
                </div>
                <div class="col-lg-12 col-xs-12">
                    <div class="ps_colorpicker2"></div>
                </div>
                <input type="hidden" class="data_input" name="secondary_color"
                    value="{if isset($template_appearance['secondary_color'])}{$template_appearance['secondary_color']}{else}#D78F00{/if}"
                />

            </div>
        </div>
        
    </div>
</div>
