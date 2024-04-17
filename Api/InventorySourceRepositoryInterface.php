<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Api;

use Improntus\UberInventory\Api\Data\InventorySourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;

interface InventorySourceRepositoryInterface
{
    /**
     * Save
     *
     * @param InventorySourceInterface $inventorySource
     * @return mixed
     */
    public function save(InventorySourceInterface $inventorySource);

    /**
     * @param $entity_id
     * @return InventorySourceInterface
     * @throws NoSuchEntityException
     */
    public function get($entity_id);

    /**
     * @param string $source_code
     * @return InventorySourceInterface
     * @throws NoSuchEntityException
     */
    public function getBySourceCode(string $source_code);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return InventorySourceInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param InventorySourceInterface $inventorySource
     * @return mixed
     */
    public function delete(InventorySourceInterface $inventorySource);
}
