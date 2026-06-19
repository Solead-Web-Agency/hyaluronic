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

class Compressor
{
    const ZIP = 1;
    const GZ = 2;
    const GZIP = 3;

    protected $settings = [];

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function getCompressorName($typeId)
    {
        $list = [
            self::ZIP => 'ZIP',
            self::GZ => 'GZ',
            self::GZIP => 'GZIP',
        ];

        return !empty($list[$typeId]) ? $list[$typeId] : '';
    }

    public function compress($xmlFileName = '', $fromString = '')
    {
        if (empty($this->settings['zip_file_name']) || empty($this->settings['compressor_type'])) {
            return false;
        }

        if (empty($xmlFileName) && empty($fromString)) {
            return false;
        }

        $methods = [
            self::ZIP => 'createZipFile',
            self::GZ => 'createGZipFile',
            self::GZIP => 'createGZipFile',
        ];

        $name = $methods[$this->settings['compressor_type']];

        if (empty($name)) {
            return false;
        }

        return $this->$name($xmlFileName, $fromString);
    }

    public function createGZipFile($xmlFileName = '', $fromString = '', $level = 9)
    {
        $filename = $this->settings['zip_file_name'].'.xml.'.($this->settings['compressor_type'] == self::GZIP ? 'gzip' : 'gz');
        $path = _PS_ROOT_DIR_.'/modules/xmlfeeds/xml_files/';
        $tempFileName = 'temp_blmod_rand_'.$this->settings['zip_file_name'].'.xml';

        if (!empty($fromString)) {
            $this->removeFile($path.$tempFileName);

            file_put_contents($path.$tempFileName, $fromString);
            $xmlFileName = $tempFileName;
        }

        $this->removeFile($path.$filename);

        if ($fp_out = gzopen($path.$filename, 'wb'.$level)) {
            if ($fp_in = fopen($path . $xmlFileName,'rb')) {
                while (!feof($fp_in)) {
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                }

                fclose($fp_in);
            } else {
                return false;
            }

            gzclose($fp_out);
        } else {
            return false;
        }

        $this->removeFile($path.$tempFileName);

        return true;
    }

    public function createZipFile($xmlFileName = '', $fromString = '')
    {
        $filename = $this->settings['zip_file_name'].'.zip';
        $path = _PS_ROOT_DIR_.'/modules/xmlfeeds/xml_files/';

        $this->removeFile($path.$filename);

        $zip = new ZipArchive();

        if ($zip->open($path.$filename, ZipArchive::CREATE ) === true) {
            if (empty($fromString)) {
                $zip->addFile($path.$xmlFileName, $this->settings['zip_file_name'].'.xml');
            } else {
                $zip->addFromString($this->settings['zip_file_name'].'.xml',  $fromString);
            }

            $zip->close();
        }
    }

    public function getExtensionByType($type)
    {
        $extensions = [
            self::ZIP => 'zip',
            self::GZ => 'gz',
            self::GZIP => 'gzip',
        ];

        return !empty($extensions[$type]) ? $extensions[$type] : '';
    }

    protected function removeFile($path)
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
