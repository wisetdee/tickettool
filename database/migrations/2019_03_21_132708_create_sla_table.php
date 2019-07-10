<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sla', function (Blueprint $table) {
            $table->string('id');
            $table->string('domain');
            $table->timestamps();
        });

        DB::table('sla')->insert(['id' => 'IBM' , 'domain' => 'ch.ibm.com']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sla');
    }
}
