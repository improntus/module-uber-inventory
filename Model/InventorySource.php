<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Model;

use Improntus\UberInventory\Api\Data\InventorySourceInterface;
use Magento\Framework\Model\AbstractModel;

class InventorySource extends AbstractModel implements InventorySourceInterface
{
    /**
     * @var string
     */
    public const CACHE_TAG = 'uber_inventory_source';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string $_eventPrefix
     */
    protected $_eventPrefix = 'uber_inventory_source';

    /**
     * @var string $_eventObject
     */
    protected $_eventObject = 'source';

    /**
     * Init Construct
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Source::class);
    }

    /**
     * @inheritDoc
     */
    public function getSourceCode()
    {
        return $this->getData(InventorySourceInterface::SOURCE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setSourceCode(string $sourceCode)
    {
        return $this->setData(InventorySourceInterface::SOURCE_CODE, $sourceCode);
    }

    /**
     * @inheritDoc
     */
    public function getOrganizationId()
    {
        return $this->getData(InventorySourceInterface::ORGANIZATION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrganizationId(int $organizationId)
    {
        return $this->setData(InventorySourceInterface::ORGANIZATION_ID, $organizationId);
    }

    /**
     * @inheritDoc
     */
    public function getMondayOpen()
    {
        return $this->getData(InventorySourceInterface::MONDAY_OPEN);
    }

    /**
     * @inheritDoc
     */
    public function setMondayOpen(int $mondayOpen)
    {
        return $this->setData(InventorySourceInterface::MONDAY_OPEN, $mondayOpen);
    }

    /**
     * @inheritDoc
     */
    public function getMondayClose()
    {
        return $this->getData(InventorySourceInterface::MONDAY_CLOSE);
    }

    /**
     * @inheritDoc
     */
    public function setMondayClose(int $mondayClose)
    {
        return $this->setData(InventorySourceInterface::MONDAY_CLOSE, $mondayClose);
    }

    /**
     * @inheritDoc
     */
    public function getTuesdayOpen()
    {
        return $this->getData(InventorySourceInterface::TUESDAY_OPEN);
    }

    /**
     * @inheritDoc
     */
    public function setTuesdayOpen(int $tuesdayOpen)
    {
        return $this->setData(InventorySourceInterface::TUESDAY_OPEN, $tuesdayOpen);
    }

    /**
     * @inheritDoc
     */
    public function getTuesdayClose()
    {
        return $this->getData(InventorySourceInterface::TUESDAY_CLOSE);
    }

    /**
     * @inheritDoc
     */
    public function setTuesdayClose(int $tuesdayClose)
    {
        return $this->setData(InventorySourceInterface::TUESDAY_CLOSE, $tuesdayClose);
    }

    /**
     * @inheritDoc
     */
    public function getWednesdayOpen()
    {
        return $this->getData(InventorySourceInterface::WEDNESDAY_OPEN);
    }

    /**
     * @inheritDoc
     */
    public function setWednesdayOpen(int $wednesdayOpen)
    {
        return $this->setData(InventorySourceInterface::WEDNESDAY_OPEN, $wednesdayOpen);
    }

    /**
     * @inheritDoc
     */
    public function getWednesdayClose()
    {
        return $this->getData(InventorySourceInterface::WEDNESDAY_CLOSE);
    }

    /**
     * @inheritDoc
     */
    public function setWednesdayClose(int $wednesdayClose)
    {
        return $this->setData(InventorySourceInterface::WEDNESDAY_CLOSE, $wednesdayClose);
    }

    /**
     * @inheritDoc
     */
    public function getThursdayOpen()
    {
        return $this->getData(InventorySourceInterface::THURSDAY_OPEN);
    }

    /**
     * @inheritDoc
     */
    public function setThursdayOpen(int $thursdayOpen)
    {
        return $this->setData(InventorySourceInterface::THURSDAY_OPEN, $thursdayOpen);
    }

    /**
     * @inheritDoc
     */
    public function getThursdayClose()
    {
        return $this->getData(InventorySourceInterface::THURSDAY_CLOSE);
    }

    /**
     * @inheritDoc
     */
    public function setThursdayClose(int $thursdayClose)
    {
        return $this->setData(InventorySourceInterface::THURSDAY_CLOSE, $thursdayClose);
    }

    /**
     * @inheritDoc
     */
    public function getFridayOpen()
    {
        return $this->getData(InventorySourceInterface::FRIDAY_OPEN);
    }

    /**
     * @inheritDoc
     */
    public function setFridayOpen(int $fridayOpen)
    {
        return $this->setData(InventorySourceInterface::FRIDAY_OPEN, $fridayOpen);
    }

    /**
     * @inheritDoc
     */
    public function getFridayClose()
    {
        return $this->getData(InventorySourceInterface::FRIDAY_CLOSE);
    }

    /**
     * @inheritDoc
     */
    public function setFridayClose(int $fridayClose)
    {
        return $this->setData(InventorySourceInterface::FRIDAY_CLOSE, $fridayClose);
    }

    /**
     * @inheritDoc
     */
    public function getSaturdayOpen()
    {
        return $this->getData(InventorySourceInterface::SATURDAY_OPEN);
    }

    /**
     * @inheritDoc
     */
    public function setSaturdayOpen(int $saturdayOpen)
    {
        return $this->setData(InventorySourceInterface::SATURDAY_OPEN, $saturdayOpen);
    }

    /**
     * @inheritDoc
     */
    public function getSaturdayClose()
    {
        return $this->getData(InventorySourceInterface::SATURDAY_CLOSE);
    }

    /**
     * @inheritDoc
     */
    public function setSaturdayClose(int $sundayClose)
    {
        return $this->setData(InventorySourceInterface::SATURDAY_CLOSE, $sundayClose);
    }

    /**
     * @inheritDoc
     */
    public function getSundayOpen()
    {
        return $this->getData(InventorySourceInterface::SUNDAY_OPEN);
    }

    /**
     * @inheritDoc
     */
    public function setSundayOpen(int $sundayOpen)
    {
        return $this->setData(InventorySourceInterface::SUNDAY_OPEN, $sundayOpen);
    }

    /**
     * @inheritDoc
     */
    public function getSundayClose()
    {
        return $this->getData(InventorySourceInterface::SUNDAY_CLOSE);
    }

    /**
     * @inheritDoc
     */
    public function setSundayClose(int $sundayClose)
    {
        return $this->setData(InventorySourceInterface::SUNDAY_CLOSE, $sundayClose);
    }
}
