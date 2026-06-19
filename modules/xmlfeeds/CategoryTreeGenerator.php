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

class CategoryTreeGenerator
{
    private $htmlCategory = '';
    private $recurseDone = [];
    private $isGoogle = false;
    private $isGender = false;
    private $genderValues = [];

    protected $context;
    protected $shotLang = 0;
    protected $moduleImgPath = '';
    protected $googleCategoriesMap = [];
    protected $feedId = 0;

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @param int $shotLang
     */
    public function setShotLang($shotLang)
    {
        $this->shotLang = $shotLang;
    }

    /**
     * @param string $moduleImgPath
     */
    public function setModuleImgPath($moduleImgPath)
    {
        $this->moduleImgPath = $moduleImgPath;
    }

    /**
     * @param array $googleCategoriesMap
     */
    public function setGoogleCategoriesMap($googleCategoriesMap)
    {
        $this->googleCategoriesMap = $googleCategoriesMap;
    }

    /**
     * @param int $feedId
     */
    public function setFeedId($feedId)
    {
        $this->feedId = $feedId;
    }

    public function save($feedId, $genderCategories)
    {
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_gender_map WHERE feed_id = '.(int)$feedId);

        if (empty($genderCategories)) {
            return true;
        }

        foreach ($genderCategories as $cateogyrId => $name) {
            if (empty($name)) {
                continue;
            }

            Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'blmod_xml_gender_map
                (`feed_id`, `category_id`, `name`)
                VALUE
                ("'.(int)$feedId.'", "'.(int)$cateogyrId.'", "'.pSQL($name).'")');
        }

        return true;
    }

    public function get($feedId)
    {
        $categories = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'blmod_xml_gender_map WHERE feed_id = '.(int)$feedId);
        $values = [];

        foreach ($categories as $c) {
            $values[$c['category_id']] = $c['name'];
        }

        return $values;
    }

    public function categoriesTree($selected = false, $isGoogleCat = false, $checkboxName = 'categoryBox', $isGender = false)
    {
        $this->isGoogle = $isGoogleCat;
        $this->isGender = $isGender;
        $this->recurseDone = [];
        $langId = !empty($this->shopLang) ? $this->shopLang : $this->context->language->id;

        if (!empty($selected)) {
            $sel_array = explode(',', $selected);
        } else {
            $sel_array = [];
        }

        $margin = 'margin-right: 9px;';
        $border = '';
        $checkDelBoxes = "checkDelBoxes(this.form, '".$checkboxName."[]', this.checked)";
        $hideMessage = '<div class="categories_list_button" style="cursor: pointer; color: #268CCD; text-align: left; margin-top: 10px;">'.$this->l('[Hide]').'</div>';
        $selectBox = '<th><input type="checkbox" name="checkme" class="noborder" onclick="'.$checkDelBoxes.'"></th>';

        $this->htmlCategory = '';

        if ($this->isGoogle || $this->isGender) {
            $margin = '';
            $border = 'border: 0px; margin-top: 6px;';
            $hideMessage = '';
            $selectBox = '';
        }

        if ($this->isGender) {
            $this->genderValues = $this->get($this->feedId);
        }

        $this->htmlCategory .= '<div style = "'.$margin.'">
				<table cellspacing="0" cellpadding="0" class="table blmod-table-light table-no-space" id="radio_div" style="'.$border.'">
					<tr>
						'.$selectBox.'
						<th>'.$this->l('ID').'</th>
						<th style="width: 400px">'.$this->l('Name').'</th>
					</tr>';

        $categories = Category::getCategories($langId, false);

        if (!empty($categories)) {
            $categories[0][1] = isset($categories[0][1]) ? $categories[0][1] : false;
            $this->recurseCategoryForIncludePref(null, $categories, $categories[0][1], 1, null, $sel_array, $checkboxName);
        }

        $this->htmlCategory .= '</table>
				'.$hideMessage.'
			</div>';

        return $this->htmlCategory;
    }

    protected function recurseCategoryForIncludePref($indexedCategories, $categories, $current, $id_category = 1, $id_category_default = null, $selected = array(), $checkboxName = 'categoryBox')
    {
        $img_type = 'png';

        static $irow;

        if (!isset($this->recurseDone[$current['infos']['id_parent']])) {
            $this->recurseDone[$current['infos']['id_parent']] = 0;
        }

        $this->recurseDone[$current['infos']['id_parent']] += 1;

        $categories[$current['infos']['id_parent']] = isset($categories[$current['infos']['id_parent']]) ? $categories[$current['infos']['id_parent']] : false;

        $todo = count($categories[$current['infos']['id_parent']]);
        $doneC = $this->recurseDone[$current['infos']['id_parent']];

        $level = $current['infos']['level_depth'] + 1;
        $img = $level == 1 ? 'lv1.'.$img_type : 'lv'.$level.'_'.($todo == $doneC ? 'f' : 'b').'.'.$img_type;
        $levelImg = '<img src="'.$this->moduleImgPath.''.$img.'" alt="" />';

        if ($level > 5) {
            $levelSpace = (($level - 2) * 24) - 12;
            $levelImg = '<div class="category-level" style="width: '.$levelSpace.'px;"><br></div>';
            $levelImg .= '<div class="category-level-'.($todo == $doneC ? 'f' : 'b').'"><br></div>';
        }

        $checked = false;

        if (in_array($id_category, $selected)) {
            $checked = 'checked="yes"';
        }

        $selectBox = '<td class="center">
				<input type="checkbox" id="'.$checkboxName.'_'.$id_category.'" name="'.$checkboxName.'[]" '.$checked.' value="'.$id_category.'" class="noborder">
			</td>';

        $selectBoxW = '';
        $inputField = '';

        if ($this->isGoogle) {
            $selectBox = '';
            $selectBoxW = 'width: 25px;';

            $googleCatValue = '';

            if (!empty($this->googleCategoriesMap[$id_category])) {
                $googleCatValue = $this->googleCategoriesMap[$id_category]['name'];
            }

            $inputField = '
                <div><input type="text" placeholder="'.$this->l('Enter category name').'" id="google_cat_map_'.$id_category.'" class="google_cat_map_blmod" name="google_cat_map['.$id_category.']" value="'.$googleCatValue.'"></div>
            <div style="clear: both;"></div>';
        }

        if ($this->isGender) {
            $selectBox = '';
            $selectBoxW = 'width: 25px;';

            $inputField = '
                <div><input type="text" placeholder="'.$this->l('Enter gender').'" name="gender_category['.$id_category.']" value="'.(!empty($this->genderValues[$id_category]) ? htmlspecialchars($this->genderValues[$id_category], ENT_QUOTES) : '').'"></div>
            <div style="clear: both;"></div>';
        }

        $this->htmlCategory .= '<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
			'.$selectBox.'
			<td style="'.$selectBoxW.'">
				'.$id_category.'
			</td>
			<td>
				<div style="float: left;">'.$levelImg.'</div>
				<div style="float: left;">
				    <label style="line-height: 26px;" for="'.$checkboxName.'_'.$id_category.'" class="t">'.Tools::stripslashes($current['infos']['name']).'</label>
				</div>
                <div style="clear: both;"></div>
				'.$inputField.'
			</td>
		</tr>';

        if (isset($categories[$id_category])) {
            foreach ($categories[$id_category] as $key => $row) {
                if ($key != 'infos') {
                    $this->recurseCategoryForIncludePref($indexedCategories, $categories, $categories[$id_category][$key], $key, null, $selected, $checkboxName);
                }
            }
        }
    }

    protected function l($string)
    {
        return $string;
    }
}
