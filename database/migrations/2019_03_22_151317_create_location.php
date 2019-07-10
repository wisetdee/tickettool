<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('name_long');
            $table->timestamps();
        });
        DB::table('location')->insert(['name' => 'IBMVK', 'name_long' => 'Vulkanstrasse']);
        DB::table('location')->insert(['name' => 'IBMEM', 'name_long' => 'Eugene Marziano']);
        DB::table('location')->insert(['name' => 'IBMBY', 'name_long' => 'Bussigny']);
        DB::table('location')->insert(['name' => 'IBMBE', 'name_long' => 'Bern']);
        DB::table('location')->insert(['name' => 'IBMLU', 'name_long' => 'Lugano']);
        DB::table('location')->insert(['name' => 'All locations', 'name_long' => 'All IBM Locations']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location');
    }
}
