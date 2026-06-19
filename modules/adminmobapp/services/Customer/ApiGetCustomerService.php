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
class ApiGetCustomerService extends Core
{
    public function getData()
    {
        try {
            $statistics = $this->getCustomerServiceStats();
            $this->response['response']['data'] = $statistics;
        } catch (Exception $e) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            );
        }

        return $this->fetchJSONResponse();
    }

    private function getCustomerServiceStats()
    {
        $db = Db::getInstance();

        // Get counts of customer service threads by status
        $statuses = ['Open', 'Closed', 'Pending1', 'Pending2'];
        $statusCounts = [];
        foreach ($statuses as $status) {
            $statusCounts[$status] = (int) $db->getValue(
                'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'customer_thread WHERE status = "' . pSQL($status) . '"'
            );
        }

        // Total threads
        $totalThreads = (int) $db->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'customer_thread'
        );

        // Threads in the last 30 days
        $last30DaysThreads = $db->executeS(
            'SELECT id_customer_thread, date_add FROM ' . _DB_PREFIX_ . 'customer_thread WHERE date_add > DATE_SUB(NOW(), INTERVAL 30 DAY)'
        );

        // Calculate average response time and messages per thread
        $totalResponseTime = 0;
        $totalMessages = 0;
        $totalCustomerMessages = 0;
        $totalEmployeeMessages = 0;
        $threadCount = count($last30DaysThreads);

        foreach ($last30DaysThreads as $thread) {
            $responses = $db->executeS(
                'SELECT date_add, private FROM ' . _DB_PREFIX_ . 'customer_message WHERE id_customer_thread = ' . (int) $thread['id_customer_thread']
            );

            $messageCount = count($responses);
            $totalMessages += $messageCount;

            foreach ($responses as $response) {
                if ($response['private']) {
                    $totalEmployeeMessages++;
                } else {
                    $totalCustomerMessages++;
                }
            }

            if ($messageCount > 1) {
                $firstMessageTime = strtotime($responses[0]['date_add']);
                $lastMessageTime = strtotime(end($responses)['date_add']);
                $responseTime = $lastMessageTime - $firstMessageTime;
                $totalResponseTime += $responseTime;
            }
        }

        $averageResponseTime = $threadCount > 0 ? $totalResponseTime / $threadCount : 0;
        $messagesPerThread = $threadCount > 0 ? $totalMessages / $threadCount : 0;

        // Get unread threads
        $unreadThreads = (int) $db->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'customer_thread WHERE status = "Open" AND id_customer_thread NOT IN (SELECT id_customer_thread FROM ' . _DB_PREFIX_ . 'customer_message WHERE private = 1)'
        );

        // Prepare the statistics array
        $statistics = [
            'total_threads' => $totalThreads,
            'threads_pending' => $statusCounts['Pending1'] + $statusCounts['Pending2'],
            'total_customer_messages' => $totalCustomerMessages,
            'total_employee_messages' => $totalEmployeeMessages,
            'unread_threads' => $unreadThreads,
            'closed_threads' => $statusCounts['Closed'],
            'status_counts' => $statusCounts,
            'average_response_time' => round($averageResponseTime / 60, 2) . ' minutes',
            'messages_per_thread' => round($messagesPerThread, 2)
        ];

        return $statistics;
    }
}
