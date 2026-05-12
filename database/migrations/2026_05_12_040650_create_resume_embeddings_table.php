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
        Schema::create('resume_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            // Kolom vector ini yang nyimpen 'makna' CV dalam bentuk angka koordinat
            // Angka 1536 adalah standar dimensi vector OpenAI text-embedding-3-small / text-embedding-ada-002
            $table->vector('embedding', 1536)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_embeddings');
    }
};
