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
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
    $table->foreignId('route_id')->constrained()->cascadeOnDelete();
    $table->foreignId('gps_device_id')->nullable()->constrained()->nullOnDelete();
    $table->string('plate_number')->unique();
    $table->enum('type', ['car', 'bus'])->default('bus');
    $table->enum('status', ['active', 'stopped', 'out_of_service'])->default('active');
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
