<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoSlaReasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('no_sla_reason', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });
        DB::table('no_sla_reason')->insert(['name' => 'IBM Server Problem']);
        DB::table('no_sla_reason')->insert(['name' => 'IBM Netzwerk Problem']);
        DB::table('no_sla_reason')->insert(['name' => 'Fremdsystem']);
        DB::table('no_sla_reason')->insert(['name' => 'User Fehler']);
        DB::table('no_sla_reason')->insert(['name' => 'User Fragen']);
        DB::table('no_sla_reason')->insert(['name' => 'User Auftrag']);
        DB::table('no_sla_reason')->insert(['name' => 'End-of-Life (CPU-Board)']);
        DB::table('no_sla_reason')->insert(['name' => 'End-of-Life (Other Electronic)']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('no_sla_reason');
    }
}
