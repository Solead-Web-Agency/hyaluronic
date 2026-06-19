/*
 * NOTICE OF LICENSE
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * @author    Peter Sliacky (Prestasmart)
 * @copyright Peter Sliacky (Prestasmart)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Copyright (c) 2021-2022
 */

// If we're in an iframe, let's do full page reload
var confirmationInIframe = (window.self !== window.top)

if (confirmationInIframe) {
    window.top.location.href = window.location.href;
}

