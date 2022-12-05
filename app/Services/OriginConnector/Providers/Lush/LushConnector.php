<?php

namespace App\Services\OriginConnector\Providers\Lush;

use App\Models\AccountConnectorSettings;
use App\Models\Property;
use App\Services\OriginConnector\Providers\Lush\Models\LushPlayer;
use App\Services\OriginConnector\Connector;
use App\Services\RealWinSolution\Traits\RealWinPlayerEarningTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Origin;

class LushConnector extends Connector
{
    use Traits\PlayerTrait;
    use Traits\PropertyTrait;
    use Traits\PlayerEarningTrait;
    use Traits\PlayerRedemptionTrait;
    use RealWinPlayerEarningTrait;

    /**
     * @inheritDoc
     */
    public function cacheId(string $appends): string
    {
        return 'origin::lush' . $appends;
    }

    /**
     * @inheritDoc
     */
    public function accountPointsName(): string
    {
        return 'Points';
    }

    /**
     * @inheritDoc
     */
    public function accountCompsName(): string
    {
        return 'Comps';
    }

    /**
     * @inheritDoc
     */
    public function accountPromoName(): string
    {
        return 'Promo';
    }

    /**
     * @inheritDoc
     */
    function supportedFeaturesList(): array
    {
        return [
            'global' => [
                'multi-site' => false,
                'exclude-dap' => true,
                'local-property-groups' => false,
                'local-player-tiers' => false,
                'account-balances' => [
                    'points' => true,
                    'tier-points' => false,
                    'free-play' => false,
                    'comps' => true,
                    'bucket-awards' => false,
                    'earned-today' => [
                        'points' => true,
                        'tier-points' => false,
                    ],
                ],
                'redemption-accounts' => [
                    'points' => true,
                    'promo' => false,
                ],
                'loyalty-offers' => [
                    'loyalty-offers' => false,
                    'remove-offer-from-kiosk-once-redeemed' => false,
                    'show-available-offer-balance' => false,
                ],
                'bucket-awards' => [
                    'bucket-awards' => false,
                ],
                'earning-methods' => [
                    'slot' => true,
                    'pit' => true,
                    'pit-slot' => true,
                    'poker' => config('origin.use_local_ratings', false), // Supported via local ratings only
                    'retail' => false,
                    'hotel' => false,
                ],
                'earning-ratings' => [
                    'points-earned' => true,
                    'cash-in' => true,
                    'theo-win' => true,
                    'actual-win' => true,
                    'tier-points-earned' => false,
                    'comp-earned' => false,
                ],
                'table-ratings' => true,
                'non-gaming-ratings' => true,
                'duetto-integration' => true,
                'onelink-integration' => [
                    'onelink-integration' => true,
                    'castnet-api' => true,
                    'display-drawing-winners' => true,
                    'display-drawing-countdowns' => true,
                ],
            ],
            'promotion' => [
                'all' => [
                    'criteria' => true,
                    'criteria-types' => [
                        'tier-points-since-enrollment' => false,
                        'points-earned' => true,
                        'comps-earned' => true,
                    ],
                    'earning-periods' => true,
                    'invited-guest-list' => true,
                    'excluded-guest-list' => true,
                    'employee-access' => false,
                    'reward-types' => [
                        'points' => true,
                        'tier-points' => false,
                        'promo' => true,
                        'prize' => true,
                        'food-coupon' => true,
                        'gift' => true,
                        'comp' => true,
                        'manual-drawing-entry' => true,
                        'misc' => true,
                        'bucket-awards' => false,
                        'virtual-drawing-entry' => true,
                        'no-prize' => true,
                        'offer-prize' => false,
                        'inventory-item' => true,
                    ],
                    'can-copy-promotions' => true,
                    'add-player-after-promotion-start' => true,
                    'quick-lookup-for-player-in-promotion' => true,
                ],
                'type' => [
                    'drawing' => true,
                    'earnwin' => true,
                    'swipewin' => true,
                    'pickem' => true,
                    'bonus' => true,
                    'game' => true,
                    'static' => true,
                ],
                'drawing' => [
                    'multiple-earning-types' => true,
                    'multiple-drawing-events' => true,
                    'earnings-by-location' => true,
                    'submission-by-location' => true,
                    'event-included-guest-list' => true,
                    'event-excluded-guest-list' => true,
                    'auto-submit-entries' => false,
                    'auto-submit-entries-on-card-in' => false,
                    'auto-submit-entries-on-rating' => false,
                    'auto-submit-free-entries' => false,
                    'auto-submit-free-entries-by-tier' => false,
                    'gift-free-entries-by-tier' => true,
                    'recurring-earning-periods' => true,
                    'recurring-submission-periods' => true,
                    'activate-submissions-mins-before-drawing' => true,
                    'recurring-drawing-events' => true,
                    'multiple-rewards-per-drawing-event' => true,
                    'limit-rewards' => [
                        'per-promotion' => true,
                        'per-day' => true,
                        'per-drawing' => true,
                        'per-tier' => true,
                    ],
                    'entry-rollover' => true,
                    'redraw-player-if-multiple-winners-in-one-drawing' => true,
                    'guests-can-choose-drawing-to-add-entries-into' => true,
                    'guests-can-divide-entries-and-choose-multiple-drawings' => true,
                ],
                'earn-and-get' => [
                    'tier-credit-threshold' => true,
                    'can-add-rewards-after-promotion-start' => true,
                    'player-earning-restrictions' => true,
                    'repeating-events' => true,
                    'reward-variety' => [
                        'printed-drawing-voucher' => true,
                    ],
                ],
                'kiosk-games' => [
                    'vgt-game-themes' => true,
                    'can-add-rewards-after-promotion-start' => true,
                    'reward-variety' => [
                        'no-reward' => true,
                    ],
                ],
                'swipe-and-win' => [],
            ],
            'player' => [
                'import-ratings' => false,
                'ext-id-validation' => 'numeric',
                'ext-id-validation-message' => ['numeric' => 'Invalid Player ID format.'],
                'enrollment' => true,
            ],
            'kiosk' => [
                'player-contact-info-show' => true,
                'player-contact-info-edit' => true,
                'player-reset-pin' => true,
                'player-card-reprint' => false,
                'player-id-scanner' => true,
                'exclude-dap' => true,
                'win-loss-report' => true,
                'tier-progress-screen' => [
                    'show' => true,
                    'account' => 'points',
                    'label' => 'points',
                ],
                'player-enrollment-print-card' => false,
                'player-profile' => [
                    'hide-address' => true,
                    'hide-display-bucket-balances' => true,
                ]

            ],
            'reporting' => [],
            'user' => [],
            'setting' => [
                'points-per-dollar' => false,
                'comps-per-dollar' => false,
                'player-email-optin-message' => true,
                'player-phone-optin-message' => true,
            ],
            'misc' => []
        ];
    }

    /**
     * @inheritDoc
     */
    public function connectionSettingsIndex(): View
    {
        $account = auth()->user()->account;

        $properties = collect([]);
        $selectedProperty = Property::first();
        $canGetPlayer = parent::canGetPlayer($account->connectorSettings->settings['connection_test_player_id']);
        $canGetPlayerAccounts = parent::canGetPlayerAccounts($account->connectorSettings->settings['connection_test_player_id']);
        $originProperty = parent::canGetPropertyInfo();

        return view('settings.connection.index',
            compact('account', 'properties', 'canGetPlayer', 'canGetPlayerAccounts', 'originProperty', 'selectedProperty'));
    }

    /**
     * @inheritDoc
     */
    public function connectionSettingsUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Test player
            'connection_test_player_id' => 'nullable',

            // Phi Mock
            'lush_api_url' => 'required',
            'lush_api_key' => 'required',
        ]);

        // If update test player button pressed
        if ($request->update_player) {
            try {
                Origin::getPlayer($request->connection_test_player_id);
            } catch (\Exception $e) {
                return redirect()->route('settings.index')->with('app-error',
                    'We could not establish a connection with this player ID. It could be incorrect or the property may not be configured.');
            }
        }

        $account = auth()->user()->account;

        // We create a new account_connector_settings row each time we update
        // rather than update the same row so that we have a audit trail
        // of what was updated and who did the updating.
        AccountConnectorSettings::create([
            'account_id' => $account->id,
            'user_id' => auth()->user()->id,
            'settings' => [
                'lush_api_url' => $data['lush_api_url'],
                'lush_api_key' => $data['lush_api_key'],
                'connection_test_player_id' => $data['connection_test_player_id'],
            ]
        ]);

        return redirect()->route('settings.index')
            ->with('app-success', 'Your connection settings have been updated.');
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
        return '1.0.0';
    }

    /**
     * @inheritDoc
     */
    public function getPlayerRankId(int $id): int
    {
        $player = LushPlayer::findOrFail($id);
        return $player->lushrank->id;
    }


/**
* Returns an array of buckets available via the Mock CMS connector.
* @param bool $name
* @return array|mixed|null
*/
    public function balanceDisplayOptions(bool $name = false): mixed
    {
        $options = [
            [
                'internal_identifier' => 'points',
                'identifier' => 'points',
                'show_on_kiosk' => true,
                'label' => 'Points',
                'currency' => false,
                'decimals' => 0,
                'sort' => 0,
            ],
            [
                'internal_identifier' => 'points-earned-today',
                'identifier' => 'points_earned_today',
                'show_on_kiosk' => true,
                'label' => 'Points Earned Today',
                'currency' => false,
                'decimals' => 0,
                'sort' => 1,
            ],
            [
                'internal_identifier' => 'comps',
                'identifier' => 'comps',
                'show_on_kiosk' => true,
                'label' => 'Comps',
                'currency' => true,
                'decimals' => 2,
                'sort' => 2,
            ],
            [
                'internal_identifier' => 'comps-earned-today',
                'identifier' => 'comps_earned_today',
                'show_on_kiosk' => true,
                'label' => 'Comps Earned Today',
                'currency' => true,
                'decimals' => 2,
                'sort' => 3,
            ],
            [
                'internal_identifier' => 'promo',
                'identifier' => 'promo',
                'show_on_kiosk' => true,
                'label' => 'Promo',
                'currency' => true,
                'decimals' => 2,
                'sort' => 4,
            ],
            [
                'internal_identifier' => 'promo-earned-today',
                'identifier' => 'promo_earned_today',
                'show_on_kiosk' => true,
                'label' => 'Promo Earned Today',
                'currency' => true,
                'decimals' => 2,
                'sort' => 4,
            ],
        ];

        if (!$name) {
            return $options;
        }

        return collect($options)->keyBy('identifier')->get($name, null);
    }
}
