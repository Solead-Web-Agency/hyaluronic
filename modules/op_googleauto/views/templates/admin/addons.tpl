<div class="panel ps-panel ">
<ps-alert-hint class="op_zohocrm">

    <p>{l s='Do you like this module?' mod='op_zohocrm'}</p>
    <p>{l s='Discover our other modules directly on' mod='op_zohocrm'}</p>
    <p>{if isset($addons_id) && $addons_id}<a href="https://addons.prestashop.com/{$iso_code|escape:'htmlall':'UTF-8'}/2_community-developer?contributor=163165" target="_blank" title="Prestashop Addons Market Place"><img src="{$img_path|escape:'htmlall':'UTF-8'}prestashop-addons-logo.png" alt="Prestashop Addons Market Place" class="img-responsive" /></a>{/if}</p>

</ps-alert-hint>


{if isset($json_modules) && $json_modules}
    {foreach from=$json_modules.products item=module name=foo}

    {if $smarty.foreach.foo.index % 3 == 0}<div class="row addons-products clearfix">{/if}

        <a href="{$module.url|escape:'html':'UTF-8'}" target="_blank" class="addons-link">
        <div class="col-lg-4{if $smarty.foreach.foo.index % 3 == 2} last-item{elseif $smarty.foreach.foo.index % 3 == 0} first-item{else} item{/if}">

            <img src="{$module.img|escape:'html':'UTF-8'}" class="pull-left" style="padding:0 5px 5px 0" />
            <h5>{$module.categoryName|escape:'html':'UTF-8'}</h5>
            <h4>{$module.displayName|escape:'html':'UTF-8'|stripslashes} <span class="price pull-right">{$module.price['EUR']|escape:'html':'UTF-8'} €</span></h4>

            <p>{$module.fullDescription|strip_tags|escape:'html':'UTF-8'|stripslashes}</p>

        </div>
        </a>

    {if $smarty.foreach.foo.index % 3 == 2 || $smarty.foreach.foo.last}</div>{/if}

    {/foreach}

{/if}
</div>
