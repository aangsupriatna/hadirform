<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_attendee_lookups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('identifier', 100)->nullable(); // NIP, NIK, No Tiket
            $table->string('name', 200);
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('institution', 200)->nullable();
            $table->string('position', 150)->nullable();
            $table->json('custom_data_json')->nullable();
            $table->boolean('is_attended')->default(false);
            $table->dateTime('attended_at')->nullable();
            $table->foreignId('response_id')->nullable()->constrained('responses')->nullOnDelete();
            $table->timestamps();

            $table->index(['form_id', 'name']);
            $table->index(['form_id', 'identifier']);
            $table->index(['form_id', 'phone']);
            $table->index(['form_id', 'email']);
            $table->index(['form_id', 'is_attended']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_attendee_lookups');
    }
};
