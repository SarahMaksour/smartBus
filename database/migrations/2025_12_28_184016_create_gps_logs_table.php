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
       Schema::create('gps_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bus_id')->constrained()->cascadeOnDelete();
    $table->decimal('lat', 10, 7);
    $table->decimal('lng', 10, 7);
    $table->float('speed')->nullable();
    $table->float('heading')->nullable(); 
    $table->boolean('is_online')->default(true);
    $table->timestamp('recorded_at');
    $table->index(['bus_id', 'recorded_at']); 
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_logs');
    }
};
