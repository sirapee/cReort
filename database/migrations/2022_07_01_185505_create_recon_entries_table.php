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
        Schema::create('reconciled_entries', function (Blueprint $table) {
            $table->id();

            $table->string('BatchNumber', 100);
            $table->string('SolId', 5);
            $table->string('Region', 50)->nullable();
            $table->string('Pan', 100)->nullable();
            $table->string('AccountNumber', 100)->nullable();
            $table->string('RetrievalReferenceNumberPostilion', 100)->nullable();
            $table->string('RetrievalReferenceNumberFinacle', 100)->nullable();
            $table->string('StanPostilion', 100)->nullable();
            $table->string('StanFinacle', 100)->nullable();
            $table->string('TranType', 100)->nullable();
            $table->dateTime('DateLocal')->nullable();
            $table->double('AmountPostilion');
            $table->double('AmountFinacle')->nullable();
            $table->string('TranIdFinacle', '15')->nullable();
            $table->string('TerminalIdPostilion', 100)->nullable();
            $table->string('TerminalIdFinacle', 100)->nullable();
            $table->string('AccountNumberFinacle', 15)->nullable();
            $table->string('AccountNameFinacle', 100)->nullable();
            $table->string('NarrationFinacle', 50)->nullable();
            $table->string('TranCurrencyFinacle', 5)->nullable();
            $table->string('MessageType', 100)->nullable();
            $table->string('IssuerName', 100)->nullable();
            $table->dateTime('RequestDate')->nullable();
            $table->date('EntryDate')->nullable();
            $table->string('Status', 50)->nullable();
            $table->string('ResponseCode', 100)->nullable();
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
            $table->string('PostingResponseCode', 12)->nullable();
            $table->string('PostingResponseMessage', 12)->nullable();
            $table->string('PostingTranId', 12)->nullable();
            $table->date('PostingDate')->nullable();
            $table->timestamps();

            $table->index('BatchNumber');
            $table->index('SolId');
            $table->index(['StanPostilion', 'RequestDate']);
            $table->index(['StanFinacle', 'EntryDate']);
            $table->index(['PostingTranId']);
            $table->index(['PostingTranId', 'PostingDate']);
            $table->index(['Posted', 'Picked', 'ThreadId', 'ApprovedForPosting']);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reconciled_entries');
    }
};
