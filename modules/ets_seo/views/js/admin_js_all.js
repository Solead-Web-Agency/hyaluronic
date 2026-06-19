/**
 * 2007-2021 ETS-Soft
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
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
(function($){

   $(document).on('click','#tab-AdminEtsSeo', function(e){
        $(this).stop().toggleClass('actived');
        $('#subtab-AdminEtsSeoGeneralDashboard,#subtab-AdminEtsSeoUrlAndRemoveId,#subtab-AdminEtsSeoSearchAppearanceSitemap,#subtab-AdminEtsSeoSearchAppearanceRSS,#subtab-AdminEtsSeoFileEditor,#subtab-AdminEtsSeoRatingSnippet,#subtab-AdminEtsSeoSearchAppearanceContentType,#subtab-AdminEtsSeoSocial,#subtab-AdminEtsSeoTraffic,#subtab-AdminEtsSeoSettings').stop().toggleClass('ets_sidebar_menu_show');
   }); 

})(jQuery);





