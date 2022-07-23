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
        Schema::create('finacle_data', function (Blueprint $table) {
            $table->id();
            $table->string('BatchNumber', 100);
            $table->string('SolId', 5);
            $table->string('Region', 50)->nullable();
            $table->string('PanFinacle', 50)->nullable();
            $table->string('RetrievalReferenceNumberFinacle', 100)->nullable();
            $table->string('StanFinacle', 100)->nullable();
            $table->double('AmountFinacle')->nullable();
            $table->string('TranIdFinacle',50)->nullable();
            $table->string('TerminalIdFinacle', 100)->nullable();
            $table->string('AccountNumberFinacle', 15)->nullable();
            $table->string('AccountNameFinacle', 100)->nullable();
            $table->string('NarrationFinacle', 50)->nullable();
            $table->string('TranCurrencyFinacle', 5)->nullable();
            $table->char('TerminalType', 1)->nullable();
            $table->date('ValueDateFinacle')->nullable();
            $table->date('TranDateFinacle')->nullable();
            $table->date('EntryDate')->nullable();
            $table->string('TriggeredBy', 100)->nullable();
            $table->string('DebitAccount', 15)->nullable();
            $table->string('CreditAccount', 15)->nullable();
            $table->double('PostingAmount')->nullable();
            $table->string('PostingNarration', 50)->nullable();
            $table->string('PostingStan', 12)->nullable();
            $table->date('ValueDate')->nullable();
            $table->integer('ThreadId')->default(1);
            $table->integer('Priority')->default(2);
            $table->char('Picked', 1)->default('N');
            $table->char('Posted', 1)->default('N');
            $table->boolean('ApprovedForPosting')->default(false);
            $table->string('ApprovedForPostingBy', 50)->nullable();
            $table->date('ApprovedDate')->nullable();
            $table->string('PostingResponseCode', 12)->nullable();
            $table->string('PostingResponseMessage', 12)->nullable();
            $table->string('PostingTranId', 12)->nullable();
            $table->string('Status', 50)->nullable();
            $table->boolean('Reversed')->default(false);
            $table->date('PostingDate')->nullable();

            $table->timestamps();


            $table->index('BatchNumber');
            $table->index('SolId');
            $table->index(['PostingTranId', 'PostingDate']);
            $table->index(['StanFinacle', 'EntryDate']);
            $table->index(['Reversed', 'BatchNumber']);
            $table->index(['BatchNumber','ValueDateFinacle', 'TranDateFinacle', 'PanFinacle', 'TerminalIdFinacle', 'RetrievalReferenceNumberFinacle', 'StanFinacle', 'AmountFinacle'], 'ReversalProcessingIndex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('finacle_data');
    }
};
