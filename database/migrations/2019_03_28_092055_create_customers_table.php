<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('domain');
        });
        DB::table('customers')->insert(['name' => 'IBM'            , 'domain' => '*.ibm.com']);
        DB::table('customers')->insert(['name' => 'Customer_1'     , 'domain' => 'Customer_1.com']);
        DB::table('customers')->insert(['name' => 'Customer_2'     , 'domain' => 'Customer_2.com']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
