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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('seats');
            $table->enum('status', ['free', 'occupied', 'reserved', 'cleaning'])->default('free');
            $table->string('pin', 64)->unique()->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('tables')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
