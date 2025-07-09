<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'destination',
        'departure_date',
        'return_date',
        'status_id'
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
    ];

    protected $with = ['status']; // Sempre carregar o status

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TripStatus::class, 'status_id');
    }

    // Accessor para manter compatibilidade com código existente
    public function getStatusNameAttribute()
    {
        return $this->status?->name;
    }

    // Método para verificar se pode alterar status
    public function canChangeStatusTo($newStatusName)
    {
        return $this->status?->canChangeTo($newStatusName) ?? false;
    }

    // Scopes para filtros
    public function scopeWithStatus($query, $statusName)
    {
        return $query->whereHas('status', function ($q) use ($statusName) {
            $q->where('name', $statusName);
        });
    }

    public function scopeWithDestination($query, $destination)
    {
        return $query->where('destination', 'like', '%' . $destination . '%');
    }

    public function scopeWithDateRange($query, $from, $to)
    {
        return $query->whereBetween('departure_date', [$from, $to]);
    }
}
