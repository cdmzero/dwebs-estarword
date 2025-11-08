<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Planet extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'rotation_time', 'population', 'climate'];

    public function spaceships()
    {
        return $this->hasMany(Spaceship::class);
    }
}

