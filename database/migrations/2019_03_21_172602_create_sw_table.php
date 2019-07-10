<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });
        DB::table('sw')->insert(['name' => 'Software_Problem_1']);
        DB::table('sw')->insert(['name' => 'Software_Problem_2']);
        DB::table('sw')->insert(['name' => 'Software_Problem_3']);
        DB::table('sw')->insert(['name' => 'Other']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw');
    }
}
