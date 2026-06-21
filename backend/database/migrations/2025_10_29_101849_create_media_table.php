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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable');
            $table->string('disk');
            $table->string('path');
            $table->string('public_id');
            $table->string('resource_type');
            $table->string('format');
            $table->string('quality')->nullable();
            $table->json('cloud_response')->nullable();
            $table->timestamps();

            $table->index('public_id');
            $table->index(['mediable_type', 'mediable_id', 'resource_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
