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
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });

        Schema::table('pets', function (Blueprint $table) {
            $table->index('status');
            $table->index('name');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index('status');
            $table->index('appointment_date');
            $table->index('service_type');
        });

        Schema::table('vaccinations', function (Blueprint $table) {
            $table->index('date_administered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            $table->dropIndex(['date_administered']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['appointment_date']);
            $table->dropIndex(['service_type']);
        });

        Schema::table('pets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });
    }
};
