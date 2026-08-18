<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Model;

/**
 * Backward-compatible alias of InventorySource.
 *
 * Kept so existing references to this class and to its generated SourceFactory keep working while
 * the canonical implementation (and the InventorySourceInterface contract) lives in InventorySource.
 */
class Source extends InventorySource
{
}
