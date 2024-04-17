<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Model;

use Improntus\UberInventory\Api\Data\InventorySourceInterface;
use Improntus\UberInventory\Api\Data\InventorySourceInterfaceFactory;
use Improntus\UberInventory\Api\Data\InventorySourceSearchResultInterface;
use Improntus\UberInventory\Api\Data\InventorySourceSearchResultInterfaceFactory;
use Improntus\UberInventory\Api\InventorySourceRepositoryInterface;
use Improntus\UberInventory\Model\ResourceModel\Source as SourceModel;
use Improntus\UberInventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class InventorySourceRepository implements InventorySourceRepositoryInterface
{

    /**
     * @var SourceModel $sourceModel
     */
    protected SourceModel $sourceModel;

    /**
     * @var InventorySourceInterfaceFactory $sourceInterfaceFactory
     */
    protected InventorySourceInterfaceFactory $sourceInterfaceFactory;

    /**
     * @var SourceCollectionFactory
     */
    protected $sourceCollectionFactory;

    /**
     * @var InventorySourceSearchResultInterfaceFactory $sourceResultInterfaceFactory
     */
    protected InventorySourceSearchResultInterfaceFactory $sourceResultInterfaceFactory;

    /**
     * @var CollectionProcessorInterface $collectionProcessor
     */
    protected CollectionProcessorInterface $collectionProcessor;

    /**
     * @param SourceModel $sourceModel
     * @param SourceCollectionFactory $sourceCollectionFactory
     * @param InventorySourceInterfaceFactory $sourceInterfaceFactory
     * @param InventorySourceSearchResultInterfaceFactory $sourceResultInterfaceFactory
     */
    public function __construct(
        SourceModel $sourceModel,
        SourceCollectionFactory $sourceCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        InventorySourceInterfaceFactory $sourceInterfaceFactory,
        InventorySourceSearchResultInterfaceFactory $sourceResultInterfaceFactory
    ) {
        $this->sourceModel = $sourceModel;
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceInterfaceFactory = $sourceInterfaceFactory;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->sourceResultInterfaceFactory = $sourceResultInterfaceFactory;
    }

    /**
     * Save
     *
     * @param InventorySourceInterface $inventorySource
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function save(InventorySourceInterface $inventorySource)
    {
        try {
            $this->sourceModel->save($inventorySource);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the transaction: %1',
                $exception->getMessage()
            ));
        }
        return $inventorySource;
    }

    /**
     * Get
     *
     * @param $entity_id
     * @return InventorySourceInterface
     * @throws NoSuchEntityException
     */
    public function get($entity_id)
    {
        $inventorySource = $this->sourceInterfaceFactory->create();
        $this->sourceModel->load($inventorySource, $entity_id, InventorySourceInterface::ENTITY_ID);
        if (!$inventorySource->getId()) {
            throw new NoSuchEntityException(__('Source with id "%1" does not exist.', $entity_id));
        }
        return $inventorySource;
    }

    /**
     * Get By Source Code
     *
     * @param string $source_code
     * @return InventorySourceInterface
     * @throws NoSuchEntityException
     */
    public function getBySourceCode(string $source_code)
    {
        $inventorySource = $this->sourceInterfaceFactory->create();
        $this->sourceModel->load($inventorySource, $source_code, InventorySourceInterface::SOURCE_CODE);
        if (!$inventorySource->getId()) {
            throw new NoSuchEntityException(__('Source with source code "%1" does not exist.', $source_code));
        }
        return $inventorySource;
    }

    /**
     * Get List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return InventorySourceSearchResultInterface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ) {
        $collection = $this->sourceCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->sourceResultInterfaceFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete
     *
     * @param InventorySourceInterface $inventorySource
     * @return mixed
     */
    public function delete(InventorySourceInterface $inventorySource)
    {
        try {
            $sourceModel = $this->sourceInterfaceFactory->create();
            $this->sourceModel->load($sourceModel, $inventorySource->getId());
            $this->sourceModel->delete($sourceModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete Token: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }
}
