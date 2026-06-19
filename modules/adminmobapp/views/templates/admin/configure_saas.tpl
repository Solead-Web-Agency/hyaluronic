{*
* 2007-2025 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="ai_prestashop" style="display:block;">
  <prestashop-accounts></prestashop-accounts>
  <div id="ps-billing"></div>
  <div id="ps-modal"></div>
</div>
<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel="preload"></script>
<script src="{$urlBilling|escape:'htmlall':'UTF-8'}" rel="preload"></script>

<script>
    window?.psaccountsVue?.init();
    if(window.psaccountsVue.isOnboardingCompleted() != true)
    {
       setTimeout(() => {
            disableSettingsForm();
        }, 200);
    } else {
      setTimeout(() => {
            disableSettingsForm();
      }, 200);
      window.psBilling.initialize(window.psBillingContext.context, '#ps-billing', '#ps-modal', (type, data) => {
            if (!data.subscription) {
                disableSettingsForm();
            }
            planName = data.subscription.plan_id;
            is_cancel = data.subscription.cancelled_at;
            const subscriptionStatus = data.subscription.status;
            enableSettingsForm();
            if (is_cancel) {
              disableSettingsForm();
              //const currentDate='1718140800';
              const currentDate = Math.floor(Date.now() / 1000); // Current timestamp in seconds
              let trialEnd;
              if (subscriptionStatus === 'in_trial') {
                trialEnd = data.subscription.trial_end;
              } else {
                trialEnd = data.subscription.current_term_end;
              }
              // Check if trial period has ended
              if (trialEnd && currentDate > trialEnd) {
                  disableSettingsForm();
              } else {
                  // Subscription is active and trial period has not ended, enable settings form
                  enableSettingsForm();
              }
            }
            // Event hook listener
              switch (type) {
                // Hook triggered when PrestaShop Billing is initialized
                  case window.psBilling.EVENT_HOOK_TYPE.BILLING_INITIALIZED:
                      console.log('Billing initialized', data);
                      break;
                // Hook triggered when the subscription is created or updated
                  case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_UPDATED:
                      console.log('Sub updated', data);
                    break;
                // Hook triggered when the subscription is cancelled
                  case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_CANCELLED:
                      console.log('Sub cancelled', data);
                      const currentDate = Math.floor(Date.now() / 1000); // Current timestamp in seconds
                      //const trialEnd = data.subscription.trial_end;
                      const subscriptionStatus = data.subscription.status;

                      let trialEnd;
                      if (subscriptionStatus === 'in_trial') {
                        trialEnd = data.subscription.trial_end;
                      } else {
                        trialEnd = data.subscription.current_term_end;
                      }

                      // Check if trial period has ended
                      if (trialEnd && currentDate > trialEnd) {
                          disableSettingsForm();
                      } else {
                          // Subscription is active and trial period has not ended, enable settings form
                          enableSettingsForm();
                      }
                      //disableSettingsForm();
                      break;
              }
      });
    }
    
  
    function enableSettingsForm() {
        var moduleForm = document.getElementById('module_form');
        moduleForm.classList.remove("disable-form");
    }

    function disableSettingsForm() {
        var moduleForm = document.getElementById('module_form');
        moduleForm.classList.add("disable-form");
        $("#ai_prestashop").css("display", "block");
    }
</script>
<style type="text/css">
  .disable-form {
/*    display: none;*/
  opacity: 0.5;
  position: relative;
  z-index: -1;
}

.ai_prestashop {
  padding: 0 18px;
  overflow: hidden;
  background-color: #f1f1f1;
}

</style>
