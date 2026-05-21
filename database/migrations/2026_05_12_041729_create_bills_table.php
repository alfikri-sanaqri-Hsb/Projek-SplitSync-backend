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
            // Menghubungkan bill dengan user yang membuatnya (Alfikri)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            
            $table->string('title'); // Contoh: "Makan Siang di Kantin UB"
            $table->decimal('total_price', 15, 2)->default(0); // Total nominal tagihan
            $table->string('status')->default('pending'); // Status: pending/paid
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
