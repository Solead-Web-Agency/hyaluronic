{extends file="helpers/form/form.tpl"}
{block name="input_row"}
  {if $input.type == 'checkbox_table'}
    {assign var=all_setings value=$input.values}
    {assign var=id value=$all_setings['id']}
    {assign var=name value=$all_setings['name']}
    {if isset($all_setings) && count($all_setings) > 0}
      <div class="form-group {$input.class_block|escape:'html'}" {if isset($input.tab)}data-tab-id="{$input.tab|escape:'htmlall':'UTF-8'}"{/if}  {if $input.display}style="display: block" {/if}>
        <label class="control-label col-lg-3">
        <span class="{if $input.hint}label-tooltip{else}control-label{/if}" data-toggle="tooltip" data-html="true" title="" data-original-title="{$input.hint|escape:'htmlall':'UTF-8'}">
          {$input.label|escape:'htmlall':'UTF-8'}
        </span>
        </label>
        <div class="col-lg-9">
          <div class="row">
            <div class="col-lg-6">
              <table class="table table-bordered">
                <thead>
                <tr>
                  {if $input.class_block == 'product_list'}
                    <th class="fixed-width-xs">
                      <span class="title_box">
                          <input type="checkbox" class="check-all"/>
                          {l s='Select all'  mod='ordersexport'}
                      </span>
                    </th>
                  {/if}
                  {if $input.class_block != 'product_list'}
                    <th class="fixed-width-xs">
                      <span class="title_box">
                        {l s='Check'  mod='exportproducts'}
                      </span>
                    </th>
                  {/if}
                  <th>
                    <span class="id-box">
                     {l s='ID'  mod='exportproducts'}
                    </span>
                  </th>
                  {if $input.search}
                    <th>
                      <a href="#" id="show_checked" class="btn btn-default"><i class="icon-check-sign"></i> {l s='Show Checked'  mod='exportproducts'}</a>
                      &nbsp;
                      <a href="#" id="show_all" class="btn btn-default"><i class="icon-check-empty"></i> {l s='Show All'  mod='exportproducts'}</a>
                    </th>
                  {/if}
                  <th>
                    <span class="title_box">
                      {if $input.search}
                        <input type="text" class="search_checkbox_table" placeholder="{l s='search...'  mod='exportproducts'}">
                      {/if}
                    </span>
                  </th>
                </tr>
                </thead>
                <tbody>
                {foreach $all_setings['query'] as $key => $setings}
                  <tr>
                    <td>
                      <input type="checkbox" class="{$input.type|escape:'htmlall':'UTF-8'} {$input.class_input|escape:'htmlall':'UTF-8'}" name="{$input.name|escape:'htmlall':'UTF-8'}_{$setings[$id]|escape:'htmlall':'UTF-8'}" id="{$input.name|escape:'htmlall':'UTF-8'}_{$setings[$id]|escape:'htmlall':'UTF-8'}" value="{$setings[$id]|escape:'htmlall':'UTF-8'}" {if $all_setings['value'] && in_array($setings[$id], $all_setings['value'])}checked="checked" {/if} />
                    </td>
                    <td>{$setings[$id]|escape:'htmlall':'UTF-8'}</td>
                    <td>
                      <label for="{$input.name|escape:'htmlall':'UTF-8'}_{$setings[$id]|escape:'htmlall':'UTF-8'}">
                          {$setings[$name]|escape:'htmlall':'UTF-8'}
                          {if isset($setings['reference']) && $setings['reference']}
                              ({$setings['reference']|escape:'htmlall':'UTF-8'})
                          {/if}

                          {if isset($all_setings['name2']) && $all_setings['name2']}
                              {$setings[$all_setings['name2']]|escape:'htmlall':'UTF-8'}
                          {/if}
                      </label>
                    </td>
                  </tr>
                {/foreach}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    {/if}
  {elseif $input.type == 'checkbox_fields'}
    <div class="form-group {$input.class_block|escape:'htmlall':'UTF-8'}" {if isset($input.tab)}data-tab-id="{$input.tab|escape:'htmlall':'UTF-8'}"{/if} {if $input.display}style="display: block" {/if}>
      <div class="col-lg-9 col-lg-offset-3">
        {foreach $input['values']['query'] as $field}
          <div class="checkbox {$input.class|escape:'htmlall':'UTF-8'}">
            <label>
              <input type="checkbox" {if isset($input['values']['checked'][$field[$input['values']['value']]])}checked="checked" {/if}  {if isset($field['disabled'])}disabled="disabled" {/if} name="field[{$field[$input['values']['value']]|escape:'htmlall':'UTF-8'}]" value="{$field[$input['values']['name']]|escape:'htmlall':'UTF-8'}">
              {if isset($field['disabled'])}<input type="hidden" name="field[{$field[$input['values']['value']]|escape:'htmlall':'UTF-8'}]" value="{$field[$input['values']['name']]|escape:'htmlall':'UTF-8'}" >{/if}
              {$field[$input['values']['name']]|escape:'htmlall':'UTF-8'}
            </label>
          </div>
        {/foreach}
      </div>
    </div>
  {else}
    {$smarty.block.parent}
  {/if}
{/block}
