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
class AmazonSPXSD
{
    public static $descriptionDataAdditionalFields = array(
        'Designer' => array(),
        'MerchantCatalogNumber' => array(),
        'MSRP' => array(),
        'MSRPWithTax' => array(),
        'MaxOrderQuantity' => array(),
        'SerialNumberRequired' => array(),
        'CPSIAWarning' => array(),
        'CPSIAWarningDescription' => array(),
        'LegalDisclaimer' => array(),
        'TargetAudience' => array(),
        'TSDAgeWarning' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK')),
        'TSDWarning' => array(),
        'TSDLanguage' => array(),
        'OptionalPaymentTypeExclusion' => array(),
        'Battery' => array(),
        'AreBatteriesIncluded' => array(),
        'AreBatteriesRequired' => array(),
        'BatterySubgroup' => array(),
        'NumberOfBatteries' => array(),
        'BatteryType' => array(),
        'BatteryCellType' => array(),
        'BatteryWeight' => array(),
        'NumberOfLithiumMetalCells' => array(),
        'NumberOfLithiumIonCells' => array(),
        'LithiumBatteryPackaging' => array(),
        'LithiumBatteryEnergyContent' => array(),
        'LithiumBatteryWeight' => array(),
        'SupplierDeclaredDGHZRegulation' => array(),
        'CaliforniaProposition65ComplianceType' => array(),
        'DepartmentName' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK', 'AU')),
        'ProductExpirationType' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK', 'AU')),
        'SizeName' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK', 'AU')),
        'CountryOfOrigin' => array(
            'requiredFor' => array('FR', 'ES', 'IT', 'DE', 'UK', 'NL'),
            'recommendedFor' => array('AU'),
            'includeInParent' => true
        ),
        'UnitCount' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK', 'AU')),
        'PPUCountType' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK', 'AU')),
        'IsExpirationDatedProduct' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK', 'AU')),
        'EnergyEfficiencyRating' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK')),
        'EuEnergyLabelEfficiencyClass' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK'))
    );

    public static $productXsdGenericFields = array(
        'NumberOfItems' => array('recommendedFor' => array('*')),
        'PesticideMarkingType' => array('recommendedFor' => array('US')),
        'PesticideMarkingRegistrationStatus' => array('recommendedFor' => array('US')),
        'PesticideMarkingCertificationNumber' => array('recommendedFor' => array('US')),
        'MeltingTemperature' => array('recommendedFor' => array('US', 'JP', 'FR', 'ES', 'IT', 'DE', 'UK')),
        'MinimumOrderQuantity' => array('recommendedFor' => array('FR', 'ES', 'IT', 'DE', 'UK')),
        'LiquidateRemainder' => array(),
        'IsHeatSensitive' => array('recommendedFor' => array('US', 'CA', 'FR', 'ES', 'IT', 'DE', 'UK')),
        'MinimumOrderQuantity' => array(),
        'ItemForm' => array(),
    );

    public static $excludedFromDescriptionData = array('WineAndAlcohol.xsd' => array('ItemVolume' => 1));

    public static $uppercaseVariationThemes = array(
        'Baby' => array(
            "BabyProducts",
            "InfantToddlerCarSeat",
            "Stroller",
            "BabyCarrier"
        ),
        'Home' => array(
            "Home",
            "BedAndBath",
            "FurnitureAndDecor",
            "Kitchen",
            "OutdoorLiving",
            "SeedsAndPlants",
            "Art",
            "Fabric",
            "VacuumCleaner",
            "Mattress",
            "Bed",
            "Headboard",
            "Dresser",
            "Cabinet",
            "Chair",
            "Table",
            "Bench",
            "Sofa",
            "Desk",
            "FloorCover",
            "Bakeware",
            "Cookware",
            "Cutlery",
            "Dinnerware",
            "Serveware",
            "KitchenTools",
            "SmallHomeAppliances",
            "BedLinen",
            "WaterPurificationUnit",
        )
    );
}
