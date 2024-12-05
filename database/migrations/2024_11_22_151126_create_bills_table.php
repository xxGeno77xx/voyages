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
            $table->string("line");
            $table->integer("objects_total")->nullable();
            $table->integer("other_amount")->nullable();
            $table->integer("total")->nullable();
            $table->integer("paid_amount")->nullable();
            $table->integer("remaining_amount")->nullable();
            $table->json("objects");
            $table->string("bill_number");
            $table->unsignedBigInteger("manager_id")->nullable();
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
