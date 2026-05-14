<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('google_sub')->nullable()->unique();
            $table->string('avatar_url')->nullable();
            $table->string('role', 20)->default('member');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // PostgreSQL CHECK constraint: role must be 'admin' or 'member'
        // SQL CHECK constraints must be added via raw SQL because Laravel's schema builder
        // does not have a built-in method for CHECK constraints.
        // Keep this in sync with the ROLES array in app/Models/User.php
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'member'))");

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
