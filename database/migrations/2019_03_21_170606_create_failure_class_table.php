<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailureClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failure_class', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('solution_hour');
            $table->integer('warning_hour');
            $table->timestamps();
        });
        DB::table('failure_class')->insert(['id' => '1' , 'solution_hour' => '8'    ,'warning_hour' => '6']);
        DB::table('failure_class')->insert(['id' => '2' , 'solution_hour' => '24'   ,'warning_hour' => '20']);
        DB::table('failure_class')->insert(['id' => '3' , 'solution_hour' => '120'  ,'warning_hour' => '48']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failure_class');
    }
}
