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
{if isset($onMobile) && $onMobile}
    {if isset($enable_force_rating) && $enable_force_rating}
        <div class="snippet-preview--rating {if $rating_config}{else}hide{/if}" data-rating="{if $rating_config}{$rating_config.avg_rating|escape:'html':'UTF-8'}{/if}">
            <div class="rating-info rating-left">
                <span class="avg-rating">{if $rating_config}{number_format($rating_config.avg_rating, 1, '.', '')|escape:'html':'UTF-8'}{/if}</span>
            </div>
            <div class="box-star">
                <div class="rating-star"></div>
                <div class="bg-star" {if $rating_config} style="width: {$rating_config.avg_rating / 5 * 100|escape:'html':'UTF-8'}%;"{/if}></div>
            </div>
            <div class="rating-info rating-right">
                (<span class="rating-count">{if $rating_config}{number_format($rating_config.rating_count)|escape:'html':'UTF-8'}{/if}</span>)
            </div>

        </div>
    {elseif isset($enable_rating) && $enable_rating && isset($comment_product_data)}
        <div class="snippet-preview--rating {if !$comment_product_data.avg_rating || !$comment_product_data.rating_count}hide{/if}" data-rating="{$comment_product_data.avg_rating|escape:'html':'UTF-8'}">
            <div class="rating-avg top">
                <span class="avg-rating">{number_format($comment_product_data.avg_rating, 1, '.', '')|escape:'html':'UTF-8'}</span>
            </div>
            <div class="box-star">
                <div class="rating-star"></div>
                <div class="bg-star" style="width: {$comment_product_data.avg_rating / 5 * 100|escape:'html':'UTF-8'}%;"></div>
            </div>
            <div class="rating-info bottom">
                (<span class="rating-count">{number_format($comment_product_data.rating_count)|escape:'html':'UTF-8'}</span>)
            </div>
        </div>
    {/if}
{else}
    {if isset($enable_force_rating) && $enable_force_rating}
        <div class="snippet-preview--rating {if $rating_config}{else}hide{/if}" data-rating="{if $rating_config}{$rating_config.avg_rating|escape:'html':'UTF-8'}{/if}">
            <div class="box-star">
                <div class="rating-star"></div>
                <div class="bg-star" {if $rating_config} style="width: {$rating_config.avg_rating / 5 * 100|escape:'html':'UTF-8'}%;"{/if}></div>
            </div>
            <div class="rating-info {if !$rating_config}hide{/if}">
                {l s='Rating:' mod='ets_seo'} <span class="avg-rating">{if $rating_config}{number_format($rating_config.avg_rating, 1, '.', '')|escape:'html':'UTF-8'}{/if}</span> - <span class="rating-count">{if $rating_config}{number_format($rating_config.rating_count)|escape:'html':'UTF-8'}{/if}</span> <span class="text-vote">{if $rating_config && $rating_config.rating_count < 2}{l s='review' mod='ets_seo'}{else}{l s='reviews' mod='ets_seo'}{/if}</span>
            </div>
        </div>
    {elseif isset($enable_rating) && $enable_rating && isset($comment_product_data)}
        <div class="snippet-preview--rating {if !$comment_product_data.avg_rating || !$comment_product_data.rating_count}hide{/if}" data-rating="{$comment_product_data.avg_rating|escape:'html':'UTF-8'}">
            <div class="box-star">
                <div class="rating-star"></div>
                <div class="bg-star" style="width: {$comment_product_data.avg_rating / 5 * 100|escape:'html':'UTF-8'}%;"></div>
            </div>
            <div class="rating-info ">
                {l s='Rating:' mod='ets_seo'} <span class="avg-rating">{number_format($comment_product_data.avg_rating, 1, '.', '')|escape:'html':'UTF-8'}</span> - <span class="rating-count">{number_format($comment_product_data.rating_count)|escape:'html':'UTF-8'}</span> <span class="text-vote">{if $comment_product_data.rating_count < 2}{l s='review' mod='ets_seo'}{else}{l s='reviews' mod='ets_seo'}{/if}</span>
            </div>
        </div>
    {/if}

{/if}
