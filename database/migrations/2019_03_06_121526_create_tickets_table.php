<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subject')->nullable();
            $table->mediumText('content')->nullable();
            $table->string('owner');
            $table->string('owner_mail');
            $table->string('cc')->nullable();
            $table->string('sla')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('status_id')->default(1)->nullable();
            $table->dateTime('reacted_at')->nullable();
            $table->timestamp('closed_at')->nullable();            
            $table->timestamps();
        });
        // DB::update("ALTER TABLE tickets AUTO_INCREMENT = 20000;");   //id = ticket No. can begin with 20001 , not tested
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
