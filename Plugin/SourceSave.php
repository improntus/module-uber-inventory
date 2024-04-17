<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Plugin;

use Exception;
use Improntus\Uber\Api\Data\StoreInterfaceFactory;
use Improntus\Uber\Helper\Data;
use Improntus\Uber\Model\OrganizationRepository;
use Improntus\Uber\Model\StoreRepository as uberStoreRepository;
use Improntus\Uber\Model\Uber;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Serialize\SerializerInterface as Json;
use Magento\Inventory\Model\Source\Command\Save;
use Magento\InventoryApi\Api\Data\SourceInterface;

class SourceSave
{
    /**
     * @var Uber $uber
     */
    protected Uber $uber;

    /**
     * @var Encryptor $encryptor
     */
    protected Encryptor $encryptor;

    /**
     * @var Json $json
     */
    protected Json $json;

    /**
     * @var Data $helper;
     */
    protected Data $helper;

    /**
     * @var OrganizationRepository $organizationRepository
     */
    protected OrganizationRepository $organizationRepository;

    /**
     * @var uberStoreRepository $uberStore
     */
    protected uberStoreRepository $uberStore;

    /**
     * @var StoreInterfaceFactory $uberStoreInterfaceFactory
     */
    protected StoreInterfaceFactory $uberStoreInterfaceFactory;

    /**
     * @param Uber $uber
     * @param Json $json
     * @param Data $helper
     * @param Encryptor $encryptor
     * @param uberStoreRepository $uberStore
     * @param OrganizationRepository $organizationRepository
     * @param StoreInterfaceFactory $uberStoreInterfaceFactory
     */
    public function __construct(
        Uber $uber,
        Json $json,
        Data $helper,
        Encryptor $encryptor,
        uberStoreRepository $uberStore,
        OrganizationRepository $organizationRepository,
        StoreInterfaceFactory $uberStoreInterfaceFactory
    ) {
        $this->uber = $uber;
        $this->json = $json;
        $this->helper = $helper;
        $this->encryptor = $encryptor;
        $this->uberStore = $uberStore;
        $this->organizationRepository = $organizationRepository;
        $this->uberStoreInterfaceFactory = $uberStoreInterfaceFactory;
    }

    /**
     * @param Save $subject
     * @param mixed $result
     * @param SourceInterface $source
     * @return void
     * @throws Exception
     */
    public function afterExecute(Save $subject, $result, SourceInterface $source): void
    {
        // Is Valid Uber Source?
        if (!$source->getIsPickupLocationActive()) {
            return;
        }

        /**
         * Sync Source
         */
        $syncSource = true;

        /**
         * Generate Hash Validation
         */
        $storeHash = $this->generateStoreHash($source);

        /**
         * Get Store by Source Code
         */
        $uberStore = $this->uberStore->getBySourceCode($source->getSourceCode());
        if ($uberStore) {
            // Compare Hash
            $syncSource = $this->compareHash($storeHash, $uberStore->getHash());

            // Delete Current Store
            if ($syncSource) {
                try {
                    $this->uberStore->delete($uberStore);
                } catch (CouldNotSaveException|StateException $e) {
                    $this->helper->log('MSI Delete Source ' . $e->getMessage());
                }
            }
        }

        /**
         * Sync Store with Uber
         */
        if ($syncSource) {
            // Create new Store
            try {
                $uberStoreModel = $this->uberStoreInterfaceFactory->create();
                $uberStoreModel->setHash($storeHash);
                $uberStoreModel->setSourceCode($source->getSourceCode());
                $this->uberStore->save($uberStoreModel);

                // Get Entity
                $externalStoreId = $uberStoreModel->getId();

                // Create in Uber
                $addressData = json_encode([
                    'street_address' => [$source->getStreet()],
                    'city' => $source->getCity(),
                    'state' => $source->getRegion(),
                    'zip_code' => $source->getPostcode(),
                    'country' => $source->getCountryId(),
                ], JSON_UNESCAPED_SLASHES);

                $requestData = [
                    'pickup_address'    => $addressData,
                    'dropoff_address'   => $addressData,
                    'external_store_id' => $externalStoreId,
                ];

                // Send Request
                $organizationData = $this->getOrganization($source->getExtensionAttributes()->getOrganizationId());
                $organizationId = $organizationData['organizationId'];
                $storeResponse = $this->uber->getEstimateShipping($requestData, $organizationId, $organizationData['websiteId']);

                // Log Debug Mode
                $this->helper->logDebug(" === Uber Create Store MSI === ");
                $this->helper->logDebug("Organization ID / Customer ID: $organizationId");
                $this->helper->logDebug("Source Code: {$source->getSourceCode()}");
                $this->helper->logDebug("External Store ID: $externalStoreId");
                $this->helper->logDebug("Payload: " . json_encode($requestData));
                $this->helper->logDebug("Response: " . json_encode($storeResponse));
            } catch (CouldNotSaveException $e) {
                $this->helper->log('MSI CREATE Source in Uber ' . $e->getMessage());
            }
        }
    }

    /**
     * @return mixed|string
     * @throws Exception
     */
    private function getOrganization($organizationId)
    {
        if (str_contains($organizationId, 'W') !== false) {
            // Use ROOT Organization from Shipping Configuration
            [$letter, $websiteId] = explode('W', $organizationId);
            return ['organizationId' => $this->helper->getCustomerId($websiteId, 'website'), 'websiteId' => (int)$websiteId];
        }
        // Get from Organization
        $organizationModel = $this->organizationRepository->get($organizationId);
        if ($organizationModel->getId() === null) {
            throw new Exception(__("It was not possible to create the Waypoint in Uber. Try again later"));
        }
        return ['organizationId' => $organizationModel->getUberOrganizationId(), 'websiteId' => $organizationModel->getStoreId()];
    }

    /**
     * Compare Hash
     *
     * @param $newHash
     * @param $hash
     * @return bool
     */
    private function compareHash($newHash, $hash): bool
    {
        return ($newHash !== $hash);
    }

    /**
     * Generate Store Hash
     *
     * @param SourceInterface $source
     * @return string
     */
    private function generateStoreHash(SourceInterface $source): string
    {
        return $this->encryptor->hash("{$source->getStreet()}-{$source->getCity()}-{$source->getPostcode()}-{$source->getRegion()}-{$source->getCountryId()}");
    }
}
