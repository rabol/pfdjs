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
        Schema::create('overlay_positions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            // $table->foreignId('user_doc_id')->constrained('user_docs')->default(0);
            $table->foreignId('user_doc_id')->default(0);
            $table->decimal('top', 10, 4);
            $table->decimal('left', 10, 4);
            $table->decimal('width', 10, 4);
            $table->decimal('height', 10, 4);
            $table->string('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overlay_positions');
    }
};
