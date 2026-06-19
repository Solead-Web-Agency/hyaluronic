<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

if (class_exists(Module::class)) {
    if (!defined('_PS_VERSION_')) {
        exit;
    }
}
class AmazonSellerPartnerConstant
{
    const OAUTH_CENTRAL_URL = 'https://ap.common-services.com/sp-api/oauth';
    const AMZ_APP_IS_DRAFT = false;

    const MKP_REGION_EU = 'eu-west-1';
    const MKP_REGION_EU_2 = 'eu-west-2';
    const MKP_REGION_NA = 'us-east-1';
    const MKP_REGION_FE = 'us-west-2';

    // Europe
    const MKP_FR = 'A13V1IB3VIYZZH';
    const MKP_ES = 'A1RKKUPIHCS9HS';
    const MKP_DE = 'A1PA6795UKMFR9';
    const MKP_IT = 'APJ6JRA9NG5V4';
    const MKP_UK = 'A1F83G8C2ARO7P';
    const MKP_NL = 'A1805IZSGTT6HS';
    const MKP_SE = 'A2NODRKZP88ZB9';    // Sweden
    const MKP_PL = 'A1C3SOZRARQ6R3';
    const MKP_EG = 'ARBP9OOSHTCHU';
    const MKP_TR = 'A33AVAJ2PDY3EV';
    const MKP_SA = 'A17E79C6D8DWNP';    // Saudi Arabia
    const MKP_AE = 'A2VIGQ35RCS4UG';
    const MKP_IN = 'A21TJRUUN4KGV';
    const MKP_BE = 'AMEN7PMS3EDWL';
    const MKP_ZA = 'AE08WJ6YKNBMC'; // South Africa
    // North America
    const MKP_CA = 'A2EUQ1WTGCTBG2';
    const MKP_US = 'ATVPDKIKX0DER';
    const MKP_MX = 'A1AM78C64UM0Y8';
    const MKP_BR = 'A2Q3Y263D00KWC';
    // Far East
    const MKP_SG = 'A19VAU5U5O7RUS';
    const MKP_AU = 'A39IBJ37TRP1C6';
    const MKP_JP = 'A1VC38T7YXB528';
}
