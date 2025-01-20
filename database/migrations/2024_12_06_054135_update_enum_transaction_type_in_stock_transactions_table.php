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
        Schema::table('stock_transactions', function (Blueprint $table) {
            DB::statement("ALTER TABLE `stock_transactions` MODIFY COLUMN `transaction_type` ENUM('Added', 'Deducted', 'Sold', 'Deleted') AFTER `product_id`");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            // DB::statement("ALTER TABLE `stock_transactions` MODIFY COLUMN `transaction_type` ENUM('Added', 'Deducted') AFTER `product_id`");

        });
    }
};
