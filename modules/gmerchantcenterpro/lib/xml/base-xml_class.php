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

abstract class BT_BaseXml
{
    /**
     * @var string $sContent : store the XML content
     */
    public $sContent = '';

    /**
     * @var string $sSep : define the separator
     */
    public $sSep = "\n";

    /**
     * @var string $sTab : define the tabulation
     */
    public $sTab = "\t";

    /**
     * @var string $sDblSep : define the double separator
     */
    public $sDblSep = "\n\n";

    /**
     * @var string $sFileName : store file name
     */
    public $sFileName = '';

    /**
     * @var obj $oFile : store the file object
     */
    protected $oFile = null;

    /**
     * @var bool $bOutput : define if we display directly the content
     */
    protected $bOutput = null;


    /**
     * @param array $aParams
     */
    abstract public function __construct($aParams = array());

    /**
     * set the XML header
     *
     * @param array $aParams
     * @return bool
     */
    public function header(array $aParams = null)
    {
        // get meta
        $aMeta = Meta::getMetaByPage('index', (int) $aParams['iLangId']);

        $sContent = ''
            . '<?xml version="1.0" encoding="UTF-8"?>' . $this->sSep
            . '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . $this->sSep
            . '<channel>' . $this->sSep
            . "\t" . '<title><![CDATA[' . Tools::stripslashes(Configuration::get('PS_SHOP_NAME')) . ']]></title>' . $this->sSep
            . "\t" . '<description><![CDATA[' . Tools::stripslashes($aMeta['description']) . ']]></description>' . $this->sSep
            . "\t" . '<link>' . GMerchantCenterPro::$conf['GMCP_LINK'] . '</link>' . $this->sSep;

        if (
            !empty($this->bOutput)
            || !empty($aParams['bOutput'])
        ) {
            echo $sContent;
        } else {
            $this->sContent .= $sContent;
        }

        return true;
    }

    /**
     * set the XML footer
     *
     * @param array $aParams
     * @return bool
     */
    public function footer($aParams = null)
    {
        $sContent = ''
            . '</channel>' . $this->sSep
            . '</rss>';

        if (
            !empty($this->bOutput)
            || !empty($aParams['bOutput'])
        ) {
            echo $sContent;
        } else {
            $this->sContent .= $sContent;
        }

        return true;
    }

    /**
     * set the File obj
     *
     * @param obj $oFile
     * @return array
     */
    public function setFile($oFile)
    {
        $this->oFile = $oFile;
    }

    /**
     * write the XML file content
     *
     * @param string $sFileName
     * @param string $sContent
     * @param bool $bVerbose - display comments
     * @param bool $bAdd - adding data
     * @param bool $bStripTag - strip all HTML tags
     * @return bool
     */
    public function write($sFileName, $sContent, $bVerbose = false, $bAdd = false, $bStripTag = false)
    {
        if (empty($this->bOutput)) {
            $this->oFile->write($sFileName, $sContent, $bVerbose, $bAdd, $bStripTag);
        }
        return true;
    }

    /**
     * delete XML file
     *
     * @param string $sFileName
     * @return bool
     */
    public function delete($sFileName)
    {
        return is_file($sFileName) && $this->oFile->delete($sFileName) ? true : false;
    }
}
