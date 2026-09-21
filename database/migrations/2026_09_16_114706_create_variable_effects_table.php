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
    Schema::create('variable_effects', function (Blueprint $table) {
        $table->id();
        $table->foreignId('choice_id')->constrained()->cascadeOnDelete();
        $table->foreignId('variable_id')->constrained()->cascadeOnDelete();
        $table->enum('operation', ['set', 'increment', 'decrement']);
        $table->integer('value');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variable_effects');
    }
};
