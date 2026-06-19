{*
* 2007-2021 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2021 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{if isset($enable_force_rating) && $enable_force_rating}
<div class="ets-seo-box-rating">
    <div class="card">
       <div class="card-header">
           <h3 class="card-title">{l s='Forced ratings' mod='ets_seo'}</h3>
       </div>
        <div class="card-body">
            <div class="form-group">
                <label>{l s='Enable forced ratings?' mod='ets_seo'}</label>
                <select class="form-control" name="ets_seo_rating_enable">
                    <option value="0" {if isset($rating_setting['enable']) && $rating_setting['enable'] == 0}selected="selected"{/if}>{l s='No' mod='ets_seo'}</option>
                    <option value="1" {if isset($rating_setting['enable']) && $rating_setting['enable'] == 1}selected="selected"{/if}>{l s='Yes' mod='ets_seo'}</option>
                </select>
                <div class="help-block">{l s='Forced ratings allow you to specify the rating value displayed on search engines as you want' mod='ets_seo'}</div>
            </div>
            <div class="form-group js-ets-seo-rating-config">
                <label class="ets-seo-label required">{l s='Average rating' mod='ets_seo'}</label>
                <input type="text" name="ets_seo_rating_average" class="form-control" value="{if isset($rating_setting['average_rating'])}{$rating_setting['average_rating']|escape:'html':'UTF-8'}{/if}">
            </div>
            <div class="form-group js-ets-seo-rating-config">
                <label>{l s='Best rating' mod='ets_seo'}</label>
                <input type="number" min="1" max="5" name="ets_seo_rating_best" class="form-control" value="{if isset($rating_setting['best_rating'])}{$rating_setting['best_rating']|escape:'html':'UTF-8'}{/if}">
            </div>
            <div class="form-group js-ets-seo-rating-config">
                <label>{l s='Worst rating' mod='ets_seo'}</label>
                <input type="number" min="1" max="5" name="ets_seo_rating_worst" class="form-control" value="{if isset($rating_setting['worst_rating'])}{$rating_setting['worst_rating']|escape:'html':'UTF-8'}{/if}">
            </div>
            <div class="form-group js-ets-seo-rating-config">
                <label class="ets-seo-label required">{l s='Rating count' mod='ets_seo'}</label>
                <input type="number" min="1" name="ets_seo_rating_count" class="form-control" value="{if isset($rating_setting['rating_count'])}{$rating_setting['rating_count']|escape:'html':'UTF-8'}{/if}">
            </div>
        </div>
    </div>
</div>
{/if}