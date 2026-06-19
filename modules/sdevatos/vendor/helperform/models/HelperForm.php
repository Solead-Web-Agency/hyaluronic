<?php

namespace ScaleDEV;

use ReflectionClass;
use Exception;

class HelperForm
{
    const TypeButton = 'button';
    const TypeCheckbox = 'checkbox';
    const TypeColor = 'color';
    const TypeDate = 'date';
    const TypeDatetimeLocal = 'datetime-local';
    const TypeEmail = 'email';
    const TypeFile = 'file';
    const TypeHidden = 'hidden';
    const TypeImage = 'image';
    const TypeMonth = 'month';
    const TypeNumber = 'number';
    const TypePassword = 'password';
    const TypeRadio = 'radio';
    const TypeReset = 'reset';
    const TypeSearch = 'search';
    const TypeSelect = 'select';
    const TypeSubmit = 'submit';
    const TypeTel = 'tel';
    const TypeText = 'text';
    const TypeTime = 'time';
    const TypeUrl = 'url';
    const TypeWeek = 'week';

    const TypeMapping = 'mapping';
    const TypeSwitch = 'switch';

    /**
     * Get the field list.
     *
     * @return array
     */
    public function getConstFieldList()
    {
        $field_list = array();
        $ReflectionClass = new ReflectionClass(get_class($this));
        foreach ($ReflectionClass->getConstants() as $const => $value) {
            if (strpos($const, 'Type') !== false) {
                $field_list[$const] = $value;
            }
        }
        return (array)$field_list;
    }

    const FORM_START = 'form_start';
    const FORM_END = 'form_end';

    const FORM_METHOD = 'form_method';
    const FORM_METHOD_GET = 'GET';
    const FORM_METHOD_POST = 'POST';
    const FORM_METHOD_DIALOG = 'DIALOG';
    const FORM_ACTION = 'form_action';
    const FORM_ATTR = 'form_attr';

    const FORM_ENCTYPE_APPLICATION = 'application/x-www-form-urlencoded';
    const FORM_ENCTYPE_MULTIPART = 'multipart/form-data';
    const FORM_ENCTYPE_TEXT = 'text/plain';

    const FIELD = 'field';
    const LABEL = 'label';
    const LABEL_ATTR = 'label_attr';

    const FORM_GROUP = 'form_group';
    const FORM_GROUP_ATTR = 'form_group_attr';
    
    const MARGIN_FORM = 'margin_form';
    const MARGIN_FORM_ATTR = 'margin_form_attr';

    const PREFIX = 'prefix';
    const PREFIX_ATTR = 'prefix_attr';

    const ATTR = 'attr';

    const DEFAULT_OPTION = 'default_option';
    const DEFAULT_OPTION_ATTR = 'default_option_attr';
    const OPTIONS = 'options';
    const OPTIONS_ATTR = 'options_attr';

    const SUFFIX = 'suffix';
    const SUFFIX_ATTR = 'suffix_attr';

    const HELPER = 'helper';
    const HELPER_ATTR = 'helper_attr';

    /**
     * Get all field attributes.
     *
     * @return array
     */
    public function getConstFieldAttributes()
    {
        $field_attributes = array();
        $ReflectionClass = new ReflectionClass(get_class($this));
        foreach ($ReflectionClass->getConstants() as $const => $value) {
            if (strpos($const, 'Type') === false
                && (strpos($const, 'FORM_') === false || strpos($const, 'FORM_GROUP') !== false)
            ) {
                $field_attributes[$const] = $value;
            }
        }
        return (array)$field_attributes;
    }

    /** @var string $template_path - Template path. */
    private $template_path;

    /** @var array $form_method_allowed_list - Form method allowed list. */
    private $form_method_allowed_list = array(
        self::FORM_METHOD_GET,
        self::FORM_METHOD_POST,
        self::FORM_METHOD_DIALOG
    );

    /** @var array $form_enctype_allowed_list - Form enctype allowed list. */
    private $form_enctype_allowed_list = array(
        self::FORM_ENCTYPE_APPLICATION,
        self::FORM_ENCTYPE_MULTIPART,
        self::FORM_ENCTYPE_TEXT
    );

    /** @param string $form_method - Form method (GET, POST or DIALOG). */
    private $form_method;

    /** @var string $form_action - Form action. */
    private $form_action;

    /** @var string $form_enctype - Form enctype. */
    private $form_enctype;

    /** @var array $form_attr - Form attributes. */
    private $form_attr = array();

    /** @var bool $has_form_group - Display a form-group or not. */
    public $has_form_group = true;

    /** @var array $label_attr - Label attributes. */
    public $label_attr = array();

    /** @var bool $has_margin_form - Display a margin-form or not. */
    public $has_margin_form = true;

    /** @var array $margin_form_attr - Margin-form attributes. */
    public $margin_form_attr = array();

    /** @var array $field_list - Field list. */
    private $field_list = array();

    /**
     * Constructor.
     *
     * @param string $method - Form method (GET, POST or DIALOG).
     * @param string $action - Form action.
     * @param array $attr - Form attributes.
     * @param string $enctype - Form enctype.
     */
    public function __construct($method = self::FORM_METHOD_POST, $action = null, $attr = array(), $enctype = self::FORM_ENCTYPE_APPLICATION)
    {
        $this->template_path = dirname(dirname(__FILE__)).'/views/';
        try {
            if ($method) {
                if (in_array(strtoupper($method), $this->form_method_allowed_list)) {
                    $this->form_method = $method;
                } else {
                    throw new Exception('The form method \'' . $method . '\' is disallowed !');
                }
            }

            if ($action) {
                if (is_string($action)) {
                    $this->form_action = $action;
                } else {
                    throw new Exception('The form action must be a string !');
                }
            }

            if ($attr) {
                if (is_array($attr)) {
                    $this->form_attr = (array)$attr;
                } else {
                    throw new Exception('The form attributes must be an array !');
                }
            }

            if ($enctype) {
                if (in_array(strtolower($enctype), $this->form_enctype_allowed_list)) {
                    $this->form_enctype = $enctype;
                } else {
                    throw new Exception('The form enctype \'' . $enctype . '\' is disallowed !');
                }
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get form.
     *
     * @return array
     */
    public function getForm()
    {
        try {
            if (!is_array($this->field_list)) {
                throw new Exception('The field list must be an array !');
            } elseif (!empty($this->field_list)) {
                foreach ($this->field_list as $field_name => $field_value) {
                    $this->field_list[$field_name] = $this->getField($field_value);
                }
            }
            $this->form_attr['method'] = $this->form_method;
            $this->form_attr['action'] = $this->form_action;
            $this->form_attr['enctype'] = $this->form_enctype;
            $this->form_attr = array(self::ATTR => $this->form_attr);
            $this->field_list['form_start'] = $this->display('form_start', $this->form_attr);
            $this->field_list['form_end'] = $this->display('form_end');
            return (array)$this->field_list;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get field.
     *
     * @param array $field - Field.
     * @return array
     */
    private function getField($field)
    {
        try {
            if (is_array($field)) {
                $return = array();
                foreach ($field as $field_name => $field_value) {
                    if (in_array($field_name, $this->getConstFieldList())
                        && in_array(array_keys($field_value)[0], $this->getConstFieldAttributes())
                    ) {
                        return $this->display($field_name, $field_value);
                    } else {
                        $return[$field_name] = $this->getField($field_value);
                    }
                }
                return (array)$return;
            } else {
                throw new Exception('The field data must be an array !');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Display the field template.
     */
    private function display($field, $field_attr = array())
    {
        $form_group = true;
        $form_group_attr = array();
        $label = null;
        $label_attr = array();
        $margin_form = true;
        $margin_form_attr = array();
        $attr = array();
        $prefix = null;
        $prefix_attr = array();
        $suffix = null;
        $suffix_attr = array();
        $helper = null;
        $helper_attr = array();

        if (is_array($field_attr) && !empty($field_attr)) {
            // FORM-GROUP
            if (!(bool)$this->has_form_group
                || (array_key_exists(self::FORM_GROUP, $field_attr)
                && !(bool)$field_attr[self::FORM_GROUP])
            ) {
                $form_group = false;
            }

            // FORM-GROUP ATTRIBUTES
            if ((bool)$form_group
                && array_key_exists(self::FORM_GROUP_ATTR, $field_attr)
                && is_array($field_attr[self::FORM_GROUP_ATTR])
                && !empty($field_attr[self::FORM_GROUP_ATTR])
            ) {
                $form_group_attr = (array)$field_attr[self::FORM_GROUP_ATTR];
            }

            // LABEL
            if (array_key_exists(self::LABEL, $field_attr)) {
                $label = $field_attr[self::LABEL];
            }

            // LABEL ATTRIBUTES
            if ($label
                && array_key_exists(self::LABEL_ATTR, $field_attr)
                && is_array($field_attr[self::LABEL_ATTR])
                && !empty($field_attr[self::LABEL_ATTR])
            ) {
                $label_attr = (array)$field_attr[self::LABEL_ATTR];
            } elseif ($this->label_attr
                && is_array($this->label_attr)
                && !empty($this->label_attr)
            ) {
                $label_attr = (array)$this->label_attr;
            }

            // MARGIN-FORM
            if (!(bool)$this->has_margin_form
                || array_key_exists(self::MARGIN_FORM, $field_attr)
                && !(bool)$field_attr[self::MARGIN_FORM]
            ) {
                $margin_form = false;
            }

            // MARGIN-FORM ATTRIBUTES
            if ((bool)$margin_form
                && array_key_exists(self::MARGIN_FORM_ATTR, $field_attr)
                && is_array($field_attr[self::MARGIN_FORM_ATTR])
                && !empty($field_attr[self::MARGIN_FORM_ATTR])
            ) {
                $margin_form_attr = (array)$field_attr[self::MARGIN_FORM_ATTR];
            } elseif ($this->margin_form_attr
                && is_array($this->margin_form_attr)
                && !empty($this->margin_form_attr)
            ) {
                $margin_form_attr = (array)$this->margin_form_attr;
            }

            // PREFIX
            if (array_key_exists(self::PREFIX, $field_attr)) {
                $prefix = $field_attr[self::PREFIX];
            }

            // PREFIX ATTRIBUTES
            if ($prefix
                && array_key_exists(self::PREFIX_ATTR, $field_attr)
                && is_array($field_attr[self::PREFIX_ATTR])
                && !empty($field_attr[self::PREFIX_ATTR])
            ) {
                $prefix_attr = (array)$field_attr[self::PREFIX_ATTR];
            }

            // FIELD ATTRIBUTES
            if (array_key_exists(self::ATTR, $field_attr)
                && is_array($field_attr[self::ATTR])
                && !empty($field_attr[self::ATTR])
            ) {
                $attr = (array)$field_attr[self::ATTR];
            }

            // CHECKBOX - RADIO - SELECT
            if ($field == self::TypeCheckbox
                || $field == self::TypeRadio
                || $field == self::TypeSelect
            ) {
                // DEFAULT OPTION
                if (array_key_exists(self::DEFAULT_OPTION, $field_attr)) {
                    $default_option = $field_attr[self::DEFAULT_OPTION];
                }

                // DEFAULT OPTION ATTRIBUTES
                if (array_key_exists(self::DEFAULT_OPTION_ATTR, $field_attr)
                    && is_array($field_attr[self::DEFAULT_OPTION_ATTR])
                    && !empty($field_attr[self::DEFAULT_OPTION_ATTR])
                ) {
                    $default_option_attr = (array)$field_attr[self::DEFAULT_OPTION_ATTR];
                }

                // OPTIONS
                if (array_key_exists(self::OPTIONS, $field_attr)
                    && is_array($field_attr[self::OPTIONS])
                    && !empty($field_attr[self::OPTIONS])
                ) {
                    $options = (array)$field_attr[self::OPTIONS];
                }

                // OPTIONS ATTRIBUTES
                if (array_key_exists(self::OPTIONS_ATTR, $field_attr)
                    && is_array($field_attr[self::OPTIONS_ATTR])
                    && !empty($field_attr[self::OPTIONS_ATTR])
                ) {
                    $options_attr = (array)$field_attr[self::OPTIONS_ATTR];
                }
            }

            // SUFFIX
            if (array_key_exists(self::SUFFIX, $field_attr)) {
                $suffix = $field_attr[self::SUFFIX];
            }

            // SUFFIX ATTRIBUTES
            if ($suffix
                && array_key_exists(self::SUFFIX_ATTR, $field_attr)
                && is_array($field_attr[self::SUFFIX_ATTR])
                && !empty($field_attr[self::SUFFIX_ATTR])
            ) {
                $suffix_attr = (array)$field_attr[self::SUFFIX_ATTR];
            }

            // HELPER
            if (array_key_exists(self::HELPER, $field_attr)) {
                $helper = $field_attr[self::HELPER];
            }

            // HELPER ATTRIBUTES
            if ($helper
                && array_key_exists(self::HELPER_ATTR, $field_attr)
                && is_array($field_attr[self::HELPER_ATTR])
                && !empty($field_attr[self::HELPER_ATTR])
            ) {
                $helper_attr = (array)$field_attr[self::HELPER_ATTR];
            }
        }

        $input_field_list = array(
            self::TypeButton,
            self::TypeColor,
            self::TypeDate,
            self::TypeDatetimeLocal,
            self::TypeEmail,
            self::TypeFile,
            self::TypeHidden,
            self::TypeImage,
            self::TypeMonth,
            self::TypeNumber,
            self::TypePassword,
            self::TypeReset,
            self::TypeSearch,
            self::TypeSubmit,
            self::TypeTel,
            self::TypeText,
            self::TypeTime,
            self::TypeUrl,
            self::TypeWeek
        );

        ob_start();
        if ($field != self::FORM_START && $field != self::FORM_END) {
            include($this->template_path.'display_field_start.php');
        }
        include($this->template_path.($field != 'form_start' && $field != 'form_end' ? 'fields/' : null).'display_'.(in_array($field, $input_field_list) ? 'input' : $field).'.php');
        if ($field != self::FORM_START && $field != self::FORM_END) {
            include($this->template_path.'display_field_end.php');
        }
        return ob_get_clean();
    }

    /**
     * Get the form method.
     *
     * @return string
     */
    public function getFormMethod()
    {
        return $this->form_method;
    }

    /**
     * Get the form action.
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->form_action;
    }

    /**
     * Get the form enctype.
     *
     * @return string
     */
    public function getFormEnctype()
    {
        return $this->form_enctype
            ? $this->form_enctype
            : self::FORM_ENCTYPE_APPLICATION;
    }

    /**
     * Get the form attributes.
     *
     * @return array
     */
    public function getFormAttr()
    {
        return (array)$this->form_attr;
    }

    /**
     * Set the field list.
     *
     * @param array $field_list - Field list.
     */
    public function setFieldList($field_list)
    {
        try {
            if (is_array($field_list)) {
                $this->field_list = (array)$field_list;
            } else {
                throw new Exception('The field list must be an array !');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}
