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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_price');

            $table->decimal('total_before_discount', 8, 2)->after('status');
            $table->decimal('discount', 8, 2)->default(0)->after('total_before_discount');
            $table->decimal('total_after_discount', 8, 2)->after('discount');
            $table->string('coupon_code')->nullable()->after('total_after_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_before_discount');
            $table->dropColumn('discount');
            $table->dropColumn('total_after_discount');
            $table->dropColumn('coupon_code');

            $table->decimal('total_price', 8, 2);
        });
    }
};
