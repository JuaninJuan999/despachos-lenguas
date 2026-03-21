<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'logged_in_at',
        'logged_out_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_in_at'  => 'datetime',
            'logged_out_at' => 'datetime',
            'last_seen_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Duración real de la sesión en minutos.
     * Si no hay logout, usa last_seen_at o now() si la sesión está activa.
     */
    public function getDurationMinutesAttribute(): int
    {
        if ($this->logged_out_at) {
            return max(0, (int) $this->logged_in_at->diffInMinutes($this->logged_out_at));
        }
        if ($this->last_seen_at) {
            return max(0, (int) $this->logged_in_at->diffInMinutes($this->last_seen_at));
        }
        // Sesión activa sin heartbeat aún: calcular desde login hasta ahora
        if ($this->is_active) {
            return max(0, (int) $this->logged_in_at->diffInMinutes(now()));
        }
        return 0;
    }

    /**
     * Indica si la sesión sigue activa.
     */
    public function getIsActiveAttribute(): bool
    {
        if (! is_null($this->logged_out_at)) {
            return false;
        }
        // Tiene heartbeat reciente
        if ($this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) <= 10) {
            return true;
        }
        // Recién inició sesión (sin heartbeat aún, dentro de los últimos 10 min)
        if (is_null($this->last_seen_at) && $this->logged_in_at->diffInMinutes(now()) <= 10) {
            return true;
        }
        return false;
    }
}
