<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2023
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/Core.php');
class ApiGetSettingsDash extends Core
{
    public function getData()
    {
        $id_language = (int) Tools::getValue('id_language');
        if (!$id_language) {
            $id_language = $this->context->language->id;
        }

        // $date_from = '2024-01-01 06:03:24';
        // $date_to = '2024-06-28 06:03:24';
        // $date_from = '2024-01-01';
        // $date_to = '2024-06-28';

        $date_from = Tools::getValue('date_from');
        $date_to = Tools::getValue('date_to');

        if (!$this->validateDate($date_from) || !$this->validateDate($date_to)) {
            // Invalid date format
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Invalid date format. Dates should be in Y-m-d format.'
            );
            return $this->fetchJSONResponse();
        }

        $instance = new dashtrends();
        $gross_data = $this->getDataa($date_from, $date_to);
        $tmp_data = $this->refineData($date_from, $date_to, $gross_data);
        $gross_data['average_cart_value'] = $tmp_data['average_cart_value'];
        $gross_data['detail_visits'] = $tmp_data['visits'];
        $gross_data['conversion_rate'] = $tmp_data['conversion_rate'];
        $gross_data['net_profits'] = $tmp_data['net_profits'];

        
        $currency = $this->context->currency;
        $gross_data['currency'] = $currency;
        $this->response['response'] = array(
            'status' => 'success',
            'message' => 'data populated',
            'data' => $gross_data
        );
        return $this->fetchJSONResponse();
    }

    public function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function refineData($date_from, $date_to, $gross_data)
    {
        $refined_data = [
            'sales' => [],
            'orders' => [],
            'average_cart_value' => [],
            'visits' => [],
            'conversion_rate' => [],
            'net_profits' => [],
        ];

        $from = strtotime($date_from . ' 00:00:00');
        $to = min(time(), strtotime($date_to . ' 23:59:59'));
        for ($date = $from; $date <= $to; $date = strtotime('+1 day', $date)) {

            $refined_data['sales'][$date] = 0;
            if (isset($gross_data['total_paid_tax_excl'][$date])) {
                $refined_data['sales'][$date] += $gross_data['total_paid_tax_excl'][$date];
            }

            $refined_data['orders'][$date] = isset($gross_data['orders'][$date]) ? $gross_data['orders'][$date] : 0;

            $refined_data['average_cart_value'][$date] = $refined_data['orders'][$date] ? $refined_data['sales'][$date] / $refined_data['orders'][$date] : 0;

            $refined_data['visits'][$date] = isset($gross_data['visits'][$date]) ? $gross_data['visits'][$date] : 0;

            $refined_data['conversion_rate'][$date] = $refined_data['visits'][$date] ? $refined_data['orders'][$date] / $refined_data['visits'][$date] : 0;

            $refined_data['net_profits'][$date] = 0;
            if (isset($gross_data['total_paid_tax_excl'][$date])) {
                $refined_data['net_profits'][$date] += $gross_data['total_paid_tax_excl'][$date];
            }
            if (isset($gross_data['total_purchases'][$date])) {
                $refined_data['net_profits'][$date] -= $gross_data['total_purchases'][$date];
            }
            if (isset($gross_data['total_expenses'][$date])) {
                $refined_data['net_profits'][$date] -= $gross_data['total_expenses'][$date];
            }
        }

        return $refined_data;
    }

    protected function getDataa($date_from, $date_to)
    {
        $instance = new dashtrends();
        // We need the following figures to calculate our stats
        $tmp_data = [
            'visits' => [],
            'total_visits' => [],
            'orders' => [],
            'total_orders' => [],
            'total_paid_tax_excl' => [],
            'count_paid_tax_excl' => [],
            'total_purchases' => [],
            'count_purchases' => [],
            'total_expenses' => [],
            'count_expenses' => [],
        ];

        if (Configuration::get('PS_DASHBOARD_SIMULATION')) {
            $from = strtotime($date_from . ' 00:00:00');
            $to = min(time(), strtotime($date_to . ' 23:59:59'));
            for ($date = $from; $date <= $to; $date = strtotime('+1 day', $date)) {
                $tmp_data['visits'][$date] = round(rand(2000, 20000));
                $tmp_data['conversion_rate'][$date] = rand(80, 250) / 100;
                $tmp_data['average_cart_value'][$date] = round(rand(60, 200), 2);
                $tmp_data['orders'][$date] = round($tmp_data['visits'][$date] * $tmp_data['conversion_rate'][$date] / 100);
                $tmp_data['total_paid_tax_excl'][$date] = $tmp_data['orders'][$date] * $tmp_data['average_cart_value'][$date];
                $tmp_data['total_purchases'][$date] = $tmp_data['total_paid_tax_excl'][$date] * rand(50, 70) / 100;
                $tmp_data['total_expenses'][$date] = $tmp_data['total_paid_tax_excl'][$date] * rand(0, 10) / 100;
            }
        } else {
            $tmp_data['visits'] = AdminStatsController::getVisits(false, $date_from, $date_to, 'day');
            
            $tmp_data['orders'] = AdminStatsController::getOrders($date_from, $date_to, 'day');
            $tmp_data['total_paid_tax_excl'] = $this->getTotalSalesWithRefunds($date_from, $date_to, 'day');
            $tmp_data['total_purchases'] = AdminStatsController::getPurchases($date_from, $date_to, 'day');
            $tmp_data['total_expenses'] = AdminStatsController::getExpenses($date_from, $date_to, 'day');

            $total_visits = array_sum($tmp_data['visits']);
            $tmp_data['total_visits'] = $total_visits;

            $total_orders = array_sum($tmp_data['orders']);
            $tmp_data['total_orders'] = $total_orders;

            $count_paid_tax_excl = array_sum($tmp_data['total_paid_tax_excl']);
            $tmp_data['count_paid_tax_excl'] = $count_paid_tax_excl;

            $count_purchases = array_sum($tmp_data['total_purchases']);
            $tmp_data['count_purchases'] = $count_purchases;

            $count_expenses = array_sum($tmp_data['total_expenses']);
            $tmp_data['count_expenses'] = $count_expenses;
        }

        return $tmp_data;
    }

    protected function getTotalSalesWithRefunds($date_from, $date_to, $granularity = false)
    {
        $sales = AdminStatsController::getTotalSales($date_from, $date_to, $granularity);

        $refunds = $this->getRefunds($date_from, $date_to, $granularity);
        if (!$granularity) {
            return $sales - $refunds;
        }

        foreach ($sales as $key => $value) {
            if (!isset($refunds[$key])) {
                continue;
            }
            $sales[$key] -= $refunds[$key];
        }

        return $sales;
    }

    protected function getRefunds($date_from, $date_to, $granularity = false)
    {
        $restriction = Shop::addSqlRestriction(false, 'o');
        $sqlRefunds = 'SELECT'
            . (($granularity == 'day' || $granularity == 'month') ? ' LEFT(o.invoice_date, ' . ($granularity == 'day' ? 10 : 7) . ') AS date,' : '')
            . ' SUM((ps.total_products_tax_excl - ps.total_shipping_tax_excl) / ps.conversion_rate) AS orderSlips'
            . ' FROM `' . _DB_PREFIX_ . 'orders` o'
            . ' INNER JOIN `' . _DB_PREFIX_ . 'order_slip` ps ON o.id_order = ps.id_order'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON o.current_state = os.id_order_state'
            . ' WHERE o.invoice_date BETWEEN "' . pSQL($date_from) . ' 00:00:00" AND "' . pSQL($date_to) . ' 23:59:59" AND os.logable = 1'
            . $restriction
            . (($granularity == 'day' || $granularity == 'month') ? 'GROUP BY LEFT(o.invoice_date, ' . ($granularity == 'day' ? 10 : 7) . ')' : '')
        ;
        $refunds = [];

        if ($granularity == 'day') {
            /* @phpstan-ignore-next-line */
            $result = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sqlRefunds);
            foreach ($result as $row) {
                if (!isset($refunds[strtotime($row['date'])])) {
                    $refunds[strtotime($row['date'])] = 0;
                }
                $refunds[strtotime($row['date'])] += $row['orderSlips'];
            }

            return $refunds;
        }

        if ($granularity == 'month') {
            /* @phpstan-ignore-next-line */
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sqlRefunds);
            foreach ($result as $row) {
                if (!isset($refunds[strtotime($row['date'] . '-01')])) {
                    $refunds[strtotime($row['date'] . '-01')] = 0;
                }
                $refunds[strtotime($row['date'] . '-01')] += $row['orderSlips'];
            }

            return $refunds;
        }

        /* @phpstan-ignore-next-line */
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sqlRefunds);
    }
}
