<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{   
    public function up(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->string('participant_name')->nullable()->after('item_name');
            $table->enum('payment_status', ['pending', 'completed'])->default('pending')->after('price');
        });
    }
};
