<?php
/**
 * 2010-2023 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2023 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PriceFormat
{
    const TYPE_1 = 1;
    const TYPE_2 = 2;
    const TYPE_3 = 3;
    const TYPE_4 = 4;
    const TYPE_5 = 5;
    const TYPE_6 = 6;
    const DEFAULT_PRECISION = 2;

    public function getList()
    {
        return [
            self::TYPE_1 => '1234.56 (with a dot)',
            self::TYPE_2 => '12345,56 (with a comma)',
            self::TYPE_3 => '1234 (only integer)',
            self::TYPE_4 => '123400 (price in cents)',
            self::TYPE_5 => '1235 (only integer, round up)',
            self::TYPE_6 => '1234 (only integer, round down)',
        ];
    }

    public static function convertByType($price = 0, $type = 0)
    {
        if (empty($type)) {
            return Tools::ps_round($price, self::DEFAULT_PRECISION);
        }

        $roundMode = null;

        $precision = (in_array($type, [self::TYPE_3, self::TYPE_5, self::TYPE_6,])) ? 0 : self::DEFAULT_PRECISION;

        if ($type == self::TYPE_5) {
            $roundMode = PS_ROUND_UP;
        }

        if ($type == self::TYPE_6) {
            $roundMode = PS_ROUND_DOWN;
        }

        $price = str_replace(' ', '', $price);
        $price = Tools::ps_round($price, $precision, $roundMode);

        if ($type == self::TYPE_1) {
            $price = number_format(str_replace(',', '.', $price), self::DEFAULT_PRECISION, '.', '');
        } elseif ($type == self::TYPE_2) {
            $price = str_replace('.', ',', $price);
        } elseif ($type == self::TYPE_4) {
            $price = $price * 100;
        }

        return $price;
    }
}
