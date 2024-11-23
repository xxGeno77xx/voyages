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
            $table->unsignedBigInteger("bill_id")->constrained(table: "bills");
            $table->date("date");
            $table->unsignedBigInteger("expense_category_id")->constrained("expenses_categories");
            $table->unsignedBigInteger("supplier_id")->constrained("suppliers");
            $table->string("description");
            $table->integer("amount");
            $table->string("justification");
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
