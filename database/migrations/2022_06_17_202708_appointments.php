<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Appointments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->string('appointment_date');
            $table->string('u_first_name');
            $table->string('u_last_name');
            $table->string('u_email')->nullable();
            $table->string('u_dob')->nullable();
            $table->string('u_address');
            $table->string('u_phone_number');
            $table->longText('comment');
            $table->enum('status', ['0','1'])->default('1')->comment('0=inactive');
            $table->timestamps(); 
            
            $table->foreign('service_id')
            ->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
