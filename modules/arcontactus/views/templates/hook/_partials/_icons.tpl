{*
* 2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*}
<div class="arcu-button-icon" id="{$name|escape:'htmlall':'UTF-8'}-icon">
    <input type="hidden" id="{$name|escape:'htmlall':'UTF-8'}" value="{$currentValue|escape:'htmlall':'UTF-8'}" name="{$name|escape:'htmlall':'UTF-8'}" data-serializable="true" class="arcontactus-control" />
    <input placeholder="Search" data-default="" onkeyup="{$name|escape:'htmlall':'UTF-8'}FindButtonIcon();" type="text" id="{$name|escape:'htmlall':'UTF-8'}-search" class="form-control" />
    <p class="text-right">
        {l s='Icons:' mod='arcontactus'} <span id="arcu-button-icon-count">{$icons|count}</span>
    </p>
    <div class="form-group" id="arcu-button-icon-container" style="overflow-y: scroll; overflow-x: hidden; max-height: 400px;"> 
        <ul class="list-unstyled arcu-icon-list">
            {foreach from=$icons key=id item=icon}
            <li class="col-sm-2{if $id == $currentValue} active{/if}" data-id="{$id|escape:'htmlall':'UTF-8'}">
                <div>
                    {$icon nofilter}{* SVG content. Escaping will break functionality *}
                    <div class="w-100 ph1 pv2 tc f2">
                        <span class="icon-title">{$id|escape:'htmlall':'UTF-8'}</span>
                    </div>
                </div>
            </li>
            {/foreach}
        </ul>
    </div>
</div>
<script>
    window.addEventListener('load', function(){
        $('#{$name|escape:'htmlall':'UTF-8'}-icon li').click(function(){
            $('#{$name|escape:'htmlall':'UTF-8'}-icon li.active').removeClass('active');
            $(this).addClass('active');
            $('#{$name|escape:'htmlall':'UTF-8'}').val($(this).data('id'));
        });
    });
    function {$name|escape:'htmlall':'UTF-8'}FindButtonIcon(){
        var val = $('#{$name|escape:'htmlall':'UTF-8'}-search').val();
        $('#{$name|escape:'htmlall':'UTF-8'}-icon .icon-title').each(function(){
            if ($(this).text().indexOf(val) !== -1){
                $(this).parents('li').removeClass('hidden');
            }else{
                $(this).parents('li').addClass('hidden');
            }
        });
        arcontactusUpdateButtonIconsCount();
    }
    
    function arcontactusUpdateButtonIconsCount(){
        $('#arcu-button-icon-count').text($('#arcu-button-icon li:not(.hidden)').length);
    }
</script>