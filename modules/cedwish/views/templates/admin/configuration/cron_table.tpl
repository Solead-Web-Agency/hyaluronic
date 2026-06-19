<!--
 /**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   cedwish
 */
 -->

<div class="row">
    <div class="col-md-12">
        <p>
            {l s='How to set Cron in' mod='cedwish'}
        </p>
        <table class="table">
            <tr>
                <td>{l s='cPanel' mod='cedwish'}</td>
                <td>
                    <a
                            href="https://blog.cpanel.com/how-to-configure-a-cron-job"
                            target="_blank"
                    >{l s='Click Here' mod='cedwish'}
                    </a>
                </td>
            </tr>
            <tr>
                <td>{l s='Plesk Panel' mod='cedwish'}</td>
                <td>
                    <a
                            href="https://support.plesk.com/hc/en-us/articles/115003121073-How-to-add-a-scheduled-task-in-Plesk-UI-using-crontab-syntax-"
                            target="_blank"
                    >
                        {l s='Click Here' mod='cedwish'}
                    </a>
                </td>
            </tr>
            <tr></tr>
        </table>
    </div>
    <div class="col-md-12">
        <div class="col-md-3 text-right">
            <label>{l s='Cron Secure Key' mod='cedwish'}</label>
        </div>
        <div class="col-md-6">
            <table class="table">
                <tr>
                    <td>
                        <input
                                name="CED_WISH_CRON_SECURE_KEY"
                                type="text"
                                class="form-control"
                                id="CED_WISH_CRON_SECURE_KEY"
                                value="{$cron_secure_key|escape:'htmlall':'UTF-8'}"
                        />
                    </td>
                    <td>
                        <button
                                type="button"
                                class="btn b--black"
                                onclick="generateString('10');"
                        >
                            {l s='Generate String' mod='cedwish'}
                        </button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-sm-12">
        <table class="table">
            <thead>
            <tr>
                <th><strong>{l s='Name' mod='cedwish'}</strong></th>
                <th><strong>{l s='Url' mod='cedwish'}</strong></th>
                <th></th>
                <th><strong>{l s='Recommended Time' mod='cedwish'}</strong></th>
                <th><strong>{l s='Last Execution' mod='cedwish'}</strong></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td style="width: 10%;">{l s='Order' mod='cedwish'}</td>
                <td style="width: 55%;">
                    <input
                            type="text"
                            class="form-control"
                            id="cron_1"
                            value="{$order_cron_url|escape:'htmlall':'UTF-8'}"
                    >
                </td>
                <td style="width: 5%;">
                    <button
                            type="button"
                            class="btn b--black"
                            onclick="copyMyText('cron_1');"
                    >
                        {l s='Copy' mod='cedwish'}
                    </button>
                </td>
                <td style="width: 15%;">{l s='Every 10-15 minutes' mod='cedwish'}</td>
                <td style="width: 15%;color: green">
                    {if $order_cron_execution}
                        {$order_cron_execution|escape:'htmlall':'UTF-8'}
                    {/if}
                    <input
                            type="hidden"
                            name="CED_WISH_ORDER_CRON_LAST_EXECUTION"
                            value="{$order_cron_execution|escape:'htmlall':'UTF-8'}"
                    />
                </td>
            </tr>
            <tr>
                <td style="width: 10%;">{l s='Order Status' mod='cedwish'}</td>
                <td style="width: 55%;">
                    <input
                            type="text"
                            class="form-control"
                            id="cron_3"
                            value="{$order_status_cron|escape:'htmlall':'UTF-8'}"
                    />
                </td>
                <td style="width: 5%;">
                    <button
                            type="button"
                            class="btn b--black"
                            onclick="copyMyText('cron_3');"
                    >
                        {l s='Copy' mod='cedwish'}
                    </button>
                </td>
                <td style="width: 15%;">{l s='Every 10-15 minutes' mod='cedwish'}</td>
                <td style="width: 15%;color: green">
                    {if $order_status_cron_execution}
                        {$order_status_cron_execution|escape:'htmlall':'UTF-8'}
                    {/if}
                    <input
                            type="hidden"
                            name="CED_WISH_ORDER_STATUS_CRON_LAST_EXECUTION"
                            value="{$order_status_cron_execution|escape:'htmlall':'UTF-8'}"
                    />
                </td>
            </tr>
            <tr>
                <td style="width: 10%;">{l s='Queue' mod='cedwish'}</td>
                <td style="width: 55%;">
                    <input
                            type="text"
                            class="form-control"
                            id="cron_2"
                            value="{$queue_cron_url|escape:'htmlall':'UTF-8'}"
                    />
                </td>
                <td style="width: 5%;">
                    <button
                            type="button"
                            class="btn b--black"
                            onclick="copyMyText('cron_2');">
                        {l s='Copy' mod='cedwish'}
                    </button>
                </td>
                <td style="width: 15%;">{l s='Every 10-15 minutes' mod='cedwish'}</td>
                <td style="width: 15%;color: green">
                    {if $queue_cron_execution}
                        {$queue_cron_execution|escape:'htmlall':'UTF-8'}
                    {/if}
                    <input
                            type="hidden"
                            name="CED_WISH_QUEUE_CRON_LAST_EXECUTION"
                            value="{$queue_cron_execution|escape:'htmlall':'UTF-8'}"
                    />
                </td>
            </tr>
            <tr>
                <td style="width: 10%;">{l s='Stock' mod='cedwish'}</td>
                <td style="width: 55%;">
                    <input
                            type="text"
                            class="form-control"
                            id="cron_4"
                            value="{$stock_cron_url|escape:'htmlall':'UTF-8'}"
                    />
                </td>
                <td style="width: 5%;">
                    <button
                            type="button"
                            class="btn b--black"
                            onclick="copyMyText('cron_4');"
                    >
                        {l s='Copy' mod='cedwish'}
                    </button>
                </td>
                <td style="width: 15%;">{l s='Every 10-15 minutes' mod='cedwish'}</td>
                <td style="width: 15%;color: green">
                    {if $stock_cron_execution}
                        {$stock_cron_execution|escape:'htmlall':'UTF-8'}
                    {/if}
                    <input
                            type="hidden"
                            name="CED_WISH_STOCK_CRON_LAST_EXECUTION"
                            value="{$stock_cron_execution|escape:'htmlall':'UTF-8'}"
                    />
                </td>
            </tr>
            </tbody>
        </table>
        <script>
            function copyMyText(copyMeElement) {
                var textToCopy = document.getElementById(copyMeElement);
                textToCopy.select();
                document.execCommand("copy");
            }

            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

            function generateString(length) {
                let result = '';
                const charactersLength = characters.length;
                for (let i = 0; i < length; i++) {
                    result += characters.charAt(Math.floor(Math.random() * charactersLength));
                }
                document.getElementById('CED_WISH_CRON_SECURE_KEY').value = result;
            }
        </script>
    </div>
</div>
<script type="text/javascript">
    $("document").ready(function () {
        $('select[name="CED_WISH_SHIPMENT_CREATE"]').change(function (e) {
            if ($(this).val() == 'ORDER_STATUS') {
                $('select[name="CED_WISH_SHIPMENT_CREATE_STATUS"]').parent().parent().show();
            } else {
                $('select[name="CED_WISH_SHIPMENT_CREATE_STATUS"]').parent().parent().hide();
            }
        });
        if ($('select[name="CED_WISH_SHIPMENT_CREATE"]').val() == 'ORDER_STATUS') {
            $('select[name="CED_WISH_SHIPMENT_CREATE_STATUS"]').parent().parent().show();
        } else {
            $('select[name="CED_WISH_SHIPMENT_CREATE_STATUS"]').parent().parent().hide();
        }

        $('select[name="CED_VIDAXL_DROPSHIPPING_ORDER_INVOICE"]').change(function (e) {
            if ($(this).val() == 'AFTER_ORDER_STATUS_INVOICE') {
                $('select[name="CED_VIDAXL_DROPSHIPPING_ORDER_INVOICE_STATUS"]').parent().parent().show();
            } else {
                $('select[name="CED_VIDAXL_DROPSHIPPING_ORDER_INVOICE_STATUS"]').parent().parent().hide();
            }
        });
        if ($('select[name="CED_VIDAXL_DROPSHIPPING_ORDER_INVOICE"]').val() == 'AFTER_ORDER_STATUS_INVOICE') {
            $('select[name="CED_VIDAXL_DROPSHIPPING_ORDER_INVOICE_STATUS"]').parent().parent().show();
        } else {
            $('select[name="CED_VIDAXL_DROPSHIPPING_ORDER_INVOICE_STATUS"]').parent().parent().hide();
        }
    });
</script>
