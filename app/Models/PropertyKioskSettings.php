<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use \Origin;
use App\Traits\LogsAllActivity;

use Illuminate\Database\Eloquent\Model;

class PropertyKioskSettings extends Model
{
    use LogsAllActivity;

    protected $guarded = [];
    protected $casts = [
        'play_audio' => 'boolean',
        'display_player_offers' => 'boolean',
        'display_bucket_balances' => 'boolean',
        'display_bucket_awards' => 'boolean',
    ];

    /**
     * Property relationship
     *
     * @return BelongsTo
     */
    protected function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Property badge Image
     *
     * @return BelongsTo
     */
    public function badgeImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'badge_image_id');
    }

    /**
     * Kiosk login background Image
     *
     * @return BelongsTo
     */
    public function loginImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'login_image_id');
    }

    /**
     * Kiosk dashboard background Image
     *
     * @return BelongsTo
     */
    public function backgroundImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'background_id');
    }

    /**
     * Kiosk Dashboard Background URL
     *
     * @return string|null
     */
    public function getBackgroundImageUrlAttribute(): ?string
    {
        return $this->backgroundImage->url ?? null;
    }

    /**
     * Kiosk Login Background Url
     *
     * @return string|null
     */
    public function getLoginImageUrlAttribute(): ?string
    {
        return $this->loginImage->url ?? null;
    }

    /**
     * Property badge URL
     *
     * @return string|null
     */
    public function getBadgeImageUrlAttribute(): ?string
    {
        return $this->badgeImage->url ?? null;
    }

    /**
     * Win Loss Mail Logo Image
     *
     * @return BelongsTo
     */

    public function winLossReportMailLogoImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'win_loss_report_mail_logo_id');
    }

    /**
     * Win Loss Mail Logo URL
     *
     * @return string|null
     */
    public function getWinLossReportMailLogoUrlAttribute(): ?string
    {

        return $this->winLossReportMailLogoImage->url ?? null;
    }

    /**
     * Accessor to return the array of tier icons stored
     * in the tier_icons_json field. If no icons
     * have been stored returns an empty array.
     *
     * @return Collection
     */
    public function getTierIconsAttribute(): Collection
    {
        return collect(json_decode($this->tier_icons_json) ?? [])->map(function ($image_id) {
            return Image::find($image_id);
        });
    }

    /**
     * Accessor to return the array of tier icons stored
     * in the tier_icons_json field. If no icons
     * have been stored returns an empty array.
     *
     * @return Collection
     */
    public function getTierIconUrlsAttribute(): Collection
    {
        $tier_icons = $this->tier_icons;

        return getActivePropertyRanks()
            ->map(function ($tier) use ($tier_icons) {
                $rtn = [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'image_url' => asset('assets/img/icon-crown.png'),
                    'image_set' => false,
                ];

                if ($tier_icon = $tier_icons->get($tier->id)) {
                    $rtn['image_url'] = $tier_icon->url;
                    $rtn['image_set'] = true;
                }

                return (object)$rtn;
            })->values();
    }

    /**
     * Store an Image ID for a specific Tier Icon.
     * Do this here to avoid the Indirect Modification
     * Of Overloaded Property Has No Effect exception
     * when using a mutator.
     *
     * @param int $tier_id
     * @param int $image_id
     */
    public function setTierIcon($tier_id, $image_id): void
    {
        $tier_icons = $this->tier_icons;
        $tier_icons->transform(function ($image) {
            return $image->id ?? null;
        })->put($tier_id, $image_id);

        $this->setTierIcons($tier_icons);
    }

    /**
     * Set the entire array of Tier Icons in one shot.
     * Do this to be consistent with other methoerd that
     * are not using a mutator to avoid throwing an
     * error.
     *
     * @param $icons
     */
    public function setTierIcons($icons): void
    {
        $this->tier_icons_json = json_encode($icons);
    }

    /**
     * Removes tier icon for given ID and deletes related Image object
     * @param $tier_id
     * @return bool
     */
    public function removeIconImage($tier_id): bool
    {
        $icons = $this->tier_icons;
        if ($image = $icons->get($tier_id)) {
            $image->delete();
            $this->setTierIcons($icons->forget($tier_id)->map(function ($image) {
                return $image->id;
            }));

            return $this->update();
        }

        return false;
    }

    /**
     * Return a collection of audio files keyed by action
     * including File object
     *
     * @return Collection
     */
    public function getAudioFilesAttribute(): Collection
    {
        return collect(json_decode($this->audio_files_json) ?? [])->map(function ($file_id) {
            return File::find($file_id);
        });
    }

    public function getAudioFileUrlsAttribute(): Collection
    {
        $audio_files = $this->audio_files;

        return collect(config('sounds.kiosk_actions'))
            ->map(function ($action, $key) use ($audio_files) {
                $rtn = [
                    'action' => $key,
                    'title' => $action['title'],
                    'file_url' => asset($action['default_uri']),
                    'file_set' => false,
                ];

                if ($audio_file = $audio_files->get($key)) {
                    $rtn['file_url'] = $audio_file->url;
                    $rtn['file_set'] = true;
                }

                return (object)$rtn;
            })->values();
    }

    /**
     * Store a File ID for a specific Action.
     * Do this here to avoid the Indirect Modification
     * Of Overloaded Property Has No Effect exception
     * when using a mutator.
     *
     * @param string $action
     * @param int $file_id
     */
    public function setAudioFile($action, $file_id): void
    {
        $audio_files = $this->audio_files;
        $audio_files->transform(function ($file) {
            return $file->id ?? null;
        })->put($action, $file_id);
        $this->setAudioFiles($audio_files);
    }

    /**
     * Set the entire collection of Audio Files in one shot.
     * Do this to be consistent with other methods that
     * are not using a mutator to avoid throwing an
     * error.
     *
     * @param /Illuminate/Support/Collection $audio_files
     */
    public function setAudioFiles($audio_files): void
    {
        $this->audio_files_json = json_encode($audio_files);
    }

    /**
     * Removes audio file for given action and deletes related File object
     * @param string $action
     * @return bool
     */
    public function removeAudioFile(string $action): bool
    {
        $audios = $this->audio_files;
        if ($file = $audios->get($action)) {
            $file->delete();
            $this->setAudioFiles($audios->forget($action)->map(function ($file) {
                return $file->id;
            }));

            return $this->update();
        }

        return false;
    }

    /**
     * Accessor to return the array of tier icons stored
     * in the default_promotion_images_json field. If no icons
     * have been stored returns an empty array.
     *
     * @return Collection
     */
    public function getDefaultPromotionImagesAttribute(): Collection
    {
        return collect(json_decode($this->default_promotion_images_json) ?? [])->map(function ($image_id) {
            return Image::find($image_id);
        });
    }

    /**
     * Accessor to return the array of tier icons stored
     * in the default_promotion_images_json field. If no icons
     * have been stored returns an empty array.
     *
     * @return Collection
     */
    public function getDefaultPromotionImageUrlsAttribute(): Collection
    {
        $default_promotion_images = $this->default_promotion_images;

        return PromotionType::whereNotIn('identifier', ['earnwin', 'swipewin', 'static'])->get()
            ->map(function ($promo_type) use ($default_promotion_images) {
                $rtn = [
                    'identifier' => $promo_type->identifier,
                    'name' => $promo_type->name,
                    'image_url' => null,
                    'image_set' => false,
                ];

                if ($default_image = $default_promotion_images->get($promo_type->identifier)) {
                    $rtn['image_url'] = $default_image->url;
                    $rtn['image_set'] = true;
                }

                return (object)$rtn;
            })->values();
    }

    /**
     * Store an Image ID for a specific Default Promotion Image.
     *
     * @param string $type_identifier
     * @param int    $image_id
     */
    public function setDefaultPromotionImage($type_identifier, $image_id): void
    {
        $default_promotion_images = $this->default_promotion_images;
        $default_promotion_images->transform(function ($image) {
            return $image->id ?? null;
        })->put($type_identifier, $image_id);

        $this->setDefaultPromotionImages($default_promotion_images);
    }

    /**
     * Set the entire array of Default Promotion Images in one shot.
     *
     * @param $images
     */
    public function setDefaultPromotionImages($images): void
    {
        $this->default_promotion_images_json = json_encode($images);
    }

    /**
     * Removes default promotion image given promotion type identifier and deletes related Image object
     * @param $type_identifier
     * @return bool
     */
    public function removeDefaultPromotionImage($type_identifier): bool
    {
        $images = $this->default_promotion_images;
        if ($image = $images->get($type_identifier)) {
            $image->delete();
            $this->setDefaultPromotionImages($images->forget($type_identifier)->map(function ($image) {
                return $image->id;
            }));

            return $this->update();
        }

        return false;
    }

    /**
     * Accessor to return the array of tier icons stored
     * in the promotion_backgrounds_json field. If no icons
     * have been stored returns an empty array.
     *
     * @return Collection
     */
    public function getPromotionBackgroundsAttribute(): Collection
    {
        return collect(json_decode($this->promotion_backgrounds_json) ?? [])->map(function ($image_id) {
            return Image::find($image_id);
        });
    }

    /**
     * Accessor to return the array of tier icons stored
     * in the promotion_backgrounds_json field. If no icons
     * have been stored returns an empty array.
     *
     * @return Collection
     */
    public function getPromotionBackgroundUrlsAttribute(): Collection
    {
        $promotion_backgrounds = $this->promotion_backgrounds;

        return PromotionType::whereIn('identifier', ['drawing', 'bonus'])->get()
            ->map(function ($promo_type) use ($promotion_backgrounds) {
                $rtn = [
                    'identifier' => $promo_type->identifier,
                    'name' => $promo_type->name,
                    'image_url' => null,
                    'image_set' => false,
                ];

                if ($promotion_background = $promotion_backgrounds->get($promo_type->identifier)) {
                    $rtn['image_url'] = $promotion_background->url;
                    $rtn['image_set'] = true;
                }

                return (object)$rtn;
            })->values();
    }

    /**
     * Store an Image ID for a specific Promotion Background.
     *
     * @param string $type_identifier
     * @param int    $image_id
     */
    public function setPromotionBackground($type_identifier, $image_id): void
    {
        $promotion_backgrounds = $this->promotion_backgrounds;
        $promotion_backgrounds->transform(function ($image) {
            return $image->id ?? null;
        })->put($type_identifier, $image_id);

        $this->setPromotionBackgrounds($promotion_backgrounds);
    }

    /**
     * Set the entire array of Promotion Backgrounds in one shot.
     *
     * @param $backgrounds
     */
    public function setPromotionBackgrounds($backgrounds): void
    {
        $this->promotion_backgrounds_json = json_encode($backgrounds);
    }

    /**
     * Removes promotion background given promotion type identifier and deletes related Image object
     * @param $type_identifier
     * @return bool
     */
    public function removePromotionBackground($type_identifier): bool
    {
        $backgrounds = $this->promotion_backgrounds;
        if ($background = $backgrounds->get($type_identifier)) {
            $background->delete();
            $this->setPromotionBackgrounds($backgrounds->forget($type_identifier)->map(function ($image) {
                return $image->id;
            }));

            return $this->update();
        }

        return false;
    }

    /**
     * Accessor to return the array of promotion icons stored
     * in the promotion_icons_json field. If no icons
     * have been stored returns an empty array.
     *
     * @return Collection
     */
    public function getPromotionIconsAttribute(): Collection
    {
        return collect(json_decode($this->promotion_icons_json) ?? [])->map(function ($image_id) {
            return Image::find($image_id);
        });
    }

    /**
     * Accessor to return the array of tier icons stored
     * in the promotion_icons_json field. If no icons
     * have been stored returns an empty array.
     *
     * @return Collection
     */
    public function getPromotionIconUrlsAttribute(): Collection
    {
        $promotion_icons = $this->promotion_icons;

        return PromotionType::whereIn('identifier', ['drawing', 'bonus'])->get()
            ->map(function ($promo_type) use ($promotion_icons) {
                $rtn = [
                    'identifier' => $promo_type->identifier,
                    'name' => $promo_type->name,
                    'image_url' => null,
                    'image_set' => false,
                ];

                if ($promotion_icon = $promotion_icons->get($promo_type->identifier)) {
                    $rtn['image_url'] = $promotion_icon->url;
                    $rtn['image_set'] = true;
                }

                return (object)$rtn;
            })->values();
    }

    /**
     * Store an Image ID for a specific Promotion icon.
     *
     * @param string $type_identifier
     * @param int    $image_id
     */
    public function setPromotionIcon($type_identifier, $image_id): void
    {
        $promotion_icons = $this->promotion_icons;
        $promotion_icons->transform(function ($image) {
            return $image->id ?? null;
        })->put($type_identifier, $image_id);

        $this->setPromotionIcons($promotion_icons);
    }

    /**
     * Set the entire array of Promotion icons in one shot.
     *
     * @param $icons
     */
    public function setPromotionIcons($icons): void
    {
        $this->promotion_icons_json = json_encode($icons);
    }

    /**
     * Removes promotion icon given promotion type identifier and deletes related Image object
     * @param $type_identifier
     * @return bool
     */
    public function removePromotionIcon($type_identifier): bool
    {
        $icons = $this->promotion_icons;
        if ($icon = $icons->get($type_identifier)) {
            $icon->delete();
            $this->setPromotionIcons($icons->forget($type_identifier)->map(function ($image) {
                return $image->id;
            }));

            return $this->update();
        }

        return false;
    }

    /**
     * Accessor to return the array of custom screen colors stored
     * in the background_colors_json field. If no icons
     * have been stored returns an empty array.
     *
     * @return Collection
     */
    public function getBackgroundColorsAttribute()
    {
        return collect(json_decode($this->background_colors_json) ?? []);
    }

    /**
     * Store a color hex code for a specific promotion type
     *
     * @param string $type_identifier
     * @param string $color
     */
    public function setBackgroundColor($type_identifier, $color): void
    {
        $background_colors = $this->background_colors->toArray();
        $background_colors[$type_identifier] = $color;
        $this->background_colors_json = json_encode($background_colors);
    }

    /**
     * Replaces image & json fields with convenient url & array values for kiosk consumption.
     */
    public function prepareForKiosk(): void
    {
        $this->append([
            'badge_image_url', 'background_image_url', 'login_image_url',
            'tier_icon_urls', 'audio_file_urls', 'win_loss_report_mail_logo_url',
            'default_promotion_image_urls', 'promotion_background_urls',
            'promotion_icon_urls', 'background_colors'
        ]);
        $this->hidden = [
            'badge_image', 'badge_images_json', 'background_image',
            'background_images_json', 'login_image', 'tier_icons_json',
            'audio_files_json', 'win_loss_report_mail_logo_image',
            'win_loss_report_mail_logo_id', 'default_promotion_images_json',
            'promotion_backgrounds_json', 'promotion_icons_json',
            'background_colors_json'
        ];

        $this->play_audio = $this->property->enable_kiosk_audio && $this->play_audio;
        $this->display_win_loss_report = !!$this->property->reportServerSettings->url && $this->display_win_loss_report;
        $this->display_email_win_loss_report = !!$this->property->mailServerSettings->host;

    }

}
