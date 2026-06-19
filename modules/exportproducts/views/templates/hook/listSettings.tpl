{if isset($setting) && $setting}

        <div class="save-heading"></i>{l s='Saved settings'  mod='exportproducts'}</div>
        <ul class="form-wrapper-list-settings">
            {foreach $setting as $key => $set}
                <li {if $set['id'] == $id} class="active_setting" {/if}>
                    <span class="settings_key">{$set['id']|escape:'htmlall':'UTF-8'}.</span>
                    <a href="{$base_url|escape:'htmlall':'UTF-8'}&settings={$set['id']|escape:'htmlall':'UTF-8'}" class="one_setting">{$set['name']|escape:'htmlall':'UTF-8'}</a>
                    <a id-setting="{$set['id']|escape:'htmlall':'UTF-8'}" class="delete_setting btn btn-default"><i class="icon-trash"></i></a>
                </li>
            {/foreach}
        </ul>

{/if}

