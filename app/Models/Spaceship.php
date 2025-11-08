<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Spaceship extends Model
{
    use HasFactory;

    protected $fillable = ['planet_id', 'name', 'model', 'crew', 'passengers', 'type'];

    public function planet()
    {
        return $this->belongsTo(Planet::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function pilots()
    {
        return $this->belongsToMany(Pilot::class, 'spaceship_pilots')
                    ->using(SpaceshipPilot::class)
                    ->withPivot('assigned_date', 'exit_date')
                    ->withTimestamps();
    }
}

