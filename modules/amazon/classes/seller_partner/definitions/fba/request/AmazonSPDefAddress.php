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
class AmazonSPDefAddress extends AmazonSPDefObject
{
    // Require
    public $name;
    public $addressLine1;
    public $stateOrRegion;
    public $countryCode;

    // Optional
    public $addressLine2;
    public $addressLine3;
    public $city;
    public $districtOrCounty;
    public $postalCode;
    public $phone;

    public function validate()
    {
        return $this->name && $this->addressLine1 && $this->stateOrRegion && $this->countryCode;
    }

    public function sanityAddress()
    {
        $this->name = $this->name ? mb_substr($this->name, 0, 50) : '';
        $this->addressLine1 = $this->addressLine1 ? mb_substr($this->addressLine1, 0, 50) : '';
        $this->addressLine2 = $this->addressLine2 ? mb_substr($this->addressLine2, 0, 60) : '';
        $this->addressLine3 = $this->addressLine3 ? mb_substr($this->addressLine3, 0, 60) : '';
        $this->districtOrCounty = $this->districtOrCounty ? mb_substr($this->districtOrCounty, 0, 150) : '';
        $this->city = $this->city ? mb_substr($this->city, 0, 50) : '';
        $this->stateOrRegion = $this->stateOrRegion ? mb_substr($this->stateOrRegion, 0, 50) : '';
        $this->countryCode = $this->countryCode ? mb_substr($this->countryCode, 0, 2) : '';
        $this->postalCode = $this->postalCode ? mb_substr($this->postalCode, 0, 20) : '';
        $this->phone = $this->phone ? mb_substr($this->phone, 0, 20) : '';

        return $this;
    }

    public function toAPI()
    {
        return array(
            'name' => $this->name,
            'addressLine1' => $this->addressLine1,
            'addressLine2' => $this->addressLine2,
            'addressLine3' => $this->addressLine3,
            'stateOrRegion' => $this->stateOrRegion,
            'countryCode' => $this->countryCode,
            'city' => $this->city,
            'districtOrCounty' => $this->districtOrCounty,
            'postalCode' => $this->postalCode,
            'phone' => $this->phone,
        );
    }
}
