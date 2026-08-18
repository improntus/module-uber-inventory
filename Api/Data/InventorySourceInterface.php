<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (http://www.improntus.com)
 */

namespace Improntus\UberInventory\Api\Data;

interface InventorySourceInterface
{
    public const ENTITY_ID = 'entity_id';
    public const SOURCE_CODE = 'source_code';
    public const ORGANIZATION_ID = 'organization_id';
    public const MONDAY_OPEN = 'monday_open';
    public const MONDAY_CLOSE = 'monday_close';
    public const TUESDAY_OPEN = 'tuesday_open';
    public const TUESDAY_CLOSE = 'tuesday_close';
    public const WEDNESDAY_OPEN = 'wednesday_open';
    public const WEDNESDAY_CLOSE = 'wednesday_close';
    public const THURSDAY_OPEN = 'thursday_open';
    public const THURSDAY_CLOSE = 'thursday_close';
    public const FRIDAY_OPEN = 'friday_open';
    public const FRIDAY_CLOSE = 'friday_close';
    public const SATURDAY_OPEN = 'saturday_open';
    public const SATURDAY_CLOSE = 'saturday_close';
    public const SUNDAY_OPEN = 'sunday_open';
    public const SUNDAY_CLOSE = 'sunday_close';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param int $id
     * @return mixed
     */
    public function setId(int $id);

    /**
     * @return mixed
     */
    public function getSourceCode();

    /**
     * @param string $sourceCode
     * @return mixed
     */
    public function setSourceCode(string $sourceCode);

    /**
     * @return string|null
     */
    public function getOrganizationId();

    /**
     * @param string $organizationId
     * @return mixed
     */
    public function setOrganizationId(string $organizationId);

    /**
     * @return mixed
     */
    public function getMondayOpen();

    /**
     * @param string $mondayOpen
     * @return mixed
     */
    public function setMondayOpen(string $mondayOpen);

    /**
     * @return mixed
     */
    public function getMondayClose();

    /**
     * @param string $mondayClose
     * @return mixed
     */
    public function setMondayClose(string $mondayClose);

    /**
     * @return mixed
     */
    public function getTuesdayOpen();

    /**
     * @param string $tuesdayOpen
     * @return mixed
     */
    public function setTuesdayOpen(string $tuesdayOpen);

    /**
     * @return mixed
     */
    public function getTuesdayClose();

    /**
     * @param string $tuesdayClose
     * @return mixed
     */
    public function setTuesdayClose(string $tuesdayClose);

    /**
     * @return mixed
     */
    public function getWednesdayOpen();

    /**
     * @param string $wednesdayOpen
     * @return mixed
     */
    public function setWednesdayOpen(string $wednesdayOpen);

    /**
     * @return mixed
     */
    public function getWednesdayClose();

    /**
     * @param string $wednesdayClose
     * @return mixed
     */
    public function setWednesdayClose(string $wednesdayClose);

    /**
     * @return mixed
     */
    public function getThursdayOpen();

    /**
     * @param string $thursdayOpen
     * @return mixed
     */
    public function setThursdayOpen(string $thursdayOpen);

    /**
     * @return mixed
     */
    public function getThursdayClose();

    /**
     * @param string $thursdayClose
     * @return mixed
     */
    public function setThursdayClose(string $thursdayClose);

    /**
     * @return mixed
     */
    public function getFridayOpen();

    /**
     * @param string $fridayOpen
     * @return mixed
     */
    public function setFridayOpen(string $fridayOpen);

    /**
     * @return mixed
     */
    public function getFridayClose();

    /**
     * @param string $fridayClose
     * @return mixed
     */
    public function setFridayClose(string $fridayClose);

    /**
     * @return mixed
     */
    public function getSaturdayOpen();

    /**
     * @param string $saturdayOpen
     * @return mixed
     */
    public function setSaturdayOpen(string $saturdayOpen);

    /**
     * @return mixed
     */
    public function getSaturdayClose();

    /**
     * @param string $sundayClose
     * @return mixed
     */
    public function setSaturdayClose(string $sundayClose);

    /**
     * @return mixed
     */
    public function getSundayOpen();

    /**
     * @param string $sundayOpen
     * @return mixed
     */
    public function setSundayOpen(string $sundayOpen);

    /**
     * @return mixed
     */
    public function getSundayClose();

    /**
     * @param string $sundayClose
     * @return mixed
     */
    public function setSundayClose(string $sundayClose);
}
