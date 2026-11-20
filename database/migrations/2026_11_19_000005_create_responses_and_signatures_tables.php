<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('form_version_id')->constrained('form_versions')->cascadeOnDelete();
            $table->string('receipt_code', 32)->unique();
            $table->string('status', 20)->default('submitted'); // submitted, verified, rejected, voided
            $table->string('source', 30)->default('public'); // public, operator
            $table->dateTime('submitted_at');
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->string('dedupe_key', 128)->nullable();
            $table->string('consent_version', 20)->nullable();
            $table->dateTime('consented_at')->nullable();
            $table->string('idempotency_token', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['form_id', 'submitted_at']);
            $table->index(['event_id', 'status']);
            $table->index(['organization_id', 'submitted_at']);
            $table->index(['form_id', 'dedupe_key']);
            $table->index(['form_id', 'idempotency_token']);
        });

        Schema::create('response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('responses')->cascadeOnDelete();
            $table->string('field_key', 64);
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->unique(['response_id', 'field_key']);
        });

        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('response_id')->constrained('responses')->cascadeOnDelete();
            $table->enum('signer_type', ['participant', 'operator', 'admin'])->default('participant');
            $table->foreignId('signed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path');
            $table->string('mime_type', 50)->default('image/png');
            $table->unsignedInteger('file_size')->default(0);
            $table->string('sha256_hash', 64);
            $table->dateTime('signed_at');
            $table->string('consent_version', 20)->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['response_id', 'signer_type']);
        });

        Schema::create('response_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('responses')->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision', 20); // verified, rejected, voided
            $table->text('reason')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_verifications');
        Schema::dropIfExists('signatures');
        Schema::dropIfExists('response_answers');
        Schema::dropIfExists('responses');
    }
};
