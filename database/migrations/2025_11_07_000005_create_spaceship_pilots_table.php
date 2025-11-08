
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpaceshipPilotsTable extends Migration
{
    public function up()
    {
        Schema::create('spaceship_pilots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spaceship_id')->constrained('spaceships')->onDelete('cascade');
            $table->foreignId('pilot_id')->constrained('pilots')->onDelete('cascade');
            $table->date('assigned_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->timestamps();
            $table->unique(['spaceship_id', 'pilot_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('spaceship_pilots');
    }
}

