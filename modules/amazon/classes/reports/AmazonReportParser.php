<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }

class AmazonReportParser
{
    protected $filePath;
    protected $debug = array();

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function getDebug()
    {
        return $this->debug;
    }

    public function parseReport()
    {
        // 1. Validation
        $lines = $this->processReportExplodeLines();
        if (count($lines) < 1) {
            $this->ed('Nothing to do!');
            return array();
        }

        // 2. Extract header
        $headerLine = array_shift($lines);
        $headerNamedKey = $this->processReportExtractHeader($headerLine);
        if (count($headerNamedKey) < 1) {
            $this->ed('Ignore file because of header!');
            return array();
        }

        // 3. Extract body
        return $this->processReportExtractBody($lines, $headerNamedKey);
    }

    protected function processReportExplodeLines()
    {
        $this->ed('Processing report file');

        $reportData = AmazonTools::fileGetContents($this->filePath);
        if (!$reportData) {
            $this->ed(sprintf('Unable to read input file! (%s)', $this->filePath));
            return array();
        }

        if (empty($reportData)) {
            $this->ed('Report file is empty!');
            return array();
        }

        $lines = explode(Amazon::LF, $reportData);
        if (!is_array($lines) || !count($lines)) {
            $this->ed('Report file with no line!');
            return array();
        }

        $this->ed(sprintf('Report has %d line', count($lines)), true);

        return $lines;
    }

    /**
     * @param $headerLine
     * @return array|int[]|string[] array(column_name => index_in_file)
     */
    protected function processReportExtractHeader($headerLine)
    {
        if (empty($headerLine)) {
            $this->ed('Report header is empty');
            return array();
        }

        $headerColumns = $this->explodeReportLine($headerLine, "\t", true);

        return array_flip($headerColumns);
    }

    protected function explodeReportLine($line, $separate, $ignoreInvalidCharacter = false)
    {
        if ($ignoreInvalidCharacter) { //Remove invalid UTF-8 character
            $line = iconv("UTF-8", "UTF-8//IGNORE", $line);
        }
        $lineExplode = explode($separate, $line);

        // Some column is enclose inside "", trim them
        return array_map(function ($element) {
            return trim($element, " \t\n\r\0\x0B\"");
        }, $lineExplode);
    }

    protected function processReportExtractBody($body, $headerNamedKey)
    {
        $reportRows = array();

        foreach ($body as $line) {
            if (empty($line)) {
                continue;
            }

            $lineData = $this->explodeReportLine($line, "\t");
            $reportRowFirstParse = array();
            foreach ($headerNamedKey as $headerName => $headerIndex) {
                $reportRowFirstParse[$headerName] = isset($lineData[$headerIndex]) ? $lineData[$headerIndex] : '';
            }

            $reportRows[] = $reportRowFirstParse;
        }

        return $reportRows;
    }

    protected function ed()
    {
        $this->debug[] = Amazon::dbt(func_get_args());
    }
}
