<?php

namespace App\Models;


use App\Traits\LogsAllActivity;
use App\Services\TicketPrinter\TicketPrinterFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TicketPrinter extends Model
{
    use LogsAllActivity;

    protected $fillable = ['name', 'description', 'ticket_printer_type_id', 'kiosk_id', 'property_id', 'connection_info'];
    protected $casts = ['connection_info' => 'array'];

    private $ticketPrinterAdapter = null;

    public function getConnectionInfoAttribute($connectionInfo)
    {
        return json_decode($connectionInfo);
    }

    public function ticketPrinterType(): HasOne
    {
        return $this->hasOne(TicketPrinterType::class, 'id', 'ticket_printer_type_id');
    }

    public function kiosk()
    {
        return $this->belongsTo(Kiosk::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Create and/or Return an instance of the TicketPrinterAdapter
     * type for this model instance.
     *
     * @return App\Services\TicketPrinter\TicketPrinter
     */
    public function adapter()
    {
        if (null === $this->ticketPrinterAdapter) {
            try {
                $this->ticketPrinterAdapter = TicketPrinterFactory::make($this);
            } catch (\Exception $e) {
                \Log::error("Unable to create TicketPrinterAdapter for Ticket Printer ({$this->id}) {$this->name}.");
                $this->ticketPrinterAdapter = null;
            }
        }

        return $this->ticketPrinterAdapter;
    }
}
