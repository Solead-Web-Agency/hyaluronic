<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
defined('_PS_VERSION_') or exit;

/**
 * Class EtsGeoIpCityDatabaseUpdater
 *
 * @since 1.1.7
 */
class EtsGeoIpCityDatabaseUpdater
{
    const MAXMIND_API_KEY = 'eUvNCKA9SwdtNdQj';
    /**
     * @var bool
     */
    private $is16;
    /**
     * @var string
     */
    private $downloadDir = _PS_CACHE_DIR_ . 'egl-tmp';
    /**
     * @var string
     */
    private $extractDir = _PS_GEOIP_DIR_;
    /**
     * @var self
     */
    private static $_instance;

    /**
     * EtsGeoIpCityDatabaseUpdater constructor.
     *
     * @param bool $is16
     */
    public function __construct($is16)
    {
        $this->is16 = $is16;
        if (!is_dir($this->downloadDir) && !mkdir($this->downloadDir, 0775) && !is_dir($this->downloadDir)) {
            throw new \RuntimeException(sprintf($this->l('Directory "%s" was not created. GeoLiteIpCity Updater can not working properly'), $this->downloadDir));
        }
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self(version_compare(_PS_VERSION_, '1.7', '<'));
        }

        return self::$_instance;
    }

    /**
     * @throws \ErrorException
     */
    public function runUpdate()
    {
        $sources = [
            'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=' . self::MAXMIND_API_KEY . '&suffix=tar.gz',
        ];
        $rs = false;
        foreach ($sources as $url) {
            $zipFileName = 'GeoLite2-City.tar.gz';
            if ($this->downloadFile($url, $zipFileName)) {
                $rs = $this->unzip($zipFileName, $this->extractDir);
            }
        }
        if (!$rs) {
            throw new \ErrorException($this->l('Download & extract database failed'));
        }
        if ($this->is16 && @!file_exists($this->extractDir . _PS_GEOIP_CITY_FILE_)) {
            $url = 'https://onedrive.live.com/download?cid=79CEADAC174D772A&resid=79CEADAC174D772A%21107&authkey=AEw5Uk9n2CAzMOg';
            $zipFileName = 'GeoLiteCity.dat.zip';
            $put = false;
            if ($this->downloadFile($url, $zipFileName)) {
                $put = $this->unzip($zipFileName, $this->extractDir);
            }
            if (!$put) {
                touch($this->extractDir . _PS_GEOIP_CITY_FILE_);
            }
        }

        return true;
    }

    /**
     * @param string $url
     * @param string $saveFileName
     *
     * @return bool|string
     *
     * @throws \ErrorException
     */
    private function downloadFile($url, $saveFileName)
    {
        $fileResource = fopen($this->downloadDir . DIRECTORY_SEPARATOR . $saveFileName, 'wb+');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1800);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FILE, $fileResource);
        $result = curl_exec($ch);
        if (!$result) {
            throw new \ErrorException(sprintf($this->l('Download failed. CURL error: %s'), curl_error($ch)));
        }
        fclose($fileResource);
        curl_close($ch);

        return $result;
    }

    /**
     * @param string $zipFile
     * @param string $targetDirectory
     *
     * @return bool
     *
     * @throws \ErrorException
     */
    private function unzip($zipFile, $targetDirectory)
    {
        $targetDirectory = rtrim($targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775) && !is_dir($targetDirectory)) {
            throw new \RuntimeException(sprintf($this->l('Target directory "%s" was not created. Cannot extract zip file'), $targetDirectory));
        }
        if (@!file_exists(realpath($zipFile)) && !($isUsingOnlyFileName = @file_exists($this->downloadDir . DIRECTORY_SEPARATOR . $zipFile))) {
            throw new \ErrorException($this->l('File does not exist'));
        }
        $zipFile = $isUsingOnlyFileName ? $this->downloadDir . DIRECTORY_SEPARATOR . $zipFile : realpath($zipFile);
        /** @var \PharData|\PharFileInfo[] $extractor */
        $extractor = new PharData($zipFile);
        foreach ($extractor as $item) {
            if ($item->isDir() && strpos($curPharDir = $item->getPathname(), 'GeoLite2-City_') !== false) {
                $childPhar = new PharData($curPharDir);
                foreach ($childPhar as $child) {
                    if ($child->isFile() && $child->getFilename() === 'GeoLite2-City.mmdb') {
                        return copy($child->getPathname(), $targetDirectory . 'GeoLite2-City.mmdb');
                    }
                }
            }
            if ($item->isFile() && $item->getFilename() === 'GeoLiteCity.dat') {
                return copy($item->getPathname(), $targetDirectory . $item->getFilename());
            }
        }
        throw new \ErrorException($this->l('Unable to locate GeoLite2-City.mmdb in archive file'));
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private function l($str)
    {
        $module = Module::getInstanceByName('ets_geolocation');
        if ($module) {
            return $module->l($str, 'db-updater');
        }

        return '';
    }
}
