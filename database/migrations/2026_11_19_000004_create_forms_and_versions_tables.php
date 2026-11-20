<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('public_slug')->unique();
            $table->string('status', 20)->default('draft'); // draft, published, closed
            $table->dateTime('opens_at')->nullable();
            $table->dateTime('closes_at')->nullable();
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->json('settings_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['event_id', 'status']);
        });

        Schema::create('form_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('schema_json');
            $table->dateTime('published_at');
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('schema_hash', 64);
            $table->timestamps();

            $table->unique(['form_id', 'version_number']);
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->foreign('current_version_id')->references('id')->on('form_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });
        Schema::dropIfExists('form_versions');
        Schema::dropIfExists('forms');
    }
};
