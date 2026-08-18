<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (http://www.improntus.com)
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
     * Keys redacted from a logged Uber response.
     *
     * Mirrors Improntus\Uber\Model\Uber::RESPONSE_REDACT_KEYS, which is private and therefore not
     * reachable from here.
     *
     * @var string[]
     */
    private const RESPONSE_REDACT_KEYS = [
        'access_token',
        'tracking_url',
        'document',
        'image_url',
        'signature',
        'signer_name',
        'signer_relationship',
        'proof_of_delivery',
        'verification',
        'dropoff',
        'pickup',
        'courier',
        'point_of_contact',
        'dropoff_name',
        'dropoff_phone_number',
        'dropoff_address',
        'pickup_name',
        'pickup_phone_number',
        'pickup_address',
        'phone_number',
        'phone_details',
        'email',
    ];

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
     */
    public function afterExecute(Save $subject, $result, SourceInterface $source): void
    {
        // Is Valid Uber Source?
        if (!$source->getIsPickupLocationActive()) {
            return;
        }

        try {
            $this->syncSourceWithUber($source);
        } catch (\Throwable $e) {
            // A carrier problem must never abort the core MSI source save.
            $this->helper->log('MSI CREATE Source in Uber ' . $e->getMessage());
        }
    }

    /**
     * Keep the Uber store mapping of the source in sync with its address.
     *
     * @param SourceInterface $source
     * @return void
     * @throws Exception
     */
    private function syncSourceWithUber(SourceInterface $source): void
    {
        $extensionAttributes = $source->getExtensionAttributes();
        $organizationId = $extensionAttributes === null ? null : $extensionAttributes->getOrganizationId();
        if ($organizationId === null || $organizationId === '') {
            // The organization is only mandatory in the admin form; REST/CLI/import saves may omit it.
            $this->helper->log("MSI Source {$source->getSourceCode()} has no Uber organization, skipping sync");
            return;
        }

        /**
         * Generate Hash Validation
         */
        $storeHash = $this->generateStoreHash($source);

        /**
         * Get Store by Source Code
         */
        $currentUberStore = $this->uberStore->getBySourceCode($source->getSourceCode());
        if ($currentUberStore && !$this->compareHash($storeHash, $currentUberStore->getHash())) {
            // Address unchanged, the existing mapping is still valid
            return;
        }

        /**
         * Create the new mapping BEFORE removing the previous one, so a failed registration can
         * never leave the source without an Uber mapping.
         */
        $uberStoreModel = $this->uberStoreInterfaceFactory->create();
        $uberStoreModel->setHash($storeHash);
        $uberStoreModel->setSourceCode($source->getSourceCode());
        $this->uberStore->save($uberStoreModel);

        // Get Entity
        $externalStoreId = $uberStoreModel->getId();

        try {
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
                'external_store_id' => $externalStoreId
            ];

            // Send Request
            $organizationData = $this->getOrganization($organizationId);
            $uberOrganizationId = $organizationData['organizationId'];
            $storeResponse = $this->uber->getEstimateShipping(
                $requestData,
                $uberOrganizationId,
                $organizationData['websiteId']
            );

            // Log Debug Mode
            $this->helper->logDebug(" === Uber Create Store MSI === ");
            $this->helper->logDebug("Organization ID / Customer ID: $uberOrganizationId");
            $this->helper->logDebug("Source Code: {$source->getSourceCode()}");
            $this->helper->logDebug("External Store ID: $externalStoreId");
            $this->helper->logDebug("Payload: " . json_encode($this->sanitizeRequestData($requestData)));
            $this->helper->logDebug("Response: " . json_encode($this->sanitizeResponse($storeResponse)));
        } catch (\Throwable $e) {
            // Compensating write: drop the half-created mapping and keep the previous one.
            $this->deleteUberStore($uberStoreModel, 'MSI Rollback Source in Uber ');
            throw $e;
        }

        // Registration succeeded, the previous mapping can be dropped safely.
        if ($currentUberStore) {
            $this->deleteUberStore($currentUberStore, 'MSI Delete Source ');
        }
    }

    /**
     * @param mixed $uberStore
     * @param string $logPrefix
     * @return void
     */
    private function deleteUberStore($uberStore, string $logPrefix): void
    {
        try {
            $this->uberStore->delete($uberStore);
        } catch (CouldNotSaveException|StateException $e) {
            $this->helper->log($logPrefix . $e->getMessage());
        }
    }

    /**
     * @param $organizationId
     * @return array
     * @throws Exception
     */
    private function getOrganization($organizationId): array
    {
        if ($organizationId === null || $organizationId === '') {
            throw new Exception(__("It was not possible to create the Waypoint in Uber. Try again later"));
        }
        $organizationId = (string)$organizationId;
        if (str_contains($organizationId, 'W')) {
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
     * Redact the source address before the payload reaches the log.
     *
     * @param array $requestData
     * @return array
     */
    private function sanitizeRequestData(array $requestData): array
    {
        $sanitized = $requestData;
        foreach (['pickup_address', 'dropoff_address'] as $addressKey) {
            if (isset($sanitized[$addressKey])) {
                $sanitized[$addressKey] = '***REDACTED***';
            }
        }
        return $sanitized;
    }

    /**
     * Recursively redact secrets and PII from an Uber response before it reaches the log.
     *
     * @param mixed $responseBody
     * @return mixed
     */
    private function sanitizeResponse($responseBody)
    {
        if (!is_array($responseBody)) {
            return $responseBody;
        }

        foreach ($responseBody as $key => $value) {
            if (is_string($key) && in_array($key, self::RESPONSE_REDACT_KEYS, true)) {
                $responseBody[$key] = '***REDACTED***';
                continue;
            }
            if (is_array($value)) {
                $responseBody[$key] = $this->sanitizeResponse($value);
            }
        }

        return $responseBody;
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
