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
if (class_exists(Module::class)) {
    if (!defined('_PS_VERSION_')) {
        exit;
    }
}
abstract class AmazonSPFeedMessage implements IAmazonFeedMessageContent
{
    // All specified information overwrites any existing information. Any unspecified information is erased
    const OPERATION_TYPE_UPDATE = 'Update';

    // Product feed only.
    // All specified information overwrites any existing information, but unspecified information is unaffected
    const OPERATION_TYPE_PARTIAL_UPDATE = 'PartialUpdate';

    // All information is removed
    const OPERATION_TYPE_DELETE = 'Delete';

    protected $operationType;

    public function __construct($operationType = '')
    {
        $this->operationType = $operationType;
    }

    public function generateMessage($domDoc)
    {
        $content = $this->generateRemainingMessage($domDoc);

        if ($this->operationType) {
            $operationType = $domDoc->createElement('OperationType', $this->operationType);
            array_unshift($content, $operationType);
        }

        return $content;
    }

    /**
     * @param DOMDocument $domDoc
     * @return DOMElement[]
     */
    abstract protected function generateRemainingMessage($domDoc);

    protected function isDeleteOperation()
    {
        return $this->operationType === self::OPERATION_TYPE_DELETE;
    }
}
