<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevAtos
 * Support by mail : contact@scaledev.fr
 */

namespace ScaleDEV\SdevAtos;

use ScaleDEV\SdevAtos\SdevAtosModel;
use ScaleDEV\SdevAtos\SdevModule;

require_once(dirname(__FILE__).'../../autoload.php');

class SdevAtosForm extends SdevAtosModel
{
    const _FORM_GROUP_ = 'form_group';
    const _FORM_GROUP_ATTR_ = 'form_group_attr';
    const _LABEL_ = 'label';
    const _LABEL_ATTR_ = 'label_attr';
    const _MARGIN_FORM_ATTR_ = 'margin_form_attr';
    const _PREFIX_LEFT_ = 'prefix_left';
    const _PREFIX_RIGHT_ = 'prefix_right';
    const _FIELD_ = 'field';
    const _ATTR_ = 'attr';
    const _HELPER_ = 'helper';
    const _HELPER_ATTR_ = 'helper_attr';

    private $smarty_vars = array();

    /**
     * Path to the form builder's templates.
     *
     * @var string $path
     */
    private $path = '/views/templates/hooks/formbuilder/';

    /**
     * Creates an input type button.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function button($properties = array())
    {
        return $this->display('button', $properties);
    }

    /**
     * Creates an input type checkbox.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * - - (mixed)options: An array of options.
     * - - (mixed)options_disabled: An array of options disabled.
     * @return void
     */
    public function checkbox($properties = array())
    {
        $options = array();
        $options_disabled = array();
        $isInline = false;

        if (is_array($properties) && !empty($properties)) {
            if (array_key_exists('options', $properties)) {
                $options = (array)$properties['options'];
            }
            if (array_key_exists('options_disabled', $properties)) {
                $options_disabled = (array)$properties['options_disabled'];
            }
            if (array_key_exists('inline', $properties)) {
                $isInline = (bool)$properties['inline'];
            }
        }

        $this->smarty_vars['options'] = (array)$options;
        $this->smarty_vars['options_disabled'] = (array)$options_disabled;
        $this->smarty_vars['isInline'] = (bool)$isInline;
        return $this->display('checkbox', $properties);
    }

    /**
     * Creates an input type color.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function color($properties = array())
    {
        return $this->display('color', $properties);
    }

    /**
     * Creates an input type date.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function date($properties = array())
    {
        return $this->display('date', $properties);
    }

    /**
     * Creates an input type datetime-local.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function datetimeLocal($properties = array())
    {
        return $this->display('datetime-local', $properties);
    }

    /**
     * Creates an input type email.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function email($properties = array())
    {
        return $this->display('email', $properties);
    }

    /**
     * Creates an input type file.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function file($properties = array())
    {
        return $this->display('file', $properties);
    }

    /**
     * Creates an input type hidden.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function hidden($properties = array())
    {
        return $this->display('hidden', $properties);
    }

    /**
     * Creates an input type image.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function image($properties = array())
    {
        return $this->display('image', $properties);
    }

    /**
     * Create a mapping.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * - - (array)mapping: Set mapping.
     * - - (mixed)values_original: Set original value(s) to mapped by a string or an array.
     * - - (mixed)values_mapped: Set mapped value(s) by a string or an array.
     * - A string is used to set an input text instead of an array is used to set a select.
     * @return void
     */
    public function mapping($properties = array())
    {
        $mapping = array();

        // @todo: Trouver un moyen de ne pas répéter le code du template 2 fois.
        // $values_original = array();
        // $values_mapped   = array();

        if (is_array($properties)
            && !empty($properties)
            && array_key_exists('mapping', $properties)
            && is_array($properties['mapping'])
            && !empty($properties['mapping'])
        ) {
            $mapping = (array)$properties['mapping'];
        }

        $this->smarty_vars['mapping'] = (array)$mapping;
        return $this->display('mapping', $properties);
    }

    /**
     * Creates an input type month.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function month($properties = array())
    {
        return $this->display('month', $properties);
    }

    /**
     * Creates an input type number.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function number($properties = array())
    {
        return $this->display('number', $properties);
    }

    /**
     * Creates an input type password.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function password($properties = array())
    {
        return $this->display('password', $properties);
    }

    /**
     * Creates an input type radio.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * - - (mixed)options: An array of options.
     * - - (mixed)options_disabled: An array of options disabled.
     * @return void
     */
    public function radio($properties = array())
    {
        $default_option = false;
        $options = array();
        $options_disabled = array();

        if (is_array($properties) && !empty($properties)) {
            if (array_key_exists('default_option', $properties)) {
                $default_option = is_array($properties['default_option'])
                    ? (array)$properties['default_option']
                    : (string)$properties['default_option'];
            }
            if (array_key_exists('options', $properties)) {
                $options = (array)$properties['options'];
            }
            if (array_key_exists('options_disabled', $properties)) {
                $options_disabled = (array)$properties['options_disabled'];
            }
        }

        $this->smarty_vars['default_option'] = $default_option;
        $this->smarty_vars['options'] = (array)$options;
        $this->smarty_vars['options_disabled'] = (array)$options_disabled;
        return $this->display('radio', $properties);
    }

    /**
     * Creates an input type reset.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function reset($properties = array())
    {
        return $this->display('reset', $properties);
    }

    /**
     * Creates an input type search.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function search($properties = array())
    {
        return $this->display('search', $properties);
    }

    /**
     * Creates a select.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * - - (array)default_option: Adds a default option with a value.
     * - - (array)default_option_attr: Adds attributes to the default option.
     * - - (array)options: Adds options with their value.
     * - - (array)array_to_check: Checks this array to filter options.
     * - - (array)filter: Defines if it filters options or not.
     * @return void
     */
    public function select($properties = array())
    {
        $default_option = array();
        $default_option_attr = array();
        $options = array();
        $array_to_check = array();
        $filter = false;

        if (is_array($properties) && !empty($properties)) {
            // DEFAULT OPTION
            if (array_key_exists('default_option', $properties)) {
                $default_option = (array)$properties['default_option'];
            }

            // DEFAULT OPTION's ATTRIBUTES
            if (array_key_exists('default_option_attr', $properties)) {
                $default_option_attr = (array)$properties['default_option_attr'];
            }

            // OPTIONS
            if (array_key_exists('options', $properties)) {
                $options = (array)$properties['options'];
            }

            // FILTER
            if (array_key_exists('array_to_check', $properties)
                && array_key_exists('filter', $properties)
            ) {
                $array_to_check = (array)$properties['array_to_check'];
                $filter = (bool)$properties['filter'];

                if (!empty($array_to_check)
                    && !empty($options)
                    && ($filter === true)
                ) {
                    // CLEAR OPTIONS
                    $options = array();

                    // FILTER OPTIONS
                    foreach ((array)$properties['options'] as $key => $value) {
                        if (!in_array($value, $array_to_check)) {
                            $options[$key] = (string)$value;
                        }
                    }

                    // REMOVE THE ARRAY TO CHECK
                    unset($properties['array_to_check']);
                    unset($properties['filter']);
                }
            }
        }

        $this->smarty_vars['default_option'] = (array)$default_option;
        $this->smarty_vars['default_option_attr'] = (array)$default_option_attr;
        $this->smarty_vars['options'] = (array)$options;
        return $this->display('select', $properties);
    }

    /**
     * Creates an input type submit.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function submit($properties = array())
    {
        return $this->display('submit', $properties);
    }

    /**
     * Creates a switcher.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * - - (string)btn_one: The button one's label.
     * - - (array)btn_one_attr: Adds attributes to your button one.
     * - - (string)btn_two: The button two's label.
     * - - (array)btn_two_attr: Adds attributes to your button two.
     * - - (array)switcher_attr: Adds attributes to your switcher.
     * @return void
     */
    public function switcher($properties = array())
    {
        $btn_one = false;
        $btn_one_attr = array();
        $btn_two = false;
        $btn_two_attr = array();
        $switcher_attr = array();

        if (is_array($properties) && !empty($properties)) {
            // BUTTON 1
            if (array_key_exists('btn_one', $properties)) {
                $btn_one = (string)$properties['btn_one'];
            }

            // BUTTON 1's ATTRIBUTES
            if (array_key_exists('btn_one_attr', $properties)) {
                $btn_one_attr = (array)$properties['btn_one_attr'];
            }
            // BUTTON 2
            if (array_key_exists('btn_two', $properties)) {
                $btn_two = (string)$properties['btn_two'];
            }

            // BUTTON 2's ATTRIBUTES
            if (array_key_exists('btn_two_attr', $properties)) {
                $btn_two_attr = (array)$properties['btn_two_attr'];
            }

            // SWITCHER's ATTRIBUTES
            if (array_key_exists('switcher_attr', $properties)) {
                $switcher_attr = (array)$properties['switcher_attr'];
            }
        }

        $this->smarty_vars['btn_one'] = $btn_one;
        $this->smarty_vars['btn_one_attr'] = (array)$btn_one_attr;
        $this->smarty_vars['btn_two'] = $btn_two;
        $this->smarty_vars['btn_two_attr'] = (array)$btn_two_attr;
        $this->smarty_vars['switcher_attr'] = (array)$switcher_attr;
        return $this->display('switcher', $properties);
    }

    /**
     * Creates an input type tel.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function tel($properties = array())
    {
        return $this->display('tel', $properties);
    }

    /**
     * Creates an input type text.
     *
     * @param array $properties - Set your properties in this array:
     * - - (string)form_group: Adds a div.form-group.
     * - - (array)form_group_attr: Adds attributes to the div.form-group.
     * - - (string)label: Adds a label.
     * - - (array)label_attr: Adds attributes to the label.
     * - - (array)margin_form_attr: Adds a div.margin-form and their attributes.
     * - - (string)prefix_left: Adds prefix to the left.
     * - - (string)prefix_right: Adds prefix to the right.
     * - - (string)helper: Adds helper.
     * - - (array)helper_attr: Adds attributes to the helper.
     * - - (array)attr: Adds attributes to the field.
     * @return void
     */
    public function text($properties = array())
    {
        return $this->display('text', $properties);
    }

    /**
     * Creates an input type time.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function time($properties = array())
    {
        return $this->display('time', $properties);
    }

    /**
     * Creates an input type url.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function url($properties = array())
    {
        return $this->display('url', $properties);
    }

    /**
     * Creates an input type week.
     *
     * @param array $properties - Set your properties in this array (see the function text()).
     * @return void
     */
    public function week($properties = array())
    {
        return $this->display('week', $properties);
    }

    /**
     * Displays a field.
     *
     * @param string $field - The field which will be displayed.
     * @param array $properties - Set your properties in this array.
     * @return void
     */
    private function display($field, $properties = array())
    {
        $form_group = true;
        $form_group_attr = array();
        $label = false;
        $label_attr = array();
        $margin_form_attr = array();
        $prefix_left = false;
        $prefix_right = false;
        $helper = false;
        $helper_attr = array();
        $attr = array();

        if (is_array($properties) && !empty($properties)) {
            // FORM-GROUP
            if (array_key_exists('full', $properties) && $properties['full'] !== true) {
                $form_group = false;
            }

            // FORM-GROUP's ATTRIBUTES
            if (array_key_exists(self::_FORM_GROUP_ATTR_, $properties)) {
                $form_group_attr = (array)$properties[self::_FORM_GROUP_ATTR_];
            }

            // LABEL
            if (array_key_exists(self::_LABEL_, $properties)) {
                $label = (string)$properties[self::_LABEL_];
            }

            // LABEL's ATTRIBUTES
            if (array_key_exists(self::_LABEL_ATTR_, $properties)) {
                $label_attr = (array)$properties[self::_LABEL_ATTR_];
            }

            // MARGIN-FORM's ATTRIBUTES
            if (array_key_exists(self::_MARGIN_FORM_ATTR_, $properties)) {
                $margin_form_attr = (array)$properties[self::_MARGIN_FORM_ATTR_];
            }

            // PREFIX LEFT
            if (array_key_exists(self::_PREFIX_LEFT_, $properties)) {
                $prefix_left = (string)$properties[self::_PREFIX_LEFT_];
            }

            // PREFIX RIGHT
            if (array_key_exists(self::_PREFIX_RIGHT_, $properties)) {
                $prefix_right = (string)$properties[self::_PREFIX_RIGHT_];
            }

            // FIELD's ATTRIBUTES
            if (array_key_exists(self::_ATTR_, $properties)) {
                $attr = (array)$properties[self::_ATTR_];
            }

            // HELPER
            if (array_key_exists(self::_HELPER_, $properties)) {
                $helper = (string)$properties[self::_HELPER_];
            }

            // HELPER's ATTRIBUTES
            if (array_key_exists(self::_HELPER_ATTR_, $properties)) {
                $helper_attr = (array)$properties[self::_HELPER_ATTR_];
            }
        }

        SdevModule::smartyAssign(array_merge((array)$this->smarty_vars, array(
            self::_FORM_GROUP_ => $form_group,
            self::_FORM_GROUP_ATTR_ => (array)$form_group_attr,
            self::_LABEL_ => $label,
            self::_LABEL_ATTR_ => (array)$label_attr,
            self::_MARGIN_FORM_ATTR_ => (array)$margin_form_attr,
            self::_PREFIX_LEFT_ => $prefix_left,
            self::_PREFIX_RIGHT_ => $prefix_right,
            self::_FIELD_ => (string)$field,
            self::_ATTR_ => (array)$attr,
            self::_HELPER_ => $helper,
            self::_HELPER_ATTR_ => (array)$helper_attr
        )));

        if ($field == 'button'
            || $field == 'color'
            || $field == 'date'
            || $field == 'datetime-local'
            || $field == 'email'
            || $field == 'file'
            || $field == 'hidden'
            || $field == 'image'
            || $field == 'month'
            || $field == 'number'
            || $field == 'password'
            || $field == 'reset'
            || $field == 'search'
            || $field == 'submit'
            || $field == 'tel'
            || $field == 'text'
            || $field == 'time'
            || $field == 'url'
            || $field == 'week'
        ) {
            $field = 'input';
        }

        return $this->template('display_' . $field . '.tpl');
    }

    /**
     * Display a template linked to the field.
     *
     * @param string $template - The file's name of the template linked to the field.
     * @return void
     */
    private function template($template)
    {
        $template_start = self::$module->display(
            _PS_MODULE_DIR_ . self::$module->name,
            $this->path . 'display_field_start.tpl'
        );

        $template_field = self::$module->display(
            _PS_MODULE_DIR_ . self::$module->name,
            $this->path . $template
        );

        $template_end = self::$module->display(
            _PS_MODULE_DIR_ . self::$module->name,
            $this->path . 'display_field_end.tpl'
        );

        return $template_start . $template_field . $template_end;
    }
}
