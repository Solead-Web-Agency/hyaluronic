{*
* 2007-2017 PrestaShop 
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{*YES/NO Switch*}
                                    <div>
                                            <span id="less_than_more_than" class="switch prestashop-switch input-group col-lg-2">
                                                    <input type="radio" name="less_than" id="less_than" value="1" />
                                                    <label for="less_than" class="radioCheck">
                                                            <i class="color_success"></i> {l s=' less than' mod='worldline'}
                                                    </label>
                                                    <input type="radio" name="more_than" id="more_than" value="0" checked="checked" />
                                                    <label for="more_than" class="radioCheck">
                                                            <i class="color_success"></i> {l s=' more than ' mod='worldline'}
                                                    </label>
                                                    <a class="slide-button btn"></a>
                                            </span><br/>
                                    </div>
