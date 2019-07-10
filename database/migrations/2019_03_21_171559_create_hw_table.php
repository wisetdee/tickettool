<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHwTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hw', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });
        DB::table('hw')->insert(['name' => 'Compact Flash']);
        DB::table('hw')->insert(['name' => 'Power Supply']);
        DB::table('hw')->insert(['name' => 'CPU Board']);
        DB::table('hw')->insert(['name' => 'I/O Board']);
        DB::table('hw')->insert(['name' => 'Other']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hw');
    }
}
