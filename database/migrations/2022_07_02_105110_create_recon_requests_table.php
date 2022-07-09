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
        Schema::create('recon_requests', function (Blueprint $table) {
            $table->id();
            $table->string('BatchNumber', 100);
            $table->string('Coverage', 100);
            $table->string('SolId', 5)->nullable();
            $table->string('Region', 50)->nullable();
            $table->date('TranDate');
            $table->string('RequestedBy', 50);
            $table->char('Picked', 1)->default('N');
            $table->char('Processed', 1)->default('N');
            $table->timestamp('ProcessedDate')->nullable();
            $table->timestamps();

            $table->index('BatchNumber');
            $table->index(['BatchNumber', 'TranDate']);
            $table->index(['Picked', 'Processed']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recon_requests');
    }
};
