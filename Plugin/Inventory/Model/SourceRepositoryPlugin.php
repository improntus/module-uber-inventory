<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Plugin\Inventory\Model;

use Improntus\UberInventory\Api\Data\InventorySourceInterface;
use Improntus\UberInventory\Api\Data\InventorySourceInterfaceFactory;
use Improntus\UberInventory\Api\InventorySourceRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Psr\Log\LoggerInterface;

class SourceRepositoryPlugin
{
    /**
     * Attributes this module owns, as declared in etc/extension_attributes.xml.
     *
     * Only these are persisted: entity_id and source_code are never writable from the payload,
     * otherwise a save could be redirected onto a different source's row.
     *
     * @var array<string, string>
     */
    private const UBER_ATTRIBUTES = [
        InventorySourceInterface::ORGANIZATION_ID => 'getOrganizationId',
        InventorySourceInterface::MONDAY_OPEN => 'getMondayOpen',
        InventorySourceInterface::MONDAY_CLOSE => 'getMondayClose',
        InventorySourceInterface::TUESDAY_OPEN => 'getTuesdayOpen',
        InventorySourceInterface::TUESDAY_CLOSE => 'getTuesdayClose',
        InventorySourceInterface::WEDNESDAY_OPEN => 'getWednesdayOpen',
        InventorySourceInterface::WEDNESDAY_CLOSE => 'getWednesdayClose',
        InventorySourceInterface::THURSDAY_OPEN => 'getThursdayOpen',
        InventorySourceInterface::THURSDAY_CLOSE => 'getThursdayClose',
        InventorySourceInterface::FRIDAY_OPEN => 'getFridayOpen',
        InventorySourceInterface::FRIDAY_CLOSE => 'getFridayClose',
        InventorySourceInterface::SATURDAY_OPEN => 'getSaturdayOpen',
        InventorySourceInterface::SATURDAY_CLOSE => 'getSaturdayClose',
        InventorySourceInterface::SUNDAY_OPEN => 'getSundayOpen',
        InventorySourceInterface::SUNDAY_CLOSE => 'getSundayClose',
    ];

    /**
     * @var InventorySourceRepositoryInterface $inventorySourceRepository
     */
    protected InventorySourceRepositoryInterface $inventorySourceRepository;

    /**
     * @var InventorySourceInterfaceFactory $inventorySourceFactory
     */
    protected InventorySourceInterfaceFactory $inventorySourceFactory;

    /**
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    /**
     * @param InventorySourceRepositoryInterface $inventorySourceRepository
     * @param InventorySourceInterfaceFactory $inventorySourceFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        InventorySourceRepositoryInterface $inventorySourceRepository,
        InventorySourceInterfaceFactory    $inventorySourceFactory,
        LoggerInterface                    $logger
    ) {
        $this->inventorySourceRepository = $inventorySourceRepository;
        $this->inventorySourceFactory = $inventorySourceFactory;
        $this->logger = $logger;
    }

    /**
     * Persist the Uber attributes once the core source has been saved.
     *
     * @param SourceRepositoryInterface $subject
     * @param mixed $result
     * @param SourceInterface $source
     * @return mixed
     */
    public function afterSave(
        SourceRepositoryInterface $subject,
        $result,
        SourceInterface           $source
    ) {
        $values = $this->extractUberAttributes($source);
        if ($values === []) {
            return $result;
        }

        $sourceCode = $source->getSourceCode();

        try {
            /** @var InventorySourceInterface|AbstractModel $inventorySource */
            $inventorySource = $this->getInventorySource($sourceCode);
            $inventorySource->setSourceCode($sourceCode);
            foreach ($values as $attribute => $value) {
                $inventorySource->setData($attribute, $value);
            }
            $this->inventorySourceRepository->save($inventorySource);
        } catch (CouldNotSaveException $e) {
            $this->logger->error(
                'Could not save the Uber attributes of the inventory source.',
                ['source_code' => $sourceCode, 'message' => $e->getMessage()]
            );
        }

        return $result;
    }

    /**
     * Read only the attributes declared in etc/extension_attributes.xml.
     *
     * @param SourceInterface $source
     * @return array
     */
    private function extractUberAttributes(SourceInterface $source): array
    {
        $extensionAttributes = $source->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return [];
        }

        $values = [];
        foreach (self::UBER_ATTRIBUTES as $attribute => $getter) {
            if (!method_exists($extensionAttributes, $getter)) {
                continue;
            }
            $value = $extensionAttributes->{$getter}();
            if ($value === null || $value === '') {
                continue;
            }
            $values[$attribute] = $value;
        }

        return $values;
    }

    /**
     * @param string $sourceCode
     * @return InventorySourceInterface
     */
    private function getInventorySource(string $sourceCode): InventorySourceInterface
    {
        try {
            return $this->inventorySourceRepository->getBySourceCode($sourceCode);
        } catch (NoSuchEntityException $e) {
            return $this->inventorySourceFactory->create();
        }
    }
}
