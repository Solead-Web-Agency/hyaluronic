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
<div class="cn_table">
    <div class="cn_top">
        <div class="cn_name">
            {l s='Block name' mod='xmlfeeds'}
        </div>
        <div class="cn_status" style="width: 160px;">
            {l s='Status' mod='xmlfeeds'}
        </div>
        <div class="cn_name_xml" style="padding-left: 3px; margin-right: 0;">
            {l s='Name in XML' mod='xmlfeeds'}
        </div>
    </div>
    <div class="cn_line block-name-box">
        <div class="cn_name">{l s='Root branch' mod='xmlfeeds'}</div>
        <div class="cn_status">
            <label for="file-name" style="margin-top: 0;">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='file-name' name='file-name+status' status=$b_status['file-name']}
            </label>
        </div>
        <div class="cn_name_xml" style="float: right;">
            <input style="width: 235px;" type="text" name="file-name" value="{$b_name['file-name']|escape:'htmlall':'UTF-8'}" />
        </div>
    </div>
</div>
<div class="cb"></div>
<div class="cn_name-block-name">{l s='Item branch' mod='xmlfeeds'}</div>
<div style="float: right;"><input style="width: 235px;" type="text" name="cat-block-name" value="{$b_name['cat-block-name']|escape:'htmlall':'UTF-8'}"/></div>
<div class="cb"></div>
<div class="cn_name-block-name">{l s='Description' mod='xmlfeeds'}</div>
<div style="float: right;"><input style="width: 235px;"{if !empty($disabled_branch_name)} disabled="disabled" {/if}type="text" name="desc-block-name" value="{$b_name['desc-block-name']|escape:'htmlall':'UTF-8'}" /></div>
<div class="cb"></div>
<div class="cn_name-block-name">{l s='Images' mod='xmlfeeds'}</div>
<div style="float: right;"><input style="width: 235px;"{if !empty($disabled_branch_name)} disabled="disabled" {/if}type="text" name="img-block-name" value="{$b_name['img-block-name']|escape:'htmlall':'UTF-8'}"/></div>
<div class="cb"></div>
<div class="cn_name-block-name">{l s='Default category' mod='xmlfeeds'}</div>
<div style="float: right;"><input style="width: 235px;"{if !empty($disabled_branch_name)} disabled="disabled" {/if}type="text" name="def_cat-block-name" value="{$b_name['def_cat-block-name']|escape:'htmlall':'UTF-8'}"/></div>
<div class="cb"></div>
<div class="cn_name-block-name">{l s='Attributes' mod='xmlfeeds'}</div>
<div style="float: right;"><input style="width: 235px;"{if !empty($disabled_branch_name)} disabled="disabled" {/if}type="text" name="attributes-block-name" value="{$b_name['attributes-block-name']|escape:'htmlall':'UTF-8'}"/></div>
<div class="cb"></div>
<div class="cn_name-block-name" style="width: 200px;">{l s='Extra product rows' mod='xmlfeeds'}</div>
<div style="float: right; width: 325px;">
    <textarea style="float: right; margin: 0; width: 100%; height: 69px;" name="extra-product-rows">{$b_name['extra-product-rows']|escape:'htmlall':'UTF-8'}</textarea>
    <div class="bl_comments">
        {l s='Make sure that you have entered validate XML code' mod='xmlfeeds'}<br>
        {l s='Example: <mytag>value</mytag>' mod='xmlfeeds'}
    </div>
</div>
<div class="cb"></div>
{if $settings.feed_mode == 'pub' || $settings.feed_mode == 'twi'}
    <div style="float: left; width: 200px;">{l s='Extra offer rows' mod='xmlfeeds'}</div>
    <div style="float: right; width: 325px;">
        <textarea style="float: right; margin: 0; width: 100%; height: 69px;" name="extra-offer-rows">{$b_name['extra-offer-rows']|escape:'htmlall':'UTF-8'}</textarea>
        <div class="bl_comments">{l s='[Make sure that you have entered validate XML code]' mod='xmlfeeds'}</div>
    </div>
    <div class="cb"></div>
{/if}
<br/>
<div class="info-small-blmod blmod_b10">
    {l s='Please use a slash "/" in XML tag name if you need to display a field on the second level.' mod='xmlfeeds'}<br>
    {l s='Example, XML tag name: shipping/price, result: <shipping><price>value</price></shipping>' mod='xmlfeeds'}<br><br>
    {l s='Please use the' mod='xmlfeeds'} "<a target="_blank" href="{$fullAdminUrl|escape:'htmlall':'UTF-8'}&add_affiliate_price=1">{l s='Affiliate price' mod='xmlfeeds'}</a>" {l s='feature if you need to add additional prices.' mod='xmlfeeds'}
</div>
<div style="margin-bottom: 10px;">
    <div class="table_name">{l s='Database table connector' mod='xmlfeeds'}</div>
    <div class="cb"></div>
    <div class="cn_top">
        <div class="cn_name" style="width: 190px;">
            {l s='Connective column' mod='xmlfeeds'}
        </div>
        <div class="cn_name" style="width: 190px;">
            {l s='Value column' mod='xmlfeeds'}
        </div>
        <div class="cn_name" style="width: 190px;">
            {l s='XML tag name' mod='xmlfeeds'}
        </div>
    </div>
    <div class="cb"></div>
    <select style="display: inline-block; width: 170px; margin-right: 15px;" name="table_column_connector[0]">
        <option value="">{l s='Column' mod='xmlfeeds'}</option>
        {assign var="active_table" value="none"}
        {foreach $databaseTables as $table => $columns}
            <optgroup label="{$table|escape:'htmlall':'UTF-8'}">
                {foreach $columns as $c}
                    {assign var="column_name" value="{$c|escape:'htmlall':'UTF-8'}+{$table|escape:'htmlall':'UTF-8'}"}
                    <option data-table="{$table|escape:'htmlall':'UTF-8'}" value="{$column_name|escape:'htmlall':'UTF-8'}"{if $customFields.column_connector.0 == $column_name} selected{assign var="active_table" value="{$table|escape:'htmlall':'UTF-8'}"}{/if}>{$c|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </optgroup>
        {/foreach}
    </select>
    <select style="display: inline-block; width: 170px; margin-right: 15px;" name="table_column_value[0]">
        <option value="">{l s='Column' mod='xmlfeeds'}</option>
        {foreach $databaseTables as $table => $columns}
            {if $active_table== $table}
                {foreach $columns as $c}
                    <option value="{$c|escape:'htmlall':'UTF-8'}"{if $customFields.column_value.0 == $c} selected{/if}>{$c|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            {/if}
        {/foreach}
    </select>
    <input style="display: inline-block; width: 204px; vertical-align: bottom;" type="text" name="custom_field_name[0]" value="{$customFields.name.0}" />
    <div class="bl_comments">[{l s='Here you can connect your custom table field. First, specify which column is used to join (product ID) the table, then specify which column is the value and enter XML tag name' mod='xmlfeeds'}]</div>
</div>