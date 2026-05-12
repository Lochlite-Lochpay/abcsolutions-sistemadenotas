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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('number')->nullable();
            $table->string('service_provider')->nullable();
            $table->string('service_provider_document')->nullable();
            $table->string('service_taker')->nullable();
            $table->string('service_taker_document')->nullable();
            $table->boolean('accountings')->nullable();
            $table->json('accountings_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
