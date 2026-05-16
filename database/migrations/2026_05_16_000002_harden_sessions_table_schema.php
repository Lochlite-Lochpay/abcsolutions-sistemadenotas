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
        if (! Schema::hasTable('sessions')) {
            return;
        }

        Schema::table('sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('sessions', 'id')) {
                $table->string('id')->primary();
            }

            if (! Schema::hasColumn('sessions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->index();
            }

            if (! Schema::hasColumn('sessions', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }

            if (! Schema::hasColumn('sessions', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }

            if (! Schema::hasColumn('sessions', 'payload')) {
                $table->longText('payload');
            }

            if (! Schema::hasColumn('sessions', 'last_activity')) {
                $table->integer('last_activity')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intencionalmente vazio para não remover/alterar tabela de sessão em produção.
    }
};

