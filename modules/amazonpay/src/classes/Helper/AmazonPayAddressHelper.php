<?php
/**
 * 2007-2025 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2025 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AmazonPayAddressHelper
{
    private static $replacements = [
        // Street variations
        ['pattern' => ['/str\.?|street|straße|strasse/i'], 'replace' => 'str'],

        // Common abbreviations
        ['pattern' => ['/avenue|ave\.?/i'], 'replace' => 'ave'],
        ['pattern' => ['/road|rd\.?/i'], 'replace' => 'rd'],
        ['pattern' => ['/lane|ln\.?/i'], 'replace' => 'ln'],
        ['pattern' => ['/platz|pl\.?/i'], 'replace' => 'pl'],
        ['pattern' => ['/weg|w\.?/i'], 'replace' => 'w'],

        // Number variations
        ['pattern' => ['/no\.|nr\.|nummer|number/i'], 'replace' => 'nr'],

        // Floor/Apartment variations
        ['pattern' => ['/apartment|apt\.?|app\.?/i'], 'replace' => 'apt'],
        ['pattern' => ['/floor|fl\.?|stock/i'], 'replace' => 'fl'],

        // Direction abbreviations
        ['pattern' => ['/north|n\.?/i'], 'replace' => 'n'],
        ['pattern' => ['/south|s\.?/i'], 'replace' => 's'],
        ['pattern' => ['/east|e\.?/i'], 'replace' => 'e'],
        ['pattern' => ['/west|w\.?/i'], 'replace' => 'w'],
    ];

    private static $umlautMap = [
        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue',
        'Ä' => 'ae', 'Ö' => 'oe', 'Ü' => 'ue',
        'ß' => 'ss',
    ];

    public static function normalize($address)
    {
        // Convert to lowercase
        $address = mb_strtolower($address, 'UTF-8');

        // Replace umlauts
        $address = strtr($address, self::$umlautMap);

        // Remove special characters except alphanumeric and spaces
        $address = preg_replace('/[^a-z0-9\s\-]/', '', $address);

        // Apply all replacements
        foreach (self::$replacements as $replacement) {
            foreach ($replacement['pattern'] as $pattern) {
                $address = preg_replace($pattern, $replacement['replace'], $address);
            }
        }

        // Normalize spaces
        $address = preg_replace('/\s+/', ' ', trim($address));

        return $address;
    }

    public static function compareAddresses($address1, $address2)
    {
        $normalized1 = self::normalize($address1);
        $normalized2 = self::normalize($address2);

        return $normalized1 === $normalized2;
    }
}
