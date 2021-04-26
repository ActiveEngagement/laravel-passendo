<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePassendoRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('passendo_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('click_id')->unsigned();
            $table->foreign('click_id')->references('id')->on('passendo_clicks')->onDelete('cascade')->onUpdate('cascade');
            $table->boolean('success')->nullable()->index();
            $table->integer('status')->nullable()->index();
            $table->text('exception')->nullable();
            $table->dateTime('sent_at')->nullable()->index();
            $table->dateTime('received_at')->nullable()->index();
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
        Schema::dropIfExists('passendo_requests');
    }
}
