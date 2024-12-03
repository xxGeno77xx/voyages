<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voyages', function (Blueprint $table) {
            $table->id();
            $table->date("departure");
            $table->date("arrival");
            $table->unsignedBigInteger("driver_id")->constrained(table:"drivers");
            $table->unsignedBigInteger("ass_driver_id")->constrained(table:"drivers");

            $table->unsignedBigInteger("arrival_driver_id")->nullable()->constrained(table:"drivers");
            $table->unsignedBigInteger("arrival_ass_driver_id")->nullable()->constrained(table:"drivers");

            $table->unsignedBigInteger("vehicle_id")->constrained(table:"vehicles");
            $table->unsignedBigInteger("routing_id")->constrained(table:"routings");
            $table->string("mission");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voyages');
    }
};
