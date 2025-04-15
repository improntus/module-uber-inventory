<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Model\Warehouse;

use Exception;
use Improntus\Uber\Api\WarehouseRepositoryInterface;
use Improntus\Uber\Helper\Data;
use Improntus\Uber\Model\OrganizationRepository;
use Improntus\Uber\Model\ResourceModel\Store\Collection as UberStoreCollection;
use Improntus\Uber\Model\StoreRepository;
use Improntus\UberInventory\Model\SourceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreRepository as StoreRepositoryInterface;

class SourceRepository implements WarehouseRepositoryInterface
{

    /**
     * @var SourceRepositoryInterface $sourceRepositoryInterface
     */
    protected SourceRepositoryInterface $sourceRepositoryInterface;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var OrganizationRepository $organizationRepository
     */
    protected OrganizationRepository $organizationRepository;

    /**
     * @var TimezoneInterface $timezone
     */
    protected TimezoneInterface $timezone;

    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var SourceItemRepositoryInterface
     */
    protected SourceItemRepositoryInterface $sourceItemRepository;

    /**
     * @var SourceFactory
     */
    protected SourceFactory $sourceFactory;

    /**
     * @var UberStoreCollection $uberStoreCollection
     */
    protected UberStoreCollection $uberStoreCollection;

    /**
     * @var StoreRepository $uberStoreRepository
     */
    protected StoreRepository $uberStoreRepository;

    /**
     * @var StoreRepositoryInterface
     */
    protected StoreRepositoryInterface $storeRepository;

    /** @var GetSourcesAssignedToStockOrderedByPriorityInterface */
    protected $getSourcesByPriority;

    /** @var GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite */
    protected GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite;

    /** @var WebsiteRepositoryInterface $websiteRepository */
    protected WebsiteRepositoryInterface $websiteRepository;

    /**
     * @param Data $helper
     * @param TimezoneInterface $timezone
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceRepositoryInterface $sourceRepositoryInterface
     * @param OrganizationRepository $organizationRepository
     * @param SourceFactory $sourceFactory
     * @param UberStoreCollection $uberStoreCollection
     * @param StoreRepository $uberStoreRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourceByPriority
     */
    public function __construct(
        Data                                                $helper,
        TimezoneInterface                                   $timezone,
        SearchCriteriaBuilder                               $searchCriteriaBuilder,
        SourceItemRepositoryInterface                       $sourceItemRepository,
        SourceRepositoryInterface                           $sourceRepositoryInterface,
        OrganizationRepository                              $organizationRepository,
        SourceFactory                                       $sourceFactory,
        UberStoreCollection                                 $uberStoreCollection,
        StoreRepository                                     $uberStoreRepository,
        StoreRepositoryInterface                            $storeRepository,
        WebsiteRepositoryInterface                          $websiteRepository,
        GetAssignedStockIdForWebsite                        $getAssignedStockIdForWebsite,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourceByPriority
    ) {
        $this->helper = $helper;
        $this->timezone = $timezone;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepositoryInterface = $sourceRepositoryInterface;
        $this->organizationRepository = $organizationRepository;
        $this->sourceFactory = $sourceFactory;
        $this->uberStoreCollection = $uberStoreCollection;
        $this->uberStoreRepository = $uberStoreRepository;
        $this->storeRepository = $storeRepository;
        $this->getSourcesByPriority = $getSourceByPriority;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * getAvailableSources
     *
     * Return warehouses that have stock to process the order
     *
     * @param int $storeId
     * @param array $cartItemsSku
     * @param string $countryId
     * @param $regionId
     * @return SourceInterface[]|null
     */
    public function getAvailableSources(int $storeId, array $cartItemsSku, string $countryId, $regionId): ?array
    {
        $itemsSkus = array_keys($cartItemsSku);
        $sourcesItemsSearchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $itemsSkus, 'in')
            ->create();
        $availableSources = $this->sourceItemRepository->getList($sourcesItemsSearchCriteria)->getItems();
        $sourcesByWebsite = $this->getSourcesByWebsite($storeId);
        $sourceCodes = [];
        $sourcesBySku = [];
        foreach ($availableSources as $source) {
            $sourceSku = $source->getSku();
            $sourceQuantity = $source->getQuantity();
            $sourceCode = $source->getSourceCode();

            // Add source status verification, because the item could have stock but is saved as out of stock status
            $sourceStatus = $source->getStatus();
            foreach ($cartItemsSku as $sku => $item) {
                if ($sourceStatus &&
                    $sourceQuantity >= $item &&
                    $sourceSku === (string)$sku &&
                    in_array($sourceCode, $sourcesByWebsite)
                ) {
                    $sourcesBySku[$sku][] = $sourceCode;
                }
            }
        }

        // Remove the sources that don't have all skus in stock
        foreach ($itemsSkus as $sku) {
            if (empty($sourceCodes)) {
                $sourceCodes = $sourcesBySku[$sku];
            } else {
                $sourceCodes = array_intersect($sourceCodes, $sourcesBySku[$sku]);
            }
        }

        $result = $this->getSourcesWithStock($countryId, $regionId ?: 0, $sourceCodes) ?: null;
        if (is_array($result)) {
            $this->helper->logDebug('Available Sources: ' . implode(',', array_keys($result)));
        } else {
            $this->helper->logDebug('Available Sources: none');
        }
        return $result;

    }

    /**
     * checkWarehouseWorkSchedule
     *
     * Return the available warehouse based on the delivery time.
     *
     * @param $warehouse
     * @param $deliveryTime
     * @return bool
     */
    public function checkWarehouseWorkSchedule($warehouse, $deliveryTime): bool
    {
        if ($warehouse->getIsPickupLocationActive()) {
            $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $day = strtolower($daysOfWeek[$this->timezone->date()->format('w')]);
            $openHour = $warehouse->getData("{$day}_open");
            $closeHour = $warehouse->getData("{$day}_close");
            $deliveryHour = $deliveryTime->format("H");
            // Check Waypoint Availability
            if ($deliveryHour >= $openHour && $deliveryHour <= $closeHour) {
                return true;
            }
        }
        return false;
    }

    /**
     * checkWarehouseClosest
     *
     * Return the nearest warehouse to the customer.
     *
     * @param array $uberStores
     * @param $warehouses
     * @return void
     */
    public function checkWarehouseClosest(array $uberStores, $warehouses)
    {
        // If there are no stores available on Uber, I leave directly
        if (!isset($uberStores['stores'])) {
            return null;
        }

        // Get ExternalId from UberStores
        $uberWarehouses = array_map(fn ($store) => $store['external_id'], $uberStores['stores']);

        // Generate array with Source Code
        $websiteSourcesAllowed = array_map(function ($warehouses) {
            return $warehouses->getSourceCode();
        }, $warehouses);

        /**
         * Get Sources by Uber Stores
         */
        $this->uberStoreCollection->getSelect()
            ->join(
                ["is" => "inventory_source"],
                'main_table.source_code = is.source_code'
            );
        $uberSources = $this->uberStoreCollection->addFieldToFilter("main_table.entity_id", ['in' => $uberWarehouses])
            ->addFieldToFilter("main_table.source_code", ['in' => $websiteSourcesAllowed])
            ->getItems();

        /**
         * Get Warehouse Closest
         *
         * I go through the Magento Waypoints and verify, if it corresponds to the first key of $uberWarehouses, it is the one closest to the client.
         * If NOT applicable, I store it in $alternativeWaypoint.
         * Then I determine which Warehouse / Waypoint to use.
         */
        $closestWarehouse = null;
        $alternativeWaypoint = null;
        $deliveryTimeLocal = $this->helper->getDeliveryTime();
        $showUberShipping = $this->helper->showUberShippingOBH();
        foreach ($uberSources as $uberStore) {
            if (in_array($uberStore->getId(), $uberWarehouses)) {
                /**
                 * Add additional information from Uber Inventory Source
                 */
                $populateUberInventorySource = $this->getWarehouse($uberStore->getSourceCode());
                if ($showUberShipping || $this->checkWarehouseWorkSchedule($populateUberInventorySource, $deliveryTimeLocal)) {
                    if ($populateUberInventorySource->getId() == $uberWarehouses[0]) {
                        $closestWarehouse = $populateUberInventorySource;
                        break;
                    } else {
                        // Alternative Waypoint
                        $alternativeWaypoint = $populateUberInventorySource;
                    }
                }
            }
        }
        return $closestWarehouse ?? $alternativeWaypoint;
    }

    /**
     * getWarehouseAddressData
     *
     * Returns json with the warehouse address
     *
     * @param $warehouse
     * @return mixed
     */
    public function getWarehouseAddressData($warehouse)
    {
        // Prepare Warehouse Address data
        $address = [
            'street_address' => [$warehouse->getStreet()],
            'city'           => $warehouse->getCity(),
            'state'          => $warehouse->getRegion(),
            'zip_code'       => $warehouse->getPostcode(),
            'country'        => $warehouse->getCountryId(),
        ];
        return json_encode($address, JSON_UNESCAPED_SLASHES);
    }

    /**
     * getWarehouseOrganization
     *
     * Return customerId (OrganizationId)
     *
     * @param $warehouse
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getWarehouseOrganization($warehouse)
    {
        // Get Warehouse full data
        $warehouse = $this->getWarehouse($warehouse->getSourceCode());
        $organizationId = $warehouse->getOrganizationId();
        if (str_contains($organizationId, 'W') !== false) {
            // Use ROOT Organization from Shipping Configuration
            [$letter, $websiteId] = explode('W', $organizationId);
            return $this->helper->getCustomerId($websiteId, 'website');
        }
        // Get from Organization
        $organizationModel = $this->organizationRepository->get($organizationId);
        if ($organizationModel->getId() === null) {
            throw new Exception(__("Warehouse Repository Missing Organization"));
        }
        return $organizationModel->getUberOrganizationId();
    }

    /**
     * getWarehouseId
     *
     * Return Source Code MSI
     *
     * @param $warehouse
     * @return mixed
     */
    public function getWarehouseId($warehouse)
    {
        return $warehouse->getSourceCode();
    }

    /**
     * getWarehouse
     *
     * Returns Source MSI information
     *
     * @param $warehouseId
     * @return mixed
     */
    public function getWarehouse($warehouseId)
    {
        try {
            $source = $this->sourceRepositoryInterface->get($warehouseId);
            $sourceData = $this->sourceFactory->create();
            $sourceData->load($source->getSourceCode(), 'source_code');
            foreach ($sourceData->getData() as $key => $value) {
                if (in_array($key, ['entity_id', 'source_code'])) {
                    continue;
                }
                $source->setData($key, $value);
            }
            return $source;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * getWarehousePickupData
     *
     * Returns json with the warehouse information
     *
     * @param $warehouse
     * @return mixed
     */
    public function getWarehousePickupData($warehouse)
    {
        $pickupData = [
            'pickup_phone_number'  => $warehouse->getPhone(),
            'pickup_name'          => $warehouse->getName(),
            'pickup_business_name' => $warehouse->getName(),
            'pickup_latitude'      => (float)$warehouse->getLatitude(),
            'pickup_longitude'     => (float)$warehouse->getLongitude(),
        ];

        // Set Pickup Address Data
        $pickupData['pickup_address'] = $this->getWarehouseAddressData($warehouse);

        // Set Pickup Notes
        if ($warehouse->getDescription() !== null) {
            $pickupData['pickup_notes'] = $warehouse->getDescription();
        }

        // Return Data
        return $pickupData;
    }

    /**
     * getSourcesWithStock
     *
     * Return warehouses with stock to ship
     *
     * @param string $countryId
     * @param $regionId
     * @param array $sourcesWithStock
     * @return SourceInterface[]
     */
    protected function getSourcesWithStock(string $countryId, $regionId, array $sourcesWithStock = [])
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_pickup_location_active', 1)
            ->addFilter('source_code', $sourcesWithStock, 'in')
            ->addFilter('country_id', $countryId)
            ->create();
        return $this->sourceRepositoryInterface->getList($searchCriteria)->getItems();
    }

    /**
     * Get Warehouse Store
     *
     * @param $warehouse
     * @return mixed
     */
    public function getWarehouseStore($warehouse)
    {
        $uberStore = $this->uberStoreRepository->getBySourceCode($warehouse->getSourceCode());
        if ($uberStore === null) {
            return false;
        }
        return $uberStore->getId();
    }

    /**
     * Get Sources MSI by Website
     *
     * @param $storeId
     * @return array
     */
    public function getSourcesByWebsite($storeId): array
    {
        $store = $this->storeRepository->getById($storeId);
        $website = $this->websiteRepository->getById($store->getWebsiteId());
        $stockId = $this->getAssignedStockIdForWebsite->execute($website->getCode());
        $websiteSources = $this->getSourcesByPriority->execute($stockId);
        if ($websiteSources === null) {
            return [];
        }

        return array_map(function ($websiteSource) {
            return $websiteSource->getSourceCode();
        }, $websiteSources);
    }

    /**
     * getWarehouseName
     * @param $warehouse
     * @return mixed
     */
    public function getWarehouseName($warehouse): string
    {
        return $warehouse->getName() ?: 'n/a';
    }
}
