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
        Schema::create('cashings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("bill_id")->constrained("bills");
            $table->unsignedBigInteger("cashing_nature_id")->constrained("cashing_natures");
            $table->integer("percieved_amount");
            $table->string("observations");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashings');
    }
};
