<?php

namespace App\Services\Promotions\DataTransferObject;

use App\Casts\CarbonCast;
use App\Enums\PromotionStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use phpDocumentor\Reflection\Types\ClassString;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class CreatePromotionData extends Data
{
    public function __construct(
        public string $name,
        public string|Optional $subtitle,
        public string|Optional $description,
        public array|Optional $properties,
        public int|Optional $kiosks,
        public bool|Optional $available_all_players,
        public bool|Optional $has_criteria,
        public int|Optional $criteria_birth_month,
        public int|Optional $criteria_minimum_point_balance,
        public int|Optional $criteria_minimum_age,
        public int|Optional $criteria_maximum_age,
        public int|Optional $criteria_gender,
        public int|Optional $criteria_points_earned,
        public bool|Optional $criteria_new_player,
        public int|Optional $criteria_comp_earned,
        public int|Optional $criteria_max_days_since_enrollment,
        public int|Optional $criteria_minimum_player_rank_id,
        public int|Optional $criteria_maximum_player_rank_id,
        public bool|Optional $bounce_back_previous_promotion,
        public Carbon|Optional $bounce_back_starts_at,
        public Carbon|Optional $bounce_back_starts_at_time,
        public Carbon|Optional $bounce_back_ends_at,
        public Carbon|Optional $bounce_back_ends_at_time,
        public array|Optional $restricted_groups,
        public string|Optional $rules,
        public UploadedFile|Optional $card,
        #[WithCast(CarbonCast::class)]
        public Carbon|Optional $deactivated_at,
        #[WithCast(EnumCast::class, type: PromotionStatus::class)]
        public PromotionStatus $active,
    )
    {

    }
}
