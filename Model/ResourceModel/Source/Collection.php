<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Model\ResourceModel\Source;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Improntus\UberInventory\Model\Source;

class Collection extends AbstractCollection
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Source::class,
            \Improntus\UberInventory\Model\ResourceModel\Source::class
        );
    }
}
