{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *}
<div class="card bot-zendesk">
  <h3 class="card-header">
  {l s='Module configuration' mod='zendesk'}
  </h3>
  <div class="card-body bootstrap" id="module-zendesk-configuration">
    <div id="dev_page-index" class="dev_page">
    <article class="reg-refresh">
    <h2>{l s='Account settings' mod='zendesk'}</h2>

    <form action="{$setting_url|escape:'htmlall':'UTF-8'}" method="post" class="reg">
      <input type="hidden" name="submitConfig" value="1" />
      <div class="row">
        <div class="col-md-6">
            <ul>
              <li class="domain">
                <label class="side-label" for="subdomain">{l s='Subdomain' mod='zendesk'}</label>
                <input type="text" placeholder="subdomain" name="subdomain" id="subdomain" class="required" value="{$zendesk_subdomain|escape:'htmlall':'UTF-8'}">
                <h4 class="domain-box">.zendesk.com</h4>
                <div class="shadow">.zendesk.com</div>
                <label class="error url"><span></span>{l s='Enter only letters and numbers' mod='zendesk'}</label>
                <label class="suggested"><span class="info"></span>{l s='Checking domain availability...' mod='zendesk'}</label>
                <!-- <span class="check"></span> -->
                <span class="domain-ping pulse"></span>
              </li>

              <li>
                <label class="side-label" for="email">{l s='Account email' mod='zendesk'}</label>
                <input type="text" placeholder="" name="email" id="email" class="required" value="{$zendesk_email|escape:'htmlall':'UTF-8'}">
              </li>

              <li>
                <label class="side-label" for="api_key">{l s='API key' mod='zendesk'}</label>
                <input type="text" placeholder="" name="api_key" id="api_key" class="required" value="{$zendesk_api_key|escape:'htmlall':'UTF-8'}">

                <div class="description">
                  {l s='To generate a new API token, go to' mod='zendesk'} <a href="https://{$zendesk_subdomain|escape:'htmlall':'UTF-8'}.zendesk.com/agent/admin/api" target="_blank">{l s='Zendesk > Admin > Channels > API' mod='zendesk'}</a>
                  <ul>
                    <li>{l s='In the Settings tab, select "add new token"' mod='zendesk'}</li>
                    <li>{l s='Name the new token "Prestashop". Click "Create"' mod='zendesk'}</li>
                    <li>{l s='Copy and paste your new API token in the field above. Need help?' mod='zendesk'} <a href="https://www.zendesk.com/support/contact/" target="_blank">{l s='Click here' mod='zendesk'}</a></li>
                    {if $zendesk_api_key && $zendesk_order_id_field && $zendesk_order_id_field !== false}
                      <li><a href="#" id="show_more_link">
                          {l s='Show more informations about ZenDesk App Link' mod='zendesk'}
                        </a>
                      </li>
                    {/if}
                  </ul>
                </div>

              </li>
              
              {if $zendesk_api_key && $zendesk_order_id_field && $zendesk_order_id_field !== false}
                <li class="show_more_links" style="display:none;">
                  <label class="side-label">{l s='App ZenDesk Access Token' mod='zendesk'}</label>
                  <p><strong>{$zendesk_access_token|escape:'htmlall':'UTF-8'}</strong></p>
                </li>

                <li class="show_more_links" style="display:none;">
                  <label class="side-label">{l s='App ZenDesk Order ID Field Id' mod='zendesk'}</label>
                  <p><strong>{$zendesk_order_id_field|escape:'htmlall':'UTF-8'}</strong></p>
                </li>
              {/if}
            </ul>

        </div>
      </div>


      <section id="settings_embed" class="settings">
        <div class="demo">
          <video width="414" height="462" controls autoplay loop poster="../modules/zendesk/views/img/blank_poster.png">
            <source src="../modules/zendesk/views/img/Zendesk-widget2.mp4" type="video/mp4">
            {l s='I\'m sorry; your browser doesn\'t support HTML5 video in MP4 with H.264.' mod='zendesk'}
            <!-- You can embed a Flash player here, to play your mp4 video in older browsers -->
          </video>
        </div>

        <h2>{l s='Embed Zendesk widget in Prestashop' mod='zendesk'}</h2>

        <p>{l s='The Zendesk widget seamlessly integrates Zendesk functionality into your Prestashop storefront. Using the Zendesk widget, you can reach out to your customers and offer support, provide information, or start a conversation.' mod='zendesk'} <a href="https://www.zendesk.com/embeddables/" target="_blank">{l s='See it in action' mod='zendesk'}</a></p>

        <div class="switch-container pull-right">
          <div class="switch">
            <label class="switch-label" for="embed_enabled">
            <input name="embed-toggle" type="hidden" value="0"><input {if $zendesk_widget}checked="checked"{/if} class="switch-checkbox config_set_configs_ticket_submission_enabled" id="embed_enabled" name="embed-toggle" type="checkbox" value="1">
            <span class="switch-content">
            <span class="switch-bg"></span>
            <span class="switch-toggle"></span>
            </span>
            </label>
          </div>
        </div>

        <p>
          <strong>{l s='Enable Zendesk web widget on your shop' mod='zendesk'}</strong><br>
          {l s='You can customize your widget in your' mod='zendesk'} <a href="https://{$zendesk_subdomain|escape:'htmlall':'UTF-8'}.zendesk.com/agent/admin/widget" target="_blank">{l s='Zendesk Web Widget settings' mod='zendesk'}</a>
        </p>

        <div class="switch-container pull-right">
          <div class="switch">
            <label class="switch-label" for="preload_widget_enabled">
              <input name="preload_widget_toggle" type="hidden" value="0"><input {if $zendesk_preload_widget}checked="checked"{/if} class="switch-checkbox config_set_configs_ticket_submission_enabled" id="preload_widget_enabled" name="preload_widget_toggle" type="checkbox" value="1">
              <span class="switch-content" id="switch-preload">
                <span class="switch-bg"></span>
                <span class="switch-toggle">
              </span>
            </span>
            </label>
          </div>
        </div>

        <p>
          <strong>{l s='Enable widget preload on your shop' mod='zendesk'}</strong><br>
          {l s='Select on which page preloading should be enabled (activate this option may impact page performance).' mod='zendesk'}
        </p>

        <div id="preload_widget_controllers" {if !$zendesk_preload_widget} style="display:none;"{/if}>
        {foreach $zendesk_preload_widget_controllers as $controller => $informations}
          <div class="form-check">
            {assign var="value" value=$informations['value']}
            <input class="form-check-input" type="checkbox" 
              name="preload_widget_controllers[{$controller|escape:'htmlall':'UTF-8'}]" id="preload_widget_{$controller|escape:'htmlall':'UTF-8'}_controller" 
              {if $value}value="1" checked{else}value="0"{/if} />
            <label class="form-check-label" for="preload_widget_{$controller|escape:'htmlall':'UTF-8'}_controller">
              {$informations['name']|escape:'htmlall':'UTF-8'}
            </label>
          </div>
        {/foreach}
        </div>
      </section>


      <section id="settings_enable" class="settings">
        <div class="demo">
          <img src="../modules/zendesk/views/img/prestashop-app.png" width="318" height="318" alt="">
        </div>

        <h2>{l s='Enable Prestashop app in Zendesk' mod='zendesk'}</h2>

        <p>{l s='The Zendesk for Prestashop app unites your business by displaying critical Prestashop data inside your Zendesk, next to your ticket information. This app queries your Prestashop store to find customer details and recent orders.' mod='zendesk'} <a href="https://www.zendesk.com/apps/prestashop/" target="_blank">{l s='Learn more' mod='zendesk'}</a></p>

        <div class="switch-container pull-right">
          <div class="switch">
            <label class="switch-label" for="settings_enabled">
            <input name="settings-toggle" type="hidden" value="0"><input {if $zendesk_app}checked="checked"{/if} class="switch-checkbox config_set_configs_ticket_submission_enabled" id="settings_enabled" name="settings-toggle" type="checkbox" value="1">
            <span class="switch-content">
            <span class="switch-bg"></span>
            <span class="switch-toggle"></span>
            </span>
            </label>
          </div>
        </div>

        <p>
          <strong>{l s='Enable Prestashop app in Zendesk' mod='zendesk'}</strong><br>
          {l s='You can edit your app settings in' mod='zendesk'} <a href="https://{$zendesk_subdomain|escape:'htmlall':'UTF-8'}.zendesk.com/agent/admin/apps/manage" target="_blank">{l s='Zendesk App management' mod='zendesk'}</a>
        </p>
      </section>


      <section id="settings_enable" class="settings">
        <h2>{l s='Link tickets from this shop to a Brand' mod='zendesk'}</h2>

        <p>
          {l s='If you have working with multi-shop or want to link to more than one Shop, you can bind your configuration to a Brand in ZenDesk.' mod='zendesk'}<br />
          {l s='The tickets from contact page or orders will be dispatched on this Brand.' mod='zendesk'}
        </p>

        <p>
          <strong>{l s='Select the brand' mod='zendesk'}</strong>
          <select name="brand_id" class="select">
          {foreach $zendesk_brands as $oneBrand}
              <option value="{$oneBrand.id|escape:'htmlall':'UTF-8'}"{if $oneBrand.selected == true} selected{/if}>
                {$oneBrand.name|escape:'htmlall':'UTF-8'}
              </option>
          {/foreach}
          </select>
        </p>
      </section>

      <div class="submit-row">
        <button type="submit" class="zendesk-btn">{l s='Save' mod='zendesk'}</button>
      </div>

    </form>
    </article>
    </div>
  </div>
</div>