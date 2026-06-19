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
<ul class="arcu-buttons">
{foreach $items as $item}
    <li>
        <a href="{$item.href}" style="background-color: {$item.color|escape:'htmlall':'UTF-8'}" id="arcu-button-{$item.id|escape:'htmlall':'UTF-8'}" class="arcu-button" {if $item.target eq 0}target="_blank"{/if}>
            <div class="arcu-item-icon">
                {$item.icon nofilter}
            </div>
            <div class="arcu-item-content">
                <div class="arcu-item-title">
                    {$item.title|escape:'htmlall':'UTF-8'}
                </div>
                {if $item.subtitle}
                    <div class="arcu-item-subtitle">
                        {$item.subtitle|escape:'htmlall':'UTF-8'}
                    </div>
                {/if}
            </div>
        </a>
        {if $item.type == 3}
            <script>
                window.addEventListener('load', function(){
                    $('#arcu-button-{$item.id|escape:'htmlall':'UTF-8'}').click(function(e){
                        e.preventDefault();
                        $('#arcontactus').contactUs('openCallbackPopup');
                        {$item.js nofilter}
                        return false;
                    });
                });
            </script>
        {elseif $item.type == 1}
            <script>
                window.addEventListener('load', function(){
                    $('#arcu-button-{$item.id|escape:'htmlall':'UTF-8'}').click(function(e){
                        e.preventDefault();
                        $('#{$item.id|escape:'htmlall':'UTF-8'}').click();
                        return false;
                    });
                });
            </script>
        {elseif $item.type == 2}
            <script>
                window.addEventListener('load', function(){
                    $('#arcu-button-{$item.id|escape:'htmlall':'UTF-8'}').click(function(e){
                        e.preventDefault();
                        {$item.js nofilter}
                        return false;
                    });
                });
            </script>
        {/if}
    </li>
{/foreach}
</ul>