{*
* 2007-2019 PrestaShop
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
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <title>
        Puffy
    </title>
    <!--[if !mso]><!-- -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        #outlook a {
            padding: 0;
        }

        .ReadMsgBody {
            width: 100%;
        }

        .ExternalClass {
            width: 100%;
        }

        .ExternalClass * {
            line-height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        p {
            display: block;
            margin: 13px 0;
        }

        .cta_content a {
            color: #ffffff !important;
            text-decoration: none;
        }
        
        .cta_content a:hover {
            text-decoration: none;
        }
        

        #puffy_tpl a,
        .columns .columns-title a, .columns .columns-description, .columns .columns-price{
            color: {if isset($template_appearance['secondary_color'])}{$template_appearance['secondary_color']}{else}{$color2}{/if};
        }
    </style>
    <!--[if !mso]><!-->
    <style type="text/css">
        @media only screen and (max-width:480px) {
            @-ms-viewport {
                width: 320px;
            }

            @viewport {
                width: 320px;
            }
        }
    </style>
    <!--<![endif]-->
    <!--[if mso]>
        <xml>
        <o:OfficeDocumentSettings>
          <o:AllowPNG/>
          <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
        </xml>
        <![endif]-->
    <!--[if lte mso 11]>
        <style type="text/css">
          .outlook-group-fix { width:100% !important; }
        </style>
        <![endif]-->

    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
    <style type="text/css">
        @import url(https: //fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
    </style>
    <!--<![endif]-->



    <style type="text/css">
        @media only screen and (min-width:480px) {
            .mj-column-per-100 {
                width: 100% !important;
                max-width: 100%;
            }

            .mj-column-per-33 {
                width: 33.333333333333336% !important;
                max-width: 33.333333333333336%;
            }

            .mj-column-px-160 {
                width: 160px !important;
                max-width: 160px;
            }

            .mj-column-px-200 {
                width: 200px !important;
                max-width: 200px;
            }
        }
    </style>


    <style type="text/css">
        @media only screen and (max-width:480px) {
            table.full-width-mobile {
                width: 100% !important;
            }

            td.full-width-mobile {
                width: auto !important;
            }
        }
    </style>
    <style type="text/css">
         .cap-email_reassurance img {
            background : {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};
        }
        
        .discount-code {
            width: 134px !important;
            margin: 10px auto;
            text-align: center;
            color: {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};
            background-color: #eee;
            width: 150px;
            display: block;
            font-weight: 600;
            font-size: 18px;
            padding: 10px 5px
        }

        .columns-container table {
            max-width: 370px
        }

        .columns div {
            position: relative;
            width: 100%
        }

        .columns .columns-title a {
            display: block
        }

        .columns .columns-title a:before {
            position: absolute;
            content: '';
            height: 100%;
            width: 100%;
            left: 0;
            top: 0
        }

        .columns img {
            width: 145px;
            display: block;
            margin: 0 auto
        }

        @media screen and (max-width:768px) {
            .columns img {
                width: 100%
            }
        }

        .columns .columns-title a,
        .columns .columns-description,
        .columns .columns-price {
            font-size: 14px;
        }

        @media screen and (max-width:768px) {

            .columns .columns-title a,
            .columns .columns-description,
            .columns .columns-price {
                font-size: 16px;
            }
        }

        @media screen and (max-width:768px) {
            .columns .columns-description {
                max-width: none
            }
        }

        .mobile-view {
            width: 50%;
            display: inline-block;
            vertical-align: top
        }

        @media screen and (max-width:768px) {
            .mobile-view {
                width: 100%;
                display: block;
                margin-top: 30px
            }
        }

        .socials table {
            width: 60px !important;
            height: 60px
        }

        .socials table td {
            text-align: center
        }
    </style>
</head>

<body style="background-color:{if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};" class="primary_color-backgroundcolor">


    <div id="puffy_tpl" style="background-color:{if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};" class="primary_color-backgroundcolor">


        <!--[if mso | IE]>
    
        <table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td height="40" style="vertical-align:top;height:40px;">
      
    <![endif]-->

        <div style="height:40px;">
            &nbsp;
        </div>

        <!--[if mso | IE]>
    
        </td></tr></table>
      
    <![endif]-->

        <!--	LOGO BEGINING -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      
            <td
               class="" style="vertical-align:top;width:660px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="width:auto; ">

                                                                            <a href="{$shop_url}" target="_blank">

																				<img alt="{$shop_name}" title="{$shop_name}" src="{$shop_logo}" height="auto" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;" width="96" />

																			</a>

                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>

                                                        </td>
                                                    </tr>

                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--	LOGO ENDING -->
        <!--	DASHED BORDER BEGINING -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      
            <td
               class="" style="vertical-align:top;width:660px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">

                                                    <tr>
                                                        <td style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <p style="border-top:dashed 3px {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};font-size:1;margin:0px auto;width:100%;" class="primary_color-bordercolor">
                                                            </p>

                                                            <!--[if mso | IE]>
        <table
           align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:dashed 3px {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};font-size:1;margin:0px auto;width:610px;" role="presentation" width="610px"
        >
          <tr>
            <td style="height:0;line-height:0;">
              &nbsp;
            </td>
          </tr>
        </table>
      <![endif]-->


                                                        </td>
                                                    </tr>

                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--	DASHED BORDER ENDING -->
        <!--	CONTENT BEGINING -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:left;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      <![endif]-->
                                            <!--	INTRO BEGINING -->
                                            <!--[if mso | IE]>
            <td
               class="block-outlook" style="vertical-align:top;width:660px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-100 outlook-group-fix block" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 50px;word-break:break-word;">

                                                            <div class="cap-email_custom_content_here" style="font-family:Arial, sans-serif;font-size:18px;line-height:27px;text-align:center;color:#4A4A4A;">
                                                                {if !isset($template_datas[$employeeLangId]['email_content']) || empty($template_datas[$employeeLangId]['email_content'])}Hello John Doe,{else}{$template_datas[$employeeLangId]['email_content']}{/if}
                                                            </div>

                                                        </td>
                                                    </tr>
                                                    
                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          <![endif]-->
                                            <!--	CONTENT ENDING -->

                                            <!--[if mso | IE]>
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--	DISCOUNT BEGINING -->
        <table align="center"  border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:20px 0;padding-top:0px;text-align:center;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      
            <td
               class="" style="vertical-align:top;width:660px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align:top;padding-top:0px;">

                                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">

                                                                    <tr>
                                                                        <td style="font-size:0px;padding:10px 25px;padding-bottom:15px;word-break:break-word;">

                                                                            <p style="border-top:solid 3px {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};font-size:1;margin:0px auto;width:70px;" class="primary_color-bordercolor">
                                                                            </p>

                                                                            <!--[if mso | IE]>
        <table
           align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 3px {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};font-size:1;margin:0px auto;width:70px;" role="presentation" width="70px"
        >
          <tr>
            <td style="height:0;line-height:0;">
              &nbsp;
            </td>
          </tr>
        </table>
      <![endif]-->


                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                                            <table cellpadding="0" cellspacing="0" width="100%" border="0" style="cellspacing:0;color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;">
                                                                                <tr>
                                                                                    <td style="text-align:center;font-size:18px;color:#4a4a4a; font-family:Arial,sans-serif">
																						<div class="cap-email_discount_content_here" style="cursor:auto;color:#818181;font-family:Arial, Open-sans, Helvetica, sans-serif;font-size:16px;line-height:22px;">
																							{if !isset($template_datas[$employeeLangId]['email_discount']) || empty($template_datas[$employeeLangId]['email_discount'])}Discount here{else}{$template_datas[$employeeLangId]['email_discount']}{/if}
																						</div>
																					</td>
                                                                                </tr>
                                                                            </table>

                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td style="font-size:0px;padding:10px 25px;padding-top:15px;word-break:break-word;">

                                                                            <p style="border-top:solid 3px {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};font-size:1;margin:0px auto;width:70px;" class="primary_color-backgroundcolor primary_color-bordercolor">
                                                                            </p>

                                                                            <!--[if mso | IE]>
        <table
           align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 3px {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};font-size:1;margin:0px auto;width:70px;" role="presentation" width="70px"
        >
          <tr>
            <td style="height:0;line-height:0;">
              &nbsp;
            </td>
          </tr>
        </table>
      <![endif]-->


                                                                        </td>
                                                                    </tr>

                                                                </table>

                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--	DISCOUNT ENDING -->
        <!--	CTA BEGINING -->
        <table align="center" class="cap-email_cta" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;{if empty($template_datas[$employeeLangId]['email_cta'])}display:none;{/if}">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" class="cap-email_cta-outlook" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:20px 0;padding-top:10px;text-align:center;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      
            <td
               class="" style="vertical-align:top;width:660px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align:top;padding-top:0px;">

                                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">

                                                                    <tr>
                                                                        <td align="center" vertical-align="middle" style="font-size:0px;padding:0px 15px;word-break:break-word;">

                                                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;">
                                                                                <tr>
                                                                                    <td align="center" bgcolor="{if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if}" class="primary_color-backgroundcolor" role="presentation" style="border:none;border-radius:3px;cursor:auto;padding:10px 25px;" valign="middle">
                                                                                        <p class="cta_content primary_color-backgroundcolor" style="background:{if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};color:#fff;font-family:Arial, sans-serif;font-size:18px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;">
                                                                                            <a href="#">{if !isset($template_datas[$employeeLangId]['email_cta']) || empty($template_datas[$employeeLangId]['email_cta'])}{l s='Back to cart' mod='pscartabandonmentpro'}{else}{$template_datas[$employeeLangId]['email_cta']}{/if}</a>
																						</p>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>

                                                                        </td>
                                                                    </tr>

                                                                </table>

                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--	CTA ENDING -->
        <!--	DASHED BORDER BEGINING -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:0px;text-align:center;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      
            <td
               class="" style="vertical-align:top;width:660px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">

                                                    <tr>
                                                        <td style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <p style="border-top:dashed 3px {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};font-size:1;margin:0px auto;width:100%;" class="primary_color-bordercolor">
                                                            </p>

                                                            <!--[if mso | IE]>
        <table
           align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:dashed 3px {if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}{$color1}{/if};font-size:1;margin:0px auto;width:610px;" role="presentation" width="610px"
        >
          <tr>
            <td style="height:0;line-height:0;">
              &nbsp;
            </td>
          </tr>
        </table>
      <![endif]-->


                                                        </td>
                                                    </tr>

                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--	DASHED BORDER ENDING -->
        <!--	REINSURANCE BEGINING -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      
            <td
               class="cap-email_reassurance cap-email_reassurance_1-outlook" style="vertical-align:top;width:220px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-33 outlook-group-fix cap-email_reassurance cap-email_reassurance_1" style="font-size:13px;text-align:left;direction:ltr;display:{if !empty($template_datas[$employeeLangId]['email_reassurance_text1'])}inline-block{else}none{/if};vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="width:55px;">

                                                                            <img height="auto" src="{if !empty($template_datas[$employeeLangId]['email_reassurance_img1'])}{$template_datas[$employeeLangId]['email_reassurance_img1']}{else}{$img_url}/templates/reassurance/pack1/loyalty.png{/if}" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;" width="55" />

                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>

                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <div class="cap-email_reassurance_text" style="font-family:Arial, sans-serif;font-size:14px;font-weight:bold;line-height:16px;text-align:center;text-transform:uppercase;color:#4A4A4A;">
                                                                {if isset($template_datas[$employeeLangId]['email_reassurance_text1'])}{$template_datas[$employeeLangId]['email_reassurance_text1']}{/if}
                                                            </div>

                                                        </td>
                                                    </tr>

                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
            <td
               class="cap-email_reassurance cap-email_reassurance_2-outlook" style="vertical-align:top;width:220px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-33 outlook-group-fix cap-email_reassurance cap-email_reassurance_2" style="font-size:13px;text-align:left;direction:ltr;display:{if !empty($template_datas[$employeeLangId]['email_reassurance_text2'])}inline-block{else}none{/if};vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="width:55px;">

                                                                            <img height="auto" src="{if !empty($template_datas[$employeeLangId]['email_reassurance_img2'])}{$template_datas[$employeeLangId]['email_reassurance_img2']}{else}{$img_url}/templates/reassurance/pack1/loyalty.png{/if}" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;" width="55" />

                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>

                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <div class="cap-email_reassurance_text" style="font-family:Arial, sans-serif;font-size:14px;font-weight:bold;line-height:16px;text-align:center;text-transform:uppercase;color:#4A4A4A;">
                                                                {if isset($template_datas[$employeeLangId]['email_reassurance_text2'])}{$template_datas[$employeeLangId]['email_reassurance_text2']}{/if}
                                                            </div>

                                                        </td>
                                                    </tr>

                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
            <td
               class="cap-email_reassurance cap-email_reassurance_3-outlook" style="vertical-align:top;width:220px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-33 outlook-group-fix cap-email_reassurance cap-email_reassurance_3" style="font-size:13px;text-align:left;direction:ltr;display:{if !empty($template_datas[$employeeLangId]['email_reassurance_text3'])}inline-block{else}none{/if};vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="width:55px;">

                                                                            <img height="auto" src="{if !empty($template_datas[$employeeLangId]['email_reassurance_img3'])}{$template_datas[$employeeLangId]['email_reassurance_img3']}{else}{$img_url}/templates/reassurance/pack1/loyalty.png{/if}" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;" width="55" />

                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>

                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <div class="cap-email_reassurance_text" style="font-family:Arial, sans-serif;font-size:14px;font-weight:bold;line-height:16px;text-align:center;text-transform:uppercase;color:#4A4A4A;">
                                                                {if isset($template_datas[$employeeLangId]['email_reassurance_text3'])}{$template_datas[$employeeLangId]['email_reassurance_text3']}{/if}
                                                            </div>

                                                        </td>
                                                    </tr>

                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--REINSURANCE ENDING -->
        <!--	SOCIALS BEGINING -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      
            <td
               class="" style="vertical-align:top;width:160px;"
            >
          <![endif]-->

                                            <div class="cap-social_twitter mj-column-px-160 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:{if !isset($template_datas[$employeeLangId]['email_link_twitter']) || empty($template_datas[$employeeLangId]['email_link_twitter'])}none{else}inline-block{/if};vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                                    <tbody>
                                                        <tr class="socials">
                                                            <td style="vertical-align:top;padding:0px;">

                                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">

                                                                    <tr>
                                                                        <td align="center" vertical-align="middle" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;width:114px;line-height:100%;">
                                                                                <tr>
                                                                                    <td align="center" bgcolor="#5DA8DC" role="presentation" style="border:none;border-radius:3px;cursor:auto;padding:10px 25px;" valign="middle">
                                                                                        <p style="background:#5DA8DC;color:#ffffff;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;">
																							<a href="{if !isset($template_datas[$employeeLangId]['email_link_twitter']) || empty($template_datas[$employeeLangId]['email_link_twitter'])}#{else}{$template_datas[$employeeLangId]['email_link_twitter']}{/if}" style="color:#ffffff;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">
																								<img height="40" src="{$img_url}/templates/social/twitter.png"  style="border-radius:40px;" width="40" />
																							</a>
                                                                                        </p>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>

                                                                        </td>
                                                                    </tr>

                                                                </table>

                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
            <td
               class="" style="vertical-align:top;width:160px;"
            >
          <![endif]-->

                                            <div class="cap-social_facebook mj-column-px-160 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:{if !isset($template_datas[$employeeLangId]['email_link_facebook']) || empty($template_datas[$employeeLangId]['email_link_facebook'])}none{else}inline-block{/if};vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                                    <tbody>
                                                        <tr class="socials">
                                                            <td style="vertical-align:top;padding:0px;">

                                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">

                                                                    <tr>
                                                                        <td align="center" vertical-align="middle" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;width:114px;line-height:100%;">
                                                                                <tr>
                                                                                    <td align="center" bgcolor="#3B579D" role="presentation" style="border:none;border-radius:3px;cursor:auto;padding:10px 25px;" valign="middle">
                                                                                        <p style="background:#3B579D;color:#ffffff;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;">
																							<a href="{if !isset($template_datas[$employeeLangId]['email_link_facebook']) || empty($template_datas[$employeeLangId]['email_link_facebook'])}#{else}{$template_datas[$employeeLangId]['email_link_facebook']}{/if}" style="color:#ffffff;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">
																								<img height="40" src="{$img_url}/templates/social/facebook.png"  style="border-radius:40px;" width="40" />
																							</a>
                                                                                        </p>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>

                                                                        </td>
                                                                    </tr>

                                                                </table>

                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
            <td
               class="" style="vertical-align:top;width:160px;"
            >
          <![endif]-->

                                            <div class="cap-social_instagram mj-column-px-160 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:{if !isset($template_datas[$employeeLangId]['email_link_instagram']) || empty($template_datas[$employeeLangId]['email_link_instagram'])}none{else}inline-block{/if};vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                                    <tbody>
                                                        <tr class="socials">
                                                            <td style="vertical-align:top;padding:0px;">

                                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">

                                                                    <tr>
                                                                        <td align="center" vertical-align="middle" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;width:114px;line-height:100%;">
                                                                                <tr>
                                                                                    <td align="center" bgcolor="#000" role="presentation" style="border:none;border-radius:3px;cursor:auto;padding:10px 25px;" valign="middle">
                                                                                        <p style="background:#000;color:#ffffff;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;">
																							<a href="{if !isset($template_datas[$employeeLangId]['email_link_instagram']) || empty($template_datas[$employeeLangId]['email_link_instagram'])}#{else}{$template_datas[$employeeLangId]['email_link_instagram']}{/if}" style="color:#ffffff;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">
																								<img height="40" src="{$img_url}/templates/social/instagram.png"  style="border-radius:40px;" width="40" />
																							</a>
                                                                                        </p>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>

                                                                        </td>
                                                                    </tr>

                                                                </table>

                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--	SOCIALS ENDING -->
        <!--	ADDRESS BEGINING -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      
            <td
               class="" style="vertical-align:top;width:200px;"
            >
          <![endif]-->

                                            <div class="mj-column-px-200 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;padding-top:0;word-break:break-word;">

                                                            <div style="font-family:Arial, sans-serif;font-size:20px;font-weight:bold;line-height:18px;text-align:center;color:#9B9B9B;">
                                                                {$shop_name}
                                                            </div>

                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <div style="font-family:Arial, sans-serif;font-size:16px;line-height:18px;text-align:center;color:#9b9b9b;">
                                                                {{$shop_addr1}}<br/>
																{{$shop_addr2}}<br/>
																{{$shop_zipcode}} {{$shop_city}} {{$shop_country}}
                                                            </div>

                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <div style="font-family:Arial, sans-serif;font-size:16px;line-height:18px;text-align:center;color:#9b9b9b;">
                                                                {{$shop_phone}}
                                                            </div>

                                                        </td>
                                                    </tr>

                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--	ADDRESS ENDING -->
        <!--	UNSUBSCRIBE BEGINING -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff;background-color:#fff;width:100%;">
            <tbody>
                <tr>
                    <td>


                        <!--[if mso | IE]>
      <table
         align="center" border="0" cellpadding="0" cellspacing="0" style="width:660px;" width="660"
      >
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->


                        <div style="Margin:0px auto;max-width:660px;">

                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;vertical-align:top;">
                                            <!--[if mso | IE]>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                
        <tr>
      
            <td
               class="" style="vertical-align:top;width:660px;"
            >
          <![endif]-->

                                            <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">

                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                                            <div class="cap-email_preview_unsubscribe" style="font-family:Arial, sans-serif;font-size:14px;line-height:16px;text-align:center;color:#9b9b9b;">
                                                                {if !isset($template_datas[$employeeLangId]['email_unsubscribe']) || empty($template_datas[$employeeLangId]['email_unsubscribe'])}Unsubscribe{else}{$template_datas[$employeeLangId]['email_unsubscribe']}{/if}
                                                            </div>

                                                        </td>
                                                    </tr>

                                                </table>

                                            </div>

                                            <!--[if mso | IE]>
            </td>
          
        </tr>
      
                  </table>
                <![endif]-->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>


                        <!--[if mso | IE]>
          </td>
        </tr>
      </table>
      <![endif]-->


                    </td>
                </tr>
            </tbody>
        </table>
        <!--	UNSUBSCRIBE ENDING -->
        <!--	CONTAINER ENDING -->

        <!--[if mso | IE]>
    
        <table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td height="40" style="vertical-align:top;height:40px;">
      
    <![endif]-->

        <div style="height:40px;">
            &nbsp;
        </div>

        <!--[if mso | IE]>
    
        </td></tr></table>
      
    <![endif]-->


    </div>

</body>

</html>