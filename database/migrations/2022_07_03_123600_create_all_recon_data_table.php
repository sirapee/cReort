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
        Schema::create('all_recon_data', function (Blueprint $table) {
            $table->id('Id');
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
            $table->string('TranIdFinacle', 15)->nullable();
            $table->string('StanFinacle', 100)->nullable();
            $table->string('TranType', 100)->nullable();
            $table->dateTime('DateLocal')->nullable();
            $table->string('TerminalIdPostilion', 100)->nullable();
            $table->string('TerminalIdFinacle', 100)->nullable();
            $table->string('MessageType', 100)->nullable();
            $table->string('IssuerName', 100)->nullable();
            $table->string('AccountNumberFinacle', 15)->nullable();
            $table->string('AccountNameFinacle', 100)->nullable();
            $table->string('NarrationFinacle', 50)->nullable();
            $table->string('PanFinacle', 50)->nullable();
            $table->date('ValueDateFinacle')->nullable();
            $table->date('TranDateFinacle')->nullable();
            $table->string('TranCurrencyFinacle', 5)->nullable();
            $table->dateTime('RequestDate')->nullable();
            $table->date('EntryDate')->nullable();
            $table->string('ResponseCode', 100)->nullable();
            $table->string('TriggeredBy', 100)->nullable();
            $table->string('Status',20)->nullable();
            $table->boolean('IsReversed')->default(false);
            $table->integer('ReversalId')->nullable();
            $table->timestamps();

            $table->index('BatchNumber');
            $table->index(['BatchNumber', 'Status']);
            $table->index('SolId');
            $table->index(['StanPostilion', 'RequestDate']);
            $table->index(['StanFinacle', 'EntryDate']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('all_recon_data');
    }
};
