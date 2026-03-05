<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();

            // Colleghiamo il token all'utente (se l'utente viene cancellato, via i suoi token)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('token', 64)->unique(); // Il refresh token vero e proprio
            $table->timestamp('expires_at'); // Quando scade?

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
