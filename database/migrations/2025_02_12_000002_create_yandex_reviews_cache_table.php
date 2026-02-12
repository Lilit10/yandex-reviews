<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_reviews_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('org_id')->index();
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->string('company_name')->nullable();
            $table->json('reviews')->nullable();
            $table->timestamp('cached_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'org_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yandex_reviews_cache');
    }
};
