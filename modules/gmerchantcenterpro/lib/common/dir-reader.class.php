<?php

/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

final class BT_GmcProDirReader implements Iterator
{
    /**
     * @var object $obj : stock obj
     */
    public static $obj = null;

    /**
     * @var array $aFiles : get all match files
     */
    private $aFiles = array();

    /**
     * @var array $iPosition : set pointer position
     */
    private $iPosition = 0;

    /**
     * load specified directory
     *
     * @param array params
     * @return array
     */
    public function run(array $aParams)
    {
        // test of obligatory validated path
        if (
            isset($aParams['path'])
            && is_dir($aParams['path'])
            && (isset($aParams['pattern'])
                || isset($aParams['extension']))
        ) {
            // init object
            $oDirRecIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($aParams['path']));

            // case of not recursive
            if (
                isset($aParams['recursive']) === false
                || (isset($aParams['recursive']) === true
                    && $aParams['recursive'] === false)
            ) {
                $oDirRecIterator->setMaxDepth(1);
            }
            // clear array
            $this->aFiles = array();

            // rewind
            $this->rewind();

            $iCount = 0;

            // loop on object result
            while ($oDirRecIterator->valid()) {
                if ($oDirRecIterator->isDot() === false) {
                    // get file name
                    $sFileName = $oDirRecIterator->getFilename();

                    if ((isset($aParams['pattern']) && preg_match($aParams['pattern'], $sFileName))
                        || (isset($aParams['extension'])
                            && substr(strtolower($sFileName), strrpos($sFileName, '.') + 1) == $aParams['extension'])
                    ) {
                        $this->aFiles[$iCount]['path'] = $oDirRecIterator->key();
                        $this->aFiles[$iCount]['filename'] = $sFileName;

                        // case of subpath
                        if (isset($aParams['subpath']) && $aParams['subpath']) {
                            $this->aFiles[$iCount]['subpath'] = $oDirRecIterator->getSubPath();
                        }
                        // case of subpathname
                        if (isset($aParams['subpathname']) && $aParams['subpathname']) {
                            $this->aFiles[$iCount]['subpathname'] = $oDirRecIterator->getSubPathname();
                        }
                        // case of size
                        if (isset($aParams['size']) && $aParams['size']) {
                            $this->aFiles[$iCount]['size'] = $oDirRecIterator->getSize();
                        }
                        // case of type
                        if (isset($aParams['type']) && $aParams['type']) {
                            $this->aFiles[$iCount]['type'] = $oDirRecIterator->getType();
                        }
                        // case of owner
                        if (isset($aParams['owner']) && $aParams['owner']) {
                            $this->aFiles[$iCount]['owner'] = $oDirRecIterator->getOwner();
                        }
                        // case of group
                        if (isset($aParams['group']) && $aParams['group']) {
                            $this->aFiles[$iCount]['group'] = $oDirRecIterator->getGroup();
                        }
                        // case of time
                        if (isset($aParams['time']) && $aParams['time']) {
                            $this->aFiles[$iCount]['time'] = $oDirRecIterator->getCTime();
                        }
                        // case of verbose
                        if (isset($aParams['verbose']) && $aParams['verbose']) {
                            echo '[ ', (isset($aParams['service']) ? $aParams['service'] : 'FILE'), ' ] ', date("d-m-Y à H:i:s"), ' =>  matched file : ', $sFileName, "\n";
                        }
                        ++$iCount;
                    }
                }
                $oDirRecIterator->next();
            }
            // return
            return $this->aFiles;
        } else {
            // throw exception if specified directory is not declared
            throw new Exception('Specified path or extension or pattern are not declared or is not a valid path');
        }
    }

    /**
     * goes to position 0
     *
     */
    public function rewind()
    {
        $this->iPosition = 0;
    }

    /**
     * returns current object
     *
     * @return mixed
     */
    public function current()
    {
        if (!isset($this->aFiles[$this->iPosition])) {
            return null;
        } else {
            return $this->aFiles[$this->iPosition];
        }
    }

    /**
     * returns current position
     *
     * @return int
     */
    public function key()
    {
        return $this->iPosition;
    }

    /**
     * goes to next position
     */
    public function next()
    {
        ++$this->iPosition;
    }

    /**
     * tests if current position exist
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->aFiles[$this->iPosition]);
    }

    /**
     * create directories recursively
     *
     * @param string $sPath
     * @return mixed
     */
    public static function mkdirRec($sPath)
    {
        if (!empty($sPath) && !is_dir($sPath)) {
            if (self::mkdirRec(dirname($sPath))) {
                return mkdir($sPath);
            }
        } else {
            return true;
        }
    }

    /**
     * set singleton
     *
     * @return  obj self::$obj
     */
    public static function create()
    {
        if (self::$obj === null) {
            self::$obj = new BT_GmcProDirReader();
        }
        return self::$obj;
    }
}
