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
    Schema::create('scenes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
        $table->string('title')->nullable();
        $table->string('background_image')->nullable();
        $table->string('background_music')->nullable();
        $table->integer('order')->default(0);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scenes');
    }
};
