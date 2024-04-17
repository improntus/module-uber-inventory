<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;

class SourceListOption implements OptionSourceInterface
{
    /**
     * @var SourceCollectionFactory $sourceCollectionFactory
     */
    protected SourceCollectionFactory $sourceCollectionFactory;

    /**
     * @param SourceCollectionFactory $sourceCollectionFactory
     */
    public function __construct(
        SourceCollectionFactory $sourceCollectionFactory
    ) {
        $this->sourceCollectionFactory = $sourceCollectionFactory;
    }

    /**
     * To option array function
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getSources();
    }

    /**
     * Get Sources function
     *
     * @return array
     */
    protected function getSources()
    {
        $result = [];
        $sourceCollection = $this->sourceCollectionFactory->create();
        $sourceCollection->addFieldToFilter('enabled', ['eq' => 1]);
        $sourceCollection->setOrder('name', 'ASC');
        foreach ($sourceCollection as $key => $source) {
            $result[] = ['value' => $source->getData('source_code'), 'label' => __($source->getData('name'))];
        }
        return $result;
    }
}
