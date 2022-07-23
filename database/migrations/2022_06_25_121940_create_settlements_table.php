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
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->string('BatchNumber', 100)->nullable();
            $table->string('Channel', 100)->nullable();
            $table->string('SessionId', 100);
            $table->string('TransactionType', 50)->nullable();
            $table->string('Response', 100)->nullable();
            $table->double('Amount');
            $table->dateTime('TransactionTime')->nullable();
            $table->string('SourceInstitution', 50)->nullable();
            $table->string('SenderName', 100)->nullable();
            $table->string('DestinationBank', 50)->nullable();
            $table->string('DestinationAccountName', 100)->nullable();
            $table->string('DestinationAccountNumber', 15)->nullable();
            $table->string('Narration', 100)->nullable();
            $table->string('PaymentReference', 100)->nullable();
            $table->enum('Direction', ['Inward', 'Outward'])->nullable();

            $table->string('SessionIdBank', 100)->nullable();
            $table->double('AmountBank')->nullable();
            $table->dateTime('TransactionTimeBank')->nullable();
            $table->string('SourceInstitutionBank', 50)->nullable();
            $table->string('SenderNameBank', 100)->nullable();
            $table->string('DestinationBankBank', 50)->nullable();
            $table->string('DestinationAccountNameBank', 100)->nullable();
            $table->string('DestinationAccountNumberBank', 15)->nullable();
            $table->string('Status', 100)->nullable();
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
        Schema::dropIfExists('settlements');
    }
};
