<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Plugin\Inventory\Model;

use Improntus\UberInventory\Model\Source;
use Improntus\UberInventory\Model\SourceFactory;
use Magento\Framework\App\RequestInterface;
use Magento\InventoryApi\Api\Data\SourceExtensionFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class SourceRepositoryPlugin
{
    /**
     * @var SourceExtensionFactory $extensionFactory
     */
    protected $extensionFactory;

    /**
     * @var SourceFactory $sourceFactory
     */
    protected SourceFactory $sourceFactory;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @param SourceExtensionFactory $extensionFactory
     * @param SourceFactory $sourceFactory
     * @param RequestInterface $request
     */
    public function __construct(
        SourceExtensionFactory $extensionFactory,
        SourceFactory          $sourceFactory,
        RequestInterface       $request
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->sourceFactory = $sourceFactory;
        $this->request = $request;
    }

    public function beforeSave(
        SourceRepositoryInterface $subject,
        SourceInterface           $source
    ) {
        $generalTab = $this->request->getParam('general');
        $extensionAttributes = $generalTab['extension_attributes'] ?? [];
        /** @var Source $sourceData */
        $sourceData = $this->sourceFactory->create();
        $sourceData->load($source->getSourceCode(), 'source_code');
        $sourceData->setData('source_code', $source->getSourceCode());
        foreach ($extensionAttributes as $attribute => $value) {
            $sourceData->setData($attribute, $value);
        }
        $sourceData->save();

        return [$source];
    }
}
