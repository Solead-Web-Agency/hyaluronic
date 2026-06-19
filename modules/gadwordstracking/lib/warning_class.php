<?php
/**
 * warnings_class.php file defines method for detecting warnings and display
 */

class BT_GactWarning
{
    /**
     * var $bStopExecution : defines if execution has to be stopped
     */
    public $bStopExecution = false;

    /**
     * var $sWarningMsg : stock warning message
     */
    public $sWarningMsg = '';

    /**
     * detect warnings and display them
     *
     * @param bool $bHtmlReturn
     */
    public function run($bHtmlReturn = false)
    {
        $this->sWarningMsg = '';

        $aCheck = array(
            'module' => array(
                'module' => 'gmerchantcenter',
                'stop' => false,
                'warning' => GAdwordsTracking::$oModule->l('Our Google Merchant Center module is not installed or not activated. For optimum results in your Dynamic Remarketing campaigns, you will want to associate it with our Google Merchant Center module', 'warning_class'),
            ),
        );

        foreach ($aCheck as $sType => $aWarning) {
            $bWarning = false;

            switch ($sType) {
                case 'module' :
                    if (!BT_GactModuleTools::isInstalled($aWarning['module'])) {
                        $bWarning = true;
                    }
                    break;
                case 'function' :
                    if (!function_exists($aWarning['function'])) {
                        $bWarning = true;
                    }
                    break;
                case 'callback' :
                    $mReturn = call_user_func_array($aWarning['callback'],
                        array($aWarning['parameters']));

                    if ($mReturn) {
                        $bWarning = true;
                    }
                    break;
                case 'permission' :
                    // use case - check file permission
                    if ($aWarning['type'] == 'file') {
                        if (!is_writable($aWarning['permission'])) {
                            $bWarning = true;
                        }
                    }
                    break;
                default:
                    $bWarning = false;
                    break;
            }

            if ($bWarning) {
                if ($aWarning['stop']) {
                    $this->bStopExecution = true;
                }
                if ($bHtmlReturn) {
                    $this->sWarningMsg .= '<div class="alert error">'
                        . $aWarning['warning'] . '</div>';
                } else {
                    $this->sWarningMsg .= $aWarning['warning'] . ' ';
                }
            }
        }
    }

    /**
     * manages singleton
     *
     * @return array
     */
    public static function create()
    {
        static $oWarning;

        if (null === $oWarning) {
            $oWarning = new BT_GactWarning();
        }

        return $oWarning;
    }
}
