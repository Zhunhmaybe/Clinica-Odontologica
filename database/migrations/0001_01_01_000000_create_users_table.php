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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('tel', 10)->nullable();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->integer('estado')->default(1)->comment('1=Activo, 2=Inactivo');
            $table->integer('failed_attempts')->default(0);
            $table->integer('is_locked')->default(0);
            $table->string('lock_code', 10)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('two_factor_enabled')->default(false)->after('estado');
            $table->string('two_factor_code', 6)->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
