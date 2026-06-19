<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (class_exists(Module::class)) {
    if (!defined('_PS_VERSION_')) {
        exit;
    }
}
class AmazonSPDefFeed extends AmazonSPDefObject
{
    const STATUS_DONE = 'DONE';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_IN_QUEUE = 'IN_QUEUE';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_FATAL = 'FATAL';

    // Required
    public $feedId;
    public $feedType;
    public $createdTime;
    public $processingStatus;   // CANCELLED | DONE | FATAL | IN_PROGRESS | IN_QUEUE

    // Optional
    public $marketplaceIds = [];
    public $processingStartTime;
    public $processingEndTime;
    public $resultFeedDocumentId;
    
    public function __construct($input = null)
    {
        parent::__construct($input);
    }

    public function isDone()
    {
        return $this->processingStatus == self::STATUS_DONE;
    }

    public function isProcessing()
    {
        return in_array($this->processingStatus, array(self::STATUS_IN_QUEUE, self::STATUS_IN_PROGRESS));
    }

    public function isError()
    {
        return in_array($this->processingStatus, array(self::STATUS_CANCELLED, self::STATUS_FATAL));
    }
}
