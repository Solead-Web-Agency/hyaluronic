<?php
/**
 * A Route Prestashop Extension that adds secure shipping
 * protection to your orders
 *
 * Php version 7.0^
 *
 * @author    Route Development Team <dev@routeapp.io>
 * @copyright 2019 Route App Inc. Copyright (c) https://www.routeapp.io/
 * @license   https://www.routeapp.io/merchant-terms-of-use  Proprietary License
 */

class RouteSentry
{
    const SENTRY_KEY = '07c4b78bc3d6409694ba9afbbd9ba8d7';
    const SENTRY_PROJECT_ID = '5287081';
    const SENTRY_LOG_CONTEXT_LINES = 10;

    protected $eventType = '';
    protected $eventMessage = '';
    protected $eventTrace = [];
    private $extraData = [];
    protected $requiredLogLevel = ['error', 'exception'];

    /**
     * Send log event to Sentry
     *
     * @param $eventType
     * @param $eventMessage
     * @param $eventTrace
     * @param $extraData
     *
     * @return bool|string
     */
    public function send($eventType, $eventMessage, $eventTrace, $extraData = [])
    {
        $this->eventType = $eventType;
        $this->eventMessage = $eventMessage;
        $this->eventTrace = $eventTrace;
        $this->extraData = $extraData;

        return $this->apiRequest('POST', $this->getSentryApiUrl(), $this->prepareEvent());
    }

    public static function track($eventType, $eventMessage, $eventTrace, $extraData = [])
    {
        $sentry = new self();
        $sentry->eventType = $eventType;
        $sentry->eventMessage = $eventMessage;
        $sentry->eventTrace = $eventTrace;
        $sentry->extraData = $extraData;

        return $sentry->apiRequest('POST', $sentry->getSentryApiUrl(), $sentry->prepareEvent());
    }

    /**
     * Send logs according to log level
     *
     * @param $eventType
     *
     * @return bool
     */
    private function canBeSent($eventType)
    {
        return in_array($eventType, $this->requiredLogLevel);
    }

    /**
     * Prepare event in array
     *
     * @return array
     */
    private function prepareEvent()
    {
        $eventArray = [];
        $eventArray['message'] = (string) $this->eventMessage;
        $eventArray['platform'] = 'php';
        $eventArray['type'] = (string) $this->eventMessage;
        $eventArray['tags'] = [
            ['module', 'Route Module'],
            ['module.version', (string) Module::getInstanceByName('route')->version],
            ['url', Tools::getShopDomain()],
            ['php.version', phpversion()],
            ['report.level', Tools::strtolower($this->eventType)],
            ['http.server', isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : ''],
            ['prestashop.version', AppKernel::VERSION],
        ];

        $eventArray['exception'] = [
            'values' => [
                [
                    'type' => Tools::ucfirst($eventArray['type']),
                    'module' => 'Route Pretashop Integration',
                    'value' => $eventArray['message'],
                    'stacktrace' => [
                        'frames' => $this->prepareStackTrace(),
                    ],
                ],
            ],
        ];

        if (!empty($this->extraData)) {
            $eventArray['extra']['extraData'] = $this->extraData;
        }

        return $eventArray;
    }

    /**
     * Receive error or exception traces, return formatted array for Sentry
     *
     * @return array
     */
    private function prepareStackTrace()
    {
        $tracesArray = [];
        foreach (array_reverse($this->eventTrace) as $trace) {
            if ($this->isValidTrace($trace)) {
                $traceArray = [];
                $traceArray['filename'] = $trace['file'];
                $traceArray['abs_path'] = $trace['file'];
                $traceArray['lineno'] = (int) $trace['line'];
                $traceArray['function'] = $trace['function'];
                $traceArray['context_line'] = $trace['function'];
                $traceArray['pre_context'][0] = '';
                $traceArray['post_context'][0] = '';

                $sourceCodeExcerpt = $this->getSourceCodeExcerpt(
                    $traceArray['filename'],
                    $traceArray['lineno'],
                    self::SENTRY_LOG_CONTEXT_LINES
                );

                if (isset($sourceCodeExcerpt['context_line'])) {
                    $traceArray['context_line'] = $sourceCodeExcerpt['context_line'];
                }

                if (isset($sourceCodeExcerpt['pre_context'])) {
                    $traceArray['pre_context'] = $sourceCodeExcerpt['pre_context'];
                }
                if (isset($sourceCodeExcerpt['post_context'])) {
                    $traceArray['post_context'] = $sourceCodeExcerpt['post_context'];
                }
                $traceArray['in_app'] = false;
                array_push($tracesArray, $traceArray);
            }
        }

        return $tracesArray;
    }

    /**
     * Check if is a valid trace
     *
     * @param $trace
     *
     * @return bool
     */
    private function isValidTrace($trace)
    {
        return !empty($trace['file']) && !empty($trace['line']) && !empty($trace['function']);
    }

    /**
     * Gets an excerpt of the source code around a given line.
     *
     * @param $path            string The file path
     * @param $lineNumber      int The line to centre about
     * @param $maxLinesToFetch int The maximum number of lines to fetch
     */
    protected function getSourceCodeExcerpt($path, $lineNumber, $maxLinesToFetch)
    {
        if (!is_readable($path) || !is_file($path)) {
            return [];
        }

        $frame = [
            'pre_context' => [],
            'context_line' => '',
            'post_context' => [],
        ];

        $target = max(0, ($lineNumber - ($maxLinesToFetch + 1)));
        $currentLineNumber = $target + 1;

        try {
            $file = new \SplFileObject($path);
            $file->seek($target);

            while (!$file->eof()) {
                /** @var string $line */
                $line = $file->current();
                $line = rtrim($line, "\r\n");

                if ($currentLineNumber == $lineNumber) {
                    $frame['context_line'] = $line;
                } elseif ($currentLineNumber < $lineNumber) {
                    $frame['pre_context'][] = $line;
                } elseif ($currentLineNumber > $lineNumber) {
                    $frame['post_context'][] = $line;
                }

                ++$currentLineNumber;

                if ($currentLineNumber > $lineNumber + $maxLinesToFetch) {
                    break;
                }

                $file->next();
            }
        } catch (\Exception $exception) {
            // Do nothing, if any error occurs while trying to get the excerpts
            // it's not a drama
        }

        return $frame;
    }

    /**
     * Get Sentry Auth
     *
     * @return string
     */
    private function getSentryAuth()
    {
        $sentryAuth = 'Sentry sentry_version=7, sentry_key=';
        $sentryAuth .= self::SENTRY_KEY;
        $sentryAuth .= ', sentry_client=raven-bash/0.1';

        return $sentryAuth;
    }

    /**
     * Get Sentry API endpoint
     *
     * @return string
     */
    private function getSentryApiUrl()
    {
        return 'https://sentry.io/api/' . self::SENTRY_PROJECT_ID . '/store/';
    }

    /**
     * Create a CURL Request to Route API endpoint
     *
     * @param string $method GET|POST|PUT|DELETE
     * @param string $path URL path
     * @param array $params Object of params to be requested or sent
     */
    public function apiRequest($method, $url, $params = null)
    {
        $curl = curl_init();

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Sentry-Auth',
            'X-Sentry-Auth: ' . $this->getSentryAuth(),
        ];

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);

            if (isset($params)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
            }
        } elseif ($method === 'GET' && isset($params)) {
            $path = $path . '?' . http_build_query($params);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return [
            'body' => json_decode($response, true),
            'status_code' => $status_code,
        ];
    }
}
