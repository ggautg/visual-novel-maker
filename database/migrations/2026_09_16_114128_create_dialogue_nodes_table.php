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
        Schema::create('dialogue_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('character_expression_id')->nullable()->constrained()->nullOnDelete();
            $table->text('text');
            $table->foreignId('next_node_id')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('dialogue_nodes', function (Blueprint $table) {
            $table->foreign('next_node_id')->references('id')->on('dialogue_nodes')->nullOnDelete();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialogue_nodes');
    }
};
