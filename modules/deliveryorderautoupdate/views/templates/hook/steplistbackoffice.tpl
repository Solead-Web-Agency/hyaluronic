{* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from Helloshop
* Use, copy, modification or distribution of this source file without written
* license agreement from the Helloshop is strictly forbidden.
* In order to obtain a license, please contact us: modules@helloshop.com
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par Helloshop
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la Helloshop est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter la Helloshop a l'adresse:
*                  modules@helloshop.com
* ...........................................................................
* @copyright  2007-2023 Helloshop
* @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="step-list {if isset($i)}{$i|escape:'htmlall':'UTF-8'}{/if}" {if isset($i)}data-id="{$i|escape:'htmlall':'UTF-8'}"{/if}>
    {if $steps}
    <div class="item-rows">
		 
        {if $steps.order}
        <div class="wrapper step-1"> 
            <div class="icon i-step-1" >
                <div class="border">
                    <span class="wrapper" style="background:#0080ff">
                        <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/check.png"  />
                    </span>
                </div>
            </div>
            <div style="font-weight:bold;"> {l s='Order placed' mod='deliveryorderautoupdate'}</div>
            <span class="date">{date('Y-m-d', strtotime($steps.order))|escape:'htmlall':'UTF-8'}</span>
        </div>
		
        {/if}
        {if $steps.shipped && $steps.current_status != 4}
        <div class="wrapper step-2">
            <div class="icon i-step-2">
                <div class="border">
                    <span class="wrapper" >
                        <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/check.png"  />
                    </span>
                </div>
            </div>
            <div style="font-weight:bold;"> {l s='Shipped' mod='deliveryorderautoupdate'}
            </div>
            <span class="date">{date('Y-m-d', strtotime($steps.shipped))|escape:'htmlall':'UTF-8'}</span>
        </div>
        {/if}
        {if $steps.current}
        <div class="wrapper current_status step-3">
            <div class="icon i-step-3">
                <div class="border" >
                    <span class="wrapper" style="background:{$statuses[$steps.current_status]->color|escape:'htmlall':'UTF-8'}">
                        <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$steps.current_status]->id_status|escape:'htmlall':'UTF-8'}.png"  />
                    </span>
                </div>
            </div>
            <div style="font-weight:bold;"> {$steps.status_text|escape:'htmlall':'UTF-8'}
            </div>
            <span class="date">{date('Y-m-d', strtotime($steps.current))|escape:'htmlall':'UTF-8'}</span>
        </div>
        {/if}
        {if $steps.current_status != 1}
        <div class="wrapper step-4">
            <div class="icon not-reach  i-step-4">
                <div class="border">
                    <span class="wrapper" style="background:#C8C8C8">
                        <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/1.png"  />
                    </span>
                </div>
            </div>
            <div style="color: white;font-weight:bold;"> {l s='Delivery' mod='deliveryorderautoupdate'}</div>
        </div>
        {/if}
    </div>
    {/if}
</div>
