<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePassendoConversionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('passendo_conversions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableMorphs('parent');
            $table->string('tracking_id')->unique();
            $table->integer('cpa');
            $table->integer('total_requests')->default(0);
            $table->boolean('success')->nullable()->index();
            $table->integer('status')->nullable();
            $table->text('exception')->nullable();
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
        Schema::dropIfExists('passendo_conversions');
    }
}
