<?php

use App\Enums\UploadStateEnum;
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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('hours')->nullable();
            $table->string('minutes')->nullable();
            $table->string('seconds')->nullable();
            $table->string('quality')->nullable();
            $table->boolean('longitudinal')->default(false);
            $table->enum('processed', UploadStateEnum::all())->default(UploadStateEnum::PROCESSING->value);
            $table->uuid('processing_token')->nullable()->unique();
            $table->timestamps();

            $table->index('user_id');
            $table->index('processed');
            $table->index('created_at');
            $table->index(['user_id', 'processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
