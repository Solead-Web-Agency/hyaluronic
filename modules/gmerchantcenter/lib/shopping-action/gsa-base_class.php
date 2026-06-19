<?php
/**
 * Google Merchant Center
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

abstract class GsaBase
{
    /**
     * @var string $sAction : define the action to action
     */
    protected $sAction = null;

    /**
     * assigns few information about action
     *
     * @param string $sAction
     */
    public function __construct($sAction)
    {
        // set action
        $this->sAction = $sAction;
    }

    /**
     * execute action
     *
     * @category action collection
     * @uses
     *
     * @param array $aParams
     * @return array
     */
    abstract public function run(array $aParams = null);
}
