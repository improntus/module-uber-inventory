<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Plugin;

use Improntus\UberInventory\Model\InventorySourceRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryAdminUi\Ui\DataProvider\SourceDataProvider as Subject;

class SourceDataProvider
{
    /**
     * @var InventorySourceRepository $inventorySourceRepository
     */
    protected InventorySourceRepository $inventorySourceRepository;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param InventorySourceRepository $inventorySourceRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        InventorySourceRepository $inventorySourceRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->inventorySourceRepository = $inventorySourceRepository;
    }

    /**
     * After Get Data
     *
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetData(Subject $subject, array $result)
    {
        if (!$result || isset($result['items'])) {
            return $result;
        }
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('source_code', ['in' => array_keys($result)])
            ->create();
        $collection = $this->inventorySourceRepository->getList($searchCriteria)->getItems();
        $sourcesData = [];
        foreach ($collection as $source) {
            $sourcesData[$source->getData('source_code')] = $source->getData();
        }
        foreach ($result as $sourceCode => &$tabs) {
            if (isset($sourcesData[$sourceCode]) && is_array($tabs)) {
                $data = $sourcesData[$sourceCode];
                unset($data['entity_id']);
                unset($data['source_code']);
                $currentAttributes = $tabs['general']['extension_attributes'] ?? [];
                $tabs['general']['extension_attributes'] = array_merge(
                    is_array($currentAttributes) ? $currentAttributes : [],
                    $data
                );
            }
        }
        return $result;
    }
}
