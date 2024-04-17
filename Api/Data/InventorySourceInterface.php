<?php

/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2024 Improntus (http://www.improntus.com)
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
     * @return mixed
     */
    public function getOrganizationId();

    /**
     * @param int $organizationId
     * @return mixed
     */
    public function setOrganizationId(int $organizationId);

    /**
     * @return mixed
     */
    public function getMondayOpen();

    /**
     * @param int $mondayOpen
     * @return mixed
     */
    public function setMondayOpen(int $mondayOpen);

    /**
     * @return mixed
     */
    public function getMondayClose();

    /**
     * @param int $mondayClose
     * @return mixed
     */
    public function setMondayClose(int $mondayClose);

    /**
     * @return mixed
     */
    public function getTuesdayOpen();

    /**
     * @param int $tuesdayOpen
     * @return mixed
     */
    public function setTuesdayOpen(int $tuesdayOpen);

    /**
     * @return mixed
     */
    public function getTuesdayClose();

    /**
     * @param int $tuesdayClose
     * @return mixed
     */
    public function setTuesdayClose(int $tuesdayClose);

    /**
     * @return mixed
     */
    public function getWednesdayOpen();

    /**
     * @param int $wednesdayOpen
     * @return mixed
     */
    public function setWednesdayOpen(int $wednesdayOpen);

    /**
     * @return mixed
     */
    public function getWednesdayClose();

    /**
     * @param int $wednesdayClose
     * @return mixed
     */
    public function setWednesdayClose(int $wednesdayClose);

    /**
     * @return mixed
     */
    public function getThursdayOpen();

    /**
     * @param int $thursdayOpen
     * @return mixed
     */
    public function setThursdayOpen(int $thursdayOpen);

    /**
     * @return mixed
     */
    public function getThursdayClose();

    /**
     * @param int $thursdayClose
     * @return mixed
     */
    public function setThursdayClose(int $thursdayClose);

    /**
     * @return mixed
     */
    public function getFridayOpen();

    /**
     * @param int $fridayOpen
     * @return mixed
     */
    public function setFridayOpen(int $fridayOpen);

    /**
     * @return mixed
     */
    public function getFridayClose();

    /**
     * @param int $fridayClose
     * @return mixed
     */
    public function setFridayClose(int $fridayClose);

    /**
     * @return mixed
     */
    public function getSaturdayOpen();

    /**
     * @param int $saturdayOpen
     * @return mixed
     */
    public function setSaturdayOpen(int $saturdayOpen);

    /**
     * @return mixed
     */
    public function getSaturdayClose();

    /**
     * @param int $sundayClose
     * @return mixed
     */
    public function setSaturdayClose(int $sundayClose);

    /**
     * @return mixed
     */
    public function getSundayOpen();

    /**
     * @param int $sundayOpen
     * @return mixed
     */
    public function setSundayOpen(int $sundayOpen);

    /**
     * @return mixed
     */
    public function getSundayClose();

    /**
     * @param int $sundayClose
     * @return mixed
     */
    public function setSundayClose(int $sundayClose);
}
