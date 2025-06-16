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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string("number")->unique();
            $table->enum('status', ['pending', 'processing', 'complete', 'delivering', 'completed', 'canceled'])->default('pending');
            $table->decimal('total_price', 8, 2);
            $table->timestamps();
            $table->foreign('customer_id')->references('id')
                ->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
