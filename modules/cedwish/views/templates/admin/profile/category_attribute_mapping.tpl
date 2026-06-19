<!--
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   cedwish
 */
-->

<div class="bootstrap" id="" style="">
    <div class="alert alert-info" id="">
        <span id="">Map all the Required Wish attributes with Prestashop attributes in order to prevent error at the time of product upload</span>
    </div>
</div>
<div class="panel row">
    <div class="panel-body">
        <div class="col-md-4">
            <div class="form-group">
                <b>{l s='Wish Attribute' mod='cedwish'}</b>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <b>{l s='Default Value' mod='cedwish'}</b>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <b>{l s='Store Attributes' mod='cedwish'}</b>
            </div>
        </div>
        {foreach $attributes as $key => $attribute}
            {assign var="attr_code" value="{$key|trim|escape:'htmlall':'UTF-8'}" }
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <b>{$attribute['name']|escape:'htmlall':'UTF-8'}</b>
                        </br>
                        {$attribute['desc']|escape:'htmlall':'UTF-8'}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {if isset($attribute['type']) && ($attribute['type']=='select')}
                            {if isset($profile_data['default_mapping'][{$attr_code}])}
                                <select value="{$profile_data['default_mapping'][{$attr_code|escape:'htmlall':'UTF-8'}]|escape:'htmlall':'UTF-8'}"
                                       type="text"
                                       name="default_mapping[{$attr_code|escape:'htmlall':'UTF-8'}]"
                                >
                                    {foreach $attribute['values'] as $value}
                                        {if $profile_data['default_mapping'][{$attr_code}] == $value}
                                            <option selected="selected" value="{$value|escape:'htmlall':'UTF-8'}">
                                        {else}
                                            <option value="{$value|escape:'htmlall':'UTF-8'}">
                                        {/if}
                                            {$value|escape:'htmlall':'UTF-8'}
                                        </option>
                                    {/foreach}
                                </select>
                            {else}
                                <select type="text"
                                       name="default_mapping[{$attr_code|escape:'htmlall':'UTF-8'}]"
                                >
                                    {foreach $attribute['values'] as $value}
                                        <option value="{$value|escape:'htmlall':'UTF-8'}">
                                            {$value|escape:'htmlall':'UTF-8'}
                                        </option>
                                    {/foreach}
                                </select>
                            {/if}
                        {else}
                            {if isset($profile_data['default_mapping'][{$attr_code}])}
                                <input value="{$profile_data['default_mapping'][{$attr_code|escape:'htmlall':'UTF-8'}]|escape:'htmlall':'UTF-8'}"
                                       type="text"
                                       name="default_mapping[{$attr_code|escape:'htmlall':'UTF-8'}]"
                                />
                            {else}
                                <input type="text"
                                       name="default_mapping[{$attr_code|escape:'htmlall':'UTF-8'}]"
                                />
                            {/if}
                        {/if}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <select name="attribute_mapping[{$attr_code|escape:'htmlall':'UTF-8'}]"
                                id="{$attr_code|escape:'htmlall':'UTF-8'}">
                            <option value=""></option>
                            <optgroup value="0" label="System (Default)">
                                {foreach $storeDefaultAttributes as $key => $system_attribute}
                                    {if isset($profile_data['attribute_mapping'][{$attr_code}]) && ($profile_data['attribute_mapping'][{$attr_code}]=="system-{$key}") }
                                        <option
                                                selected="selected"
                                                value="system-{$key|escape:'htmlall':'UTF-8'}"
                                        >
                                            {$system_attribute|escape:'htmlall':'UTF-8'}
                                        </option>
                                    {elseif isset($defaultAttributes[{$attr_code}]) && ($defaultAttributes[{$attr_code}]=="system-{$key}") }
                                        <option selected="selected" value="system-{$key|escape:'htmlall':'UTF-8'}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                                    {else}
                                        <option value="system-{$key|escape:'htmlall':'UTF-8'}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                                    {/if}
                                {/foreach}
                            </optgroup>
                            <optgroup value="0" label="Features">
                                {if isset($storeFeatures)}
                                    {foreach $storeFeatures as $feature}
                                        {if isset($profile_data['attribute_mapping'][{$attr_code}]) && ($profile_data['attribute_mapping'][{$attr_code}]=="feature-{$feature['id_feature']}") }
                                            <option selected="selected"
                                                    value="feature-{$feature['id_feature']|escape:'htmlall':'UTF-8'}">{$feature['name']|escape:'htmlall':'UTF-8'}</option>
                                        {elseif isset($defaultAttributes[{$attr_code}]) && ($defaultAttributes[{$attr_code}]=="feature-{$feature['id_feature']}") }
                                            <option selected="selected"
                                                    value="feature-{$feature['id_feature']|escape:'htmlall':'UTF-8'}">{$feature['name']|escape:'htmlall':'UTF-8'}</option>
                                        {else}
                                            <option value="feature-{$feature['id_feature']|escape:'htmlall':'UTF-8'}">{$feature['name']|escape:'htmlall':'UTF-8'}</option>
                                        {/if}
                                    {/foreach}
                                {/if}
                            </optgroup>
                            <optgroup value="0" label="Attributes(Variants)">
                                {if isset($storeAttributes)}
                                    {foreach $storeAttributes as $store_attribute}
                                        {if isset($profile_data['attribute_mapping'][{$attr_code}]) && ($profile_data['attribute_mapping'][{$attr_code}]=="attribute-{$store_attribute['id_attribute_group']}") }
                                            <option selected="selected"
                                                    value="attribute-{$store_attribute['id_attribute_group']|escape:'htmlall':'UTF-8'}">{$store_attribute['name']|escape:'htmlall':'UTF-8'}</option>
                                        {elseif isset($defaultAttributes[{$attr_code}]) && ($defaultAttributes[{$attr_code}]=="attribute-{$store_attribute['id_attribute_group']}") }
                                            <option selected="selected"
                                                    value="attribute-{$store_attribute['id_attribute_group']|escape:'htmlall':'UTF-8'}">{$store_attribute['name']|escape:'htmlall':'UTF-8'}</option>
                                        {else}
                                            <option value="attribute-{$store_attribute['id_attribute_group']|escape:'htmlall':'UTF-8'}">{$store_attribute['name']|escape:'htmlall':'UTF-8'}</option>
                                        {/if}
                                    {/foreach}
                                {/if}
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
</div>
