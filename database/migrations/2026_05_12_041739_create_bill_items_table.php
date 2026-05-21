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
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            // Menghubungkan item ini ke bill utamanya
            $table->foreignId('bill_id')->constrained()->onDelete('cascade'); 
            
            $table->string('item_name'); // Contoh: "Ayam Penyet"
            $table->decimal('price', 15, 2); // Harga per item
            $table->integer('quantity')->default(1); // Jumlah item yang dibeli
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};