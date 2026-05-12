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
        Schema::table('companies', function (Blueprint $table) {                                             
            $table->text('client_id_accountings')->nullable();
            $table->text('client_key_accountings')->nullable();
            $table->text('client_audience_accountings')->nullable();
            $table->text('access_token_accountings')->nullable();
            $table->timestamp('expire_token_accountings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('token_qive');
        });
    }
};
