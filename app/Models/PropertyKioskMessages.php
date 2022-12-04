<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use \Origin;
use App\Traits\LogsAllActivity;

use Illuminate\Database\Eloquent\Model;

class PropertyKioskMessages extends Model
{
    use LogsAllActivity;

    protected $fillable = ['property_id', 'messages_json'];
    protected $appends = ['messages'];

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
     * Accessor to return the array of custom messages stored in the
     * messages_json field. If no messages have been stored returns
     * a default array.
     *
     * @return Collection
     */
    public function getMessagesAttribute(): Collection
    {
        return collect(json_decode($this->messages_json) ?? [
                "card_read_warning" => "Trouble reading your card. Please carefully insert and remove your card to try again.",
                "card_read_failure" => "Trouble reading your card. Please visit the Player's Club for assistance.",
            ]);
    }

    /**
     * Store a custom message
     *
     * @param string $message_identifier
     * @param string $message_text
     */
    public function setMessage($message_identifier, $message_text): void
    {
        $messages = $this->messages->toArray();
        $messages[$message_identifier] = $message_text;
        $this->messages_json = json_encode($messages);
    }

}
