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

class FilterByFeature
{
    public function isExists($feedSettings, $productFeatures)
    {
        if (empty($feedSettings['only_with_features_status'])) {
            return true;
        }

        if (empty($feedSettings['only_with_features'])) {
            return false;
        }

        $list = $this->getIntersect($feedSettings['only_with_features'], $productFeatures);

        return !empty($list);
    }

    public function isNotExists($feedSettings, $productFeatures)
    {
        if (empty($feedSettings['only_without_features_status'])) {
            return true;
        }

        if (empty($feedSettings['only_without_features'])) {
            return true;
        }

        $list = $this->getIntersect($feedSettings['only_without_features'], $productFeatures);

        return empty($list);
    }

    protected function getIntersect($validFeatures, $productFeatures)
    {
        return array_intersect(explode(',', $validFeatures), $productFeatures);
    }
}
