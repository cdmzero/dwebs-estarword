<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SpaceshipPilot extends Pivot
{
    protected $table = 'spaceship_pilots';

    protected $fillable = [
        'spaceship_id',
        'pilot_id',
        'assigned_date',
        'exit_date',
    ];

    public $timestamps = true;

    public function spaceship()
    {
        return $this->belongsTo(Spaceship::class);
    }

    public function pilot()
    {
        return $this->belongsTo(Pilot::class);
    }
}

