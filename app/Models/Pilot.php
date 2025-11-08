<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pilot extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'height', 'birth_year', 'gender', 'image_url'];

    public function spaceships()
    {
        return $this->belongsToMany(Spaceship::class, 'spaceship_pilots')
                    ->using(SpaceshipPilot::class)
                    ->withPivot('assigned_date', 'exit_date')
                    ->withTimestamps();
    }

    public function getImageUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return asset('images/default_pilot.png');
    }
}
