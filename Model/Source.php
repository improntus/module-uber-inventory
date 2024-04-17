<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Model;

use Improntus\UberInventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Framework\Model\AbstractModel;

class Source extends AbstractModel
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
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SourceResourceModel::class);
    }
}
