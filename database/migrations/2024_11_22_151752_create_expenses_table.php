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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("voyage_id")->constrained(table: "bills");
            $table->date("date");
            $table->string("line");
            $table->unsignedBigInteger("expense_category_id")->constrained("expenses_categories");
            $table->unsignedBigInteger("supplier_id")->constrained("suppliers");
            $table->string("description")->nullable();
            $table->integer("amount");
            $table->string("justification")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
