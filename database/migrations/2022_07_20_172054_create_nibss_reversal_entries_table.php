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
        Schema::create('nibss_posting_entries', function (Blueprint $table) {
            $table->id();
            $table->string('BatchNumber', 100);
            $table->string('SessionId', 50);
            $table->double('Amount');
            $table->dateTime('TransactionTime');
            $table->string('SourceInstitution', 50)->nullable();
            $table->string('SenderName', 100)->nullable();
            $table->string('Destination', 50)->nullable();
            $table->string('DestinationAccountName', 100)->nullable();
            $table->date('EntryDate')->nullable();
            $table->string('DestinationAccountNumber', 15)->nullable();
            $table->string('DebitAccountNumber', 20)->nullable();
            $table->string('CreditAccountNumber', 20)->nullable();
            $table->double('PostingAmount')->nullable();
            $table->string('Narration', 100)->nullable();
            $table->string('PostingStatus', 100)->nullable();
            $table->string('PostingTranId', 20)->nullable();
            $table->integer('ThreadId');
            $table->char('ApprovedForPosting', 1)->default('N');
            $table->char('Picked', 1)->default('N');
            $table->char('Posted', 1)->default('N');
            $table->date('PostingDate')->nullable();
            $table->string('Status', 100)->nullable();
            $table->string('TranType', 20)->default('Reversal');
            $table->string('RequestedBy', 100)->nullable();
            $table->timestamps();
            $table->index('BatchNumber');
            $table->index(['BatchNumber', 'TranType', 'EntryDate']);
            $table->index(['Posted', 'Picked', 'ThreadId', 'ApprovedForPosting'], 'NibssPendingProcessingIndex');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nibss_posting_entries');
    }
};
