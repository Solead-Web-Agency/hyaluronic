<?php

namespace Vivawallet\VivawalletPhp\Log;

use Vivawallet\VivawalletPhp\Api\DiagnosticsClient;
use Vivawallet\VivawalletPhp\Http\Authentication\Authentication;
use Vivawallet\VivawalletPhp\Http\Response;

class DiagnosticsLogger implements Logger
{
    private $diagnosticsClient;

    public function __construct(Authentication $authentication, $config = [])
    {
        $this->diagnosticsClient = new DiagnosticsClient($authentication, $config);
    }

    public function log(array $logs): Response
    {
        return $this->diagnosticsClient->sendLogs($logs);
    }
}
