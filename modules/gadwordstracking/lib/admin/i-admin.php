<?php

/**
 * i-admin.php file defines mandatory method to manage module's admin
 */
interface BT_IAdmin
{
    /**
     * process display or updating or etc ... admin
     *
     * @param mixed $aParam => $_GET or $_POST
     *
     * @return bool
     */
    public function run(array $aParam = null);
}