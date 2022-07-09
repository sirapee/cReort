<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sol_regions', function (Blueprint $table) {
            $table->id();
            $table->string('SolId', 5);
            $table->string('EcName', 50)->nullable();
            $table->string('EcAddress', 50)->nullable();
            $table->string('State', 50)->nullable();
            $table->string('Region', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sol_regions');
    }
};
