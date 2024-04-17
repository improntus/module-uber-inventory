<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface InventorySourceSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return mixed
     */
    public function getItems();

    /**
     * @param array $items
     * @return mixed
     */
    public function setItems(array $items);
}