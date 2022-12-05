<?php

namespace App\Services\OriginConnector\Contracts;

use Illuminate\Support\Collection;

/**
 * Property interface for all system required property calls.
 */
interface PropertyContract {

    /**
     * Get all property groups or a specific
     * one by ID or Name.
     * @return Collection
     */
    public function getPropertyGroups($search = null): Collection;

    /**
     * Get default property info
     * @param int|string|null $propertyId
     * @return array
     */
    public function getPropertyInfo(int|string|null $propertyId): array;

    /**
     * Get all property ranks (player tiers, player ranks... that type of thing)
     * @return Collection
     */
    public function getPropertyRanks(): Collection;

    /**
     * Get all property transaction types
     *
     * @param int|string|null $search
     * @return Collection
     **/
    public function getPropertyTransactionTypes(int|string|null $search): Collection;

    /**
     * Get the points per dollar ratio from the property;
     *
     * @return float
     */
    public function getPropertyPointsPerDollar(): float;
}
