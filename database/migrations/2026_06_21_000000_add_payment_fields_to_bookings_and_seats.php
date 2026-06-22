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
        Schema::table('seats', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('seat_number');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->after('status');
            $table->decimal('payment_amount', 10, 2)->default(0)->after('payment_status');
            $table->string('payment_method')->nullable()->after('payment_amount');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_amount', 'payment_method', 'payment_reference', 'paid_at']);
        });

        Schema::table('seats', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
