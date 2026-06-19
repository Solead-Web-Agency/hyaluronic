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
        <i class="material-icons">info</i>
        {l s='Help for the Cart Abandonment Pro module' mod='pscartabandonmentpro'}
    </div>
    <div class="helpContentParent">
        <div class="col-lg-6">
            <div class="col-lg-2">
                <img src="{$logo_path|escape:'htmlall':'UTF-8'}" alt=""/>
            </div>
            <div class="col-lg-10" style="border-right: 1px solid #D3D3D3;">
                <p><span class="data_label" style="color:#00aff0;"><b>{l s='This module allows you to :' mod='pscartabandonmentpro'}</b></span></p>
                <br>
                <div>
                    <div class="numberCircle">1</div>
                    <div class="numberCircleText">
                        <p class="numberCircleText">
                            {l s='Create up to 5 reminders' mod='pscartabandonmentpro'}
                        </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">2</div>
                    <div class="numberCircleText">
                        <p class="numberCircleText">
                            {l s='Select a target and a interval between cart abandonment and reminder' mod='pscartabandonmentpro'}
                        </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">3</div>
                    <div class="numberCircleText">
                        <p class="numberCircleText">
                            {l s='Set ranges of discounts depending on cart value' mod='pscartabandonmentpro'}
                        </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">4</div>
                    <div class="numberCircleText">
                        <p class="numberCircleText">
                            {l s='Customize the email : model and color, subject, content, languages, reassurance elements, social network links and footer.' mod='pscartabandonmentpro'}
                        </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">5</div>
                    <div class="numberCircleText">
                        <p class="numberCircleText">
                            {l s='Set automatic cron tasks for automatic sendings of email' mod='pscartabandonmentpro'}
                        </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">6</div>
                    <div class="numberCircleText">
                        <p class="numberCircleText">
                            {l s='Observe your ROI with complete statistics' mod='pscartabandonmentpro'}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="helpContentRight-sub">
                <b>{l s='Need help ?' mod='pscartabandonmentpro'}</b><br>
                {l s='Find here the documentation of this module' mod='pscartabandonmentpro'}
                <a class="btn btn-primary" href="{$guide_link|escape:'htmlall':'UTF-8'}" target="_blank" style="margin-left:20px;" href="#">
                    <i class="fa fa-book"></i> {l s='Documentation' mod='pscartabandonmentpro'}</a>
                </a>
            </div>
        </div>
    </div>
</div>


<div class="panel panel-default col-lg-10 col-lg-offset-1 col-md-12 col-md-offset-0">
    <div class="tab-pane" id="faq">
        <div class="panel-heading">
            <i class="icon-question"></i> {l s='FAQ' mod='pscartabandonmentpro'}
        </div>
        <div style="padding: 0px 20px">
            {if $apifaq}
                {foreach from=$apifaq item=categorie name='faq'}
                    <span class="faq-h1">{$categorie->title|escape:'htmlall':'UTF-8'}</span>
                    <ul>
                        {foreach from=$categorie->blocks item=QandA}
                            {if !empty($QandA->question)}
                                <li>
                                    <span class="faq-h2"><i class="icon-info-circle"></i> {$QandA->question|escape:'htmlall':'UTF-8'}</span>
                                    <p class="faq-text hide">
                                        {$QandA->answer|escape:'htmlall':'UTF-8'|replace:"\n":"<br />"}
                                    </p>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                    {if !$smarty.foreach.faq.last}<hr/>{/if}
                {/foreach}
            {/if}
            {if !$isReady}
                {l s='You couldn\'t find any answer to your question ?' mod='pscartabandonmentpro'}
                <b><a href="http://addons.prestashop.com/contact-form.php" target="_blank">{l s='Contact us on PrestaShop Addons' mod='pscartabandonmentpro'}</a></b>
                <br/><br/>
            {/if}
        </div>
    </div>
</div>


