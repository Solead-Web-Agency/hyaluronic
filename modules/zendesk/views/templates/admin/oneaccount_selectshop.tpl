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
 <link rel="stylesheet" type="text/css" media="all" href="../modules/zendesk/views/css/fonts.css" />
 <link rel="stylesheet" type="text/css" media="all" href="../modules/zendesk/views/css/bootstrap-admin-configuration.css" />
 <link rel="stylesheet" type="text/css" media="all" href="../modules/zendesk/views/css/cloudfront.css" />
 <link rel="stylesheet" type="text/css" media="all" href="../modules/zendesk/views/css/screen.css?v=3" />

<div class="card main-zendesk"{if $isThereMesssages == true} style="margin-top:10px;"{/if}>
    <h3 class="card-header">{l s='Zendesk connector V2' mod='zendesk'}</h3>
    <div class="card-body card-body-zendeskv2">
        <div class="card">
            <h3 class="card-header">
                {l s='All stores' mod='zendesk'}
            </h3>
            <div class="card-body">
                <p>{l s='Please select a store to continue. You cannot configure in the \'All stores\' context.' mod='zendesk'}</p>
            </div>
        </div>
    </div>
</div>