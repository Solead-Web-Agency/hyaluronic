<?php
/**
 * base-dynamic-tags_class.php file defines method for handling dynamic tags properties
 */

/**
 * declare Dynamic tags Exception class
 */
class BT_GactDynTagsException extends Exception {}


abstract class BT_GactBaseDynTags
{
    /**
     * @var string $sName : stock tag type name
     */
    public static $sName = '"';

    /**
     * @var string $sQuote : character used for tagging values
     */
    public static $sQuote = '"';

    /**
     * @var bool $bValid : current object valid or not
     */
    public $bValid = false;

    /**
     * @var float $fTotalValue : total of value of cart or purchase
     */
    public $fTotalValue = null;

    /**
     * @var string $sOrderId : the order ID
     */
    public $iOrderId = null;


    /**
     * get params keys
     *
     * @param array $aParams
     */
    abstract public function __construct(array $aParams);


    /**
     * set total value
     *
     * @param string $sProperty
     * @param mixed  $mValue
     *
     * @return bool
     */
    abstract public function setTotalValue();


    /**
     * set values
     *
     * @param string $sTagsType
     * @param array  $aParams
     *
     * @return obj tags type abstract type
     */
    public function set()
    {
        // set total value
        $this->setTotalValue();
    }


    /**
     * display properties
     *
     * @return array of properties + labels
     */
    public function display()
    {
        $aProperties = array();

        // check total value
        if (!empty($this->fTotalValue)) {
            $aProperties[] = array(
                'label' => 'total',
                'value' => $this->fTotalValue,
            );

        }

        // check order id value
        if (!empty($this->iOrderId)) {
            $aProperties[] = array(
                'label' => 'order_id',
                'value' => $this->iOrderId,
            );
        }

        return $aProperties;
    }

    /**
     * instantiate matched tag object
     *
     * @throws Exception
     * @param string $sTagsType
     * @param array  $aParams
     * @return obj tags type abstract type
     */
    public static function get($sTagsType, array $aParams = null)
    {
        // if valid connector
        if (in_array($sTagsType, array_keys($GLOBALS['GACT_TAGS_TYPE']))) {
            // include
            require_once('dynamic-' . $sTagsType . '-tags_class.php');

            // set class name
            $sClassName = 'BT_GactDyn' . ucfirst($sTagsType) . 'Tags';

            // get tags type name
            self::$sName = $sTagsType;

            return new $sClassName($aParams);
        } else {
            throw new BT_GactDynTagsException(GAdwordsTracking::$oModule->l('Internal server error => invalid dynamic tags type', 'base-dynamic-tags_class'), 510);
        }
    }
}
