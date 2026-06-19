{*
* Do not edit the file if you want to upgrade the module in future.
* 
* @author    Globo Jsc <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @link	     http://www.globosoftware.net/
* @license   please read license in file license.txt
*/
*}
{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.type == 'gupsell_FAQS'}
                </div>
            </div>
        </div>
        <script type="text/javascript">
            var ad  = "";
        </script>
        {*close panel- helper form*}
        <div class="form-group">
            <div class="col-lg-12">
                <div class="col-lg-2">
                </div>
                <div class="col-lg-8">
                    <div class="form-group">
                        <div class="panel">
                            <div class="panel-heading">
                                {l s='Frequently Asked Questions' mod='g_upsellpro'}
                            </div>
                            <div class="gltabs_form">
                                <div class="gltabs_faqstb">
                                    <div class="glfaqs-title form-group">
                                        {l s='Getting Started' mod='g_upsellpro'}
                                    </div>
                                    <div class="glfaqs-title form-group">
                                        {l s='1. From the navigation bar, click ' mod='g_upsellpro'}
                                        <span class="grw_title_bold">{l s='Offers>New Offer>' mod='g_upsellpro'} </span>
                                        {l s='set up your offer (Read our document for more details).' mod='g_upsellpro'}
                                    </div>
                                    <div class="glfaqs-title form-group">
                                        {l s='2. From the navigation bar, click Settings to customize the texts and appearance of the offer.' mod='g_upsellpro'}
                                    </div>
                                    <div class="glfaqs-content">
                                        <div class="glfaqs-body">
                                            <div class="boxFAQ_row form-group">
                                                <div class="glreferenral_box ">
                                                    <div class="glbox-heading boxFAQ gmkt-borrderradius">
                                                        {l s='When the Offer widget appears?' mod='g_upsellpro'}
                                                        {*<i class="icon-plus full-right"></i>*}
                                                    </div>
                                                </div>
                                                <div class="boxFAQ_content">
                                                    <div class="boxFAQ_content_text">
                                                        {l s='The Offer widget will appear after customers add the product which is involved in the offer condition to cart.' mod='g_upsellpro'}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="boxFAQ_row form-group">
                                                <div class="glreferenral_box ">
                                                    <div class="glbox-heading boxFAQ gmkt-borrderradius">
                                                        {l s='How to create a custom Upsell product?' mod='g_upsellpro'}
                                                        {*<i class="icon-plus full-right"></i>*}
                                                    </div>
                                                </div>
                                                <div class="boxFAQ_content">
                                                    <div class="boxFAQ_content_text">
                                                        {l s='You can create a custom Upsell product right in the module admin page. From the navigation bar, click ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='Offers>' mod='g_upsellpro'} </span>
                                                        {l s='edit an ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='Offer>' mod='g_upsellpro'} </span>
                                                        {l s='Scroll down to ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='UPSELL PRODUCTS> ' mod='g_upsellpro'} </span>
                                                        {l s='click  ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='Custom Gift Products> ' mod='g_upsellpro'} </span>
                                                        {l s='add product information.' mod='g_upsellpro'}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="boxFAQ_row form-group">
                                                <div class="glreferenral_box ">
                                                    <div class="glbox-heading boxFAQ gmkt-borrderradius">
                                                        {l s='How to sort offer’s order?' mod='g_upsellpro'}
                                                        {*<i class="icon-plus full-right"></i>*}
                                                    </div>
                                                </div>
                                                <div class="boxFAQ_content">
                                                    <div class="boxFAQ_content_text">
                                                        {l s='From the navigation bar, click ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='Offers>' mod='g_upsellpro'} </span>
                                                        {l s='edit an ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='Offer' mod='g_upsellpro'} </span>
                                                        {l s='Offeryou want to set its order > scroll down to ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='ADDITIONAL SETTINGS> ' mod='g_upsellpro'} </span>
                                                        {l s='select ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='Offer order' mod='g_upsellpro'} </span> .
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="boxFAQ_row form-group">
                                                <div class="glreferenral_box">
                                                    <div class="glbox-heading boxFAQ gmkt-borrderradius">
                                                        {l s='How to totally replace parent products in cart with upsell offer?' mod='g_upsellpro'}
                                                        {*<i class="icon-plus full-right"></i>*}
                                                    </div>
                                                </div>
                                                <div class="boxFAQ_content">
                                                    <div class="boxFAQ_content_text">
                                                        {l s='From the navigation bar, click ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='Offers>' mod='g_upsellpro'} </span>
                                                        {l s='edit an ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='Offer' mod='g_upsellpro'} </span>
                                                        {l s='you want> scroll down to ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='ADDITIONAL SETTINGS> ' mod='g_upsellpro'} </span>
                                                        {l s='enable  ' mod='g_upsellpro'}
                                                            <span class="grw_title_bold">{l s='True Upsell (Upgrade)' mod='g_upsellpro'} </span> .
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                </div>
            </div>
        </div>
        <div class="form-group">
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}