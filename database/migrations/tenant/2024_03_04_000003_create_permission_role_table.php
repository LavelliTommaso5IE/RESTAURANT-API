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
        Schema::create('permission_role', function (Blueprint $table) {
            // FK -> RUOLI.id
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            // FK -> PERMESSI.id
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');

            // PK composta (id_ruolo, id_permesso)
            $table->primary(['role_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_roles');
    }
};
