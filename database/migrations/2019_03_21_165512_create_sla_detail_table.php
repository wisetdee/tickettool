<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlaDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sla_detail', function (Blueprint $table) {
            $table->string('sla_id');
            $table->integer('ticket_id');
            $table->integer('failure_class_id')->nullable();
            $table->string('no_sla_reason_id')->nullable();
            $table->string('hw_id')->nullable();
            $table->string('sw_id')->nullable();
            $table->string('location_id')->nullable();
            $table->boolean('is_warning_notified')->nullable();
            $table->boolean('is_overdue_notified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sla_detail');
    }
}
