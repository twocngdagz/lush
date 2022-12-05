<?php

/**
 * The SupportedFeaturesTrait trait is responsible for returning a
 * list of supported features the application uses to include or
 * exclude functionality based on the capabilities of the CMS.
 */

namespace App\Services\OriginConnector\Providers\PhiMock\Traits;

trait SupportedFeaturesTrait
{
    function supportedFeaturesList()
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
                    'free-play' => true,
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
                'player-card-reprint' => true,
                'player-id-scanner' => true,
                'exclude-dap' => true,
                'win-loss-report' => true,
                'tier-progress-screen' => [
                    'show' => true,
                    'account' => 'points',
                    'label' => 'points',
                ],
                'player-enrollment-print-card' => true
            ],
            'reporting' => [],
            'user' => [],
            'setting' => [
                'points-per-dollar' => false,
                'comps-per-dollar' => false,
            ],
            'misc' => []
        ];
    }
}
