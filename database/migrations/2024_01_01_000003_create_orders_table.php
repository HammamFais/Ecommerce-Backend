<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_price', 15, 2);
            $table->enum('status', ['pending', 'paid', 'shipped', 'done', 'cancelled'])->default('pending');
            $table->text('shipping_address');
            $table->string('shipping_city');
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->string('courier')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
