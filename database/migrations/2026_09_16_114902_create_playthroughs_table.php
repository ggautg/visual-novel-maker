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
    Schema::create('playthroughs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
        $table->foreignId('current_node_id')->nullable()->constrained('dialogue_nodes')->nullOnDelete();
        $table->timestamps();

        $table->unique(['user_id', 'novel_id']); // un jugador tiene un solo progreso activo por novel
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playthroughs');
    }
};
