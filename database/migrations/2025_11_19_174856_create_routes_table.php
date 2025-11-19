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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
    $table->string('code')->unique();
    $table->string('name');
    $table->boolean('is_active')->default(true);
    $table->decimal('start_lat', 10, 7)->nullable();
    $table->decimal('start_lng', 10, 7)->nullable();
    $table->decimal('end_lat', 10, 7)->nullable();
    $table->decimal('end_lng', 10, 7)->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
