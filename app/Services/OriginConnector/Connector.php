<?php

namespace App\Services\OriginConnector;

use App\Models\Player;
use App\Services\OriginConnector\Contracts\ConnectorContract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Connector implements ConnectorContract
{

    /**
     * @inheritDoc
     */
    public function cacheTag(): string
    {
        // TODO: Implement cacheTag() method.
    }

    /**
     * @inheritDoc
     */
    public function cacheId($appends): string
    {
        // TODO: Implement cacheId() method.
    }

    /**
     * @inheritDoc
     */
    public function uri($path = ''): string
    {
        // TODO: Implement uri() method.
    }

    /**
     * @inheritDoc
     */
    public function accountPointsName(): string
    {
        // TODO: Implement accountPointsName() method.
    }

    /**
     * @inheritDoc
     */
    public function accountCompsName(): string
    {
        // TODO: Implement accountCompsName() method.
    }

    /**
     * @inheritDoc
     */
    public function accountPromoName(): void
    {
        // TODO: Implement accountPromoName() method.
    }

    /**
     * @inheritDoc
     */
    function supportedFeaturesList(): array
    {
        // TODO: Implement supportedFeaturesList() method.
    }

    /**
     * @inheritDoc
     */
    public function connectionSettingsIndex()
    {
        // TODO: Implement connectionSettingsIndex() method.
    }

    /**
     * @inheritDoc
     */
    public function connectionSettingsUpdate(Request $request)
    {
        // TODO: Implement connectionSettingsUpdate() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayerGamingActivityReportGraph($extPlayerId, $days)
    {
        // TODO: Implement getPlayerGamingActivityReportGraph() method.
    }

    /**
     * @inheritDoc
     */
    public function apiVersion(): string
    {
        // TODO: Implement apiVersion() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayerFromSwipeId($swipeId): array
    {
        // TODO: Implement getPlayerFromSwipeId() method.
    }

    /**
     * @inheritDoc
     */
    public function validatePinNumber($playerId, $pin): bool
    {
        // TODO: Implement validatePinNumber() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayer($id): array
    {
        // TODO: Implement getPlayer() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayerRankId($id): int
    {
        // TODO: Implement getPlayerRankId() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayerAccounts($id): array
    {
        // TODO: Implement getPlayerAccounts() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayerAccountType($id, $accountTypeId): array
    {
        // TODO: Implement getPlayerAccountType() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayerPointsBalance($id): float
    {
        // TODO: Implement getPlayerPointsBalance() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayerPromoBalance($id): float
    {
        // TODO: Implement getPlayerPromoBalance() method.
    }

    /**
     * @inheritDoc
     */
    public function addPlayerPointsBalance($id, $amount, $description, $comment = null, Carbon $expiresAt = null): void
    {
        // TODO: Implement addPlayerPointsBalance() method.
    }

    /**
     * @inheritDoc
     */
    public function addPlayerPromoBalance($id, $amount, $description, $comment = null, Carbon $expiresAt = null): void
    {
        // TODO: Implement addPlayerPromoBalance() method.
    }

    /**
     * @inheritDoc
     */
    public function addPlayerAccountBalance($id, $accountId, $amount, $description, $comment = null, Carbon $expiresAt = null): void
    {
        // TODO: Implement addPlayerAccountBalance() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayerGroups($id): array
    {
        // TODO: Implement getPlayerGroups() method.
    }

    /**
     * @inheritDoc
     */
    public function getPlayerTracking($extPlayerId, Carbon $startDate = null, Carbon $endDate = null, string $statType = null): array
    {
        // TODO: Implement getPlayerTracking() method.
    }

    /**
     * @inheritDoc
     */
    public function convertPointsBalanceToDollars($rankId, $balance, $prefix = ''): string
    {
        // TODO: Implement convertPointsBalanceToDollars() method.
    }

    /**
     * @inheritDoc
     */
    public function updatePlayerProfile(Player $player, array $params = []): bool
    {
        // TODO: Implement updatePlayerProfile() method.
    }

    /**
     * @inheritDoc
     */
    public function getPropertyGroups($search = null): Collection
    {
        // TODO: Implement getPropertyGroups() method.
    }

    /**
     * @inheritDoc
     */
    public function getPropertyInfo($propertyId): array
    {
        // TODO: Implement getPropertyInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function getPropertyRanks(): Collection
    {
        // TODO: Implement getPropertyRanks() method.
    }

    /**
     * @inheritDoc
     */
    public function getPropertyTransactionTypes($search): Collection
    {
        // TODO: Implement getPropertyTransactionTypes() method.
    }

    /**
     * @inheritDoc
     */
    public function getPropertyPointsPerDollar(): float
    {
        // TODO: Implement getPropertyPointsPerDollar() method.
    }
}
