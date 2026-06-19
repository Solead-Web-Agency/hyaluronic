{*
 * 2010-2023 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2023 Bl Modules
 * @license
*}
<table border="0" width="100%" cellpadding="3" cellspacing="0">
    <tr style="display: none;">
        <td >Feed id:</td>
        <td>
            <input type="text" readonly="readonly" name="feed_id" value="{$page|escape:'htmlall':'UTF-8'}">
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Feed name' mod='xmlfeeds'}</td>
        <td>
            <input style="width: 310px;" type="text" name="name" value="{$s.name|escape:'htmlall':'UTF-8'}" required>
            {if !empty($s.feed_mode)}<img class="feed_type_id" alt="Feed type" title="Feed type" src="../modules/{$name|escape:'htmlall':'UTF-8'}/views/img/type_{$s.feed_mode|escape:'htmlall':'UTF-8'}.png" />{/if}
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Feed status' mod='xmlfeeds'}</td>
        <td>
            <label for="xmf_feed_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='xmf_feed_status' name='status' status=$s.status}
            </label>
        </td>
    </tr>
    <tr class="only-product order-settings">
        <td class="settings-column-name">{l s='Use cron' mod='xmlfeeds'}</td>
        <td>
            <label for="use_cron">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='use_cron' name='use_cron' status=$s.use_cron}
            </label>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Split by combination' mod='xmlfeeds'}</td>
        <td>
            <label for="split_by_combination">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='split_by_combination' name='split_by_combination' status=$s.split_by_combination}
            </label>
            <div class="clear_block"></div>
            <div class="bl_comments">{l s='[Display each combination as a separate product]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Split feed' mod='xmlfeeds'}</td>
        <td>
            <label for="split_feed" class="with-input"class="with-input">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='split_feed' name='split_feed' status=$s.split_feed}
            </label>
            <input style="width: 130px; margin-left: 14px;" placeholder="{l s='Products per feed' mod='xmlfeeds'}" type="text" name="split_feed_limit" value="{if !empty($s.split_feed_limit)}{$s.split_feed_limit|escape:'htmlall':'UTF-8'}{/if}" size="6">
            <div class="clear_block"></div>
            <div class="bl_comments">{l s='[Divide feed into few according to the amount of products]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    {if empty($s.use_cron)}
        <tr>
            <td class="settings-column-name">{l s='Use cache' mod='xmlfeeds'}</td>
            <td>
                <label for="use_cache" class="with-input">
                    {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='use_cache' name='use_cache' status=$s.use_cache}
                </label>
                <input style="width: 130px; margin-left: 14px;" placeholder="{l s='Period in minutes' mod='xmlfeeds'}" type="text" name="cache_time" value="{if !empty($s.cache_time)}{$s.cache_time|escape:'htmlall':'UTF-8'}{/if}" size="6">
                {if $s.use_cache eq 1 && empty($s.cache_time)}
                    <div class="alert-small-blmod ">{l s='You need to enter cache period in minutes (e.g. 180)' mod='xmlfeeds'}</div>
                {/if}
            </td>
        </tr>
    {/if}
    <tr class="only-product">
        <td class="settings-column-name">{l s='Compressor (archive file)' mod='xmlfeeds'}</td>
        <td>
            <label class="blmod_mr20">
                <input type="radio" name="compressor_type" value="0"{if empty($s.compressor_type)} checked="checked"{/if}> {l s='None' mod='xmlfeeds'}
            </label>
            <label class="blmod_mr20">
                <input type="radio" name="compressor_type" value="1"{if $s.compressor_type eq 1} checked="checked"{/if}> {l s='Zip' mod='xmlfeeds'}
            </label>
            <label class="blmod_mr20">
                <input type="radio" name="compressor_type" value="2"{if $s.compressor_type eq 2} checked="checked"{/if}> {l s='Gz' mod='xmlfeeds'}
            </label>
            <label>
                <input type="radio" name="compressor_type" value="3"{if $s.compressor_type eq 3} checked="checked"{/if}> {l s='Gzip' mod='xmlfeeds'}
            </label>
        </td>
    </tr>
    <tr class="only-product compressor-name-action"{if empty($s.compressor_type)} style="display: none;" {/if}>
        <td class="settings-column-name">{l s='Compressed file name' mod='xmlfeeds'}</td>
        <td>
            <input placeholder="" type="text" name="zip_file_name" value="{if !empty($s.zip_file_name)}{$s.zip_file_name|escape:'htmlall':'UTF-8'}{/if}" size="6">
            <div class="clear_block"></div>
            <div class="bl_comments">{l s='[Enter file name without the extension]' mod='xmlfeeds'}</div>
            {if !empty($s.compressor_type) && empty($s.zip_file_name)}
                <div class="alert-small-blmod">{l s='File name cannot be empty' mod='xmlfeeds'}</div>
            {/if}
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Protect by IP addresses' mod='xmlfeeds'}</td>
        <td>
            <input type="text" name="protect_by_ip" value="{$s.protect_by_ip|escape:'htmlall':'UTF-8'}" autocomplete="off">
            <div class="bl_comments">{l s='[Use a comma to separate them (e.g. 11.10.1.1, 22.2.2.3)]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Protect with password' mod='xmlfeeds'}</td>
        <td>
            <label for="use_password" class="with-input">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='use_password' name='use_password' status=$s.use_password}
            </label>
            <input style="display: inline-block; width: 130px; margin-left: 14px;" placeholder="{l s='Password' mod='xmlfeeds'}" type="password" name="password" autocomplete="off" value="{if !empty($s.password)}{$s.password|escape:'htmlall':'UTF-8'}{/if}" size="6">
            {if $s.use_password eq 1 && empty($s.password)}
                <div class="alert-small-blmod">{l s='Password cannot be empty' mod='xmlfeeds'}</div>
            {/if}
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Shipping countries' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function(){
                    boxToggle("countries_list");
                });
            </script>
            <label for="shipping_countries_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='shipping_countries_status' name='shipping_countries_status' status=$s.shipping_countries_status}
            </label>
            <span class="countries_list_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide countries]' mod='xmlfeeds'}</span>
            <div class="countries_list" style="display: none; margin-top:10px;">
                <table cellspacing="0" cellpadding="0" class="table blmod-table-light" id="radio_div" style="margin-top: 0; margin-bottom: 0;">
                    <tr>
                        <th><br></th>
                        <th>{l s='Name' mod='xmlfeeds'}</th>
                    </tr>
                    {foreach $countries as $c}
                        {if $c.id_country|in_array:$s.shipping_countries}
                            <tr>
                                <td class="center">
                                    <input type="checkbox" {if $c.id_country|in_array:$s.shipping_countries} checked{/if} id="country_{$c.id_country|escape:'htmlall':'UTF-8'}" name="shipping_countries[]" value="{$c.id_country|escape:'htmlall':'UTF-8'}" class="noborder">
                                </td>
                                <td>
                                    <label style="line-height: 26px; padding-left: 0;" for="country_{$c.id_country|escape:'htmlall':'UTF-8'}" class="t">
                                        {$c.name|escape:'htmlall':'UTF-8'}
                                    </label>
                                </td>
                            </tr>
                        {/if}
                    {/foreach}
                    {foreach $countries as $c}
                        {if !$c.id_country|in_array:$s.shipping_countries}
                            <tr>
                                <td class="center">
                                    <input type="checkbox" {if $c.id_country|in_array:$s.shipping_countries} checked{/if} id="country_{$c.id_country|escape:'htmlall':'UTF-8'}" name="shipping_countries[]" value="{$c.id_country|escape:'htmlall':'UTF-8'}" class="noborder">
                                </td>
                                <td>
                                    <label style="line-height: 26px; padding-left: 0;" for="country_{$c.id_country|escape:'htmlall':'UTF-8'}" class="t">
                                        {$c.name|escape:'htmlall':'UTF-8'}
                                    </label>
                                </td>
                            </tr>
                        {/if}
                    {/foreach}
                </table>
            </div>
        </td>
    </tr>
    {if $s.feed_mode == 'vi'}
        <tr>
            <td class="settings-column-name">{l s='Vivino, bottle size' mod='xmlfeeds'}</td>
            <td>
                <select name="vivino_bottle_size" style="width: 273px; display: inline-block;">
                    <option value="0">{l s='none' mod='xmlfeeds'}</option>
                    {foreach $productFeatures as $f}
                        <option value="{$f.id_feature|escape:'htmlall':'UTF-8'}"{if $s.vivino_bottle_size eq $f.id_feature} selected{/if}>{$f.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <input style="width: 75px; margin-top: -3px;" type="text" name="vivino_bottle_size_default" value="{$s.vivino_bottle_size_default|escape:'htmlall':'UTF-8'}" placeholder="{l s='Default size' mod='xmlfeeds'}" />
            </td>
        </tr>
        <tr>
            <td class="settings-column-name">{l s='Vivino, lot size' mod='xmlfeeds'}</td>
            <td>
                <select name="vivino_lot_size" style="width: 273px; display: inline-block;">
                    <option value="0">{l s='none' mod='xmlfeeds'}</option>
                    {foreach $productFeatures as $f}
                        <option value="{$f.id_feature|escape:'htmlall':'UTF-8'}"{if $s.vivino_lot_size eq $f.id_feature} selected{/if}>{$f.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <input style="width: 75px; margin-top: -3px;" type="text" name="vivino_lot_size_default" value="{$s.vivino_lot_size_default|escape:'htmlall':'UTF-8'}" placeholder="{l s='Default size' mod='xmlfeeds'}" />
            </td>
        </tr>
    {/if}
    {if $s.feed_mode == 'spa'}
        <tr>
            <td class="settings-column-name">{l s='Spartoo, size' mod='xmlfeeds'}</td>
            <td>
                <select name="spartoo_size">
                    <option value="0">{l s='none' mod='xmlfeeds'}</option>
                    {foreach $productAttributes as $f}
                        <option value="{$f.id_attribute_group|escape:'htmlall':'UTF-8'}"{if $s.spartoo_size eq $f.id_attribute_group} selected{/if}>{$f.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    {/if}
    {if $s.feed_mode == 's' || $s.feed_mode == 'bp'}
        <tr>
            <td class="settings-column-name">{l s='Skroutz Analytics ID' mod='xmlfeeds'}</td>
            <td>
                <input type="text" name="skroutz_analytics_id" value="{$s.skroutz_analytics_id|escape:'htmlall':'UTF-8'}">
                <div class="bl_comments">{l s='[If you want to use Skroutz Analytics, insert the shop account ID]' mod='xmlfeeds'}</div>
            </td>
        </tr>
        <tr>
            <td class="settings-column-name">{l s='Variant size' mod='xmlfeeds'}</td>
            <td>
                <select name="skroutz_variant_size">
                    <option value="0">{l s='None' mod='xmlfeeds'}</option>
                    {foreach $groups as $g}
                        <option value="{$g.id_attribute_group|escape:'htmlall':'UTF-8'}"{if $g.id_attribute_group == $s.skroutz_variant_size} selected{/if}>{$g.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <div class="bl_comments">{l s='[If you want to use variants, you need to specify product size attribute]' mod='xmlfeeds'}</div>
            </td>
        </tr>
    {/if}
    {if $s.feed_mode == 'wor'}
        <tr>
            <td class="settings-column-name">{l s='Worten, ship from country' mod='xmlfeeds'}</td>
            <td>
                <input type="text" name="worten_ship_from_country" value="{$s.worten_ship_from_country|escape:'htmlall':'UTF-8'}" autocomplete="off">
                <div class="bl_comments">{l s='[Example: FR|France]' mod='xmlfeeds'}</div>
            </td>
        </tr>
    {/if}
</table>