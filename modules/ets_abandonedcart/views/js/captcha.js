/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2022 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

var etsAcOnLoadRecaptcha = function() {
    var addCaptchaV2= false;
    var addCaptchaV3= false;
    if($('.ets-ac-lead-form-captcha').length){
        if(typeof grecaptcha === 'undefined'){
            setTimeout(function () {
                etsAcOnLoadRecaptcha();
            }, 500);
            return false;
        }
    }
    $('.ets-ac-lead-form-captcha').each(function () {
        if($(this).closest('form').find('input[name="captcha_type"]').length){
            var $this = $(this);
            var id = $(this).attr('id');
            var captchaType = $(this).closest('form').find('input[name="captcha_type"]').val();
            var siteKey = $(this).closest('form').find('input[name="captcha_site_key"]').val();

            if(captchaType == 'v2' && !addCaptchaV2){
                grecaptcha.render(id, {
                    'sitekey' : siteKey,
                });
                addCaptchaV2 =true;
            }
            else if (captchaType == 'v3' && !addCaptchaV3){

                grecaptcha.ready(function () {
                    grecaptcha.execute(siteKey, {action: 'submit'}).then(function (token) {
                        $this.closest('form').find('input[name="captcha_v3_response"]').val(token);
                    });
                });
                addCaptchaV3 = true;
            }
        }
    });

}