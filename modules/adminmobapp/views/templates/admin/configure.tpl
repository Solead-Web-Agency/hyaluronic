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

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Prestashop Admin Mobile APP' mod='adminmobapp'}</h3>
	<p>
		<strong>{l s='Mobile APP Assistant by FME!' mod='adminmobapp'}</strong><br />
		{l s='Empower your PrestaShop store management with our innovative Admin Mobile Assistant module. Seamlessly manage products, orders, and customers from anywhere with our intuitive mobile interface. Designed for efficiency and ease of use, our app enhances productivity for PrestaShop admins, ensuring you stay connected and in control at all times.' mod='adminmobapp'}<br />
	</p>
	<br />
</div>

<div id="validationModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Validation Error' mod='adminmobapp'}</h4>
            </div>
            <div class="modal-body">
                <p id="validationMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='adminmobapp'}</button>
            </div>
        </div>
    </div>
</div>
