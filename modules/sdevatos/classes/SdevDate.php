<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevAtos
 * Support by mail : contact@scaledev.fr
 */

namespace ScaleDEV\SdevAtos;

require_once(dirname(__FILE__).'../../autoload.php');

class SdevDate
{
    /**
     * Get a date.
     *
     * @param string $format - Format to get the date.
     * @param string $timestamp - Timestamp to use.
     * @return string
     */
    public static function get($format = 'Y-m-d H:i:s', $timestamp = 'time()')
    {
        try {
            if (!is_string($format)) {
                throw new Exception('The parameter $format must be a string, '.gettype($format).' given !');
            }
            if ($timestamp != 'time()' && !is_int($timestamp)) {
                throw new Exception('The parameter $timestamp must be an integer, '.gettype($timestamp).' given !');
            }
            date_default_timezone_set(date_default_timezone_get());
            return date($format, ($timestamp == 'time()' ? time() : $timestamp));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get a date for next year, next month, next day, ...
     *
     * @param string $format - Format to get the date.
     * @param int $year - Number of next year.
     * @param int $month - Number of next month.
     * @param int $day - Number of next day.
     * @param int $hour - Number of next hour.
     * @param int $minute - Number of next minute.
     * @param int $second - Number of next second.
     * @return string
     * @throws Exception
     */
    public static function getNext($format = 'Y-m-d H:i:s', $year = 0, $month = 0, $day = 0, $hour = 0, $minute = 0, $second = 0)
    {
        try {
            if (!is_string($format)) {
                throw new Exception('The parameter $format must be a string, '.gettype($format).' given !');
            }
            if (!is_int($year)) {
                throw new Exception('The parameter $year must be an integer, '.gettype($year).' given !');
            }
            if (!is_int($month)) {
                throw new Exception('The parameter $month must be an integer, '.gettype($month).' given !');
            }
            if (!is_int($day)) {
                throw new Exception('The parameter $day must be an integer, '.gettype($day).' given !');
            }
            if (!is_int($hour)) {
                throw new Exception('The parameter $hour must be an integer, '.gettype($hour).' given !');
            }
            if (!is_int($minute)) {
                throw new Exception('The parameter $minute must be an integer, '.gettype($minute).' given !');
            }
            if (!is_int($second)) {
                throw new Exception('The parameter $second must be an integer, '.gettype($second).' given !');
            }
            date_default_timezone_set(date_default_timezone_get());
            return date($format, mktime(
                date('H') + (int)$hour,
                date('i') + (int)$minute,
                date('s') + (int)$second,
                date('m') + (int)$month,
                date('d') + (int)$day,
                date('Y') + (int)$year
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get a date for the next day.
     *
     * @param string $format - Format to get the date.
     * @param int $day - Number of next day.
     * @return string
     * @throws Exception
     */
    public static function getNextDay($format = 'Y-m-d H:i:s', $day = 1)
    {
        return self::getNext($format, 0, 0, $day);
    }

    /**
     * Get a date for the next month.
     *
     * @param string $format - Format to get the date.
     * @param int $month - Number of next month.
     * @return string
     * @throws Exception
     */
    public static function getNextMonth($format = 'Y-m-d H:i:s', $month = 1)
    {
        return self::getNext($format, 0, $month);
    }

    /**
     * Get a date for the next year.
     *
     * @param string $format - Format to get the date.
     * @param int $year - Number of next year.
     * @return string
     * @throws Exception
     */
    public static function getNextYear($format = 'Y-m-d H:i:s', $year = 1)
    {
        return self::getNext($format, $year);
    }
}
