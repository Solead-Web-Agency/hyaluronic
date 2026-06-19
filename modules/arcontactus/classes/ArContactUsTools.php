<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

class ArContactUsTools
{
    public static function jsonEncode($data)
    {
        if (function_exists('json_encode')) {
            return json_encode($data);
        }
        if (method_exists('Tools', 'jsonEncode')) {
            return Tools::jsonEncode($data);
        }
        throw new Exception('No JSON encode method found');
    }
    
    public static function jsonDecode($json, $assoc = false)
    {
        if (function_exists('json_decode')) {
            return json_decode($json, $assoc);
        }
        if (method_exists('Tools', 'jsonDecode')) {
            return Tools::jsonDecode($json, $assoc);
        }
        throw new Exception('No JSON decode method found');
    }
}
