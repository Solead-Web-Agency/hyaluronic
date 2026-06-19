{*
* 2007-2019 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel panel-default col-lg-10 col-lg-offset-1 col-md-12 col-md-offset-0">
    <div class="panel-heading">
        <i class="material-icons">access_time</i>{l s='Cron task configuration' mod='pscartabandonmentpro'}
    </div>
    <br/>
    <div class="panel-body">
        <section class="col-lg-xslg-12">
            <p>{l s='To send reminder emails automatically, use a free external service that will trigger every hour the emails that the module has to send, according to your settings.' mod='pscartabandonmentpro'}</p>
        </section>
        <section class="col-lg-12 col-xs-12">
            <div class="section-title col-lg-12 col-xs-12">
                <div class="col-lg-1 col-xs-1">
                    <span class="puce">1</span>
                </div>
                <div class="col-lg-11 col-xs-11">
                    <p><strong>{l s='Connection to Easy CRON' mod='pscartabandonmentpro'}</strong></p>
                </div>
            </div>
            <div class="col-lg-offset col-xs-offset-1 col-lg-11 col-xs-11">
                <p>{l s='Go to' mod='pscartabandonmentpro'} <a href="https://www.easycron.com/user" target="_blank">www.easycron.com/user</a> {l s='and log in with an account.' mod='pscartabandonmentpro'}</p>
            </div>
        </section>
        <section class="col-lg-12 col-xs-12">
            <div class="section-title col-lg-12 col-xs-12">
                <div class="col-lg-1 col-xs-1">
                    <span class="puce">2</span>
                </div>
                <div class="col-lg-11 col-xs-11">
                    <p><strong>{l s='Task parameter' mod='pscartabandonmentpro'}</strong></p>
                </div>
            </div>
            <div class="col-lg-offset-1 col-lg-11 col-xs-11">
                <p>{l s='Create a new Cron Job and copy and paste the URL below into the required field:' mod='pscartabandonmentpro'}</p>
                <p class="url">
                    {l s='URL to call' mod='pscartabandonmentpro'} 
                    <span class="url_to_call">{$cron_url}</span>
                    <span class="btn btn-primary">{l s='Copy the URL' mod='pscartabandonmentpro'}</span>
                    <span class="btn btn-secondary" style="display:none">{l s='Copied' mod='pscartabandonmentpro'}</span>
                </p>
                <p>{l s='In \"When to execute\", select \"Every hour\".' mod='pscartabandonmentpro'}</p>
                <p>{l s='Click on \"Create CRON Job\"' mod='pscartabandonmentpro'}</p>
            </div>
        </section>
        
    </div>
</div>

