<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripStatus extends Model
{
    use HasFactory;

    protected $table = 'trip_status';

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relacionamento com TripRequest
    public function tripRequests()
    {
        return $this->hasMany(TripRequest::class, 'status_id');
    }

    // Scopes para facilitar consultas
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Métodos estáticos para os status padrão
    public static function pending()
    {
        return self::where('name', 'solicitado')->first();
    }

    public static function approved()
    {
        return self::where('name', 'aprovado')->first();
    }

    public static function cancelled()
    {
        return self::where('name', 'cancelado')->first();
    }

    // Método para verificar se o status pode ser alterado para outro
    public function canChangeTo($newStatusName)
    {
        $transitions = [
            'solicitado' => ['aprovado', 'cancelado'],
            'aprovado' => [], // Aprovado não pode ser alterado
            'cancelado' => [] // Cancelado não pode ser alterado
        ];

        return in_array($newStatusName, $transitions[$this->name] ?? []);
    }

    // Verifica se é um status final (não pode ser alterado)
    public function isFinal()
    {
        return in_array($this->name, ['aprovado', 'cancelado']);
    }
}
