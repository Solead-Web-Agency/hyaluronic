{*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @package   cdc_googletagmanager
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>

<script data-keepinline="true">
  window.renderOptIn = function() {
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
        {
          "merchant_id": "{$greviews['merchant_id']|escape:'htmlall':'UTF-8'}",
          "order_id": "{$greviews['order_id']|escape:'htmlall':'UTF-8'}",
          "email": "{$greviews['customer_email']|escape:'htmlall':'UTF-8'}",
          "delivery_country": "{$greviews['delivery_country']|escape:'htmlall':'UTF-8'}",
          "estimated_delivery_date": "{$greviews['estimated_delivery_date']|escape:'htmlall':'UTF-8'}"
        });
    });
  }
</script>


