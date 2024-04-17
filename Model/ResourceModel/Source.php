<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Model\ResourceModel;

use Improntus\Uber\Model\ResourceModel\AbstractModel;

class Source extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('uber_inventory_source', 'entity_id');
    }
}
