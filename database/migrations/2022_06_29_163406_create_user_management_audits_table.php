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
        Schema::create('user_management_audit', function (Blueprint $table) {
            $table->id();
            $table->string('function_code', 1);
            $table->string('user_id');
            $table->string('modified_field_data', 999);
            $table->string('inputter', 50);
            $table->string('authorizer', 50)->nullable();
            $table->string('approved_or_rejected',1)->default('N');
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
        Schema::dropIfExists('user_management_audit');
    }
};
