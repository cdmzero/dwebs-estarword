
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpaceshipsTable extends Migration
{
    public function up()
    {
        Schema::create('spaceships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planet_id')->constrained('planets')->onDelete('cascade');
            $table->string('name')->unique();
            $table->string('model')->nullable();
            $table->integer('crew')->default(0);
            $table->integer('passengers')->default(0);
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('spaceships');
    }
}

