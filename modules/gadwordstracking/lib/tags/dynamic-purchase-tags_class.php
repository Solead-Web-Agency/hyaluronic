<?php
/**
 * dynamic-purchase-tags_class.php file defines method for handing purchase tags
 */

if ( ! defined('_PS_VERSION_')) {
    exit(1);
}

/**
 * declare Purchase Exception class
 */
class BT_GactPurchaseException extends BT_GactDynTagsException {}

class BT_GactDynPurchaseTags extends BT_GactBaseDynTags
{
    /**
     * magic method assign
     *
     * @throws Exception
     * @param array $aParams
     */
    public function __construct(array $aParams)
    {
        // order confirmation hook already return the amount of current cart (total paid)
        if (!empty($aParams['fTotalPaid'])) {
            $this->fTotalPaid = $aParams['fTotalPaid'];
        }
        if (!empty($aParams['iOrderId'])) {
            $this->iOrderId = $aParams['iOrderId'];
        } else {
            throw new BT_GactPurchaseException(GAdwordsTracking::$oModule->l('Internal server error => invalid order id', 'dynamic-purchase-tags_class'), 520);
        }
    }


    /**
     * allow to check value assign to property
     *
     * @param array $aParams
     */
    public function __set($sName, $mValue)
    {
        switch ($sName) {
            case 'fTotalPaid' :
                $this->fTotalPaid = is_float($mValue) ? $mValue : null;
                break;
            default:
                break;
        }
    }

    /**
     * returns allowed properties
     *
     * @param string $sName
     *
     * @return property : mixed or null
     */
    public function __get($sName)
    {
        switch ($sName) {
            case 'fTotalPaid' :
                return $this->fTotalPaid;
                break;
            default:
                break;
        }

        return null;
    }


    /**
     * set total value
     *
     * @return string
     */
    public function setTotalValue()
    {
        $this->bValid = false;

        // test if total paid is already set
        if (!empty($this->fTotalPaid)) {
            $this->fTotalValue = $this->fTotalPaid;
            $this->bValid = true;
        }
    }
}
