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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("voyage_id");
            $table->date("date");
            $table->unsignedBigInteger("sender_id")->constrained("consumers");
            $table->unsignedBigInteger("receiver_id")->constrained("consumers");
            // $table->unsignedBigInteger("object_nature_id")->constrained("object_natures");
            // $table->unsignedBigInteger("conditionning_id")->constrained("conditionnings");
            // $table->integer("quantity");
            // $table->double("weight");
            // $table->double("volume");
            // $table->integer("unit_price");
            $table->integer("total");
            // $table->unsignedBigInteger("unit_id");
            $table->json("objects");
            $table->string("bill_number");
            $table->unsignedBigInteger("manager_id");
            $table->integer("commission_fees");
            $table->string("observations")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
