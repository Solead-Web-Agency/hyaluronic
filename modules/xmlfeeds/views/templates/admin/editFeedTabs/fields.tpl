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
    <tr>
        <td class="settings-column-name">{l s='Add CDATA' mod='xmlfeeds'}</td>
        <td>
            <label for="cdata_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='cdata_status' name='cdata_status' status=$s.cdata_status}
            </label>
            <div class="clear_block"></div>
            <div class="bl_comments">[{l s='It escapes characters that are not allowed to be passed to XML.' mod='xmlfeeds'}<br>{l s='This will solve the problem: "This page contains the following errors"' mod='xmlfeeds'}]</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Drop HTML tags' mod='xmlfeeds'}</td>
        <td>
            <label for="html_tags_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='html_tags_status' name='html_tags_status' status=$s.html_tags_status}
            </label>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='HTML special chars decode' mod='xmlfeeds'}</td>
        <td>
            <label>
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='is_htmlspecialchars' name='is_htmlspecialchars' status=$s.is_htmlspecialchars}
            </label>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Add "-0" to the product ID' mod='xmlfeeds'}</td>
        <td>
            <label for="product_id_with_zero">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='product_id_with_zero' name='product_id_with_zero' status=$s.product_id_with_zero}
            </label>
            <div class="clear_block"></div>
            <div class="bl_comments">[{l s='Disabled: 123, enabled: 123-0 (product without combinations)' mod='xmlfeeds'}]</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Category map' mod='xmlfeeds'}</td>
        <td>
            <select name="category_map_id">
                <option value="0">{l s='None' mod='xmlfeeds'}</option>
                {foreach $categoryMapList as $c}
                    <option value="{$c.id|escape:'htmlall':'UTF-8'}" {if $s.category_map_id == $c.id}selected{/if}>{$c.title|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Attribute map' mod='xmlfeeds'}</td>
        <td>
            <select name="attribute_map_id">
                <option value="0">{l s='None' mod='xmlfeeds'}</option>
                {foreach $attributeMapList as $c}
                    <option value="{$c.id|escape:'htmlall':'UTF-8'}" {if $s.attribute_map_id == $c.id}selected{/if}>{$c.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Feature map' mod='xmlfeeds'}</td>
        <td>
            <select name="feature_map_id">
                <option value="0">{l s='None' mod='xmlfeeds'}</option>
                {foreach $featureMapList as $c}
                    <option value="{$c.id|escape:'htmlall':'UTF-8'}" {if $s.feature_map_id == $c.id}selected{/if}>{$c.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Label when in stock' mod='xmlfeeds'}</td>
        <td>
            <input type="text" name="label_in_stock_text" value="{$s.label_in_stock_text|escape:'htmlall':'UTF-8'}" size="6">
            <div class="bl_comments">{l s='[Replace default availability label when in stock]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Label when out of stock' mod='xmlfeeds'}</td>
        <td>
            <input type="text" name="label_out_of_stock_text" value="{$s.label_out_of_stock_text|escape:'htmlall':'UTF-8'}" size="6">
            <div class="bl_comments">{l s='[Replace default availability label when out of stock (and back order allowed)]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Delivery time in-stock' mod='xmlfeeds'}</td>
        <td>
            <input type="text" name="in_stock_text" value="{$s.in_stock_text|escape:'htmlall':'UTF-8'}" size="6">
            <div class="bl_comments">{l s='[Replace default delivery time of in-stock products]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Delivery time out-of-stock' mod='xmlfeeds'}</td>
        <td>
            <input type="text" name="out_of_stock_text" value="{$s.out_of_stock_text|escape:'htmlall':'UTF-8'}" size="6">
            <div class="bl_comments">{l s='[Replace default delivery time of out-of-stock products with allowed backorders]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Delivery is not available' mod='xmlfeeds'}</td>
        <td>
            <input type="text" name="on_demand_stock_text" value="{$s.on_demand_stock_text|escape:'htmlall':'UTF-8'}" size="6">
            <div class="bl_comments">{l s='[Default text if delivery is not possible]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Header rows' mod='xmlfeeds'}</td>
        <td>
            <textarea name="header_information" style="max-width: 470px; width: 100%; height: 60px;">{$s.header_information|escape:'htmlall':'UTF-8'}</textarea>
            <div class="bl_comments">{l s='[Make sure that you have entered validate XML code]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Footer rows' mod='xmlfeeds'}</td>
        <td>
            <textarea name="footer_information" style="max-width: 470px; width: 100%; height: 60px;">{$s.footer_information|escape:'htmlall':'UTF-8'}</textarea>
            <div class="bl_comments">{l s='[Make sure that you have entered validate XML code]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Extra feed rows' mod='xmlfeeds'}</td>
        <td>
            <textarea name="extra_feed_row" style="max-width: 470px; width: 100%; height: 60px;">{$s.extra_feed_row|escape:'htmlall':'UTF-8'}</textarea>
            <div class="bl_comments">{l s='[Make sure that you have entered validate XML code]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Product category tree type' mod='xmlfeeds'}</td>
        <td>
            <label class="blmod_mr20">
                <input type="radio" name="category_tree_type" value="0"{if empty($s.category_tree_type)} checked="checked"{/if}> {l s='All categories' mod='xmlfeeds'}
            </label>
            <label class="blmod_mr20">
                <input type="radio" name="category_tree_type" value="1"{if $s.category_tree_type eq 1} checked="checked"{/if}> {l s='According breadcrumbs' mod='xmlfeeds'}
            </label>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Feed generation time' mod='xmlfeeds'}</td>
        <td>
            <label for="feed_generation_time" class="with-input">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='feed_generation_time' name='feed_generation_time' status=$s.feed_generation_time}
            </label>
            <input style="width: 155px; margin-left: 14px;" type="text" name="feed_generation_time_name" value="{$s.feed_generation_time_name|escape:'htmlall':'UTF-8'}" placeholder="Field name" size="6">
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Maximum possible quantity' mod='xmlfeeds'}</td>
        <td>
            <label for="feed_generation_time" class="with-input">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='max_quantity_status' name='max_quantity_status' status=$s.max_quantity_status}
            </label>
            <input style="width: 78px; margin-left: 14px;" type="text" name="max_quantity" value="{$s.max_quantity|escape:'htmlall':'UTF-8'}" size="6">
            <div class="bl_comments">{l s='[If the quantity of the product is greater than, it will be replaced with the specified value]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Gender field by category' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function() {
                    boxToggle("gender_field_category");
                });
            </script>
            <label class="with-input">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='gender_field_category_status' name='gender_field_category_status' status=$s.gender_field_category_status}
            </label>
            <span class="gender_field_category_button" style="cursor: pointer; color: #268CCD; margin-left: 10px; top: 5px; position: relative;">{l s='[Show/Hide categories]' mod='xmlfeeds'}</span>
            <div class="gender_field_category" style="display: none; margin-top: 20px;">
                <div class="blmod_mb10">
                    {l s='Field name' mod='xmlfeeds'} <input style="width: 155px; margin-left: 14px;" type="text" name="gender_field_category_name" value="{$s.gender_field_category_name|escape:'htmlall':'UTF-8'}" size="6">
                </div>
                <div>
                    {l s='Default value' mod='xmlfeeds'} <input style="width: 155px; margin-left: 14px;" type="text" name="gender_field_category_prime_value" value="{$s.gender_field_category_prime_value|escape:'htmlall':'UTF-8'}" size="6">
                </div>
                <div class="bl_comments blmod_mb25">{l s='[This value will be used if no gender is specified for the category]' mod='xmlfeeds'}</div>
                {$categoriesTreeGender}
            </div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Empty product description' mod='xmlfeeds'}</td>
        <td>
            <div class="mb10">
                <label>
                    <input type="radio" name="empty_description" value="0"{if $s.empty_description eq 0} checked="checked"{/if}> {l s='Leave empty' mod='xmlfeeds'}
                </label>
                <div class="blmod_cb"></div>
            </div>
            <div class="mb10">
                <label>
                    <input type="radio" name="empty_description" value="1"{if $s.empty_description eq 1} checked="checked"{/if}> {l s='Replace empty description with the product name' mod='xmlfeeds'}
                </label>
                <div class="blmod_cb"></div>
            </div>
            <div>
                <label>
                    <input type="radio" name="empty_description" value="2"{if $s.empty_description eq 2} checked="checked"{/if}>
                    <input style="width: 350px;" type="text" name="empty_description_text" value="{$s.empty_description_text|escape:'htmlall':'UTF-8'}" placeholder="{l s='Replace empty description with custom text' mod='xmlfeeds'}">
                </label>
                <div class="blmod_cb"></div>
            </div>
        </td>
    </tr>
</table>