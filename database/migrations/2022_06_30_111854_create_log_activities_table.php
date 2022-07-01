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
        Schema::create('log_activities', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 100);
            $table->string('url', 100)->nullable();
            $table->string('method', 20)->nullable();
            $table->string('ip', 100)->nullable();
            $table->string('agent', 250)->nullable();
            $table->string('user_id');
            $table->text('details')->nullable();
            $table->string('name', 250)->nullable();
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
        Schema::dropIfExists('log_activities');
    }
};
