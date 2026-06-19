/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

 /* (!)
  Tested on 12.12.2024, with piraeusbank v1.4.0 - by Inno-mods.io
  in /modules/piraeusbank/piraeusbank.php , change:

  $transactionTicket->customer_id = $this->context->customer->id;

  to

  $transactionTicket->customer_id = $this->context->customer->id ?? 0;
 */