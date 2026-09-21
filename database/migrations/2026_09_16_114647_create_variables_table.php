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
    Schema::create('variables', function (Blueprint $table) {
        $table->id();
        $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
        $table->string('key'); // ej: "afecto_maria"
        $table->string('label')->nullable(); // ej: "Afecto de María", para mostrar en el editor
        $table->integer('default_value')->default(0);
        $table->timestamps();

        $table->unique(['novel_id', 'key']); // no puede haber dos variables con la misma key en la misma novel
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variables');
    }
};
