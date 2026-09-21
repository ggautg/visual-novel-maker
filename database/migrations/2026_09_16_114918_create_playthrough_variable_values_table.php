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
    Schema::create('playthrough_variable_values', function (Blueprint $table) {
        $table->id();
        $table->foreignId('playthrough_id')->constrained()->cascadeOnDelete();
        $table->foreignId('variable_id')->constrained()->cascadeOnDelete();
        $table->integer('value')->default(0);
        $table->timestamps();

        $table->unique(['playthrough_id', 'variable_id']); // un valor por variable, por playthrough
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playthrough_variable_values');
    }
};
