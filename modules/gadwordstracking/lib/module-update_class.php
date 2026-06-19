<?php
/**
 * module-update_class.php file defines method for updating the module
 */

class BT_GactModuleUpdate
{
    /**
     * @var $aErrors : store errors
     */
    public $aErrors = array();

    /**
     * execute required function
     *
     * @param $aParam
     */
    public function run(array $aParam = null)
    {
        // get type
        $aParam['sType'] = empty($aParam['sType']) ? 'tables' : $aParam['sType'];

        switch ($aParam['sType']) {
            case 'tables' : // use case - update tables
            case 'fields' : // use case - update fields
            case 'hooks' : // use case - update hooks
            case 'templates' : // use case - update templates
                // execute match function
                call_user_func_array(array($this, 'update' . ucfirst($aParam['sType'])), array($aParam));
                break;
            default :
                break;
        }
    }


    /**
     * update tables if required
     *
     * @param array $aParam
     */
    private function updateTables(array $aParam)
    {
        // set transaction
        Db::getInstance()->Execute('BEGIN');

        if (!empty($GLOBALS['GACT_SQL_UPDATE']['table'])) {
            // loop on each elt to update SQL
            foreach ($GLOBALS['GACT_SQL_UPDATE']['table'] as $sTable => $sSqlFile) {
                // execute query
                $bResult = Db::getInstance()->ExecuteS('SHOW TABLES LIKE "' . _DB_PREFIX_ . strtolower(_GACT_MODULE_NAME) . '_' . $sTable . '"');

                // if empty - update
                if (empty($bResult)) {
                    require_once(_GACT_PATH_CONF . 'install.conf.php');
                    require_once(_GACT_PATH_LIB_INSTALL . 'install-ctrl_class.php');

                    // use case - KO update
                    if (!BT_InstallCtrl::run('install', 'sql', _GACT_PATH_SQL . $sSqlFile)) {
                        $this->aErrors[] = array(
                            'table' => $sTable,
                            'file'  => $sSqlFile,
                        );
                    }
                }
            }
        }

        if (empty($this->aErrors)) {
            Db::getInstance()->Execute('COMMIT');
        } else {
            Db::getInstance()->Execute('ROLLBACK');
        }
    }


    /**
     * update fields if required
     *
     * @param array $aParam
     */
    private function updateFields(array $aParam)
    {
        // set transaction
        Db::getInstance()->Execute('BEGIN');

        if (!empty($GLOBALS['GACT_SQL_UPDATE']['field'])) {
            // loop on each elt to update SQL
            foreach ($GLOBALS['GACT_SQL_UPDATE']['field'] as $sFieldName => $aOption) {
                // execute query
                $bResult = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM ' . _DB_PREFIX_ . strtolower(_GACT_MODULE_NAME) . '_' . $aOption['table'] . ' LIKE "' . $sFieldName . '"');

                // if empty - update
                if (empty($bResult)) {
                    require_once(_GACT_PATH_CONF . 'install.conf.php');
                    require_once(_GACT_PATH_LIB_INSTALL . 'install-ctrl_class.php');

                    // use case - KO update
                    if (!BT_InstallCtrl::run('install', 'sql', _GACT_PATH_SQL . $aOption['file'])) {
                        $aErrors[] = array(
                            'field'  => $sFieldName,
                            'linked' => $aOption['table'],
                            'file'   => $aOption['file'],
                        );
                    }
                }
            }
        }

        if (empty($this->aErrors)) {
            Db::getInstance()->Execute('COMMIT');
        } else {
            Db::getInstance()->Execute('ROLLBACK');
        }
    }

    /**
     * update hooks if required
     *
     * @param array $aParam
     */
    private function updateHooks(array $aParam)
    {
        require_once(_GACT_PATH_CONF . 'install.conf.php');
        require_once(_GACT_PATH_LIB_INSTALL . 'install-ctrl_class.php');

        // use case - hook register ko
        if (!BT_InstallCtrl::run('install', 'config', array('bHookOnly' => true))) {
            $this->aErrors[] = array(
                'table' => 'ps_hook_module',
                'file'  => GAdwordsTracking::$oModule->l('register hooks KO'),
            );
        }
    }


    /**
     * update templates if required
     *
     * @param array $aParam
     */
    private function updateTemplates(array $aParam)
    {
        require_once(_GACT_PATH_LIB_COMMON . 'dir-reader.class.php');

        // get templates files
        $aTplFiles = BT_DirReader::create()->run(array(
            'path'      => _GACT_PATH_TPL,
            'recursive' => true,
            'extension' => 'tpl',
            'subpath'   => true,
        ));

        if (!empty($aTplFiles)) {
            global $smarty;

            if (method_exists($smarty, 'clearCompiledTemplate')) {
                $smarty->clearCompiledTemplate();
            } elseif (method_exists($smarty, 'clear_compiled_tpl')) {
                foreach ($aTplFiles as $aFile) {
                    $smarty->clear_compiled_tpl($aFile['filename']);
                }
            }
        }
    }


    /**
     * returns errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->aErrors;
    }

    /**
     * manages singleton
     *
     * @return array
     */
    public static function create()
    {
        static $oModuleUpdate;

        if (null === $oModuleUpdate) {
            $oModuleUpdate = new BT_GactModuleUpdate();
        }

        return $oModuleUpdate;
    }
}
