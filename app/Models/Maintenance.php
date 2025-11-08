<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = ['spaceship_id', 'date_completed', 'date_planned', 'description', 'cost'];

    public function spaceship()
    {
        return $this->belongsTo(Spaceship::class);
    }

    /**
     * Calcula el coste del mantenimiento en función de los días transcurridos.
     * Base 100 €/día, solo si está completado.
     */
    public function calculateDurationCost(int $dailyRate = 100): int
    {
        if (! $this->date_completed || ! $this->date_planned) {
            return 0;
        }

        $start = Carbon::parse($this->date_planned);
        $end = Carbon::parse($this->date_completed);

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        $days = $start->diffInDays($end);

        return $days * $dailyRate;
    }
}

