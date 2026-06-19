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
 * @package   CedClaroshop
 */
-->

<div class="panel">
    <h3><i class="icon-tag"></i> {l s='Wish Bulk Process' mod='cedwish'}</h3>
    <div class="row">
        <div style="padding: 1%;">
            <div style="padding: 5%;">
                <div class="text-left col-md-4">
                    <i style="font-size: xx-large;" class="icon-desktop"></i>
                </div>
                <div class="text-center col-md-4">
                    <i style="font-size: xx-large;" class="icon-time"></i>
                </div>
                <div class="text-right col-md-4">
                    <i style="font-size: xx-large;" class="icon-desktop"></i>
                </div>
                <p style="font-size: x-large;text-align: center;margin-top: 15px;padding: 5%;"> Uploaded Product count
                    in Greator than {$chuck_limit|escape:'htmlall':'UTF-8'} is added to cron queue.Will be uploaded by cron for better performance.
                </p>
            </div>
        </div>
    </div>
</div>
