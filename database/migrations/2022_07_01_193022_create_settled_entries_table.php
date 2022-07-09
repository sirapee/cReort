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
        Schema::create('settled_entries', function (Blueprint $table) {
            $table->id();
            $table->string('BatchNumber', 100);
            $table->string('SolId', 5);
            $table->string('Region', 50)->nullable();
            $table->string('Pan', 100)->nullable();
            $table->string('AccountNumber', 100)->nullable();
            $table->string('RetrievalReferenceNumberPostilion', 100)->nullable();
            $table->string('RetrievalReferenceNumberFinacle', 100)->nullable();
            $table->string('StanPostilion', 100)->nullable();
            $table->double('AmountPostilion');
            $table->double('AmountFinacle')->nullable();
            $table->double('TranIdFinacle')->nullable();
            $table->string('StanFinacle', 100)->nullable();
            $table->string('TranType', 100)->nullable();
            $table->dateTime('DateLocal')->nullable();
            $table->string('TerminalIdPostilion', 100)->nullable();
            $table->string('TerminalIdFinacle', 100)->nullable();
            $table->string('MessageType', 100)->nullable();
            $table->string('IssuerName', 100)->nullable();
            $table->dateTime('RequestDate')->nullable();
            $table->date('EntryDate', )->nullable();
            $table->string('ResponseCode', 100)->nullable();
            $table->string('TriggeredBy', 100)->nullable();
            $table->char('Posted', 1)->default('N');
            $table->boolean('ApprovedForPosting')->default(false);
            $table->string('PostingResponseCode', 12)->nullable();
            $table->string('PostingResponseMessage', 12)->nullable();
            $table->string('Status', 50)->nullable();
            $table->string('PostingTranId', 12)->nullable();
            $table->date('PostingDate')->nullable();
            $table->timestamps();

            $table->index('BatchNumber');
            $table->index('SolId');
            $table->index(['StanPostilion', 'RequestDate']);
            $table->index(['StanFinacle', 'EntryDate']);
            $table->index(['PostingTranId', 'PostingDate']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settled_entries');
    }
};
